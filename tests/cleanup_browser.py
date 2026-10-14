#!/usr/bin/env python3
"""Render and execute owner maintenance controls at desktop and phone widths."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from staff_browser import signin, rendered
from public_browser import screenshot
from cleanup_integration import fixture


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-cleanup-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with sync_playwright() as engine:
            browser=engine.chromium.launch()
            for width,height in [(1440,1000),(390,844)]:
                with site(base/f'website-{width}',archive,code,'band') as s:
                    s.claim();s.client.form('install/',s.input());fixture(s,'seed')
                    context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False);page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
                    signin(s,page,s.owner,s.password);page.goto(s.base+'admin/maintenance/',wait_until='networkidle');rendered(page,f'{width}px cleanup preview');screenshot(page,f'cleanup-{width}-preview')
                    form=page.locator('form').filter(has=page.locator('input[name="action"][value="clean"]'));form.locator('[name="confirm_cleanup"]').fill('CLEAN TEMPORARY DATA');form.locator('[name="owner_password"]').fill(s.password);form.get_by_role('button',name='Clean eligible temporary data').click();page.wait_for_load_state('networkidle')
                    expect('Cleanup completed' in page.locator('main').inner_text() and not fixture(s,'snapshot')['temporary_exists'],'Browser executes a real bounded technical cleanup')
                    rendered(page,f'{width}px cleanup result');screenshot(page,f'cleanup-{width}-result');expect(errors==[],f'{width}px cleanup has no uncaught JavaScript errors');context.close()
            browser.close()
    print('Owner retention controls passed at 1440px and390px.',flush=True)


if __name__=='__main__':main()
