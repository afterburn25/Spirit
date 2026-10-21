#!/usr/bin/env python3
"""Exercise the code-free one-page installer and actual owner screens."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import build_package, site, expect, dashboard


def layout(page, label, javascript=True):
    page.wait_for_load_state('networkidle')
    if javascript:
        expect(page.evaluate('document.styleSheets.length > 0'), label + ': stylesheet loaded')
        expect(page.evaluate('document.documentElement.scrollWidth <= window.innerWidth + 1'), label + ': no horizontal page overflow')
    expect(page.locator('h1').count() == 1, label + ': main heading present')


def screenshot(page, name):
    directory=os.environ.get('SPIRIT_SCREENSHOT_DIR')
    if directory:
        path=Path(directory);path.mkdir(parents=True,exist_ok=True)
        page.screenshot(path=str(path/(name+'.png')),full_page=True)


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root runner with disposable test credentials.')
    with tempfile.TemporaryDirectory(prefix='spirit-browser-') as directory, sync_playwright() as driver:
        temp = Path(directory)
        archive, unused = build_package(temp/'packages')
        browser = driver.chromium.launch()
        try:
            # Root, subfolder, a very narrow phone, and a JavaScript-free fallback.
            for width, folder, javascript in [(1440,'',True),(390,'band',True),(320,'narrow',True),(390,'no-js',False)]:
                label=f'{width}px'+(' no-JS' if not javascript else '')
                with site(temp/(str(width)+folder),archive,unused,folder) as current:
                    context = browser.new_context(viewport={'width':width,'height':900}, reduced_motion='reduce', java_script_enabled=javascript, bypass_csp=False)
                    page = context.new_page(); errors=[]
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    response=page.goto(current.base+'install/')
                    expect(response.status == 200, label+': installer opens directly')
                    expect('no-store' in response.headers.get('cache-control','') and 'noindex' in response.headers.get('x-robots-tag',''), label+': installer is uncached and unindexed')
                    layout(page, label+' configuration',javascript)
                    expect(page.locator('#install-form').count()==1 and page.locator('form').count()==1, label+': exactly one installation form')
                    expect(page.locator('[name=setup_code]').count()==0, label+': no setup code field')
                    expect(page.locator('[name=db_name]').is_visible() and page.locator('[name=owner_name]').is_visible(), label+': database and owner settings are visible together')
                    screenshot(page,'installer-'+str(width)+('-nojs' if not javascript else '')+'-form')
                    expect(page.locator('[name=db_mode], [name^=cpanel], #cpanel-fields').count()==0, label+': no automatic mode or hosting token controls')
                    expect('PHP cURL' in page.content() and 'PHP ZIP' in page.content() and 'cURL and ZIP ·' not in page.content(), label+': optional extension checks are separate')
                    for field in ['db_host','db_port','db_name','db_user']:
                        expect(page.locator('[name='+field+']').is_enabled() and page.locator('[name='+field+']').get_attribute('required') is not None, label+': manual field remains enabled and required: '+field)

                    for name,value in current.input().items():
                        if name != 'action': page.locator('[name='+name+']').fill(value)
                    page.locator('[name=password_confirm]').fill('Different-password-123')
                    page.locator('#install-form button[type=submit]').click()
                    browser_expect(page.locator('.notice.error')).to_contain_text('passwords do not match')
                    expect(page.url==current.base+'install/', label+': validation stays on the same installer URL')
                    expect(page.locator('[name=db_name]').input_value()==current.env['SPIRIT_DB_NAME'], label+': database name survives correction')
                    for name in ['password','password_confirm','db_password']:
                        expect(page.locator('[name='+name+']').input_value()=='', label+': secret cleared after error: '+name)
                    expect(not (current.runtime/'config.php').exists(), label+': validation cannot partially install')
                    if javascript:
                        browser_expect(page.locator('.notice.error')).to_be_focused()
                    for name in ['password','password_confirm','db_password']:
                        page.locator('[name='+name+']').fill(current.input()[name])
                    page.locator('#install-form button[type=submit]').click()
                    browser_expect(page.locator('#install-complete')).to_be_visible()
                    expect(page.url==current.base+'install/', label+': success stays on the same page')
                    expect(not (current.root/'install').exists(), label+': browser install deletes installer')
                    expect(page.locator('#install-form').count()==0, label+': completed result has no resubmission control')
                    browser_expect(page.locator('#install-cleanup')).to_contain_text('Installer folder removed.')
                    expect(current.password not in page.content() and current.env['SPIRIT_DB_PASSWORD'] not in page.content(), label+': completion contains no credentials')
                    layout(page,label+' completion',javascript)
                    screenshot(page,'installer-'+str(width)+('-nojs' if not javascript else '')+'-complete')
                    page.locator('#owner-signin').click()
                    page.wait_for_url('**/admin/login.php')
                    layout(page,label+' sign-in',javascript)
                    page.locator('[name=email]').fill(current.owner)
                    page.locator('[name=password]').fill(current.password)
                    page.locator('button[type=submit]').click()
                    page.wait_for_url(lambda url: dashboard(str(url)))
                    # Existing admin regression remains at its established widths.
                    if javascript and width in [1440,390]:
                        layout(page,label+' dashboard')
                        for route in ['settings.php','account.php','installation.php','roadmap.php']:
                            expect(page.goto(current.base+'admin/'+route).status == 200,label+': '+route+' opens')
                            layout(page,label+' '+route)
                            if route=='installation.php':
                                expect(page.locator('#optional-extensions').is_visible(),label+': installed owner can inspect individual extension notices')
                                screenshot(page,'installer-'+str(width)+'-health')
                    expect(not errors,label+': no uncaught JavaScript errors')
                    context.close()
        finally:
            browser.close()
    print('Code-free one-page installer passed at1440/390/320px and without JavaScript; owner/admin workflows preserved.',flush=True)


if __name__ == '__main__': main()
