#!/usr/bin/env python3
"""Rendered search/admin controls and live metadata across persistent playback."""
from __future__ import annotations
import json
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from content_integration import seed_media_and_music
from discovery_integration import seed
from public_browser import screenshot, wait_for_audio


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-discovery-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();seed(s);seed_media_and_music(s)
            with sync_playwright() as engine:
                browser=engine.chromium.launch()
                for width,height in [(1440,1000),(390,844)]:
                    context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False)
                    page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
                    page.goto(s.base+'pages/search.php');page.get_by_label('Search the website').fill('constellation');page.get_by_role('button',name='Search',exact=True).click()
                    page.wait_for_url('**/pages/search.php?*');expect(page.get_by_role('heading',name='Approved constellation').is_visible(),'Public search follows its real rendered form')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'Public search fits {width}px')
                    expect(page.locator('meta[name="robots"]').get_attribute('content')=='noindex, nofollow','Search result metadata follows AJAX navigation')
                    screenshot(page,f'discovery-{width}-search')
                    page.get_by_role('heading',name='Approved constellation').get_by_role('link').click();page.wait_for_url('**/pages/?slug=approved-story')
                    expect(page.title().startswith('Approved search title'),'Published custom title follows internal navigation')
                    expect(page.locator('meta[property="og:image"]').count()==1 and page.locator('meta[name="robots"]').get_attribute('content')=='index, follow','Share image/robots are replaced rather than left stale')
                    expect(page.locator('.page-image').evaluate('(image)=>Math.abs(image.getBoundingClientRect().height/image.getBoundingClientRect().width-1)<.02'),'Intrinsic image dimensions do not flatten a responsive photograph')
                    page.goto(s.base+'music/release.php?slug=fixture-release');page.locator('.play-track').first.click();wait_for_audio(page)
                    page.evaluate('window.metadataAudio = document.getElementById("site-audio")')
                    if width==390:
                        page.locator('.nav-toggle').click();page.keyboard.press('Escape')
                        expect(page.locator('.nav-toggle').evaluate('(node)=>node===document.activeElement'),'Escape returns phone-menu focus to its toggle')
                        page.locator('.nav-toggle').click()
                    page.locator('#public-nav a').filter(has_text='Shows').first.click();page.wait_for_url('**/shows/')
                    page.get_by_role('link',name='Synthetic public stage',exact=True).click();page.wait_for_url('**/shows/event.php?slug=synthetic-stage')
                    data=json.loads(page.locator('#page-structured-data').inner_text())
                    expect(data['name']=='Synthetic public stage' and data['eventStatus'].endswith('EventCancelled'),'Current event JSON-LD follows navigation without script execution')
                    expect(page.evaluate('window.metadataAudio===document.getElementById("site-audio")&&!window.metadataAudio.paused'),'Metadata navigation preserves the same actual playing audio node')
                    screenshot(page,f'discovery-{width}-event')
                    if width==390:page.locator('.nav-toggle').click()
                    page.locator('#public-nav a').filter(has_text='Music').first.click();page.wait_for_url('**/music/')
                    expect(page.locator('#page-structured-data').count()==0 and page.locator('meta[property="og:image"]').count()==0,'Navigating away removes stale event markup and share artwork')
                    page.goto(s.base+'admin/login.php');page.locator('[name="email"]').fill(s.owner);page.locator('[name="password"]').fill(s.password)
                    page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')
                    page.goto(s.base+'admin/discovery.php');expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'Owner discovery settings fit {width}px')
                    screenshot(page,f'discovery-{width}-settings')
                    page.get_by_label('Public indexing').select_option('disabled');page.get_by_role('button',name='Save search visibility').click()
                    page.goto(s.base);expect(page.locator('meta[name="robots"]').get_attribute('content')=='noindex, nofollow','Owner noindex choice applies to actual public markup')
                    page.goto(s.base+'admin/discovery.php');page.get_by_label('Public indexing').select_option('enabled');page.get_by_role('button',name='Save search visibility').click()
                    expect(errors==[],f'No uncaught JavaScript errors in {width}px discovery workflow');context.close()
                browser.close()
    print('Published discovery and persistent metadata browser workflows passed.',flush=True)
if __name__=='__main__':main()
