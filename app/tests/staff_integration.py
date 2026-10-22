#!/usr/bin/env python3
"""Real staff invitation, capability isolation, recovery and upgrade journeys.
All email/gateway data are disposable fixtures, not external deliveries/payments.
"""
from __future__ import annotations
import hashlib
import json
import os
import re
import subprocess
import tempfile
from pathlib import Path
from types import SimpleNamespace
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import token, save_content, upload, png_bytes
from communication_integration import enable_fixture_smtp
from commerce_integration import seed_catalog, snapshot as commerce_snapshot
from payment_integration import configure, prepare, fixture as payment_fixture, order

PASSWORD = 'Synthetic-Staff-Password-2048!'


def fixture(s, action, values=None):
    env = {**s.env, 'SPIRIT_FIXTURE_ROOT': str(s.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(s.docroot)}
    r = subprocess.run(['php', str(ROOT/'app/tests/staff_fixture.php'), action], input=json.dumps(values or {}), env=env, capture_output=True, text=True, timeout=25)
    if r.returncode:
        raise AssertionError('Staff fixture failed: '+r.stderr)
    return json.loads(r.stdout)


def snapshot(s):
    return fixture(s, 'snapshot')


def mail_token(s, email, kind='invite'):
    body = fixture(s, 'mail', {'email': email, 'kind': kind})['html']
    m = re.search(r'complete.php\?token=([a-f0-9]{64})', body)
    if not m:
        raise AssertionError('Expected encrypted staff link for '+email)
    return m.group(1)


def invite(s, email, role='content_editor'):
    response = s.client.form('admin/staff/', {'action':'invite','email':email,'name':'Synthetic '+role,'role':role,'owner_password':s.password})
    expect(response[0] == 200 and 'Invitation queued' in response[1], 'Owner issues email-first '+role+' invitation')
    return mail_token(s, email)


def complete(s, email, raw, role='content_editor'):
    client = Client(s.base); path = 'admin/staff/complete.php?token='+raw
    before = len(snapshot(s)['users']); status, body, headers, _ = client.request(path)
    expect(status == 200 and email in body and len(snapshot(s)['users']) == before, 'Scanner GET cannot create a staff account')
    expect('no-store' in headers.get('Cache-Control','') and headers.get('Referrer-Policy') == 'no-referrer', 'Staff token page is private and prevents referrer leakage')
    expect(client.request(path, {'csrf':'bad','token':raw,'password':PASSWORD,'password_confirm':PASSWORD})[0] == 403, 'Staff completion rejects invalid CSRF')
    response = client.form(path, {'token':raw,'password':PASSWORD,'password_confirm':PASSWORD,'role':'owner','email':'attacker@example.test'})
    expect('Password saved.' in response[1], 'Email holder completes staff activation')
    user = next(u for u in snapshot(s)['users'] if u['email'] == email)
    expect(user['role'] == role and len(snapshot(s)['users']) == before+1, 'Completion cannot replace assigned role or email through POST tampering')
    expect(client.request('admin/')[3].endswith('/admin/login.php'), 'Activation does not silently create an administrator session')
    client.request(path, {'csrf':token(client.request('admin/login.php')[1]),'token':raw,'password':PASSWORD,'password_confirm':PASSWORD})
    expect(len(snapshot(s)['users']) == before+1, 'Staff invitation is single-use')
    login(client, email)
    return client, user


def login(client, email, password=PASSWORD):
    response = client.form('admin/login.php', {'email':email,'password':password})
    expect(response[3].endswith('/admin/'), 'Staff signs in through the normal protected login')


def grant(s, user, role=None, active='1', version=None):
    return s.client.form('admin/staff/', {'action':'update','id':str(user['id']),'version':str(user['session_version'] if version is None else version),'role':role or user['role'],'active':active,'owner_password':s.password})


def user_row(s, email):
    return next(u for u in snapshot(s)['users'] if u['email'] == email)


def access_and_recovery(s):
    enable_fixture_smtp(s)
    values = {'action':'invite','name':'Synthetic editor','email':'editor@example.test','role':'content_editor','owner_password':s.password}
    expect(s.client.request('admin/staff/', {**values,'csrf':'invalid'})[0] == 403, 'Staff invitation requires CSRF')
    s.client.form('admin/staff/', {**values,'owner_password':'not-the-owner-password'})
    expect(snapshot(s)['tokens'] == [], 'Owner password confirmation is enforced before access changes')
    raw = invite(s, 'editor@example.test'); snap = snapshot(s)
    expect(snap['tokens'][0]['token_hash'] == hashlib.sha256(raw.encode()).hexdigest() and raw not in json.dumps(snap), 'Only a hash and encrypted mail body persist the invitation secret')
    editor, editor_row = complete(s, 'editor@example.test', raw)
    expect('admin/staff/' not in editor.request('admin/')[1] and 'admin/orders/payments.php' not in editor.request('admin/')[1], 'Editor navigation omits owner and payment settings')
    for route in ['admin/content/?kind=page','admin/media/','admin/account.php']:
        expect(editor.request(route)[0] == 200, 'Editor has only its configured capability: '+route)
    shadow = SimpleNamespace(client=editor, base=s.base)
    page_id = save_content(shadow, 'page', 'Staff published fixture', {'body':'Editor-authored fixture content'}, slug='staff-fixture')
    expect(page_id > 0 and 'Staff published fixture' in Client(s.base).request('pages/?slug=staff-fixture')[1], 'Real content editor can publish a page')
    image_id, status, _ = upload(shadow, 'staff-private.png', 'image/png', png_bytes())
    expect(status == 200 and image_id > 0 and editor.request(f'media/file.php?id={image_id}')[0] == 200 and Client(s.base).request(f'media/file.php?id={image_id}')[0] == 404, 'Staff media capability retains private upload access without public leakage')
    for route in ['admin/staff/','admin/activity/','admin/communication/','admin/mail/','admin/bookings/','admin/subscribers/','admin/settings.php','admin/installation.php','admin/updates.php','admin/shop/','admin/orders/']:
        expect(editor.request(route)[0] == 403, 'Editor cannot access unrelated owner data: '+route)
    before = len(snapshot(s)['tokens'])
    s.client.form('admin/staff/', {**values,'email':s.owner,'role':'owner'})
    expect(len(snapshot(s)['tokens']) == before, 'An invitation cannot overwrite an existing owner email')
    store_raw = invite(s, 'store@example.test', 'store_manager'); manager, manager_row = complete(s, 'store@example.test', store_raw, 'store_manager')
    for route in ['admin/shop/','admin/shop/categories.php','admin/media/','admin/orders/']:
        expect(manager.request(route)[0] == 200, 'Store manager can use catalog and fulfillment: '+route)
    for route in ['admin/staff/','admin/activity/','admin/bookings/','admin/content/?kind=page','admin/communication/','admin/subscribers/','admin/orders/payments.php','admin/orders/policy.php','admin/orders/webhooks.php','admin/shop/customers.php','admin/shop/settings.php']:
        expect(manager.request(route)[0] == 403, 'Store manager cannot acquire owner privileges: '+route)
    expect(editor.request('admin/staff/',{**values,'csrf':token(editor.request('admin/account.php')[1])})[0] == 403, 'Direct POST cannot bypass a hidden staff navigation item')
    expect(manager.request('admin/orders/',{'csrf':token(manager.request('admin/account.php')[1]),'action':'process'})[0] == 403, 'Store manager cannot invoke owner order-maintenance actions')
    grant(s, editor_row, active='0')
    expect(editor.request('admin/content/?kind=page')[3].endswith('/admin/login.php'), 'Suspension revokes existing staff sessions immediately')
    grant(s, editor_row, active='1')
    expect(int(user_row(s,'editor@example.test')['active']) == 0, 'Stale role/access edit cannot reactivate a suspended user')
    grant(s, user_row(s,'editor@example.test'), active='1'); login(editor,'editor@example.test')
    second = Client(s.base); login(second,'editor@example.test')
    response = editor.form('admin/account.php', {'current_password':PASSWORD,'new_password':PASSWORD+'X','confirm_password':PASSWORD+'X'})
    expect(response[0] == 200 and second.request('admin/')[3].endswith('/admin/login.php'), 'Staff password change revokes other sessions without granting owner access')
    recover = Client(s.base); path = 'admin/staff/recover.php'
    unknown = recover.form(path, {'email':'unknown@example.test'})
    known = recover.form(path, {'email':'editor@example.test'})
    expect('If this is an active staff address' in unknown[1] and 'If this is an active staff address' in known[1], 'Recovery uses the same public response for existing and unknown accounts')
    reset = mail_token(s,'editor@example.test','reset'); reset_path='admin/staff/complete.php?token='+reset
    recover.request(reset_path)
    expect(editor.request('admin/')[3].endswith('/admin/'), 'Email scanner GET does not reset credentials or revoke access')
    response = recover.form(reset_path, {'token':reset,'password':PASSWORD+'RESET','password_confirm':PASSWORD+'RESET'})
    expect('Password saved.' in response[1] and editor.request('admin/')[3].endswith('/admin/login.php'), 'Explicit reset revokes all earlier sessions')
    login(editor,'editor@example.test',PASSWORD+'RESET')
    expect('unavailable' in recover.request(reset_path)[1], 'Used staff reset cannot be replayed')
    cancelled = invite(s,'cancelled@example.test'); pending = snapshot(s)['tokens'][-1]
    s.client.form('admin/staff/', {'action':'cancel','id':str(pending['id']),'owner_password':s.password})
    expect('unavailable' in Client(s.base).request('admin/staff/complete.php?token='+cancelled)[1], 'Cancelled invitation cannot create an account')
    expired = invite(s,'expired@example.test'); fixture(s,'expire',{'email':'expired@example.test'})
    expect('unavailable' in Client(s.base).request('admin/staff/complete.php?token='+expired)[1], 'Expired staff invitation is unavailable')
    fixture(s,'due'); result = fixture(s,'process')
    expect(result['sent'] == 0 and result['cancelled'] >= 5, 'Used, cancelled and expired staff-link emails are suppressed before sending')
    own = user_row(s,s.owner); grant(s,own,role='store_manager')
    expect(user_row(s,s.owner)['role'] == 'owner', 'Owner cannot demote or remove their own access')
    body = s.client.request('admin/activity/')[1]
    expect('staff.invited' in body and 'staff.access.updated' in body and raw not in body and 'encrypted_body' not in body, 'Owner audit history shows actions without usable credentials or token bodies')
    expect('password_hash' not in s.client.request('admin/staff/')[1], 'Owner staff listing never includes password hashes')
    for route in ['admin/staff/','admin/activity/']:
        expect(Client(s.base).request(route)[3].endswith('/admin/login.php'), 'Anonymous staff or audit access is denied')


def fulfillment(s):
    enable_fixture_smtp(s); raw=invite(s,'shipping@example.test','store_manager'); manager, member=complete(s,'shipping@example.test',raw,'store_manager')
    pid, vid, image=seed_catalog(s)
    variant=commerce_snapshot(s)['product_variants'][0]
    response=manager.form(f'admin/shop/variants.php?id={pid}', {'action':'stock','variant_id':str(vid),'revision':str(variant['revision']),'delta':'1','reason':'Store manager test adjustment','nonce':'c'*64})
    expect(response[0]==200 and int(commerce_snapshot(s)['product_variants'][0]['stock'])==9,'Store manager records a real audited inventory adjustment')
    configure(s)
    buyer, oid, _, _=prepare(s,vid)
    expect(payment_fixture(s,'pay',{'id':oid})['mutating_calls'] == 1, 'Synthetic paid order prepared for staff authorization tests')
    path=f'admin/orders/view.php?id={oid}'; body=manager.request(path)[1]
    expect('Full payment reversal' not in body and 'Provider transaction ID' not in body and 'Signature Key' not in body, 'Fulfillment view omits gateway controls, private keys and reversal tools')
    for action in ['refund','void','reconcile','cancel']:
        expect(manager.request(path,{'csrf':token(body),'action':action,'owner_password':s.password,'confirm_reversal':'1'})[0] == 403, 'Store manager cannot POST forbidden order action: '+action)
    row=order(s,oid);manager.form(path,{'action':'fulfill','fulfillment':'shipped','carrier':'Synthetic courier','tracking':'FIXTURE-TRACK-99','revision':str(row['revision'])})
    expect(order(s,oid)['fulfillment_state'] == 'shipped' and 'FIXTURE-TRACK-99' in buyer.request('shop/order.php?reference='+row['reference'])[1], 'Store manager updates real shipment state without changing payment state')
    grant(s,member,role='content_editor')
    expect(manager.request(path)[3].endswith('/admin/login.php'), 'Role change invalidates the old fulfillment session')
    login(manager,'shipping@example.test')
    expect(manager.request(path)[0] == 403 and manager.request('admin/content/?kind=page')[0] == 200, 'New session has only its newly assigned content permissions')


def upgrade(s):
    migration=s.root/'config/database/migrations/006.sql'; data=migration.read_bytes();migration.unlink()
    s.claim();s.client.form('install/',s.input());s.login()
    s.client.form('admin/settings.php',{'site_name':'Preserved owner band','tagline':'Preserved','welcome':'Preserved'})
    migration.write_bytes(data)
    expect(s.client.request('admin/staff/')[3].endswith('/admin/updates.php'), 'Existing .8 installation requires the additive staff schema')
    expect(Client(s.base).request('admin/staff/recover.php')[0] == 503, 'Staff credential flow fails closed until schema is applied')
    expect(s.client.form('admin/updates.php',{})[0] == 200, 'Owner applies staff migration without reinstalling')
    expect(len(snapshot(s)['users']) == 1 and snapshot(s)['tokens'] == [] and 'Preserved owner band' in Client(s.base).request('')[1], 'Staff migration preserves original owner and content without seeding accounts')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK')!='1':
        raise RuntimeError('Non-root disposable database and payment-network guard required.')
    with tempfile.TemporaryDirectory(prefix='spirit-staff-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder in ['', 'band']:
            with site(base/('root' if not folder else 'nested'),archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login();access_and_recovery(s)
        with site(base/'fulfillment',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();fulfillment(s)
        with site(base/'upgrade',archive,code,'band') as s:upgrade(s)
    print('Staff SQL/HTTP permissions, invitations, recovery, fulfillment and upgrade checks passed.',flush=True)


if __name__=='__main__':main()
