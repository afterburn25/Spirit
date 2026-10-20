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


def admin_fixture(s, action, values=None):
    env = {**s.env, 'SPIRIT_FIXTURE_ROOT': str(s.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(s.docroot)}
    result = subprocess.run(['php', str(ROOT / 'tests/analytics_admin_fixture.php'), action], env=env, input=json.dumps(values or {}),
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
    visitors = route + 'visitors.php'
    anon = Client(s.base)
    for path in [route, route+'live.php', geography, visitors]:
        result = anon.request(path)
        expect(result[3].endswith('/admin/login.php'), 'Anonymous analytics route requires owner sign-in: '+path)
    page = s.client.request(route)
    expect(page[0] == 200 and 'analytics-heading' in page[1] and 'no-store' in page[2].get('Cache-Control', '')
           and 'noindex' in page[2].get('X-Robots-Tag', ''), 'Actual owner dashboard is available, private and unindexed')
    expect('admin/analytics/' in page[1] and 'data-live-url' in page[1], 'Admin navigation and live endpoint are wired')
    for path in [route, route+'live.php', geography, visitors]:
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
        for path in [route, route+'live.php', geography, visitors]:
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
    visitor_journey(s, visitors)


def visitor_journey(s, route):
    first = admin_fixture(s, 'explore')
    aggregate, explorer = first['report'], first['explorer']
    expect(aggregate['available'] and explorer['available'], 'Expanded chart aggregates and visitor query execute on the packaged database')
    expect(explorer['total'] == aggregate['summary']['visits'] == 67 and len(explorer['visitors']) == 25
           and explorer['pages'] == 3, 'Explorer paginates the complete selected-date session cohort at 25 visits')
    second = admin_fixture(s, 'explore', {'filters': {'page': '2'}})['explorer']
    expect(not ({row['record_id'] for row in explorer['visitors']} & {row['record_id'] for row in second['visitors']}), 'Stable visitor pagination has no repeated records across adjacent pages')
    third = admin_fixture(s, 'explore', {'filters': {'page': '999'}})['explorer']
    expect(third['page'] == 3 and len(third['visitors']) == 17, 'Out-of-range pagination clamps to the final bounded result page')
    expect(sum(row['pageviews'] for row in aggregate['hourly']) == aggregate['summary']['pageviews']
           and len(aggregate['hourly']) == 24 and len(aggregate['heatmap']) == 168
           and sum(row['pageviews'] for row in aggregate['heatmap']) == aggregate['summary']['pageviews'], 'Hourly and weekday heatmap buckets fill all UTC slots and conserve actual pageview totals')
    for key in ['channels', 'depth', 'engagement', 'entry_pages']:
        expect(sum(row['visits'] for row in aggregate[key]) == 67, key + ' accounts for the selected visit cohort')
    expect(sum(row['visits'] for row in aggregate['exit_pages']) == aggregate['summary']['completed_visits'], 'Exit pages include completed sessions only')
    listing = s.client.request(route + '?range=7')
    expect(listing[0] == 200 and listing[1].count('data-visitor-id=') == 25
           and 'Next visits' in listing[1] and 'no-store' in listing[2].get('Cache-Control', '')
           and 'noindex' in listing[2].get('X-Robots-Tag', ''), 'Actual explorer route is private, indexed by session and natively paginated')
    expect(request(s.client, route, headers={'Sec-Fetch-Site': 'cross-site'})[0] == 403,
           'Cross-site fetch metadata cannot read detailed visitor sessions')
    expect(s.client.request(route, {'action':'unknown'})[0] == 405, 'Visitor explorer has no implicit POST mutation action')

    fixture = admin_fixture(s, 'explorer-fixture')
    scope = {'start': fixture['start'], 'end': fixture['end']}
    ids = fixture['ids']
    def explore(filters=None):
        return admin_fixture(s, 'explore', {**scope, 'filters': filters or {}})
    def url(filters=None):
        return route + '?' + urllib.parse.urlencode({**scope, **(filters or {})})
    state = explore(); aggregate = state['report']; explorer = state['explorer']
    expect(explorer['total'] == 6 and aggregate['summary']['visits'] == 6, 'A session entering before the date range qualifies only through a page inside the range')
    channel_counts = {row['key']: row['visits'] for row in aggregate['channels']}
    expect(channel_counts['email'] == 1 and channel_counts['paid'] == 1 and channel_counts['organic'] == 1
           and channel_counts['social'] == 1 and channel_counts['referral'] == 1 and channel_counts['campaign'] == 1,
           'Derived channels respect campaign medium and exact search/social host boundaries')
    host_cases = {'www.google.co.uk': 'organic', 'google.ca': 'organic', 'google.com.au': 'organic',
                  'www.bing.com': 'organic', 'search.yahoo.com': 'organic', 'yandex.ru': 'organic',
                  'google.com.evil': 'referral', 'google.evil': 'referral', 'yandex.com.evil': 'referral',
                  'evilgoogle.com': 'referral', 'facebook.com.evil': 'referral', '': 'direct'}
    expect(admin_fixture(s, 'classify-sources', {'sources': list(host_cases)}) == host_cases,
           'Supported search domains classify correctly and short provider lookalikes remain referrals')
    expect(next(row['visits'] for row in aggregate['engagement'] if row['label'] == 'Unmeasured') == 1,
           'Unmeasured activity has its own engagement bucket rather than a zero-duration assertion')
    selected = explore({'visit': str(ids['journey'])})['explorer']
    expect(selected['selected']['record_id'] == ids['journey'] and len(selected['journey']) == 3
           and selected['journey'][0]['page_path'] == '/' and selected['journey'][0]['in_period'] is False
           and selected['journey'][1]['page_path'] == '/music/' and selected['journey'][1]['in_period'] is True,
           'Session drill-down retains chronological history and labels pages outside the chosen dates')
    expect(selected['selected']['search_term'] == 'live blues' and selected['selected']['utm_source'] == 'newsletter'
           and selected['selected']['os'] == 'Windows' and selected['selected']['city'] == 'Lafayette',
           'Drill-down exposes retained attribution, client and approximate location fields')
    details = s.client.request(url({'visit': str(ids['journey'])}))
    expect(details[0] == 200 and 'Session journey' in details[1] and 'live blues' in details[1]
           and 'Outside selected dates' in details[1] and 'newsletter' in details[1], 'Actual native detail route renders the selected session and page journey')
    expect(hashlib.sha256(b'explorer-journey').hexdigest() not in details[1] and 'visit_key' not in details[1],
           'Session details never disclose the internal visit hash')
    missing = s.client.request(url({'visit': str(ids['outside'])}))
    expect(missing[0] == 404 and 'not available within these filters' in missing[1], 'An out-of-cohort session cannot bypass date scope through its ID')
    filtered = explore({'country': 'US', 'browser': 'Chrome', 'os': 'Windows', 'device': 'Desktop',
                        'region': 'Louisiana', 'city': 'Lafayette', 'channel': 'email', 'path': '/music/'})['explorer']
    expect(filtered['total'] == 1 and filtered['visitors'][0]['record_id'] == ids['journey'], 'Combined client, location, acquisition and page filters select the actual matching visit')
    expect(explore({'source': "' OR 1=1 --"})['explorer']['total'] == 0, 'Referrer filter input is a bound exact value and cannot alter SQL predicates')
    for status, expected in [('live', {ids['live']}), ('recent', {ids['recent']}),
                             ('completed', {ids['journey'], ids['unmeasured'], ids['lookalike'], ids['long']}),
                             ('bounced', {ids['unmeasured'], ids['lookalike']})]:
        rows = explore({'status': status})['explorer']['visitors']
        expect({row['record_id'] for row in rows} == expected, 'Visitor status filter has the correct live/idle/completion boundary: ' + status)
    unmeasured = explore({'visit': str(ids['unmeasured'])})['explorer']
    expect(unmeasured['selected']['measured_seconds'] is None and unmeasured['journey'][0]['measured'] is False,
           'Visitor details and per-page journey preserve missing-timing coverage')
    escaped = s.client.request(url({'visit': str(ids['unmeasured'])}))[1]
    expect('<script>alert(1)</script>' not in escaped and '&lt;script&gt;alert(1)&lt;/script&gt;' in escaped,
           'Retained attribution is escaped in visitor detail output')
    later = explore({'visit': str(ids['long']), 'journey_page': '2'})['explorer']
    expect(later['journey_total'] == 105 and later['journey_pages'] == 2 and len(later['journey']) == 5
           and later['journey'][0]['sequence'] == 101, 'Long page journeys paginate without dropping or inventing retained events')
    for values in [{'country':'USA'}, {'channel':'untrusted'}, {'status':'expired'}, {'page':'-1'},
                   {'visit':'1 OR 1=1'}, {'path':'/music/?secret=private'}, {'browser[]':'Chrome'}]:
        expect(s.client.request(url(values))[0] == 400, 'Malformed visitor filter receives a controlled rejection: ' + next(iter(values)))


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
