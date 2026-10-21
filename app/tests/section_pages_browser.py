#!/usr/bin/env python3
"""Actual editor, saved section previews and public layouts; synthetic artwork only."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import Client, build_package, expect, site, dashboard
from content_integration import save_content
from gallery_browser import upload_image
from public_browser import screenshot


def fit(page,label):
    expect(page.locator('h1').count()==1,label+': single primary heading')
    expect(page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1'),label+': no horizontal overflow')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable SQL environment required.')
    engine=os.environ.get('SPIRIT_BROWSER','chromium')
    if engine not in ['chromium','firefox','webkit']:raise RuntimeError('Unknown engine')
    with tempfile.TemporaryDirectory(prefix='spirit-section-browser-') as directory, sync_playwright() as driver:
        base=Path(directory);archive,code=build_package(base/'packages');browser=getattr(driver,engine).launch()
        try:
            for folder,area in [('', 'root'),('band','subfolder')]:
                with site(base/area,archive,code,folder) as s:
                    s.claim();s.client.form('install/',s.input());s.login()
                    landscape=upload_image(s,1600,900,1);portrait=upload_image(s,800,1400,2)
                    for width in [1440,390,320]:
                        context=browser.new_context(viewport={'width':width,'height':960},reduced_motion='reduce',bypass_csp=False)
                        page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
                        page.goto(s.base+'admin/login.php');page.locator('[name=email]').fill(s.owner);page.locator('[name=password]').fill(s.password);page.get_by_role('button',name='Enter backstage').click();page.wait_for_url(lambda u:dashboard(str(u)))
                        page.goto(s.base+'admin/content/?kind=page',wait_until='networkidle')
                        browser_expect(page.locator('.section-directory-card')).to_have_count(8);fit(page,f'{engine}/{area}/{width}/directory')
                        if area=='root':screenshot(page,f'sections-{engine}-{width}-directory')
                        page.goto(s.base+'admin/content/edit.php?kind=page&placement=music',wait_until='networkidle')
                        page.locator('[name=subtitle]').fill(f'Saved music introduction {width}')
                        page.locator('[name=body]').fill('Synthetic page copy for layout testing, not band content.')
                        page.locator('[name=image_id]').select_option(str(landscape));page.locator('[name=focal]').select_option('right')
                        page.get_by_role('button',name='Save private draft',exact=True).click();page.wait_for_load_state('networkidle')
                        browser_expect(page.locator('.artwork-inspection')).to_contain_text('1600 × 900 pixels')
                        browser_expect(page.locator('.artwork-inspection')).to_contain_text('Still review')
                        fit(page,f'{engine}/{area}/{width}/editor')
                        if area=='root':screenshot(page,f'sections-{engine}-{width}-editor')
                        editor_url=page.url
                        with page.expect_popup() as event:page.get_by_role('link',name='Preview saved draft').click()
                        preview=event.value;preview.wait_for_load_state('networkidle')
                        browser_expect(preview.locator('.preview-banner')).to_be_visible();browser_expect(preview.locator('h1')).to_have_text('OUR MUSIC')
                        image=preview.locator('.section-artwork img');image.evaluate('(n)=>n.decode()')
                        expect(image.evaluate('(n)=>n.naturalWidth>0 && getComputedStyle(n).objectFit==="contain"'),'Preview uses a loaded, contained responsive artwork image')
                        fit(preview,f'{engine}/{area}/{width}/music-preview')
                        if area=='root':screenshot(preview,f'sections-{engine}-{width}-music-preview')
                        expect(f'Saved music introduction {width}' not in Client(s.base).request('music/')[1],'Private preview does not update anonymous music page')
                        preview.close();page.goto(editor_url)
                        page.get_by_role('button',name='Publish approved content').click();page.wait_for_load_state('networkidle')
                        page.goto(s.base+'music/',wait_until='networkidle');browser_expect(page.locator('.gold-subtitle')).to_have_text(f'Saved music introduction {width}')
                        expect(page.locator('.preview-banner').count()==0,'Published page has no private-preview banner')
                        # Other slots use the same existing typed media references.
                        for slug,route,title in [('shows','shows/','UPCOMING SHOWS'),('media','media/','WATCH & EXPLORE'),('merch','shop/','WEAR THE MOVEMENT')]:
                            lookup=s.client.request('admin/content/edit.php?kind=page&placement='+slug)[3]
                            import re
                            found=re.search(r'[?&]id=(\d+)',lookup)
                            item=save_content(s,'page',title,{'subtitle':'Approved '+slug+' intro','body':'Synthetic introduction only.','image_id':str(portrait)},item_id=int(found.group(1)) if found else 0,slug=slug)
                            page.goto(s.base+route,wait_until='networkidle');browser_expect(page.locator('h1')).to_have_text(title)
                            artwork=page.locator('.section-artwork img');artwork.evaluate('(n)=>n.decode()');fit(page,f'{engine}/{area}/{width}/'+slug)
                            expect(artwork.evaluate('(n)=>{const r=n.getBoundingClientRect();return r.height<=441&&r.right<=innerWidth+1}'),'Portrait artwork stays within its bounded presentation')
                            if area=='root' and width==390:screenshot(page,f'sections-{engine}-390-'+slug)
                        # Make a true booking placement using a single fixed record.
                        lookup=s.client.request('admin/content/edit.php?kind=page&placement=booking')[3];found=re.search(r'[?&]id=(\d+)',lookup)
                        booking=save_content(s,'page','Private booking preview',{'subtitle':'Preview booking subtitle','body':'Fixture only','image_id':str(portrait)},action='save',item_id=int(found.group(1)) if found else 0,slug='booking')
                        page.goto(s.base+f'admin/content/preview.php?id={booking}',wait_until='networkidle')
                        browser_expect(page.get_by_role('button',name='Send inquiry')).to_be_disabled();browser_expect(page.locator('.form-notice')).to_contain_text('form is disabled')
                        fit(page,f'{engine}/{area}/{width}/booking-preview')
                        expect(not errors,'No uncaught page exceptions: '+repr(errors));context.close()
                    # Native no-JavaScript fallback is also a real protected preview.
                    context=browser.new_context(viewport={'width':390,'height':960},java_script_enabled=False,bypass_csp=False)
                    page=context.new_page();page.goto(s.base+'admin/login.php');page.locator('[name=email]').fill(s.owner);page.locator('[name=password]').fill(s.password);page.get_by_role('button',name='Enter backstage').click();page.wait_for_url(lambda u:dashboard(str(u)))
                    page.goto(s.base+f'admin/content/preview.php?id={booking}',wait_until='load')
                    expect(page.locator('#public-nav').is_visible(),'No-JavaScript section preview retains native navigation')
                    browser_expect(page.get_by_role('button',name='Send inquiry')).to_be_disabled();context.close()
        finally:browser.close()
    print('Section artwork/editor/preview/public layouts passed for '+engine+'.',flush=True)

if __name__=='__main__':main()
