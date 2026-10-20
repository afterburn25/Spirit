#!/usr/bin/env python3
"""Owner analytics reports/imports and offline geography through packaged HTTP routes."""
from __future__ import annotations
import gzip
import hashlib
import json
import os
import re
import secrets
import subprocess
import tempfile
import urllib.parse
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from analytics_integration import request
from content_integration import token


def admin_fixture(s, action):
    env = {**s.env, 'SPIRIT_FIXTURE_ROOT': str(s.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(s.docroot)}
    result = subprocess.run(['php', str(ROOT / 'tests/analytics_admin_fixture.php'), action], env=env,
                            capture_output=True, text=True, timeout=45)
    if result.returncode:
        raise AssertionError('Analytics admin fixture failed: ' + action + ' ' + result.stderr)
    return json.loads(result.stdout)


def multipart(client, route, values, field, filename, payload, *, csrf=None):
    if csrf is None:
        csrf = token(client.request(route)[1])
    values = {**values, 'csrf': csrf}
    boundary = 'SpiritAnalytics' + secrets.token_hex(12)
    parts = [f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode()
             for key, value in values.items()]
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{field}"; filename="{filename}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode() + payload + b'\r\n')
    parts.append(f'--{boundary}--\r\n'.encode())
    return request(client, route, b''.join(parts), {'Content-Type': f'multipart/form-data; boundary={boundary}'})


def query_values():
    from datetime import datetime, timedelta, timezone
    end = datetime.now(timezone.utc).date()
    return {'action': 'query-import', 'query_start': str(end - timedelta(days=6)), 'query_end': str(end)}


def journey(s):
    route = 'admin/analytics/'
    geography = route + 'geography.php'
    anon = Client(s.base)
    for path in [route, route+'live.php', geography]:
        result = anon.request(path)
        expect(result[3].endswith('/admin/login.php'), 'Anonymous analytics route requires owner sign-in: '+path)
    page = s.client.request(route)
    expect(page[0] == 200 and 'Know who' in page[1] and 'no-store' in page[2].get('Cache-Control', '')
           and 'noindex' in page[2].get('X-Robots-Tag', ''), 'Actual owner dashboard is available, private and unindexed')
    expect('admin/analytics/' in page[1] and 'data-live-url' in page[1], 'Admin navigation and live endpoint are wired')
    for path in [route, route+'live.php', geography]:
        expect(request(s.client, path, method='PUT')[0] == 405, 'Analytics rejects unsupported methods: '+path)
    expect(request(s.client, route+'live.php', headers={'Sec-Fetch-Site':'cross-site'})[0] == 403, 'Cross-site live report requests are rejected')
    head = request(s.client, route+'live.php', method='HEAD')
    expect(head[0] == 200 and head[1] == '', 'Live HEAD reports headers without a JSON body')

    initial = admin_fixture(s, 'snapshot')
    expect(initial['queries']['available'] and initial['queries']['query_count'] == 0, 'Fresh query report is explicitly empty')
    csv = b'Top queries,Clicks,Impressions,CTR,Position\nspiritual gorillaz,12,120,10%,2.3\n"live music, lafayette",3,60,5%,5.4\n'
    imported = multipart(s.client, route, query_values(), 'queries', 'Queries.csv', csv)
    snapshot = admin_fixture(s, 'snapshot')
    expect(imported[0] == 200 and 'Imported 2 Search Console queries' in imported[1], 'Owner imports an English query CSV through the real multipart handler')
    expect(snapshot['queries']['query_count'] == 2 and snapshot['queries']['clicks'] == 15
           and snapshot['queries']['impressions'] == 180 and snapshot['queries']['ctr'] == 8.33, 'Stored query snapshot returns exact row totals and calculated CTR')
    expect(snapshot['counts'] == initial['counts'] and snapshot['config_digest'] == initial['config_digest'], 'Import preserves visitor history and installed configuration')
    expect(snapshot['queries']['start'] == query_values()['query_start'] and 'cannot be connected to a particular visitor' in imported[1], 'Query dates and aggregate-only meaning remain explicit')
    bad_inputs = [
        b'Top queries,Clicks,Impressions,CTR,Position\nvalid,1,10,10%,2\nbroken,2,1,200%,1\n',
        b'Top queries,Clicks,Impressions,CTR,Position\nbad,"1,2",100,12%,2\n',
        b'Top queries,Clicks,Impressions,CTR,Position\n',
    ]
    for payload in bad_inputs:
        result = multipart(s.client, route, query_values(), 'queries', 'Queries.csv', payload)
        expect(result[0] == 200 and 'role="alert"' in result[1]
               and admin_fixture(s, 'snapshot')['query_digest'] == snapshot['query_digest'], 'Invalid whole CSV preserves the previous query snapshot')
    future = {**query_values(), 'query_end': '2999-01-01'}
    expect('role="alert"' in multipart(s.client, route, future, 'queries', 'Queries.csv', csv)[1]
           and admin_fixture(s, 'snapshot')['query_digest'] == snapshot['query_digest'], 'Invalid import dates cannot replace query data')
    expect(multipart(s.client, route, query_values(), 'queries', 'Queries.csv', csv, csrf='bad')[0] == 403, 'Query import requires CSRF')
    expect(s.client.request(route, {'action':'query-clear', 'csrf':'bad'})[0] == 403, 'Query removal requires CSRF')
    expect(admin_fixture(s, 'stale-owner')['rejected'] and admin_fixture(s, 'snapshot')['query_digest'] == snapshot['query_digest'], 'Locked owner-session recheck protects an imported snapshot')
    replacement = csv.replace(b'spiritual gorillaz', b'<script>alert(1)</script>')
    body = multipart(s.client, route, query_values(), 'queries', 'Queries.csv', replacement)[1]
    expect('<script>alert(1)</script>' not in body and '&lt;script&gt;alert(1)&lt;/script&gt;' in body, 'Imported query text is escaped in chart labels and details')
    filtered = s.client.request(route+'?range=90')[1]
    expect('Imported period: '+query_values()['query_start'] in filtered, 'Visitor date filters do not relabel the imported query period')
    expect(s.client.form(route, {'action':'query-clear'})[0] == 200 and admin_fixture(s, 'snapshot')['queries']['query_count'] == 0, 'Owner removes the imported snapshot through its native form')

    geo_page = s.client.request(geography)
    expect(geo_page[0] == 200 and 'DB-IP Lite Country' in geo_page[1] and 'type="file"' in geo_page[1], 'Owner geography page exposes the included country source and actual upload form')
    city_bytes = (ROOT/'tests/fixtures/analytics-geo/GeoIP2-City-Test.mmdb').read_bytes()
    city_path = s.runtime/'analytics-geo/city.mmdb'
    before = admin_fixture(s, 'snapshot')
    uploaded = multipart(s.client, geography, {'action':'upload'}, 'city_database', 'City.mmdb', city_bytes)
    problem = re.search(r'<div class="notice error"[^>]*>(.*?)</div>', uploaded[1])
    expect(uploaded[0] == 200 and 'City database installed privately' in uploaded[1] and city_path.is_file(), 'Owner installs a City MMDB through real file upload' + (': '+problem.group(1) if problem else ''))
    expected_hash = hashlib.sha256(city_bytes).hexdigest()
    expect(hashlib.sha256(city_path.read_bytes()).hexdigest() == expected_hash and city_path.stat().st_mode & 0o777 == 0o600
           and city_path.parent.stat().st_mode & 0o777 == 0o700, 'Published City database bytes and filesystem permissions are correct')
    expect(admin_fixture(s, 'snapshot')['geography']['city_available'], 'A new PHP request recognizes the uploaded City database')
    expect(admin_fixture(s, 'snapshot')['counts'] == before['counts'], 'Geography publication does not rewrite stored visitor history')
    for filename, payload in [('City.mmdb', b'invalid '*200), ('City.mmdb.gz', gzip.compress(city_bytes)[:-5]),
                              ('City.mmdb.gz', gzip.compress(city_bytes)+b'trailing'), ('City.zip', city_bytes),
                              ('Country.mmdb', (ROOT/'app/GeoData/country.mmdb').read_bytes())]:
        result = multipart(s.client, geography, {'action':'upload'}, 'city_database', filename, payload)
        expect(result[0] == 200 and 'role="alert"' in result[1]
               and hashlib.sha256(city_path.read_bytes()).hexdigest() == expected_hash, 'Invalid City replacement retains the working database: '+filename)
    compressed = multipart(s.client, geography, {'action':'upload'}, 'city_database', 'City.mmdb.gz', gzip.compress(city_bytes))
    expect(compressed[0] == 200 and 'City database installed privately' in compressed[1]
           and hashlib.sha256(city_path.read_bytes()).hexdigest() == expected_hash, 'A complete gzip City upload expands to the original MMDB bytes')
    expect(not list(city_path.parent.glob('.city-stage-*')), 'Finished and rejected geography uploads leave no staged file behind')
    expect(multipart(s.client, geography, {'action':'upload'}, 'city_database', 'City.mmdb', city_bytes, csrf='bad')[0] == 403, 'City upload requires CSRF')
    expect(s.client.request(geography, {'action':'remove','csrf':'bad'})[0] == 403 and city_path.is_file(), 'City removal requires CSRF and preserves existing bytes on rejection')

    # Exercise owner boundaries using the existing database fixture, then restore the role.
    s.database('editor')
    try:
        for path in [route, route+'live.php', geography]:
            expect(s.client.request(path)[0] == 403, 'Non-owner cannot read analytics or geography: '+path)
            expect(s.client.request(path, {'action':'query-clear','csrf':'bad'})[0] == 403, 'Non-owner cannot mutate analytics or geography: '+path)
    finally:
        s.database('owner')
    removed = s.client.form(geography, {'action':'remove'})
    expect(removed[0] == 200 and not city_path.exists() and admin_fixture(s, 'snapshot')['geography']['country_available'], 'Owner removal preserves included country reporting')
    expect(admin_fixture(s, 'snapshot')['config_digest'] == initial['config_digest'], 'All admin workflows preserve installation identity and credentials')

    admin_fixture(s, 'seed')
    today = query_values()['query_end']
    exported = s.client.form(route, {'action':'export','start':query_values()['query_start'],'end':today})
    expect(exported[0] == 200 and 'text/csv' in exported[2].get('Content-Type','') and 'attachment;' in exported[2].get('Content-Disposition','')
           and 'no-store' in exported[2].get('Cache-Control','') and 'IP Geolocation by DB-IP' in exported[1], 'Actual export is a private CSV with attribution and report sections')
    expect('daily' in exported[1] and 'crawlers' in exported[1] and 'United States' in exported[1], 'Export contains the real selected-period synthetic aggregates')


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root runner and disposable test database.')
    with tempfile.TemporaryDirectory(prefix='spirit-analytics-admin-') as directory:
        base = Path(directory); archive, code = build_package(base/'packages')
        for folder in ['', 'band']:
            with site(base/(folder or 'root'), archive, code, folder) as current:
                current.claim(); current.client.form('install/', current.input()); current.login(); journey(current)
    print('Analytics admin imports, reports, owner boundaries and City uploads passed at root and subfolder.', flush=True)


if __name__ == '__main__':
    main()
