#!/usr/bin/env python3
"""Real native multipart City MMDB upload/removal on disposable packaged PHP sites."""
from __future__ import annotations
import fcntl
import gzip
import hashlib
import json
import os
import re
import secrets
import subprocess
import tempfile
import urllib.error
import urllib.request
import zlib
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from analytics_integration import fixture as analytics_fixture, request

ROUTE = 'admin/analytics/geography.php'
FIXTURES = ROOT / 'tests/fixtures/analytics-geo'


def fixture(s, action, values=None):
    env = {**s.env, 'SPIRIT_FIXTURE_ROOT': str(s.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(s.docroot)}
    result = subprocess.run(['php', str(ROOT / 'tests/analytics_geo_fixture.php'), action], input=json.dumps(values or {}), env=env, capture_output=True, text=True, timeout=30)
    if result.returncode:
        raise AssertionError('Geography fixture failed: ' + action + ' ' + result.stderr)
    return json.loads(result.stdout)


def token(client):
    status, body, _, _ = client.request(ROUTE)
    found = re.search(r'name="csrf" value="([a-f0-9]{64})"', body)
    if status != 200 or found is None:
        raise AssertionError('Expected native owner geography form')
    return found.group(1)


def multipart(client, data, filename, payload):
    boundary = 'spirit-test-' + secrets.token_hex(16)
    chunks = []
    for name, value in data.items():
        chunks.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"\r\n\r\n{value}\r\n'.encode())
    chunks.append(f'--{boundary}\r\nContent-Disposition: form-data; name="city_database"; filename="{filename}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode())
    chunks.extend([payload, f'\r\n--{boundary}--\r\n'.encode()])
    return request(client, ROUTE, b''.join(chunks), {'Content-Type': 'multipart/form-data; boundary=' + boundary})


def upload(s, name, data, csrf=None):
    return multipart(s.client, {'action': 'upload', 'csrf': csrf if csrf is not None else token(s.client)}, name, data)


def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()


def preserved(path, expected, directory):
    return path.is_file() and not path.is_symlink() and digest(path) == expected and not list(directory.glob('.city-stage-*'))


def journey(s, stress=False):
    s.claim(); s.client.form('install/', s.input()); s.login()
    target = s.runtime / 'analytics-geo/city.mmdb'; directory = target.parent
    country = s.root / 'app/GeoData/country.mmdb'
    country_digest = digest(country); config_digest = digest(s.runtime / 'config.php')
    analytics_fixture(s, 'exercise')
    history = analytics_fixture(s, 'state')['tables']
    initial_runtime = sorted(p.name for p in s.runtime.iterdir())
    status, body, headers, _ = s.client.request(ROUTE)
    expect(status == 200 and 'DB-IP Lite Country' in body and 'Not installed' in body and 'IP Geolocation by DB-IP' in body, 'Country-only owner geography page reports real availability and attribution')
    expect('multipart/form-data' in body and 'name="city_database"' in body and 'Current PHP file limit:' in body and 'whole-request limit:' in body, 'City upload uses a native form with the actual hosting limits')
    expect('no-store' in headers.get('Cache-Control', '') and headers.get('Referrer-Policy') == 'no-referrer', 'Geography administration is private and noncacheable')
    expect(request(s.client, ROUTE, method='HEAD')[0] == 200 and request(s.client, ROUTE, method='PUT')[0] == 405, 'Geography route accepts read-only HEAD and refuses unsupported methods')
    s.client.request(ROUTE + '?action=remove')
    expect(not directory.exists() and sorted(p.name for p in s.runtime.iterdir()) == initial_runtime, 'GET, HEAD and action-like query fields create no geography storage')
    anonymous = Client(s.base)
    expect(anonymous.request(ROUTE)[3].endswith('/admin/login.php'), 'Anonymous requests cannot read geography management')
    valid = (FIXTURES / 'GeoIP2-City-Test.mmdb').read_bytes()
    expect(upload(s, 'City.mmdb', valid, csrf='invalid')[0] == 403 and not directory.exists(), 'Invalid multipart CSRF cannot create a City database or staging directory')
    status, body, _, url = upload(s, 'City.mmdb', valid)
    expected = hashlib.sha256(valid).hexdigest()
    expect(status == 200 and url.endswith('/' + ROUTE) and 'City database installed privately.' in body and preserved(target, expected, directory), 'A real owner multipart upload installs the exact validated MMDB bytes')
    expect(directory.stat().st_mode & 0o777 == 0o700 and target.stat().st_mode & 0o777 == 0o600, 'City directory and database have private 0700 and 0600 permissions')
    expect((directory / '.htaccess').read_text() == 'Require all denied\n', 'Private geography storage also receives deny-all access rules')
    state = fixture(s, 'state')
    expect(state['status']['city_available'] and state['lookup']['city'] == 'London' and state['lookup']['region'] == 'England', 'Installed upstream synthetic City fixture supplies a real local city and region lookup')
    s.client.request(ROUTE + '?action=remove')
    expect(preserved(target, expected, directory), 'An action-like GET cannot remove an installed City database')

    invalids = [
        ('City.zip', valid, 'ending in .mmdb'),
        ('City.mmdb', b'invalid database' * 80, 'valid City MMDB'),
        ('City.mmdb', valid[:1000], 'valid City MMDB'),
        ('Country.mmdb', (FIXTURES / 'MaxMind-DB-test-ipv4-24.mmdb').read_bytes(), 'valid City MMDB'),
        ('City.mmdb.gz', gzip.compress(valid)[:-8], 'single, undamaged'),
        ('City.mmdb.gz', gzip.compress(valid) + b'trailing-unexpected-data', 'single, undamaged'),
        ('City.mmdb.gz', gzip.compress(valid) + gzip.compress(valid), 'single, undamaged'),
        ('City.mmdb.gz', b'not a gzip file' * 100, 'gzip file is damaged'),
    ]
    for name, payload, message in invalids:
        status, body, _, _ = upload(s, name, payload)
        expect(status == 200 and message in body and preserved(target, expected, directory), 'Invalid type, metadata or gzip leaves the installed database intact: ' + message)

    status, body, _, _ = upload(s, '../../outside.mmdb.gz', gzip.compress(valid))
    expect(status == 200 and 'City database installed privately.' in body and preserved(target, expected, directory) and not (s.home / 'outside.mmdb.gz').exists(), 'Compressed upload expands exactly once to the fixed private destination regardless of submitted filename')
    with (directory / '.update.lock').open('r+b') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        status, body, _, _ = upload(s, 'City.mmdb', valid)
        expect(status == 200 and 'already running' in body and preserved(target, expected, directory), 'Concurrent geography update lock rejects replacement without damaging the previous database')
    expect(s.client.request(ROUTE, {'action': 'remove', 'csrf': 'invalid'})[0] == 403 and preserved(target, expected, directory), 'City removal requires valid CSRF')
    for role in ['content_editor', 'store_manager']:
        csrf = token(s.client); fixture(s, 'role', {'role': role})
        try:
            expect(s.client.request(ROUTE)[0] == 403 and multipart(s.client, {'action': 'upload', 'csrf': csrf}, 'City.mmdb', valid)[0] == 403 and s.client.request(ROUTE, {'action': 'remove', 'csrf': csrf})[0] == 403 and preserved(target, expected, directory), 'Staff role cannot read, upload or remove owner geography: ' + role)
        finally:
            fixture(s, 'role', {'role': 'owner'})
    for label in fixture(s, 'guard-checks')['checks']:
        expect(True, label)

    sentinel = s.home / 'sentinel.mmdb'; sentinel.write_bytes(valid); sentinel.chmod(0o600)
    target.unlink(); target.symlink_to(sentinel)
    try:
        status, body, _, _ = upload(s, 'City.mmdb', valid)
        expect(status == 200 and 'Unsafe files were left unchanged.' in body and target.is_symlink() and digest(sentinel) == expected, 'A symlink target cannot redirect replacement outside the fixed private database')
        status, body, _, _ = s.client.form(ROUTE, {'action': 'remove'})
        expect(status == 200 and 'Unsafe files were left unchanged.' in body and target.is_symlink() and digest(sentinel) == expected, 'City removal refuses a symlink and preserves its external target')
    finally:
        target.unlink(); target.write_bytes(valid); target.chmod(0o600)
    target.chmod(0o644)
    try:
        status, body, _, _ = upload(s, 'City.mmdb', valid)
        expect(status == 200 and 'Unsafe files were left unchanged.' in body and digest(target) == expected and not fixture(s, 'state')['status']['city_available'], 'Unsafe existing file permissions fail closed without silently changing the file')
    finally:
        target.chmod(0o600)

    if stress:
        compressor = zlib.compressobj(level=9, wbits=31); pieces = []
        for _ in range(257):
            pieces.append(compressor.compress(b'\0' * 1048576))
        pieces.append(compressor.flush())
        status, body, _, _ = upload(s, 'Oversized.mmdb.gz', b''.join(pieces))
        expect(status == 200 and 'expanded database must each be no larger than 256 MiB' in body and preserved(target, expected, directory), 'A small compressed expansion beyond 256 MiB is bounded and leaves no staged or replaced file')
        status, body, _, _ = upload(s, 'Upload-Limit.mmdb', b'x' * (3 * 1048576))
        expect(status == 200 and 'upload did not complete' in body and preserved(target, expected, directory), 'PHP per-file upload rejection is explained and preserves the installed database')
        status, body, _, _ = upload(s, 'Request-Limit.mmdb', b'x' * (9 * 1048576))
        expect(status == 413 and 'whole-request limit' in body and preserved(target, expected, directory), 'PHP discarded oversized whole-request body is rejected with no requested mutation')

    extra = directory / 'owner-keep.txt'; extra.write_text('unrelated owner file'); extra.chmod(0o600)
    status, body, _, _ = s.client.form(ROUTE, {'action': 'remove'})
    expect(status == 200 and 'City database removed.' in body and not target.exists() and extra.read_text() == 'unrelated owner file', 'Valid owner removal deletes only the fixed City database')
    state = fixture(s, 'state')
    expect(state['status']['country_available'] and not state['status']['city_available'] and state['lookup']['precision'] == 'country', 'Removing City data resumes included country-only geography')
    expect(s.client.form(ROUTE, {'action': 'remove'})[0] == 200 and not target.exists(), 'Repeating City removal is harmless')
    expect(digest(country) == country_digest and digest(s.runtime / 'config.php') == config_digest and analytics_fixture(s, 'state')['tables'] == history, 'All geography workflows preserve included country data, private configuration and saved analytics history')
    expect(not list(directory.glob('.city-stage-*')), 'Rejected and completed uploads leave no private staging files')


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable SQL environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-analytics-geo-') as directory:
        base = Path(directory); archive, code = build_package(base / 'packages')
        for folder, label in [('', 'root'), ('band', 'subfolder')]:
            with site(base / label, archive, code, folder) as current:
                journey(current, stress=label == 'root')
    print('Analytics geography native multipart, private storage and owner-boundary checks passed.', flush=True)


if __name__ == '__main__':
    main()
