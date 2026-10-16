#!/usr/bin/env python3
"""Owner diagnostics and oversized-upload correction in real Chromium."""
from __future__ import annotations
import json
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import build_package, expect, site, dashboard
from launch_integration import limited_php
from public_browser import screenshot


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-launch-browser-') as directory:
        base=Path(directory);archive,unused=build_package(base/'packages')
        with limited_php(base/'php'),site(base/'website',archive,unused,'band') as s:
            s.claim();s.client.form('install/',s.input())
            from launch_integration import fixture
            fixture(s,'configure');fixture(s,'extras')
            with sync_playwright() as driver:
                browser=driver.chromium.launch()
                for width in [1440,390,320]:
                    context=browser.new_context(viewport={'width':width,'height':900},reduced_motion='reduce',bypass_csp=False,accept_downloads=True)
                    page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
                    page.goto(s.base+'admin/login.php')
                    page.locator('[name=email]').fill(s.owner);page.locator('[name=password]').fill(s.password)
                    page.get_by_role('button',name='Enter backstage').click();page.wait_for_url(lambda url:dashboard(str(url)))
                    page.get_by_role('link',name='Open launch checks').click();page.wait_for_url('**/admin/launch/')
                    browser_expect(page.locator('#sidebar a[aria-current="page"]')).to_contain_text('Launch checks')
                    expect(page.locator('h1').count()==1 and page.locator('.launch-check').count()==45,f'{width}px diagnostics render all bounded checks')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'{width}px diagnostic states and text do not overflow')
                    page.get_by_role('button',name='Download support report').focus()
                    with page.expect_download() as event:page.keyboard.press('Enter')
                    download=event.value;data=json.loads(Path(download.path()).read_text())
                    expect(download.suggested_filename=='Spirit-support-report.json' and data['format']=='spirit-support-v1',f'{width}px keyboard export produces a real report')
                    expect('example.test' not in json.dumps(data) and str(s.runtime) not in json.dumps(data),'Browser report does not expose owner email or private filesystem location')
                    browser_expect(page.locator('#check-payment_mode')).to_contain_text('live-charge risk')
                    expect(data['check_states']['mail_pending']=='attention' and data['check_states']['external_merchant']=='review','Downloaded report preserves follow-up and acceptance distinctions')
                    expect(page.locator('.launch-report-stamp').evaluate('(node)=>node.getBoundingClientRect().top - node.parentElement.querySelector("button[type=submit]").getBoundingClientRect().bottom >= 12'),f'{width}px support export focus ring does not overlap its timestamp')
                    # Keep full-page captures anchored to the top; preserve real
                    # content, focus styling and the fixed mobile menu position.
                    page.evaluate('window.scrollTo(0, 0)')
                    screenshot(page,f'launch-{width}-checks')
                    page.goto(s.base+'admin/readiness/',wait_until='networkidle')
                    expect(page.locator('.launch-check').count()==45,'Former preview URL serves the same authenticated dashboard')
                    page.goto(s.base+'admin/media/',wait_until='networkidle')
                    expect('Current PHP file limit: 0.02 MiB' in page.content(),'Media form reports the actual small PHP file ceiling')
                    page.locator('[name=file]').set_input_files({'name':'large-synthetic.png','mimeType':'image/png','buffer':b'x'*100000})
                    page.locator('[name=rights_confirmed]').check()
                    with page.expect_response(lambda response:response.url==s.base+'admin/media/' and response.request.method=='POST') as event:
                        page.get_by_role('button',name='Upload privately').click()
                    expect(event.value.status==413,'Actual oversized browser POST receives 413 rather than an expired-form error')
                    alert=page.get_by_role('alert');browser_expect(alert).to_contain_text('PHP discarded');browser_expect(alert).to_be_focused()
                    expect(page.locator('[name=file]').is_visible() and page.locator('[name=file]').input_value()=='','Rejected upload leaves a fresh usable form without replaying file input')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'{width}px oversized-upload feedback reflows')
                    screenshot(page,f'launch-{width}-upload-feedback')
                    expect(not errors,f'{width}px diagnostic/feedback workflow has no uncaught JavaScript errors')
                    context.close()
                browser.close()
    print('Launch diagnostics and oversized feedback passed at1440/390/320px.',flush=True)


if __name__=='__main__':main()
