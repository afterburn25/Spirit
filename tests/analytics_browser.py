#!/usr/bin/env python3
"""Rendered analytics owner controls with synthetic data and a real live browser visit."""
from __future__ import annotations
import json
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import Client, build_package, expect, site
from analytics_admin_integration import admin_fixture, multipart, query_values
from analytics_integration import fixture as collection_fixture
from browser_smoke import screenshot


def browser_login(page, s):
    page.goto(s.base+'admin/login.php')
    page.locator('[name=email]').fill(s.owner)
    page.locator('[name=password]').fill(s.password)
    page.locator('button[type=submit]').click()
    page.wait_for_url(lambda url: str(url).endswith(('/admin/', '/admin/index.php')))


def layout(page, label):
    page.wait_for_load_state('networkidle')
    expect(page.locator('h1').count() == 1, label+': exactly one primary heading')
    fits = page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1')
    if not fits:
        print(json.dumps(page.evaluate('''() => ({width:innerWidth,scroll:document.documentElement.scrollWidth,items:Array.from(document.querySelectorAll('main *')).filter(el=>!el.closest('.table-scroll') && el.getBoundingClientRect().right>innerWidth+1).slice(0,12).map(el=>({tag:el.tagName,cls:el.className,text:el.textContent.slice(0,70),right:el.getBoundingClientRect().right}))})''')), flush=True)
    expect(fits, label+': no horizontal page overflow')
    expect(page.locator('link[href*="admin-analytics.css"]').count() == 1, label+': analytics stylesheet is loaded')


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root runner and disposable database.')
    with tempfile.TemporaryDirectory(prefix='spirit-analytics-browser-') as directory, sync_playwright() as driver:
        base = Path(directory); archive, code = build_package(base/'packages')
        engine_name = os.environ.get('SPIRIT_BROWSER', 'chromium')
        browser = getattr(driver, engine_name).launch()
        try:
            for folder in ['', 'band']:
                with site(base/(folder or 'root'), archive, code, folder) as current:
                    current.claim(); current.client.form('install/', current.input()); current.login()
                    admin_fixture(current, 'seed')
                    query_csv = b'Top queries,Clicks,Impressions,CTR,Position\nspiritual gorillaz,120,1200,10%,2.3\nlive music lafayette,84,1400,6%,4.1\nblues band louisiana,36,720,5%,6.2\n'
                    multipart(current.client, 'admin/analytics/', query_values(), 'queries', 'Queries.csv', query_csv)
                    before = admin_fixture(current, 'snapshot')
                    context = browser.new_context(viewport={'width':1440,'height':960}, reduced_motion='reduce', accept_downloads=True)
                    page = context.new_page(); errors = []
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    browser_login(page, current)
                    # Navigate through the actual grouped administration link.
                    page.locator('[data-nav-link="analytics"]').click()
                    browser_expect(page.locator('.analytics-heading h1')).to_contain_text('listening')
                    page.locator('.analytics-presets a').filter(has_text='7 days').click()
                    for width in [1440, 390, 320]:
                        page.set_viewport_size({'width':width,'height':960})
                        layout(page, f'{engine_name} {folder or "root"} {width}px dashboard')
                        expect(page.locator('.analytics-stats .stat').count() == 6, 'All six report metrics render at '+str(width)+'px')
                        expect(page.locator('.analytics-trend .chart-visits').count() == 1
                               and page.locator('.analytics-trend .chart-pageviews').count() == 1, 'Both real trend series are rendered')
                        expect(page.locator('#search-console').inner_text().find('120') >= 0, 'Imported query metrics remain present on the page')
                        if not folder and width in [1440,390]:
                            screenshot(page, 'analytics-'+engine_name+'-'+str(width)+'-synthetic')
                    page.set_viewport_size({'width':1440,'height':960})
                    page.locator('.analytics-detail summary').filter(has_text='View daily data').click()
                    expect(page.locator('.analytics-chart-panel tbody tr').count() == 7, 'Daily table matches the selected inclusive seven-day filter')
                    page.locator('#analytics-start').fill(query_values()['query_start'])
                    page.locator('#analytics-end').fill(query_values()['query_end'])
                    page.locator('.analytics-date-form button').click()
                    browser_expect(page.locator('#analytics-start')).to_have_value(query_values()['query_start'])
                    with page.expect_download() as downloaded:
                        page.locator('button').filter(has_text='Export visitor CSV').click()
                    export = downloaded.value
                    expect(export.suggested_filename.endswith('.csv') and 'Spiritual-Gorillaz-analytics-' in export.suggested_filename, 'Owner export downloads a real CSV from the packaged form')

                    # Keep a separate unsigned-in browser on a real public page.
                    # HeadlessChrome is correctly reported as automation by the
                    # application. Use an ordinary browser UA for this human-visit fixture.
                    visitors = browser.new_context(viewport={'width':1280,'height':900}, user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36')
                    visit = visitors.new_page(); visit.goto(current.base+'music/?utm_source=browser-fixture')
                    browser_expect(visit.locator('template[data-analytics]')).to_have_count(1)
                    visit.wait_for_function('document.querySelector("template[data-analytics]") !== null')
                    page.reload(wait_until='networkidle')
                    browser_expect(page.locator('[data-live-count]')).to_have_text('1', timeout=20000)
                    browser_expect(page.locator('[data-live-rows]')).to_contain_text('/music/')
                    toggle = page.locator('[data-live-toggle]')
                    toggle.click(); browser_expect(toggle).to_have_attribute('aria-pressed','true')
                    browser_expect(page.locator('[data-live-status]')).to_contain_text('paused')
                    toggle.click(); browser_expect(toggle).to_have_attribute('aria-pressed','false')
                    browser_expect(page.locator('[data-live-status]')).to_contain_text('updated')
                    expect(True, 'Real visible visit reaches the live panel and pause/resume controls work')
                    entry_token = visit.locator('template[data-analytics]').get_attribute('data-token')
                    visit.evaluate('window.analyticsNavigationWitness = true')
                    # Owner-page interactions can leave this tab backgrounded.
                    # Measure actual visible time before testing its final sample.
                    visit.bring_to_front()
                    visit.wait_for_function('document.visibilityState === "visible"')
                    visit.wait_for_timeout(2200)
                    # The new page token is rendered before the departing page's
                    # asynchronous beacon commits. Await that beacon response;
                    # Chromium does not expose Blob request bodies to Playwright.
                    with visit.expect_response(lambda response:
                        response.url == current.base+'pages/analytics.php'
                        and response.request.method == 'POST'
                        and response.request.resource_type == 'ping'
                    ) as departed:
                        visit.locator('#public-nav a').filter(has_text='Shows').first.click()
                    expect(departed.value.status == 204, 'The departing page activity request completes successfully')
                    visit.wait_for_url('**/shows/')
                    browser_expect(visit.locator('template[data-analytics]')).not_to_have_attribute('data-token', entry_token)
                    expect(visit.evaluate('window.analyticsNavigationWitness === true'), 'Public navigation retains the document while installing a new analytics page token')
                    state = collection_fixture(current, 'state')['tables']
                    active = [row for row in state['analytics_visits'] if row['utm_source'] == 'browser-fixture']
                    expect(len(active) == 1 and int(active[0]['page_count']) == 2, 'AJAX navigation counts two public pages in the same real visitor session')
                    observed = [row for row in state['analytics_pageviews'] if int(row['visit_id']) == int(active[0]['id']) and row['page_path'] == '/music/']
                    expect(len(observed) == 1 and int(observed[0]['active_seconds']) >= 1, 'The departing AJAX page flushes its observed visible time')
                    if not folder: screenshot(page, 'analytics-'+engine_name+'-live-synthetic')
                    visitors.close()

                    page.locator('a').filter(has_text='Manage location data').click()
                    for width in [1440,390,320]:
                        page.set_viewport_size({'width':width,'height':960})
                        layout(page, f'{engine_name} {folder or "root"} {width}px geography')
                        expect(page.locator('input[name=city_database]').count() == 1, 'Geography route has the actual file control')
                        if not folder and width in [1440,390]: screenshot(page, 'analytics-'+engine_name+'-geography-'+str(width))
                    expect(not errors, 'Analytics and geography pages have no uncaught JavaScript errors')
                    context.close()

                    # The reports and owner forms must also work with scripting disabled.
                    native = browser.new_context(viewport={'width':390,'height':960}, java_script_enabled=False)
                    native_page = native.new_page(); browser_login(native_page, current)
                    native_page.goto(current.base+'admin/analytics/?range=7')
                    layout(native_page, 'No-JavaScript analytics')
                    expect(native_page.locator('[data-live-toggle]').is_hidden(), 'No-JavaScript report does not advertise an inoperative live button')
                    expect(native_page.locator('.analytics-trend').count() == 1 and native_page.locator('#analytics-retention').is_enabled(), 'Native charts and analytics settings remain usable without JavaScript')
                    native_page.locator('.analytics-settings-form [name=enabled]').uncheck()
                    native_page.locator('.analytics-settings-form button').click()
                    browser_expect(native_page.locator('.notice').filter(has_text='Visitor collection is paused')).to_be_visible()
                    expect('Visitor collection is paused' in native_page.content(), 'Collection can be paused with the native owner form')
                    native.close()
                    after = admin_fixture(current, 'snapshot')
                    expect(after['queries'] == before['queries'] and after['config_digest'] == before['config_digest'], 'Rendered controls preserve query snapshot and installation configuration')
        finally:
            browser.close()
    print('Rendered analytics dashboard, live visitors, CSV, geography and native forms passed.', flush=True)


if __name__ == '__main__':
    main()
