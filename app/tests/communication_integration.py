#!/usr/bin/env python3
"""Real SQL/HTTP communication journeys; synthetic data and no live email."""
from __future__ import annotations
import hashlib
import html
import json
import os
import re
import socket
import subprocess
import tempfile
from pathlib import Path
from http_integration import Client, ROOT, build_package, expect, site
from content_integration import token


def fixture(s, action: str, values: dict | None = None, ca: Path | None = None):
    env = {**s.env, 'SPIRIT_FIXTURE_ROOT': str(s.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(s.docroot)}
    command = ['php']
    if ca:
        command += ['-d', 'openssl.cafile=' + str(ca)]
    command += [str(ROOT / 'app/tests/communication_fixture.php'), action]
    result = subprocess.run(command, input=json.dumps(values or {}), env=env, text=True, capture_output=True, timeout=25)
    if result.returncode:
        raise AssertionError('Communication fixture failed: ' + action + ' ' + result.stderr)
    return json.loads(result.stdout)


def booking_input(client: Client):
    status, body, _, _ = client.request('booking/')
    nonce = re.search(r'name="submission_nonce" value="([a-f0-9]{64})"', body)
    if status != 200 or nonce is None:
        raise AssertionError('Missing booking form')
    return {'csrf': token(body), 'submission_nonce': nonce.group(1), 'name': 'Synthetic organizer', 'email': 'organizer@example.test', 'location': 'Test venue, Louisiana', 'event_type': 'festival', 'preferred_date': '2099-06-14', 'audience': '250', 'message': '<script>private fixture</script>', 'contact_consent': '1'}


def state(s):
    return fixture(s, 'snapshot')


def run_mail_task(s):
    result=subprocess.run(
        ['php',str(s.root/'app/tasks/mail.php'),'--document-root='+str(s.docroot)],
        env=s.env,text=True,capture_output=True,timeout=30
    )
    expect(result.returncode==0,'Actual scheduled mail CLI task completes in the disposable environment')
    payload=json.loads(result.stdout)
    expect({'sent','failed','uncertain','cancelled','waiting'}.issubset(payload),'Mail task preserves its original top-level result contract')
    expect(payload.get('heartbeat') is True,'Successful mail task reports heartbeat persistence')
    path=s.runtime/'cache/task-mail.json'
    expect(path.is_file() and (path.stat().st_mode & 0o077)==0,'Mail heartbeat is stored only in protected private runtime cache')
    stamp=json.loads(path.read_text())
    expect(isinstance(stamp.get('ran_at'),str) and (stamp['ran_at'].endswith('Z') or stamp['ran_at'].endswith('+00:00')),'Mail heartbeat contains only a UTC completion timestamp')


def public_booking(s):
    visitor = Client(s.base); values = booking_input(visitor)
    expect(visitor.request('booking/', {**values, 'csrf': 'wrong'})[0] == 403, 'Booking rejects invalid CSRF')
    visitor.request('booking/', {**values, 'company_website': 'bot.invalid'})
    expect(len(state(s)['bookings']) == 0, 'Booking honeypot prevents storage')
    visitor.request('booking/', {**values, 'contact_consent': '0'})
    expect(len(state(s)['bookings']) == 0, 'Booking requires explicit contact consent')
    status, body, headers, url = visitor.request('booking/', values)
    expect(status == 200 and url.endswith('/booking/thanks.php'), 'Public inquiry reaches its private saved receipt')
    snapshot = state(s); record = snapshot['bookings'][0]
    expect(len(snapshot['bookings']) == 1 and len(snapshot['mail_queue']) == 2, 'Inquiry creates one saved record and two deduplicated notifications')
    expect(record['reference'] in body and 'no-store' in headers.get('Cache-Control', ''), 'Receipt is session-bound and uncached')
    expect(record['reference'] not in Client(s.base).request('booking/thanks.php')[1], 'Another visitor cannot retrieve the receipt')
    expect(len(snapshot['subscribers']) == 0, 'Booking never subscribes an organizer to marketing')
    expect(all(row['status'] == 'queued' for row in snapshot['mail_queue']), 'Unconfigured SMTP leaves messages queued without claiming delivery')
    visitor.request('booking/', values)
    expect(len(state(s)['bookings']) == 1 and len(state(s)['mail_queue']) == 2, 'Repeated inquiry POST cannot duplicate its record or notifications')
    path = f'admin/bookings/view.php?id={record["id"]}'
    status, body, _, _ = s.client.request(path)
    expect(status == 200 and '&lt;script&gt;private fixture&lt;/script&gt;' in body and '<script>private fixture' not in body, 'Owner detail escapes organizer-supplied markup')
    s.client.form(path, {'status': 'reviewing', 'private_notes': 'OWNER-ONLY-TEST-NOTE', 'revision': '1', 'assigned_to': '1'})
    expect(state(s)['bookings'][0]['status'] == 'reviewing', 'Owner can update and assign a booking inquiry')
    response = s.client.form(path, {'status': 'declined', 'private_notes': 'stale', 'revision': '1', 'assigned_to': '0'})
    expect('changed in another session' in response[1] and state(s)['bookings'][0]['private_notes'] == 'OWNER-ONLY-TEST-NOTE', 'Stale owner changes cannot overwrite inquiry notes')
    expect('OWNER-ONLY-TEST-NOTE' not in visitor.request('booking/')[1], 'Owner notes never enter the public booking page')
    for route in ['admin/bookings/', path, 'admin/communication/', 'admin/mail/', 'admin/subscribers/']:
        expect(Client(s.base).request(route)[3].endswith('/admin/login.php'), 'Anonymous communication administration is denied: ' + route)
    s.database('editor')
    for route in ['admin/bookings/', 'admin/communication/', 'admin/mail/', 'admin/subscribers/']:
        expect(s.client.request(route)[0] == 403, 'Editor cannot inherit owner communication privileges: ' + route)
    s.database('owner')
    fixture(s, 'queue_break')
    try:
        another = Client(s.base)
        expect(another.request('booking/', booking_input(another))[3].endswith('/booking/thanks.php'), 'Queue failure does not discard a valid booking inquiry')
    finally:
        fixture(s, 'queue_restore')
    expect(int(state(s)['bookings'][-1]['notifications_queued']) == 0, 'Interrupted notification creation remains durably pending')
    result = fixture(s, 'process')
    expect(result['sent'] == 4 and all(int(r['notifications_queued']) == 1 for r in state(s)['bookings']), 'Worker reconciles the saved inquiry and processes each notification once')
    expect(fixture(s, 'process')['sent'] == 0, 'Repeated worker execution does not resend accepted messages')


def delivery_queue(s):
    fixture(s, 'clear_mail')
    first = fixture(s, 'enqueue', {'key': 'fixture:failure'})['id']
    expect(fixture(s, 'enqueue', {'key': 'fixture:failure'})['id'] == first, 'Queue deduplication returns the existing message')
    result = fixture(s, 'process', {'mode': 'fail'}); row = state(s)['mail_queue'][0]
    expect(result['failed'] == 1 and row['status'] == 'retry' and row['last_error'] == 'delivery_failed', 'Pre-acceptance failure schedules retry with redacted diagnostics')
    expect(fixture(s, 'process')['sent'] == 0, 'Retry backoff is respected')
    for _ in range(4):
        fixture(s, 'due'); fixture(s, 'process', {'mode': 'fail'})
    expect(state(s)['mail_queue'][0]['status'] == 'failed', 'Automatic retries stop after five failures')
    expect(fixture(s, 'retry', {'id': first})['ok'], 'Owner may requeue a definite failure')
    expect(fixture(s, 'process', {'mode': 'nested'})['nested_lock_rejected'], 'Concurrent delivery worker cannot acquire the same queue')
    uncertain = fixture(s, 'enqueue', {'key': 'fixture:uncertain'})['id']
    expect(fixture(s, 'process', {'mode': 'uncertain'})['uncertain'] == 1, 'Unverified SMTP acceptance has an explicit uncertain state')
    expect(not fixture(s, 'retry', {'id': uncertain})['ok'], 'Uncertain retry requires duplicate-risk acknowledgement')
    expect(fixture(s, 'retry', {'id': uncertain, 'ack': True})['ok'], 'Acknowledged uncertain retry can be queued')
    fixture(s, 'process')
    interrupted = fixture(s, 'enqueue', {'key': 'fixture:interrupted'})['id']
    fixture(s, 'interrupt', {'id': interrupted}); fixture(s, 'process')
    expect(state(s)['mail_queue'][-1]['status'] == 'uncertain', 'Abandoned sending claim is not automatically resent')
    expect(fixture(s, 'limit')['allowed'] and not fixture(s, 'limit')['allowed'], 'Persistent request limiting blocks an exceeded bucket')


def enable_fixture_smtp(s):
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0)); port = sock.getsockname()[1]
    values = {'action': 'save', 'from_name': 'Synthetic band', 'from_email': 'band@example.test', 'smtp_host': '127.0.0.1', 'smtp_port': str(port), 'smtp_security': 'starttls', 'smtp_user': 'fixture-user', 'smtp_password': 'synthetic-password-not-production', 'enabled': '1', 'newsletter_enabled': '1', 'public_email': 'public@example.test', 'booking_email': ''}
    expect(s.client.request('admin/communication/', {**values, 'csrf': 'bad'})[0] == 403, 'SMTP settings reject invalid CSRF')
    status, body, _, _ = s.client.form('admin/communication/', values)
    snapshot = state(s)
    expect(status == 200 and snapshot['settings'].get('enabled') is True, 'Owner saves enabled TLS SMTP settings')
    encrypted = snapshot['settings']['smtp_password']
    expect(encrypted.startswith('v1:') and 'synthetic-password-not-production' not in json.dumps(snapshot) and encrypted not in body, 'SMTP password is encrypted in storage and never reflected in HTML')
    s.client.form('admin/communication/', {**values, 'smtp_password': ''})
    expect(state(s)['settings']['smtp_password'] == encrypted, 'Leaving the password blank preserves the encrypted secret')
    s.client.form('admin/communication/', {**values, 'smtp_security': 'none'})
    expect(state(s)['settings']['smtp_security'] == 'starttls', 'Owner cannot disable SMTP transport encryption')


def subscription(s):
    fixture(s, 'clear_mail'); enable_fixture_smtp(s)
    visitor = Client(s.base)
    body = visitor.request('newsletter/')[1]; csrf = token(body)
    request = {'email': 'fan@example.test', 'marketing_consent': '1', 'csrf': csrf}
    expect(visitor.request('newsletter/subscribe.php', {**request, 'csrf': 'bad'})[0] == 403, 'Newsletter signup rejects invalid CSRF')
    visitor.request('newsletter/subscribe.php', {**request, 'marketing_consent': '0'})
    expect(state(s)['subscribers'] == [], 'Newsletter requires separate marketing consent')
    status, body, _, _ = visitor.request('newsletter/subscribe.php', request)
    snapshot = state(s); subscriber = snapshot['subscribers'][0]; confirmation = snapshot['mail_queue'][0]
    expect(status == 200 and subscriber['status'] == 'pending' and 'Check your email' in body, 'Signup is pending, not subscribed, before confirmation')
    visitor.request('newsletter/subscribe.php', request)
    expect(len(state(s)['mail_queue']) == 1, 'Repeated signup within cooldown does not send duplicate confirmation')
    secret_body = fixture(s, 'decrypt', {'id': confirmation['id']})['html']
    confirmation_token = re.search(r'confirm.php\?token=([a-f0-9]{64})', secret_body).group(1)
    expect(subscriber['confirm_hash'] == hashlib.sha256(confirmation_token.encode()).hexdigest() and confirmation_token not in json.dumps(snapshot), 'Confirmation tokens are hashed and message bodies encrypted at rest')
    path = 'newsletter/confirm.php?token=' + confirmation_token
    status, body, headers, _ = visitor.request(path)
    expect(state(s)['subscribers'][0]['status'] == 'pending', 'Email scanner GET cannot confirm a subscription')
    expect('no-store' in headers.get('Cache-Control', '') and headers.get('Referrer-Policy') == 'no-referrer', 'Token pages prohibit caching and referrer leakage')
    visitor.request(path, {'token': confirmation_token, 'csrf': token(body)})
    expect(state(s)['subscribers'][0]['status'] == 'confirmed', 'CSRF-protected confirmation records explicit opt-in')
    welcome = state(s)['mail_queue'][-1]
    visitor.form(path, {'token': confirmation_token})
    expect(len(state(s)['mail_queue']) == 2, 'Replayed confirmation cannot create another welcome')
    export = s.client.form('admin/subscribers/', {'action': 'export'})
    expect('fan@example.test' in export[1] and 'organizer@example.test' not in export[1] and 'text/csv' in export[2].get('Content-Type', ''), 'Confirmed-only CSV export excludes booking contacts')
    unsubscribe_body = fixture(s, 'decrypt', {'id': welcome['id']})['html']
    unsubscribe_token = re.search(r'unsubscribe.php\?token=([a-f0-9]{64})', unsubscribe_body).group(1)
    path = 'newsletter/unsubscribe.php?token=' + unsubscribe_token
    visitor.request(path)
    expect(state(s)['subscribers'][0]['status'] == 'confirmed', 'Email scanner GET cannot unsubscribe a fan')
    visitor.form(path, {'token': unsubscribe_token})
    expect(state(s)['subscribers'][0]['status'] == 'unsubscribed', 'Explicit unsubscribe records suppression')
    expect('You have been unsubscribed' in visitor.form(path, {'token': unsubscribe_token})[1], 'Unsubscribe is safe to repeat')
    expect('fan@example.test' not in s.client.form('admin/subscribers/', {'action': 'export'})[1], 'Unsubscribed email is removed from confirmed export')
    fixture(s, 'due'); result = fixture(s, 'process')
    expect(result['cancelled'] == 2 and result['sent'] == 0, 'Old confirmation and welcome jobs are suppressed after unsubscribe')
    visitor.request('newsletter/subscribe.php', {**request, 'email': 'expired@example.test'})
    row = state(s)['mail_queue'][-1]; body = fixture(s, 'decrypt', {'id': row['id']})['html']
    expired = re.search(r'confirm.php\?token=([a-f0-9]{64})', body).group(1)
    fixture(s, 'expire', {'email': 'expired@example.test'})
    visitor.form('newsletter/confirm.php?token=' + expired, {'token': expired})
    expect(state(s)['subscribers'][-1]['status'] == 'pending', 'Expired token cannot activate a subscription')
    expect('expired@example.test' not in s.client.form('admin/subscribers/', {'action': 'export'})[1], 'Pending addresses are excluded from subscriber exports')
    expect(s.client.request('app/tasks/mail.php')[0] == 403, 'Private app directory denies HTTP access before the scheduled mail worker can run')
    expect(s.client.request('tasks/mail.php')[0] == 404, 'Retired scheduled-worker URL is not reintroduced')


def upgrade(s):
    migration = s.root / 'config/database/migrations/003.sql'
    body = migration.read_bytes(); migration.unlink()
    s.claim(); s.client.form('install/', s.input()); s.login()
    s.client.form('admin/settings.php', {'site_name': 'Preserved Band', 'tagline': 'Preserved', 'welcome': 'Preserved welcome'})
    migration.write_bytes(body)
    expect(s.client.request('admin/bookings/')[3].endswith('/admin/updates.php'), 'Older schema routes owner to the additive update')
    expect(Client(s.base).request('booking/')[0] == 503, 'Public forms fail safely before the communication migration')
    expect(s.client.form('admin/updates.php', {})[0] == 200, 'Owner adds communication schema without reinstalling')
    expect(s.database('count')['users'] == 1 and 'Preserved Band' in Client(s.base).request('')[1], 'Communication migration preserves the owner and existing settings')
    expect(state(s)['bookings'] == [] and s.client.form('admin/updates.php', {})[0] == 200, 'Communication schema update is idempotent')


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-communication-') as directory:
        base = Path(directory); archive, code = build_package(base / 'packages')
        for folder in ['', 'spirit']:
            with site(base / ('nested' if folder else 'root'), archive, code, folder) as current:
                current.claim(); current.client.form('install/', current.input()); current.login()
                public_booking(current); delivery_queue(current); subscription(current); run_mail_task(current)
        with site(base / 'upgrade', archive, code, 'band') as current:
            upgrade(current)
    print('Communication SQL/HTTP, recovery and consent checks passed.', flush=True)


if __name__ == '__main__':
    main()
