#!/usr/bin/env python3
"""Real owner logo editing and public shell refresh; synthetic assets only."""
from __future__ import annotations
import os
import tempfile
import time
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as be
from http_integration import Client, build_package, dashboard, expect, site
from content_integration import seed_media_and_music
from gallery_browser import upload_image, open_nav
from public_browser import screenshot, wait_for_audio
from branding_integration import mutate, logo_present


def fits(page, label):
    expect(page.locator('h1').count()==1,label+': one primary heading')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),label+': no horizontal overflow')
    if page.locator('#branding-editor').count():
        aligned=page.locator('#branding-editor .checkbox-label').evaluate("n=>{const i=n.querySelector('input').getBoundingClientRect(),s=n.querySelector('span').getBoundingClientRect();return getComputedStyle(n).display==='flex'&&i.width===16&&i.height===16&&i.top>=s.top&&i.top<=s.top+8&&i.right+5<=s.left;}")
        expect(aligned,label+': approval checkbox aligns with the first text line at a readable size')


def loaded_logo(page, id, shape):
    logo=page.locator('.site-header .site-logo')
    be(logo).to_have_count(1)
    be(logo).to_have_attribute('width',str(shape[0]));be(logo).to_have_attribute('height',str(shape[1]))
    deadline=time.monotonic()+10
    while True:
        value=logo.evaluate('n=>({loaded:n.complete&&n.naturalWidth>0,src:n.currentSrc,w:n.getBoundingClientRect().width,h:n.getBoundingClientRect().height,fit:getComputedStyle(n).objectFit})')
        if value['loaded']:break
        if time.monotonic()>deadline:raise AssertionError('Logo did not decode')
        time.sleep(.05)
    expect(f'file.php?id={id}' in value['src'] and value['fit']=='contain','The selected authorized image actually loads without cropping')
    expect(abs(value['w']/value['h']-shape[0]/shape[1])<.05 and value['h']<=77,'Logo retains its intrinsic ratio and bounded header height')
    be(page.locator('.site-header .wordmark')).to_have_attribute('aria-label','Spiritual Gorillaz home')
    be(page.locator('.site-footer .wordmark')).to_have_attribute('aria-label','Spiritual Gorillaz home')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    name=os.environ.get('SPIRIT_BROWSER','chromium')
    if name not in ('chromium','firefox','webkit'):raise RuntimeError('Unsupported browser engine')
    with tempfile.TemporaryDirectory(prefix='spirit-brand-browser-') as directory, sync_playwright() as driver:
        base=Path(directory);archive,code=build_package(base/'packages');browser=getattr(driver,name).launch()
        try:
            for folder,area in [('', 'root'),('band','subfolder')]:
                with site(base/area,archive,code,folder) as s:
                    s.claim();s.client.form('install/',s.input());s.login();seed_media_and_music(s)
                    wide=upload_image(s,900,300,11);tall=upload_image(s,200,650,12)
                    for width,javascript in [(1440,True),(390,True),(320,True),(390,False)]:
                        label=f'{name}-{area}-{width}-js{int(javascript)}';mutate(s,'withdraw')
                        context=browser.new_context(viewport={'width':width,'height':950},reduced_motion='reduce',java_script_enabled=javascript,bypass_csp=False)
                        page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
                        try:
                            page.goto(s.base+'admin/login.php');page.locator('[name=email]').fill(s.owner);page.locator('[name=password]').fill(s.password)
                            page.get_by_role('button',name='Enter backstage',exact=True).click();page.wait_for_url(lambda u:dashboard(str(u)))
                            page.goto(s.base+'admin/branding/',wait_until='networkidle');fits(page,label+'/editor')
                            page.get_by_label('Logo image',exact=True).select_option(str(wide))
                            page.get_by_role('button',name='Save private logo draft',exact=True).click()
                            be(page.locator('body')).to_contain_text('Logo draft saved privately')
                            with page.expect_popup() as event:page.get_by_role('link',name='Preview saved logo').click()
                            preview=event.value;preview.wait_for_load_state('networkidle')
                            be(preview.locator('.preview-banner')).to_be_visible();loaded_logo(preview,wide,(900,300));fits(preview,label+'/preview')
                            expect(not logo_present(Client(s.base).request('')[1],wide),'Private logo preview has not altered anonymous public HTML')
                            if javascript and area=='root':screenshot(preview,'branding-'+label+'-private-preview')
                            preview.close()
                            page.locator('[name=approved]').check();page.get_by_role('button',name='Publish approved logo',exact=True).click()
                            be(page.locator('body')).to_contain_text('Approved logo published')
                            if javascript and area=='root':screenshot(page,'branding-'+label+'-editor')
                            public=context.new_page();public.goto(s.base,wait_until='networkidle')
                            loaded_logo(public,wide,(900,300));be(public.locator('.preview-banner')).to_have_count(0)
                            mutate(s,'save',tall);public.reload(wait_until='networkidle');loaded_logo(public,wide,(900,300))
                            expect(True,'Saving a replacement draft keeps the published logo in the browser')
                            if javascript:
                                public.goto(s.base+'music/release.php?slug=fixture-release',wait_until='networkidle')
                                public.locator('.play-track').first.click();wait_for_audio(public)
                                audio=public.locator('#site-audio').element_handle()
                                mutate(s,'publish',tall)
                                open_nav(public,'Shows');public.wait_for_url('**/shows/')
                                loaded_logo(public,tall,(200,650))
                                expect(public.locator('#site-audio').evaluate('(n,old)=>n===old',audio),'Logo publication refresh does not replace the persistent audio node')
                                wait_for_audio(public)
                                expect(True,'Real playback continues after the refreshed logo and page navigation')
                            else:
                                mutate(s,'publish',tall);public.reload(wait_until='networkidle');loaded_logo(public,tall,(200,650))
                                expect(True,'Native no-JavaScript pages render the published image without a client-only replacement')
                            fits(public,label+'/published')
                            page.reload(wait_until='networkidle');page.get_by_role('button',name='Withdraw published logo',exact=True).click()
                            be(page.locator('body')).to_contain_text('Published logo withdrawn')
                            if javascript:
                                open_nav(public,'Music');public.wait_for_url('**/music/')
                            else:public.reload(wait_until='networkidle')
                            be(public.locator('.site-logo')).to_have_count(0)
                            be(public.locator('.bundled-site-logo')).to_have_count(2)
                            expect(True,'Withdrawal updates both logo locations and restores the scalable bundled fallback')
                            expect(not errors,label+': no uncaught editor errors')
                        finally:context.close()
        finally:browser.close()
    print('Branding draft/preview/publication/navigation browser checks passed for '+name+'.',flush=True)

if __name__=='__main__':main()
