#!/usr/bin/env python3
"""Capture owner analytics from an exact final ZIP using disposable synthetic data.

The archive is installed without modifying its runtime code. All fixture records,
imports and the live browser visit belong only to this temporary test installation.
"""
from __future__ import annotations
import argparse
import json
import os
import tempfile
import zipfile
from datetime import datetime, timezone
from pathlib import Path

from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import expect, site
from analytics_admin_integration import admin_fixture, multipart, query_values
from analytics_browser import browser_login, page_drilldowns
from capture_review import HUMAN_UA, digest, record


def element_record(page, output, name, route, viewport, selector):
    element = page.locator(selector)
    element.scroll_into_view_if_needed()
    destination = output / (name + '.png')
    element.screenshot(path=str(destination), animations='disabled')
    return {'file':destination.name, 'sha256':digest(destination), 'route':route,
            'viewport':viewport, 'full_page':False, 'element':selector,
            'page_title':page.title()}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive', required=True, type=Path,
                        help='The exact cumulative Full ZIP being delivered.')
    parser.add_argument('--output', required=True, type=Path)
    args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root runner and the disposable SQL test environment.')
    archive = args.archive.resolve()
    with zipfile.ZipFile(archive) as bundle:
        version = bundle.read('VERSION').decode().strip()
        expect(all(name in bundle.namelist() for name in [
            'admin/analytics/index.php', 'admin/analytics/geography.php',
            'admin/analytics/visitors.php', 'app/AnalyticsCharts.php',
            'assets/css/admin-analytics-overview.css',
            'database/migrations/007.sql', 'database/migrations/008.sql',
            'assets/css/admin-analytics.css', 'app/GeoData/country.mmdb',
        ]), 'The supplied final ZIP includes the complete analytics module')
    output = args.output.resolve()
    output.mkdir(parents=True, exist_ok=True)
    if list(output.glob('analytics-*-Synthetic.png')) or (output / 'analytics-capture-manifest.json').exists():
        raise FileExistsError('Choose an output folder without previous analytics captures.')

    manifest = {
        'version': version, 'package_name': archive.name, 'package_sha256': digest(archive),
        'captured_at_utc': datetime.now(timezone.utc).isoformat(),
        'synthetic_data_only': True,
        'data_notice': 'All audience history and Search Console counts are synthetic fixtures in a disposable installation. The live row is a test browser visiting that installation. These are not production analytics.',
        'runtime_code_modified': False, 'production_site_accessed': False,
        'device_scale_factor': 1, 'reduced_motion': 'reduce',
        'screenshots': [],
    }
    with tempfile.TemporaryDirectory(prefix='spirit-analytics-review-') as directory:
        with site(Path(directory) / 'website', archive, None) as current:
            current.claim()
            installed = current.client.form('install/', current.input())
            expect(installed[0] == 200 and 'id="install-complete"' in installed[1],
                   'The analytics capture site was installed from the supplied Full ZIP')
            current.login()
            admin_fixture(current, 'seed')
            queries = (b'Top queries,Clicks,Impressions,CTR,Position\n'
                       b'spiritual gorillaz,120,1200,10%,2.3\n'
                       b'live music lafayette,84,1400,6%,4.1\n'
                       b'blues band louisiana,36,720,5%,6.2\n')
            imported = multipart(current.client, 'admin/analytics/', query_values(),
                                 'queries', 'Queries.csv', queries)
            expect(imported[0] == 200 and 'Imported 3 Search Console queries' in imported[1],
                   'Synthetic query fixture was imported through the actual owner form')
            with sync_playwright() as engine:
                browser = engine.chromium.launch()
                manifest['browser'] = 'Chromium'
                manifest['browser_version'] = browser.version
                try:
                    visitors = browser.new_context(viewport={'width':1280, 'height':900},
                                                   user_agent=HUMAN_UA, reduced_motion='reduce')
                    visitor = visitors.new_page()
                    visitor.goto(current.base + 'music/?utm_source=synthetic-review', wait_until='networkidle')
                    browser_expect(visitor.locator('template[data-analytics]')).to_have_count(1)
                    context = browser.new_context(viewport={'width':1440, 'height':1000},
                                                  device_scale_factor=1, reduced_motion='reduce')
                    page = context.new_page(); errors = []
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    browser_login(page, current)
                    for width, height in [(1440,1000), (390,844), (320,844)]:
                        viewport = {'width':width, 'height':height}
                        page.set_viewport_size(viewport)
                        page.goto(current.base + 'admin/', wait_until='networkidle')
                        browser_expect(page.locator('[data-analytics-snapshot]')).to_be_visible()
                        manifest['screenshots'].append(record(page, output,
                            f'analytics-overview-{width}-Synthetic', 'admin/', viewport))
                        if width == 1440:
                            manifest['screenshots'].append(record(page, output,
                                'analytics-overview-1440-first-screen-Synthetic', 'admin/', viewport,
                                full_page=False))
                    page.set_viewport_size({'width':1440, 'height':1000})
                    response = page.goto(current.base + 'admin/analytics/?range=7', wait_until='networkidle')
                    expect(response.status == 200, 'Packaged owner analytics opens')
                    expect('v' + version in page.locator('.admin-build').inner_text(),
                           'The rendered owner sidebar shows the exact ZIP version')
                    browser_expect(page.locator('[data-live-count]')).to_have_text('1', timeout=20000)
                    browser_expect(page.locator('[data-live-rows]')).to_contain_text('/music/')
                    for width, height in [(1440,1000), (390,844), (320,844)]:
                        viewport = {'width':width, 'height':height}
                        page.set_viewport_size(viewport)
                        page.evaluate('scrollTo(0, 0)')
                        manifest['screenshots'].append(record(page, output,
                            f'analytics-{width}-full-Synthetic', 'admin/analytics/?range=7', viewport))
                        if width == 1440:
                            manifest['screenshots'].append(record(page, output,
                                'analytics-1440-overview-Synthetic', 'admin/analytics/?range=7', viewport,
                                full_page=False))
                    viewport = {'width':1440, 'height':1000}
                    page.set_viewport_size(viewport)
                    manifest['screenshots'].append(element_record(page, output,
                        'analytics-1440-live-panel-Synthetic', 'admin/analytics/?range=7', viewport,
                        '.analytics-live-panel'))
                    manifest['screenshots'].append(element_record(page, output,
                        'analytics-1440-traffic-charts-Synthetic', 'admin/analytics/?range=7', viewport,
                        '.analytics-primary-grid'))

                    response = page.goto(current.base + 'admin/analytics/visitors.php?range=7', wait_until='networkidle')
                    expect(response.status == 200, 'Packaged owner visitor explorer opens')
                    for width, height in [(1440,1000), (390,844), (320,844)]:
                        viewport = {'width':width, 'height':height}
                        page.set_viewport_size(viewport)
                        page.evaluate('scrollTo(0, 0)')
                        manifest['screenshots'].append(record(page, output,
                            f'analytics-visitors-{width}-Synthetic', 'admin/analytics/visitors.php?range=7', viewport))
                    sessions = admin_fixture(current, 'explore')['explorer']['visitors']
                    selected = next(row for row in sessions if row['page_count'] >= 3)
                    detail_route = 'admin/analytics/visitors.php?range=7&visit=' + str(selected['record_id'])
                    response = page.goto(current.base + detail_route, wait_until='networkidle')
                    expect(response.status == 200, 'Packaged owner anonymous session journey opens')
                    browser_expect(page.locator('#visit-detail .analytics-journey-card')).to_have_count(selected['retained_pageviews'])
                    for width, height in [(1440,1000), (390,844)]:
                        viewport = {'width':width, 'height':height}
                        page.set_viewport_size(viewport)
                        page.evaluate('scrollTo(0, 0)')
                        manifest['screenshots'].append(record(page, output,
                            f'analytics-visit-detail-{width}-Synthetic', detail_route, viewport))
                        manifest['screenshots'].append(element_record(page, output,
                            f'analytics-visit-detail-panel-{width}-Synthetic', detail_route, viewport,
                            '#visit-detail'))
                    response = page.goto(current.base + 'admin/analytics/geography.php', wait_until='networkidle')
                    expect(response.status == 200, 'Packaged owner geography opens')
                    for width, height in [(1440,1000), (390,844)]:
                        viewport = {'width':width, 'height':height}
                        page.set_viewport_size(viewport)
                        page.evaluate('scrollTo(0, 0)')
                        manifest['screenshots'].append(record(page, output,
                            f'analytics-geography-{width}-Synthetic', 'admin/analytics/geography.php', viewport))
                    # Preserve the baseline review images above, then show the
                    # exact-page drilldown correction using separate test data.
                    visitors.close()
                    detail_paths = admin_fixture(current, 'detail-paths-fixture')
                    page.set_viewport_size({'width':1440, 'height':1000})
                    page_drilldowns(page, current, detail_paths, 'Exact-ZIP review',
                                    [detail_paths['paths'][0]])
                    detail_route = page.url[len(current.base):]
                    for width, height in [(1440,1000), (390,844)]:
                        viewport = {'width':width, 'height':height}
                        page.set_viewport_size(viewport)
                        page.evaluate('scrollTo(0, 0)')
                        manifest['screenshots'].append(record(page, output,
                            f'analytics-page-filter-{width}-Synthetic', detail_route, viewport))
                    expect(errors == [], 'Captured analytics pages have no uncaught browser errors')
                    context.close()
                finally:
                    browser.close()
    (output / 'analytics-capture-manifest.json').write_text(json.dumps(manifest, indent=2) + '\n')
    print(f'Captured {len(manifest["screenshots"])} actual analytics screenshots for {version}.', flush=True)
    print('Synthetic data only. Manifest: ' + str(output / 'analytics-capture-manifest.json'), flush=True)


if __name__ == '__main__':
    main()
