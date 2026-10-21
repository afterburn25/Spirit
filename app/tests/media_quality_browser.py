#!/usr/bin/env python3
"""Rendered responsive-image, keyboard, feedback and transfer-budget regressions.
This is targeted evidence, not a claim of full WCAG conformance or field CWV.
"""
from __future__ import annotations
import json
import os
import re
import tempfile
from pathlib import Path
from urllib.parse import urlsplit, parse_qs
from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import build_package, expect, site
from content_integration import save_content, seed_media_and_music
from media_quality_integration import seed, fixture
from public_browser import screenshot, wait_for_audio


def tab_to(page,selector,limit=60):
    for _ in range(limit):
        page.keyboard.press('Tab')
        if page.locator(selector).evaluate('(node)=>node===document.activeElement'):return
    raise AssertionError('Keyboard could not reach '+selector)


def home_artwork_check(page, width):
    """Catch real clipping/hidden-background regressions in the contained Home hero."""
    metrics=page.locator('.concept-home').evaluate('''hero=>{
      const box=hero.getBoundingClientRect(), image=hero.querySelector('.fallback-hero');
      const art=image.getBoundingClientRect(), overlay=getComputedStyle(hero,'::after');
      const content=[...hero.querySelectorAll('.hero-kicker,h1,h1 em,.hero-intro,.button,.hero-bottom p')];
      const label=hero.querySelector('.hero-bottom p').getBoundingClientRect();
      const overlap=[...hero.querySelectorAll('.hero-copy .button')].some(node=>{const r=node.getBoundingClientRect();
        return r.right>label.left&&r.left<label.right&&r.bottom>label.top&&r.top<label.bottom;});
      return {width:box.width,height:box.height,artWidth:art.width,artHeight:art.height,
        nativeRatio:image.naturalWidth/image.naturalHeight,
        background:getComputedStyle(hero).backgroundImage,
        wrapperBackground:getComputedStyle(image.parentElement).backgroundColor,
        overlayHeight:parseFloat(overlay.height),overlayMask:overlay.maskImage,overlap,
        clipped:content.filter(node=>{const r=node.getBoundingClientRect();return r.left<box.left-1||
          r.right>box.right+1||r.top<box.top-1||r.bottom>box.bottom+1;}).map(node=>node.className||node.tagName)};
    }''')
    expect(metrics['clipped']==[],f'{width}px Home heading, copy and buttons are fully inside the hero: '+repr(metrics['clipped']))
    expect(metrics['width']<=1321,f'{width}px Home keeps its centered 1320px maximum')
    expect(not metrics['overlap'],f'{width}px Home buttons do not overlap the movement caption')
    if width>820:
        expect(abs(metrics['artWidth']/metrics['artHeight']-metrics['nativeRatio'])<.002,
               f'{width}px Home foreground retains its complete native frame without CSS crop')
        expect('spiritual-gorillaz-home-hero-wide.webp' in metrics['background'] and metrics['wrapperBackground']=='rgba(0, 0, 0, 0)',
               f'{width}px the wide Home artwork is visible through its transparent foreground container')
        expect(abs(metrics['overlayHeight']-(metrics['height']-2))<1 and metrics['overlayMask']=='none',
               f'{width}px Home readability fade spans the full hero without the old 31-percent seam')
    screenshot(page,f'artwork-{width}-home')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-media-keyboard-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();image,_=seed(s)
            save_content(s,'gallery','Quality photograph',{'image_id':str(image),'caption':'Synthetic photograph with a transparent margin.'},slug='quality-photo')
            _,audio,release,_=seed_media_and_music(s)
            save_content(s,'track','Second fixture track',{'release_id':str(release),'audio_id':str(audio),'duration':'0:08'},slug='second-fixture-track')
            original=fixture(s,'inspect',image)['source']['bytes']
            with sync_playwright() as engine:
                browser=engine.chromium.launch()
                # The shortened desktop hero previously clipped text immediately
                # above the phone breakpoint and cropped the foreground at 1440px.
                artwork=browser.new_context(viewport={'width':1440,'height':1000},reduced_motion='reduce')
                artwork_page=artwork.new_page()
                for width in [1440,1180,1024,900,821,390]:
                    artwork_page.set_viewport_size({'width':width,'height':1000})
                    artwork_page.goto(s.base,wait_until='networkidle')
                    artwork_page.locator('.fallback-hero').evaluate('(image)=>image.decode()')
                    home_artwork_check(artwork_page,width)
                artwork.close()
                for width,height in [(1440,1000),(390,844),(320,800)]:
                    context=browser.new_context(viewport={'width':width,'height':height},device_scale_factor=1,reduced_motion='reduce',bypass_csp=False)
                    page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
                    page.goto(s.base+'pages/?slug=media-quality',wait_until='networkidle')
                    image_node=page.locator('img.page-image')
                    image_node.evaluate('(image)=>image.decode()')
                    src=image_node.evaluate('(image)=>image.currentSrc');selected=int(parse_qs(urlsplit(src).query).get('width',['1800'])[0])
                    expect(selected<=960 if width>600 else selected<=640,f'{width}px browser selects a bounded responsive image candidate')
                    metrics=page.evaluate('''() => ({
                        resources: performance.getEntriesByType('resource').map(e=>({name:e.name, bytes:e.encodedBodySize})),
                        overflow:document.documentElement.scrollWidth>innerWidth+1,
                        motion:getComputedStyle(document.documentElement).scrollBehavior
                    })''')
                    transferred=next(r['bytes'] for r in metrics['resources'] if r['name']==src)
                    static=sum(r['bytes'] for r in metrics['resources'] if '/assets/' in r['name'])
                    expect(0<transferred<original,f'{width}px actual image response uses fewer bytes than the original')
                    expect(static<=130000,f'{width}px loaded first-party CSS/JavaScript stays within the 130kB uncompressed fixture budget')
                    expect(not metrics['overflow'] and metrics['motion']=='auto',f'{width}px image page reflows and honors reduced motion')
                    expect(abs(image_node.evaluate('(n)=>n.getBoundingClientRect().height/n.getBoundingClientRect().width')-2/3)<.01,'Responsive image preserves its original aspect ratio')
                    print('MEASURED '+json.dumps({'viewport':width,'selected_width':selected,'image_bytes':transferred,'original_bytes':original,'first_party_asset_bytes':static}),flush=True)
                    screenshot(page,f'media-quality-{width}-image')
                    page.goto(s.base+'media/',wait_until='networkidle')
                    expect(page.locator('#dialog-photo').get_attribute('src') is None,'Hidden gallery dialog does not preload full-size photographs')
                    tab_to(page,'.photo-open');page.keyboard.press('Enter')
                    expect(page.get_by_role('dialog',name='Photograph preview').is_visible(),'Keyboard opens a named photograph dialog')
                    page.keyboard.press('Escape')
                    # Native close dispatch is asynchronous after Escape. Wait for
                    # the observable handler effects; do not fake focus or source.
                    browser_expect(page.locator('#photo-dialog')).not_to_be_visible()
                    browser_expect(page.locator('.photo-open')).to_be_focused()
                    browser_expect(page.locator('#dialog-photo:not([src])')).to_have_count(1)
                    expect(page.locator('.photo-open').evaluate('(n)=>n===document.activeElement') and page.locator('#dialog-photo').get_attribute('src') is None,'Closing the dialog restores its opener and releases the original-image source')
                    page.goto(s.base+'music/release.php?slug=fixture-release',wait_until='networkidle')
                    page.locator('.play-track').first.focus();page.keyboard.press('Enter');wait_for_audio(page)
                    browser_expect(page.locator('.play-track').first).to_have_attribute('aria-pressed','true')
                    browser_expect(page.locator('#player-next')).to_be_enabled()
                    expect(page.locator('.play-track').first.get_attribute('aria-pressed')=='true','Current playing track exposes its pressed state')
                    expect(page.locator('#player-next').is_enabled(),'Next track is available when the queue has another recording')
                    page.locator('#player-next').focus();page.keyboard.press('Enter');wait_for_audio(page)
                    browser_expect(page.locator('#player-next')).to_be_disabled()
                    browser_expect(page.locator('#player-previous')).to_be_enabled()
                    # Read real media duration, then await observable event-handler
                    # output. Playback >.15s alone does not imply timeupdate ran.
                    duration=page.locator('#site-audio').evaluate('(n)=>n.duration')
                    expect(duration==8,'Playback duration comes from the actual eight-second fixture recording')
                    browser_expect(page.locator('#player-seek')).to_be_enabled()
                    browser_expect(page.locator('#player-duration')).to_have_text('0:08')
                    browser_expect(page.locator('#player-seek')).to_have_attribute('aria-valuetext',re.compile(r'^\d+:\d{2} of 0:08$'))
                    expect(page.locator('#player-next').is_disabled() and page.locator('#player-previous').is_enabled(),'Transport availability follows real queue boundaries')
                    expect(' of ' in (page.locator('#player-seek').get_attribute('aria-valuetext') or ''),'Seek slider exposes elapsed and total playback time')
                    expect(page.locator('#player-title').evaluate('(n)=>n.getBoundingClientRect().right<=n.closest(".now-playing").getBoundingClientRect().right+1'),'Long recording title stays within its player cell instead of overlapping transport')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),'Active player does not create horizontal overflow')
                    screenshot(page,f'media-quality-{width}-player')
                    focus=page.locator('.site-footer nav a').last;focus.focus();page.wait_for_timeout(100)
                    expect(focus.evaluate('(node)=>node.getBoundingClientRect().bottom<=document.getElementById("persistent-player").getBoundingClientRect().top+1'),'Keyboard focus is not covered by the fixed audio player')
                    page.locator('#player-close').focus();page.keyboard.press('Enter')
                    expect(page.locator('.play-track').nth(1).evaluate('(node)=>node===document.activeElement'),'Closing playback returns focus to the selected track, not the document body')
                    if width<820:
                        page.locator('.nav-toggle').focus();page.keyboard.press('Enter');page.keyboard.press('Escape')
                        expect(page.locator('.nav-toggle').evaluate('(n)=>n===document.activeElement'),'Phone menu keyboard close returns focus')
                    page.goto(s.base+'booking/')
                    page.locator('[name="name"]').fill('Keyboard fixture')
                    page.locator('[name="email"]').fill('keyboard@example.test')
                    page.locator('[name="location"]').fill('   ')
                    page.locator('[name="contact_consent"]').check()
                    page.get_by_role('button',name='Send inquiry').click()
                    alert=page.get_by_role('alert');alert.wait_for(state='visible')
                    browser_expect(alert).to_be_focused()
                    expect(alert.evaluate('(n)=>n===document.activeElement'),'Server validation error receives keyboard focus without reading field values')
                    expect(page.locator('[name="name"]').input_value()=='Keyboard fixture','Validation feedback preserves the submitted non-secret form values')
                    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'{width}px validation form fits without horizontal overflow')
                    if width<=600:expect(page.locator('[name="email"]').evaluate('(n)=>parseFloat(getComputedStyle(n).fontSize)')>=16,'Phone form text is at least 16 CSS pixels')
                    screenshot(page,f'media-quality-{width}-feedback')
                    page.goto(s.base+'admin/login.php');page.locator('[name="email"]').fill(s.owner);page.locator('[name="password"]').fill(s.password)
                    page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')
                    page.goto(s.base+'admin/account.php');field=page.locator('input:not([type="hidden"])').first;field.focus();page.keyboard.press('Escape')
                    expect(field.evaluate('(n)=>n===document.activeElement'),'Escape in an ordinary admin field no longer steals focus to a closed menu')
                    expect(errors==[],f'{width}px quality workflow has no uncaught JavaScript errors');context.close()
                browser.close()
    print('Responsive image, keyboard and transfer-budget browser checks passed.',flush=True)
if __name__=='__main__':main()
