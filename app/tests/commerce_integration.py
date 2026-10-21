#!/usr/bin/env python3
"""Full catalog, inventory, cart and customer SQL/HTTP journeys; no real payments."""
from __future__ import annotations
import html
import json
import os
import re
import secrets
import subprocess
import tempfile
import urllib.parse
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import token, upload, png_bytes
from communication_integration import enable_fixture_smtp


def fixture(s, action, values=None):
    env = {**s.env, 'SPIRIT_FIXTURE_ROOT': str(s.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(s.docroot)}
    r = subprocess.run(['php', str(ROOT/'app/tests/commerce_fixture.php'), action], input=json.dumps(values or {}), text=True, capture_output=True, env=env, timeout=20)
    if r.returncode:
        raise AssertionError('Commerce fixture failed: ' + r.stderr)
    return json.loads(r.stdout)


def snapshot(s):
    return fixture(s, 'snapshot')


def product_values(s, product_id, image_id, action='draft', name='Fixture shirt'):
    row = next((r for r in snapshot(s)['products'] if int(r['id']) == product_id), None)
    return {'action': action, 'name': name, 'slug': 'fixture-shirt', 'description': 'Actual synthetic test product <script>not executable</script>', 'size_guide': 'Fixture care guide', 'category_id': '0', 'revision': str(row['revision'] if row else 1), 'image_ids[0]': str(image_id), 'approved': '1'}


def variant_values(row=None):
    return {'action':'variant','variant_id':str(row['id'] if row else 0),'revision':str(row['revision'] if row else 1),'sku':'FIXTURE-SHIRT-M','size_label':'M','color_label':'Black','price':'25.01','active':'1'}


def adjust(s, product_id, variant_id, delta, nonce=None):
    row = next(r for r in snapshot(s)['product_variants'] if int(r['id']) == variant_id)
    values = {'action':'stock','variant_id':str(variant_id),'revision':str(row['revision']),'delta':str(delta),'reason':'Synthetic inventory adjustment','nonce':nonce or secrets.token_hex(32)}
    response = s.client.form(f'admin/shop/variants.php?id={product_id}', values)
    return response, values


def seed_catalog(s):
    s.client.form('admin/shop/settings.php', {'enabled':'1','intro':'Fixture official collection'})
    image_id, status, _ = upload(s, 'store-fixture.png', 'image/png', png_bytes())
    expect(status == 200 and image_id > 0, 'Product image uploaded through the real media form')
    response = s.client.form('admin/shop/edit.php', product_values(s, 0, image_id))
    product_id = int(urllib.parse.parse_qs(urllib.parse.urlparse(response[3]).query).get('id',['0'])[0])
    expect(product_id > 0 and response[0] == 200, 'Owner creates a private merchandise draft')
    s.client.form(f'admin/shop/variants.php?id={product_id}', variant_values())
    rows = snapshot(s)['product_variants']; expect(len(rows) == 1, 'Owner adds a priced SKU variant')
    variant_id = int(rows[0]['id'])
    adjust(s, product_id, variant_id, 8)
    response = s.client.form(f'admin/shop/edit.php?id={product_id}', product_values(s, product_id, image_id, 'publish'))
    expect(response[0] == 200 and snapshot(s)['products'][0]['published_json'] is not None, 'Approved product is published with image and variant')
    return product_id, variant_id, image_id


def catalog_and_cart(s):
    anon = Client(s.base)
    expect('No payments are collected' in anon.request('shop/')[1], 'Fresh store stays closed with no demo products')
    expect('not available yet' in anon.request('account/register.php')[1], 'Account enrollment stays closed before email setup')
    expect(s.client.request('admin/shop/edit.php', {'csrf':'bad'})[0] == 403, 'Product mutation rejects invalid CSRF')
    pid, vid, image = seed_catalog(s)
    path = 'shop/product.php?slug=fixture-shirt'
    body = anon.request(path)[1]
    expect('Fixture shirt' in body and '$25.01' in body and '&lt;script&gt;' in body, 'Public product shows server price and escaped description')
    expect(anon.request(f'media/file.php?id={image}')[0] == 200, 'Published product photograph is authorized publicly')
    s.client.form(f'admin/shop/edit.php?id={pid}', product_values(s, pid, image, name='PRIVATE NEW NAME'))
    expect('PRIVATE NEW NAME' not in anon.request(path)[1] and 'Fixture shirt' in anon.request(path)[1], 'Private merchandise edit cannot alter the published snapshot')
    response, values = adjust(s, pid, vid, 2)
    s.client.form(f'admin/shop/variants.php?id={pid}', values)
    expect(int(snapshot(s)['product_variants'][0]['stock']) == 10 and len(snapshot(s)['inventory_adjustments']) == 2, 'Repeated stock adjustment is idempotent')
    values['nonce'] = secrets.token_hex(32)
    expect('Inventory changed' in s.client.form(f'admin/shop/variants.php?id={pid}',values)[1], 'Stale stock adjustment cannot overwrite newer inventory')
    adjust(s, pid, vid, -100)
    expect(int(snapshot(s)['product_variants'][0]['stock']) == 10, 'Negative resulting stock is refused')
    nonce = secrets.token_hex(32)
    values = {'action':'add','variant_id':str(vid),'quantity':'2','nonce':nonce,'price':'0.01','subtotal':'0.01'}
    response = anon.request('shop/cart.php', {**values, 'csrf':token(anon.request(path)[1])})
    expect('$50.02' in response[1] and response[0] == 200, 'Bag calculates exact server-side prices and ignores submitted totals')
    anon.form('shop/cart.php', values)
    expect('$50.02' in anon.request('shop/cart.php')[1] and '$100.04' not in anon.request('shop/cart.php')[1], 'Repeated Add to bag POST does not duplicate quantities')
    expect('bag is empty' in Client(s.base).request('shop/cart.php')[1], 'Another browser cannot read a private bag')
    expect(all(c.has_nonstandard_attr('HttpOnly') for c in anon.cookies), 'Session and bag cookies are HttpOnly')
    expect(anon.request('shop/cart.php', {**values,'csrf':'bad'})[0] == 403, 'Bag changes reject invalid CSRF')
    response = anon.form('shop/cart.php',{**values,'nonce':secrets.token_hex(32),'quantity':'11'})
    expect('not available' in response[1] and '$50.02' in response[1], 'Excess quantity is refused without altering the bag')
    row = snapshot(s)['product_variants'][0]
    s.client.form(f'admin/shop/variants.php?id={pid}', {**variant_values(row),'price':'30.00'})
    expect('$60.00' in anon.request('shop/cart.php')[1], 'Bag refreshes changed catalog prices instead of trusting a snapshot')
    adjust(s,pid,vid,-9)
    expect('Quantity exceeds current stock' in anon.request('shop/cart.php')[1], 'Reduced inventory marks stale bag quantities unavailable')
    anon.form('shop/cart.php',{'action':'set','variant_id':str(vid),'quantity':'1','nonce':secrets.token_hex(32)})
    expect('$30.00' in anon.request('shop/cart.php')[1], 'Customer can repair a stale quantity')
    s.client.form(f'admin/shop/edit.php?id={pid}',product_values(s,pid,image,'unpublish'))
    expect(anon.request(path)[0] == 404 and anon.request(f'media/file.php?id={image}')[0] == 404, 'Unpublishing product also withdraws otherwise-private media')
    expect('Item no longer available' in anon.request('shop/cart.php')[1] and 'PRIVATE NEW NAME' not in anon.request('shop/cart.php')[1], 'Unpublished bag lines cannot disclose private draft data')
    anon.form('shop/cart.php',{'action':'set','variant_id':str(vid),'quantity':'0','nonce':secrets.token_hex(32)})
    expect('bag is empty' in anon.request('shop/cart.php')[1], 'Unavailable merchandise can still be removed')
    for route in ['admin/shop/','admin/shop/edit.php','admin/shop/categories.php','admin/shop/settings.php','admin/shop/customers.php']:
        expect(Client(s.base).request(route)[3].endswith('/admin/login.php'), 'Anonymous store admin access denied: ' + route)
    s.database('editor')
    expect(s.client.request('admin/shop/')[0] == 403, 'Content editor cannot inherit owner store access')
    s.database('owner')
    return pid,vid,image


def secure_link(s, purpose):
    body=fixture(s,'mail',{'purpose':purpose})['html']
    match=re.search(r'account/complete.php\?purpose='+purpose+r'&(?:amp;)?token=([a-f0-9]{64})',body)
    if not match:
        raise AssertionError('Missing private account link')
    return 'account/complete.php?purpose='+purpose+'&token='+match.group(1)


def signup(s,email='customer@example.test'):
    client=Client(s.base)
    response=client.form('account/register.php',{'name':'Test customer','email':email,'account_consent':'1'})
    expect('Check your email' in response[1], 'Account request has a neutral email confirmation')
    path=secure_link(s,'activate'); before=snapshot(s)
    expect(before['customers'][-1]['password_hash'] is None, 'Email-first signup has no preselected attacker password')
    response=client.request(path)
    expect('no-store' in response[2].get('Cache-Control','') and 'no-referrer' == response[2].get('Referrer-Policy'), 'Account link pages prohibit caching and referrer leakage')
    expect(snapshot(s)['customers'][-1]['status']=='pending', 'Email scanner GET cannot activate an account')
    password='Unique synthetic password 2026!'
    response=client.form(path,{'password':password,'password_confirm':password})
    expect(response[3].endswith('/account/login.php'), 'Email holder chooses password and completes activation')
    expect('unavailable' in client.request(path)[1], 'Account activation token is single-use')
    return client,password


def customers(s):
    enable_fixture_smtp(s)
    s.client.form('admin/shop/settings.php',{'enabled':'1','accounts_enabled':'1','intro':'Synthetic test store'})
    client,password=signup(s)
    rows=snapshot(s)
    expect(password not in json.dumps(rows) and rows['customers'][0]['status']=='active', 'Password is stored hashed and activation is recorded')
    customer_id=int(rows['customers'][0]['id'])
    second=Client(s.base)
    for user in [client,second]:
        expect(user.form('account/login.php',{'email':'customer@example.test','password':password})[3].endswith('/account/'),'Verified customer can sign in')
    expect(client.request('admin/')[3].endswith('/admin/login.php'), 'Customer authentication never creates an administrator session')
    address={'label':'Home','recipient':'Synthetic Customer','line_one':'123 Fixture Street','line_two':'','city':'Lafayette','region':'LA','postal_code':'70501','country_code':'US'}
    expect(client.request('account/addresses.php', {**address,'csrf':'bad'})[0]==403, 'Address changes require CSRF')
    client.form('account/addresses.php',address)
    saved=snapshot(s)['customer_addresses'][0]
    expect('123 Fixture Street' in client.request('account/addresses.php')[1], 'Saved address appears only in the signed-in customer account')
    other,other_password=signup(s,'other@example.test')
    other.form('account/login.php',{'email':'other@example.test','password':other_password})
    expect('123 Fixture Street' not in other.request('account/addresses.php?edit='+str(saved['id']))[1], 'Another customer cannot retrieve an address by guessing its ID')
    other.form('account/addresses.php',{**address,'id':str(saved['id']),'revision':str(saved['revision']),'line_one':'Unauthorized overwrite'})
    other.form('account/addresses.php',{'action':'delete','id':str(saved['id']),'revision':str(saved['revision'])})
    expect(snapshot(s)['customer_addresses'][0]['line_one']=='123 Fixture Street','Address ID tampering cannot update or delete another customer record')
    client.form('account/addresses.php',{**address,'id':str(saved['id']),'revision':str(saved['revision']),'line_one':'456 Updated Street'})
    client.form('account/addresses.php',{**address,'id':str(saved['id']),'revision':str(saved['revision']),'line_one':'Stale overwrite'})
    expect(snapshot(s)['customer_addresses'][0]['line_one']=='456 Updated Street','Stale address edits cannot overwrite a new revision')
    new_password=password+' changed'
    client.form('account/password.php',{'current_password':password,'password':new_password,'password_confirm':new_password})
    expect(second.request('account/')[3].endswith('/account/login.php'), 'Password change revokes other customer sessions')
    expect('Welcome' in client.request('account/')[1], 'Password change keeps the current customer session usable')
    client.form('account/forgot.php',{'email':'customer@example.test'}); path=secure_link(s,'reset')
    expect('Choose a new password' in client.request(path)[1], 'Password reset link opens without changing credentials')
    reset_password='New reset-only synthetic password!'
    client.form(path,{'password':reset_password,'password_confirm':reset_password})
    expect(second.form('account/login.php',{'email':'customer@example.test','password':new_password})[3].endswith('/account/login.php'), 'Old password cannot authenticate after reset')
    expect(second.form('account/login.php',{'email':'customer@example.test','password':reset_password})[3].endswith('/account/'), 'Reset password authenticates through the normal login flow')
    s.client.form('admin/shop/customers.php',{'customer_id':str(customer_id),'status':'suspended'})
    expect(second.request('account/')[3].endswith('/account/login.php'), 'Owner suspension immediately revokes customer access')
    s.client.form('admin/shop/customers.php',{'customer_id':str(customer_id),'status':'active'})
    expect(second.form('account/login.php',{'email':'customer@example.test','password':reset_password})[3].endswith('/account/'),'Owner can restore verified customer access without changing credentials')
    second.request('account/logout.php',{'csrf':token(second.request('account/')[1])})
    expect(second.request('account/')[3].endswith('/account/login.php'), 'Customer logout revokes access')
    fixture(s,'cooldown');client.form('account/forgot.php',{'email':'customer@example.test'}); expired=secure_link(s,'reset');fixture(s,'expire')
    expect('unavailable' in client.request(expired)[1], 'Expired password reset is refused')
    result=fixture(s,'process')
    expect(result['cancelled']>=3, 'Used and expired account-token emails are cancelled before sending')
    expect('password_hash' not in s.client.request('admin/shop/customers.php')[1], 'Owner customer list excludes credential fields')


def upgrade(s):
    migration=s.root/'config/database/migrations/004.sql';body=migration.read_bytes();migration.unlink()
    s.claim();s.client.form('install/',s.input());s.login()
    s.client.form('admin/settings.php',{'site_name':'Preserved Band','tagline':'Preserved','welcome':'Preserved welcome'})
    migration.write_bytes(body)
    expect(s.client.request('admin/shop/')[3].endswith('/admin/updates.php'), 'Existing .6 installation requests an additive commerce migration')
    expect(Client(s.base).request('shop/')[0]==503,'Unmigrated public commerce fails safely')
    s.client.form('admin/updates.php',{})
    expect(s.database('count')['users']==1 and 'Preserved Band' in Client(s.base).request('')[1], 'Commerce upgrade preserves owner and existing website content')
    expect(snapshot(s)['products']==[],'Additive migration does not seed fake merchandise')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':
        raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-commerce-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder in ['', 'spirit']:
            with site(base/('root' if not folder else 'nested'),archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login()
                catalog_and_cart(s);customers(s)
        with site(base/'upgrade',archive,code,'band') as s:
            upgrade(s)
    print('Commerce SQL/HTTP root, subfolder and migration checks passed.',flush=True)

if __name__=='__main__':
    main()
