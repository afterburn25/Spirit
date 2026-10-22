#!/usr/bin/env python3
"""Privacy, activity, attribution and retention using a real disposable PHP/MySQL site."""
from __future__ import annotations
import argparse
import html
import json
import os
import re
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site


def fixture(s, action, values=None):
    env = {**s.env, 'SPIRIT_FIXTURE_ROOT': str(s.root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(s.docroot)}
    result = subprocess.run(['php', str(ROOT / 'app/tests/analytics_fixture.php'), action], input=json.dumps(values or {}), env=env, capture_output=True, text=True, timeout=45)
    if result.returncode:
        raise AssertionError('Analytics fixture failed: ' + action + ' ' + result.stderr)
    return json.loads(result.stdout)


def request(client, path, body=None, headers=None, method=None, follow_redirects=True):
    raw = None if body is None else (body if isinstance(body, bytes) else json.dumps(body).encode())
    req = urllib.request.Request(client.base + path, raw, headers or {}, method=method)
    opener = client.opener
    if not follow_redirects:
        class NoRedirect(urllib.request.HTTPRedirectHandler):
            def redirect_request(self, req, fp, code, msg, headers, newurl):
                return None
        opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(client.cookies), NoRedirect())
    try:
        response = opener.open(req, timeout=30)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        return response.status, response.read().decode('utf-8', errors='replace'), response.headers, response.url


def page_token(body):
    tag = re.search(r'<template\b[^>]*\bdata-analytics(?:\s|=|>)[^>]*>', body)
    if tag is None:
        raise AssertionError('Actual public page did not render its analytics metadata')
    token = re.search(r'data-token=["\']([a-f0-9]{64})["\']', tag.group(0))
    if token is None:
        raise AssertionError('Page has no bounded random collector token')
    return token.group(1)


def metrics(s):
    return fixture(s, 'state')['tables']


def journey(s):
    fixture(s, 'clear')
    visitor = Client(s.base)
    status, body, _, _ = request(visitor, '?utm_source=fixture&utm_medium=email&utm_campaign=launch&email=PRIVATE-NEVER-STORED', headers={'Referer': 'https://www.google.com/search?q=live+music&token=PRIVATE-NEVER-STORED', 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/140.0 Safari/537.36'})
    token = page_token(body)
    expect(status == 200 and len(metrics(s)['analytics_pageviews']) == 1, 'Initial actual PHP page counts without executing JavaScript')
    data = metrics(s)['analytics_visits'][0]
    expect(data['referrer_domain'] == 'google.com' and data['search_term'] == 'live music' and data['utm_campaign'] == 'launch', 'Real request captures only supplied sanitized acquisition fields')
    expect('PRIVATE-NEVER-STORED' not in json.dumps(metrics(s)), 'Arbitrary customer/token-like query fields never reach analytics storage')
    origin = urllib.parse.urlunsplit((*urllib.parse.urlsplit(s.base)[:2], '', '', ''))
    headers = {'Origin': origin, 'Sec-Fetch-Site': 'same-origin', 'Content-Type': 'application/json'}
    sample = {'token': token, 'active_seconds': 0, 'visible': True}
    expect(request(visitor, 'pages/analytics.php', sample, headers)[0] == 204, 'Same-origin token-bound JSON heartbeat is accepted')
    expect(fixture(s, 'state')['live']['active_visitors'] == 1, 'Visible heartbeat appears in approximate live sessions')
    before = metrics(s)
    expect(request(visitor, 'pages/analytics.php', sample, {**headers, 'Origin': 'https://other.example.test'})[0] == 403, 'A different Origin cannot mutate analytics')
    expect(request(visitor, 'pages/analytics.php', sample, {'Content-Type': 'application/json'})[0] == 403, 'Collector requires a supplied matching Origin')
    expect(request(visitor, 'pages/analytics.php', sample, {**headers, 'Sec-Fetch-Site': 'cross-site'})[0] == 403, 'Cross-site fetch metadata cannot bypass the Origin check')
    expect(request(visitor, 'pages/analytics.php', {**sample, 'referrer': 'private'}, headers)[0] == 400, 'Collector rejects extra fields rather than storing arbitrary telemetry')
    expect(request(visitor, 'pages/analytics.php', {**sample, 'active_seconds': '15'}, headers)[0] == 400, 'Collector rejects string activity values')
    expect(request(visitor, 'pages/analytics.php', b'x' * 2048, headers)[0] == 413, 'Collector bounds the complete request body')
    expect(request(visitor, 'pages/analytics.php', b'{broken', headers)[0] == 400, 'Malformed JSON receives a controlled response')
    expect(request(visitor, 'pages/analytics.php', sample, {**headers, 'Content-Type': 'application/x-www-form-urlencoded'})[0] == 415, 'Unsupported collector payload encoding is rejected')
    expect(request(visitor, 'pages/analytics.php')[0] == 405, 'GET cannot send collector activity')
    outsider = Client(s.base)
    expect(request(outsider, 'pages/analytics.php', sample, headers)[0] == 204 and metrics(s) == before, 'Captured page token cannot mutate another browser session')
    expect(request(visitor, 'pages/analytics.php', sample, {**headers, 'DNT': '1'})[0] == 204 and metrics(s) == before, 'Do Not Track also stops activity updates')
    expect(request(visitor, 'pages/analytics.php', {**sample, 'visible': False}, headers)[0] == 204 and fixture(s, 'state')['live']['active_visitors'] == 0, 'Visibility exit removes this browser session from live view')
    for _ in range(20):
        request(visitor, 'pages/analytics.php', sample, headers)
    expect(int(metrics(s)['analytics_pageviews'][0]['activity_samples']) <= 12, 'A token cannot submit an unbounded heartbeat burst')

    before = metrics(s)
    for extra in [{'DNT': '1'}, {'Sec-GPC': '1'}, {'Purpose': 'prefetch'}, {'Sec-Purpose': 'prefetch;prerender'}]:
        status, body, _, _ = request(Client(s.base), 'music/', headers=extra)
        expect(status == 200 and 'data-analytics' not in body, 'Privacy and prefetch request has no collector metadata')
    request(Client(s.base), 'music/', method='HEAD')
    expect(metrics(s) == before, 'DNT, GPC, prefetch and HEAD create no analytics rows')
    owner_page = s.client.request('music/')[1]
    expect('data-analytics' not in owner_page and metrics(s) == before, 'Owner preview of the public site is excluded')
    receipt_destination = None
    for route in ['pages/search.php?q=PRIVATE-QUERY', 'account/login.php', 'shop/cart.php', 'booking/thanks.php']:
        # A missing receipt redirects to the public booking form; inspect the
        # private response itself instead of counting that later public GET.
        private_status, private_body, private_headers, _ = request(Client(s.base), route, follow_redirects=False)
        expect('data-analytics' not in private_body, 'Private route does not render a collector token: ' + route.split('?')[0])
        if route == 'booking/thanks.php':
            receipt_destination = urllib.parse.urljoin(s.base, private_headers.get('Location', ''))
            expect(private_status in (302, 303) and receipt_destination == s.base + 'booking/', 'Missing private receipt redirects only to the public booking form')
    expect(metrics(s) == before, 'Private account/cart/receipt/search pages create no analytics rows')
    status, public_body, _, _ = request(Client(s.base), receipt_destination[len(s.base):], headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/140.0 Safari/537.36'})
    expect(status == 200 and page_token(public_body) and len(metrics(s)['analytics_pageviews']) == len(before['analytics_pageviews']) + 1, 'A later public booking destination counts its own pageview without recording the private receipt route')
    expect(all(row['page_path'] != '/booking/thanks.php' for row in metrics(s)['analytics_pageviews']), 'Receipt route remains absent after following its public destination')
    before = metrics(s)

    crawler = Client(s.base)
    for ua in ['Googlebot/2.1 (+http://www.google.com/bot.html)', 'bingbot/2.0 (+http://www.bing.com/bingbot.htm)', 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)']:
        status, body, _, _ = request(crawler, 'music/', headers={'User-Agent': ua})
        expect(status == 200 and 'data-analytics' not in body, 'Crawler is observed server-side without a human JS token')
    after = metrics(s)
    expect(len(after['analytics_visits']) == len(before['analytics_visits']) and len(after['analytics_crawlers']) == 3, 'Google, Bing and Yahoo crawler hits remain separate from human totals')
    expect(all(row['verification'] == 'reported_ua' for row in after['analytics_crawlers']), 'Loopback crawler claims are not marked verified Google/Bing origins')
    fixture(s, 'settings', {'enabled': '0'})
    before = metrics(s); request(Client(s.base), 'shows/')
    expect(metrics(s) == before, 'Owner pause prevents all new measurements')
    fixture(s, 'settings', {'enabled': '1'})

    expect(Client(s.base).request('admin/analytics/')[3].endswith('/admin/login.php'), 'Anonymous visitors cannot open the analytics dashboard')
    expect(s.client.request('admin/analytics/', {'action': 'save', 'csrf': 'invalid'})[0] == 403, 'Analytics settings route enforces CSRF')
    s.database('editor')
    expect(s.client.request('admin/analytics/')[0] == 403, 'Content editors cannot read owner-only analytics')
    s.database('owner')
    fixture(s, 'break')
    try:
        status, body, _, _ = request(Client(s.base), 'music/')
        expect(status == 200 and 'data-analytics' not in body, 'Unavailable analytics database table does not break a public page')
    finally:
        fixture(s, 'restore')


def migration(s):
    pending = [path for path in (s.root / 'config/database/migrations/007.sql', s.root / 'config/database/migrations/008.sql') if path.exists()]
    sources = [(path, path.read_bytes()) for path in pending]
    for path in pending:
        path.unlink()
    s.claim(); s.client.form('install/', s.input()); s.login()
    expect(Client(s.base).request('music/')[0] == 200, 'Previous schema continues with analytics unavailable')
    for path, source in sources:
        path.write_bytes(source)
    expect(s.client.request('admin/analytics/')[3].endswith('/admin/updates.php'), 'Owner is directed to the additive analytics schema update')
    expect(s.client.form('admin/updates.php', {})[0] == 200, 'Existing installation applies the additive analytics migration')
    expect(s.database('count')['users'] == 1 and fixture(s, 'state')['settings']['enabled'], 'Analytics migration retains the existing owner and enables the requested measurements')
    expect(s.client.form('admin/updates.php', {})[0] == 200, 'Applying the same analytics migration again is harmless')


def main():
    parser = argparse.ArgumentParser(); parser.add_argument('--model-only', action='store_true'); args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable SQL environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-analytics-') as directory:
        base = Path(directory); archive, code = build_package(base / 'packages')
        for folder, label in [('', 'root'), ('band', 'subfolder')]:
            with site(base / label, archive, code, folder) as current:
                current.claim(); current.client.form('install/', current.input()); current.login()
                result = fixture(current, 'exercise')
                for check in result['checks']:
                    expect(True, check + ' (' + label + ')')
                if not args.model_only:
                    journey(current)
        if not args.model_only:
            with site(base / 'upgrade', archive, code, 'music-site') as current:
                migration(current)
    print('Analytics SQL model checks passed.' if args.model_only else 'Analytics SQL/HTTP privacy, reporting and retention checks passed.', flush=True)


if __name__ == '__main__':
    main()
