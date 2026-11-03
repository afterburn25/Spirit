#!/usr/bin/env python3
"""Real packaged content pagination/history; native forms, no provider calls.

Count only the content-results region and await its destination and exact data.
A page-wide, immediate count can inspect the previous navigation or other tables.
Every round must pass; this suite does not retry failed tests or relax row limits.
"""
from __future__ import annotations

import os
import tempfile
from pathlib import Path
from urllib.parse import parse_qs, urlsplit

from playwright.sync_api import Page, expect as be, sync_playwright

from http_integration import Client, build_package, dashboard, expect, site
from content_history_integration import seed_story, fixture, snapshot
from public_browser import screenshot


def listing(page: Page, total: int, number: int, titles: list[str]) -> None:
    """Observe the server-rendered result, not an unrelated table or old page."""
    region = page.get_by_role('region', name='Content results', exact=True)
    be(region).to_be_visible()
    be(region.locator('tbody tr')).to_have_count(len(titles))
    be(region.locator('tbody tr td:first-child strong')).to_have_text(titles)
    be(page.locator('.content-filters + p[role=status]')).to_have_text(
        f'{total} matching item(s) · Page {number} of {(total + 24) // 25 or 1}'
    )
    expect(True, f'Content page {number} has exactly {len(titles)} expected records of {total}')


def at_filter(url: str, query: str, number: int) -> bool:
    parsed = urlsplit(str(url))
    values = parse_qs(parsed.query, keep_blank_values=True)
    return (parsed.path.endswith('/admin/content/') and
            values.get('kind') == ['page'] and values.get('q') == [query] and
            values.get('page', ['1']) == [str(number)] and
            values.get('sort') == ['title'] and values.get('state') == ['all'])


def apply_filter(page: Page, query: str) -> None:
    page.get_by_label('Search title or URL name').fill(query)
    page.get_by_label('Sort by').select_option('title')
    page.get_by_label('Visibility').select_option('all')
    page.get_by_role('button', name='Apply filters', exact=True).click()
    page.wait_for_url(lambda url: at_filter(str(url), query, 1), wait_until='domcontentloaded')


def browse(page: Page, label: str) -> None:
    apply_filter(page, 'Large library')
    first = [f'Large library {i:04d}' for i in range(25)]
    last = [f'Large library {i:04d}' for i in range(25, 31)]
    listing(page, 31, 1, first)
    expect(page.get_by_role('region', name='Content results', exact=True).locator('tbody tr').count() == 25,
           'Rendered content results are bounded to 25 rows')
    navigation = page.get_by_role('navigation', name='Content result pages', exact=True)
    # Repeated round trips are mandatory assertions, not retry-on-failure logic.
    for cycle in range(3):
        be(navigation.locator('[rel=prev]')).to_have_count(0)
        navigation.get_by_role('link', name='Next').click()
        page.wait_for_url(lambda url: at_filter(str(url), 'Large library', 2), wait_until='domcontentloaded')
        listing(page, 31, 2, last)
        be(navigation.locator('[rel=next]')).to_have_count(0)
        expect(True, f'{label}: next page retains all filters and the six remaining records, round {cycle + 1}')
        navigation.get_by_role('link', name='Previous').click()
        page.wait_for_url(lambda url: at_filter(str(url), 'Large library', 1), wait_until='domcontentloaded')
        listing(page, 31, 1, first)
        expect(True, f'{label}: previous page restores the exact first 25 records, round {cycle + 1}')
    page.reload(wait_until='domcontentloaded')
    listing(page, 31, 1, first)
    apply_filter(page, 'No matching synthetic history record')
    region = page.get_by_role('region', name='Content results', exact=True)
    be(region.get_by_role('heading', name='No matching content.', exact=True)).to_be_visible()
    be(region.locator('tbody tr td:first-child strong')).to_have_count(0)
    be(navigation.get_by_role('link')).to_have_count(0)
    be(page.locator('.content-filters + p[role=status]')).to_have_text('0 matching item(s) · Page 1 of 1')
    expect(True, f'{label}: empty search is not miscounted as a saved record')
    apply_filter(page, 'Large library')
    listing(page, 31, 1, first)
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), f'Content search fits {label}')


def restore_in_browser(page: Page, s, item_id: int, revision: int, expected_title: str, label: str) -> None:
    before = snapshot(s, item_id)
    response = page.goto(s.base + f'admin/content/history.php?id={item_id}&revision={revision}', wait_until='domcontentloaded')
    expect(response is not None and response.status == 200 and
           'no-store' in response.headers.get('cache-control', '') and
           response.headers.get('x-robots-tag') == 'noindex, nofollow', 'Browser revision screen is private and unindexed')
    # The page schema now includes headline, CTA and additional section slots.
    be(page.locator('.revision-field')).to_have_count(19)
    expect(True, 'Revision comparison renders every declared field')
    expect(snapshot(s, item_id) == before, 'Opening a saved comparison cannot mutate the draft or history')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), f'Revision comparison fits {label} without clipping')
    if revision == 1:
        screenshot(page, f'history-{label}-comparison')
    page.get_by_role('button', name='Restore as private draft', exact=True).click()
    page.wait_for_url(s.base + f'admin/content/edit.php?kind=page&id={item_id}', wait_until='domcontentloaded')
    be(page.locator('[name="title"]')).to_have_value(expected_title)
    be(page.locator('body')).to_contain_text('restored as a new private draft')
    after = snapshot(s, item_id)
    expect(after['row']['title'] == expected_title and
           after['row']['revision'] == before['row']['revision'] + 1 and
           len(after['history']) == len(before['history']) + 1 and
           after['history'][-1]['action'] == 'restore', 'Browser restore creates one new selected private revision')
    expect(after['row']['published_json'] == before['row']['published_json'] and
           after['row']['published_at'] == before['row']['published_at'], 'Browser restore never replaces the published snapshot')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), f'Restored editor fits {label}')


def main() -> None:
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable database required.')
    name = os.environ.get('SPIRIT_BROWSER', 'chromium')
    if name not in ('chromium', 'firefox', 'webkit'):
        raise RuntimeError('Unsupported browser engine.')
    with tempfile.TemporaryDirectory(prefix='spirit-history-browser-') as directory:
        base = Path(directory)
        archive, code = build_package(base / 'packages')
        with sync_playwright() as engine:
            browser = getattr(engine, name).launch()
            try:
                for subfolder in ('', 'band'):
                    location = subfolder or 'root'
                    with site(base / location, archive, code, subfolder) as s:
                        s.claim(); s.client.form('install/', s.input()); s.login()
                        item_id, old_image, new_image = seed_story(s)
                        expect(fixture(s, 'seed', {'kind': 'page', 'count': 31}) == {'count': 31}, 'Exactly 31 synthetic pagination records prepared')
                        anonymous = Client(s.base)
                        public_before = anonymous.request('pages/?slug=history-story')[1]
                        for width, height in ((1440, 1000), (390, 844), (320, 844)):
                            for javascript in (True, False):
                                label = f'{name}-{location}-{width}-js{int(javascript)}'
                                context = browser.new_context(viewport={'width': width, 'height': height},
                                                              reduced_motion='reduce', bypass_csp=False,
                                                              java_script_enabled=javascript)
                                page = context.new_page()
                                errors = []
                                page.on('pageerror', lambda error: errors.append(str(error)))
                                try:
                                    page.goto(s.base + 'admin/login.php')
                                    page.locator('[name="email"]').fill(s.owner)
                                    page.locator('[name="password"]').fill(s.password)
                                    page.get_by_role('button', name='Enter backstage', exact=True).click()
                                    page.wait_for_url(lambda url: dashboard(str(url)))
                                    page.goto(s.base + 'admin/content/?kind=page', wait_until='domcontentloaded')
                                    before = snapshot(s, item_id)
                                    browse(page, label)
                                    expect(snapshot(s, item_id) == before, 'Filter, pagination and reload preserve content/history/media references')
                                    if javascript:
                                        screenshot(page, f'history-{label}-search')
                                    # Retain the original revision-2 restore and exercise a
                                    # different old image privately with revision 1 as well.
                                    restore_in_browser(page, s, item_id, 2, 'History Beta', label)
                                    restore_in_browser(page, s, item_id, 1, 'History Alpha', label)
                                    if javascript:
                                        screenshot(page, f'history-{label}-restored-editor')
                                    expect(anonymous.request('pages/?slug=history-story')[1] == public_before,
                                           'Anonymous published page stays byte-identical after browser restoration')
                                    expect(anonymous.request(f'media/file.php?id={old_image}')[0] == 404 and
                                           anonymous.request(f'media/file.php?id={new_image}')[0] == 200,
                                           'Restored old artwork remains private while published artwork remains available')
                                    expect(errors == [], f'No uncaught script errors in {label} revision workflow')
                                except Exception:
                                    # Only synthetic test screens; never dump cookies,
                                    # credentials, configuration or real customer data.
                                    try:
                                        screenshot(page, f'history-{label}-failed')
                                    except Exception:
                                        print('Failure screenshot unavailable; preserving the original test failure.', flush=True)
                                    raise
                                finally:
                                    context.close()
            finally:
                browser.close()
    print(f'{name}: content history passed at 1440/390/320px, root/subfolder, with and without JavaScript.', flush=True)


if __name__ == '__main__':
    main()
