#!/usr/bin/env python3
"""Rendered inquiry submission and owner communication screens across launch widths."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from public_browser import screenshot


def rendered(page, label):
    expect(page.locator('h1').count() == 1 and page.locator('h1').is_visible(), label + ': primary heading visible')
    expect(page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1'), label + ': no horizontal overflow')
    expect(page.locator('input[type="password"][value]').count() == 0, label + ': password is not reflected in source')


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-communication-browser-') as directory:
        base = Path(directory); archive, code = build_package(base / 'packages')
        with site(base / 'website', archive, code, 'band') as current:
            current.claim(); current.client.form('install/', current.input())
            with sync_playwright() as engine:
                browser = engine.chromium.launch()
                for width, height in [(1440, 1000), (390, 844), (360, 800)]:
                    context = browser.new_context(viewport={'width': width, 'height': height}, reduced_motion='reduce', bypass_csp=False)
                    page = context.new_page(); errors = []
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    page.goto(current.base + 'booking/', wait_until='networkidle')
                    page.locator('[name="name"]').fill('Browser fixture organizer')
                    page.locator('[name="email"]').fill('browser@example.test')
                    page.locator('[name="location"]').fill('Synthetic venue, Louisiana')
                    page.locator('[name="message"]').fill('Isolated browser test, not a real booking.')
                    page.locator('[name="contact_consent"]').check()
                    page.get_by_role('button', name='Send inquiry').click()
                    page.wait_for_url('**/booking/thanks.php')
                    rendered(page, f'{width}px inquiry receipt')
                    expect(page.locator('.reference-number strong').inner_text().startswith('SG-'), 'Browser receives a real saved inquiry reference')
                    page.goto(current.base + 'admin/login.php')
                    page.locator('[name="email"]').fill(current.owner)
                    page.locator('[name="password"]').fill(current.password)
                    page.get_by_role('button', name='Enter backstage').click()
                    page.wait_for_url('**/admin/')
                    for route in ['admin/bookings/', 'admin/communication/', 'admin/mail/', 'admin/subscribers/']:
                        response = page.goto(current.base + route, wait_until='networkidle')
                        expect(response.status == 200 and 'no-store' in response.headers.get('cache-control', ''), f'{width}px private owner screen: ' + route)
                        rendered(page, f'{width}px ' + route)
                        screenshot(page, f'communication-{width}-' + route.strip('/').replace('/', '-'))
                    page.goto(current.base + 'admin/bookings/')
                    page.locator('a').filter(has_text='Open →').first.click()
                    page.locator('[name="status"]').select_option('discussing')
                    page.locator('[name="private_notes"]').fill('Browser-tested private follow-up note.')
                    page.get_by_role('button', name='Save inquiry details').click()
                    expect(page.locator('[name="private_notes"]').input_value() == 'Browser-tested private follow-up note.', 'Rendered owner form persists private notes')
                    rendered(page, f'{width}px booking review')
                    screenshot(page, f'communication-{width}-booking-review')
                    expect(errors == [], f'{width}px communication browser journey has no uncaught JavaScript errors')
                    context.close()
                browser.close()
    print('Communication browser submission and owner screens passed at 1440px, 390px and 360px.', flush=True)


if __name__ == '__main__':
    main()
