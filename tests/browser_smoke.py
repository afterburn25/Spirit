#!/usr/bin/env python3
"""Chromium smoke checks of actual installer/admin pages; not visual acceptance."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, site, expect, dashboard

def layout(page, label):
    page.wait_for_load_state('networkidle')
    expect(page.evaluate('document.styleSheets.length > 0'), label + ': stylesheet loaded')
    expect(page.evaluate('document.documentElement.scrollWidth <= window.innerWidth + 1'), label + ': no horizontal page overflow')
    expect(page.locator('h1').count() == 1, label + ': main heading present')

def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root runner with disposable test credentials.')
    with tempfile.TemporaryDirectory(prefix='spirit-browser-') as directory, sync_playwright() as driver:
        temp = Path(directory)
        archive, code = build_package(temp/'packages')
        browser = driver.chromium.launch()
        try:
            for width, folder in [(1440,''),(390,'band')]:
                with site(temp/str(width),archive,code,folder) as current:
                    context = browser.new_context(viewport={'width':width,'height':900}, reduced_motion='reduce')
                    page = context.new_page()
                    errors = []
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    expect(page.goto(current.base+'install/').status == 200, f'{width}px: installer opens')
                    layout(page, f'{width}px authorization')
                    page.locator('[name=setup_code]').fill(code)
                    page.locator('button[type=submit]').click()
                    page.wait_for_selector('[name=db_name]')
                    layout(page, f'{width}px configuration')
                    for name,value in current.input().items():
                        if name != 'action':
                            page.locator('[name='+name+']').fill(value)
                    page.locator('#install-form button[type=submit]').click()
                    page.wait_for_url('**/admin/login.php')
                    expect(not (current.root/'install').exists(), f'{width}px: browser install deletes installer')
                    layout(page, f'{width}px sign-in')
                    page.locator('[name=email]').fill(current.owner)
                    page.locator('[name=password]').fill(current.password)
                    page.locator('button[type=submit]').click()
                    page.wait_for_url(lambda url: dashboard(str(url)))
                    layout(page, f'{width}px dashboard')
                    for route in ['settings.php','account.php','installation.php','roadmap.php']:
                        expect(page.goto(current.base+'admin/'+route).status == 200, f'{width}px: {route} opens')
                        layout(page, f'{width}px {route}')
                    expect(not errors, f'{width}px: no uncaught JavaScript errors')
                    context.close()
        finally:
            browser.close()
    print('Chromium installer/admin smoke checks passed at 1440px and 390px.', flush=True)

if __name__ == '__main__':
    main()
