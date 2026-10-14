#!/usr/bin/env python3
"""Rendered private-history selection, comparison and restore at two widths."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from content_history_integration import seed_story, fixture
from public_browser import screenshot


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-history-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();id,_,_=seed_story(s)
            fixture(s,'seed',{'kind':'page','count':31})
            with sync_playwright() as engine:
                browser=engine.chromium.launch()
                for width,height in [(1440,1000),(390,844)]:
                    context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False)
                    page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
                    page.goto(s.base+'admin/login.php');page.locator('[name="email"]').fill(s.owner);page.locator('[name="password"]').fill(s.password)
                    page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')
                    page.goto(s.base+'admin/content/?kind=page');page.get_by_label('Search title or URL name').fill('Large library')
                    page.get_by_role('button',name='Apply filters').click()
                    expect(page.locator('tbody tr').count()==25,'Rendered content results are bounded to 25 rows')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'Content search fits {width}px')
                    screenshot(page,f'history-{width}-search')
                    page.locator('.content-pagination').get_by_role('link',name='Next').click()
                    expect(page.locator('tbody tr').count()==6,'Next page reaches the remaining matching records')
                    response=page.goto(s.base+f'admin/content/history.php?id={id}&revision=2')
                    expect(response.status==200 and 'no-store' in response.headers.get('cache-control',''),'Browser revision screen is private')
                    expect(page.locator('.revision-field').count()==7,'Revision comparison renders every declared field')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'Revision comparison fits {width}px without clipping')
                    screenshot(page,f'history-{width}-comparison')
                    page.get_by_role('button',name='Restore as private draft').click();page.wait_for_url('**/admin/content/edit.php?kind=page&id=*')
                    expect(page.locator('[name="title"]').input_value()=='History Beta','Browser restore creates the selected draft through its real form')
                    expect(page.locator('body').inner_text().find('restored as a new private draft')>=0,'Restore clearly distinguishes draft recovery from publication')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'Restored editor fits {width}px')
                    screenshot(page,f'history-{width}-restored-editor')
                    expect(errors==[],f'No uncaught script errors in {width}px revision workflow');context.close()
                browser.close()
    print('Content history desktop/mobile browser checks passed.',flush=True)
if __name__=='__main__':main()
