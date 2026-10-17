#!/usr/bin/env python3
"""Real desktop/mobile upload, key download, apply, confirmation and rescue."""
from __future__ import annotations
import os
import re
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from staff_browser import signin, rendered
from public_browser import screenshot
from upgrade_integration import UPGRADE_TEST_PHP_INI, package_bytes, fixture


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root isolated environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-upgrade-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages');new=base/'generated-test-update.zip';new.write_bytes(package_bytes(archive))
        with site(base/'website',archive,code,'band',php_ini=UPGRADE_TEST_PHP_INI) as s:
            s.claim();s.client.form('install/',s.input())
            with sync_playwright() as engine:
                browser=engine.chromium.launch()
                for width,height in [(1440,1000),(390,844)]:
                    context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False);page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
                    signin(s,page,s.owner,s.password);page.goto(s.base+'admin/upgrades/',wait_until='networkidle')
                    stage=page.locator('form').filter(has=page.locator('input[name="action"][value="stage"]'))
                    stage.locator('[name="package"]').set_input_files(new);stage.locator('[name="owner_password"]').fill(s.password);stage.locator('[name="trust_package"]').check();stage.get_by_role('button',name='Validate and stage package').click();page.wait_for_load_state('networkidle')
                    job=fixture(s,'state');expect(job.get('state')=='staged','Browser upload produces actual staged checkpoint; '+page.locator('main').inner_text()[-1200:])
                    rendered(page,f'{width}px staged code upgrade');screenshot(page,f'upgrade-{width}-staged')
                    form=page.locator('form.upgrade-actions');form.locator('[name="owner_password"]').fill(s.password)
                    with page.expect_download() as event:form.get_by_role('button',name='Download private recovery receipt').click()
                    dest=base/f'private-fixture-{width}.txt';event.value.save_as(dest);key=re.search(r'Recovery key: ([a-f0-9]{64})',dest.read_text()).group(1)
                    form.locator('[name="saved_receipt"]').check();form.get_by_role('button',name='Install reviewed code').click();page.wait_for_load_state('networkidle')
                    expect(fixture(s,'state')['state']=='awaiting_confirmation','New browser request runs the newly installed application under maintenance')
                    form=page.locator('form.upgrade-actions');form.locator('[name="owner_password"]').fill(s.password);form.get_by_role('button',name='Verify and reopen website').click();page.wait_for_load_state('networkidle')
                    expect(fixture(s,'state')['state']=='committed','Browser verifies and commits code upgrade')
                    rendered(page,f'{width}px completed code upgrade');screenshot(page,f'upgrade-{width}-committed')
                    page.goto(s.base+'admin/upgrades/recover.php',wait_until='networkidle');page.locator('[name="checkpoint"]').fill(job['id']);page.locator('[name="recovery_key"]').fill(key);page.locator('[name="confirm"]').fill('ROLLBACK');page.get_by_role('button',name='Restore previous code').click();page.wait_for_load_state('networkidle')
                    expect('Previous code restored' in page.locator('main').inner_text(),'Independent browser entrance restores actual code checkpoint')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'{width}px recovery page has no horizontal overflow');screenshot(page,f'upgrade-{width}-recovered')
                    expect(errors==[],f'{width}px code update/recovery has no uncaught JavaScript error');context.close()
                browser.close()
    print('Actual ZIP upgrade and independent recovery browser flows passed at 1440px/390px.',flush=True)


if __name__=='__main__':main()
