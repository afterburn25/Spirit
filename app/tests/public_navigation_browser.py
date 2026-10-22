#!/usr/bin/env python3
"""Actual public disclosure navigation and native admin controls. Synthetic sites only."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as be
from http_integration import build_package, site, expect, dashboard
from content_integration import seed_media_and_music
from commerce_integration import seed_catalog, snapshot
from public_browser import screenshot, wait_for_audio


def closed(page):
    be(page.locator('.nav-toggle')).to_have_attribute('aria-expanded', 'false')
    be(page.locator('#public-nav')).to_be_hidden()


def desktop_header_checks(browser, s, engine, area):
    context=browser.new_context(viewport={'width':1440,'height':960},reduced_motion='reduce')
    page=context.new_page();page.goto(s.base,wait_until='networkidle')
    for width in (1440,1180,1024,900,821):
        page.set_viewport_size({'width':width,'height':960})
        metrics=page.evaluate('''() => {
          const nav=document.querySelector('#public-nav'),book=nav.querySelector('.nav-book'),
            search=document.querySelector('.header-search .nav-search'),logo=document.querySelector('.site-header .wordmark');
          const n=nav.getBoundingClientRect(),b=book.getBoundingClientRect(),s=search.getBoundingClientRect(),l=logo.getBoundingClientRect();
          return {gap:s.left-b.right,center:(n.left+s.right)/2,logoRight:l.right,navLeft:n.left,
            font:parseFloat(getComputedStyle(nav.querySelector('a')).fontSize),
            aligned:Math.abs(b.top+b.height/2-s.top-s.height/2),overflow:document.documentElement.scrollWidth>innerWidth+1};
        }''')
        if abs(metrics['center']-width/2)>2 or metrics['logoRight']+4>metrics['navLeft'] or metrics['overflow']:
            print(f'Header geometry at {width}px: {metrics}', flush=True)
        expect(8<=metrics['gap']<=20 and metrics['aligned']<=2,
               f'{width}px search sits directly beside Book the band with aligned controls')
        expect(abs(metrics['center']-width/2)<=2 and metrics['logoRight']+4<=metrics['navLeft'] and not metrics['overflow'],
               f'{width}px complete navigation/search group is centered without logo collision or overflow')
        expect(metrics['font']>=(15 if width==1440 else 12),f'{width}px navigation uses the enlarged readable type')
        search=page.get_by_role('button',name='Open site search',exact=True);search.click()
        be(page.locator('#header-search-panel')).to_be_visible();be(page.locator('#header-search-query')).to_be_focused()
        box=page.locator('#header-search-panel').bounding_box()
        expect(box is not None and box['x']>=0 and box['x']+box['width']<=width+1,
               f'{width}px adjacent search dropdown stays within the viewport')
        page.keyboard.press('Escape');be(page.locator('#header-search-panel')).to_be_hidden();be(search).to_be_focused()
    page.set_viewport_size({'width':1440,'height':960})
    if area=='root':screenshot(page,'navigation-'+engine+'-1440-header')
    context.close()


def menu_checks(page, width):
    toggle=page.locator('.nav-toggle');nav=page.locator('#public-nav');links=nav.locator('a[href]')
    be(links).to_have_count(7);closed(page)
    toggle.focus();page.keyboard.press('Enter')
    be(toggle).to_have_attribute('aria-expanded','true');be(toggle).to_be_focused()
    page.keyboard.press('Tab');be(links.first).to_be_focused()
    page.keyboard.press('Shift+Tab');be(toggle).to_be_focused()
    page.keyboard.press('Shift+Tab');closed(page)
    be(page.locator('.site-header .wordmark')).to_be_focused()
    expect(True,'Tab and reverse Tab enter and leave the disclosure without a focus trap')
    toggle.focus();page.keyboard.press('ArrowDown');be(links.first).to_be_focused()
    page.keyboard.press('Escape');closed(page);be(toggle).to_be_focused()
    toggle.focus();page.keyboard.press('ArrowUp');be(links.last).to_be_focused()
    page.keyboard.press('Tab');closed(page)
    expect(not nav.evaluate('(n)=>n.contains(document.activeElement)'),'Leaving the last link closes navigation without hiding keyboard focus')
    toggle.focus();page.keyboard.press('Enter');page.mouse.click(5,200);closed(page)
    expect(not nav.evaluate('(n)=>n.contains(document.activeElement)'),'Clicking outside dismisses the menu without leaving hidden focus')
    toggle.focus();page.keyboard.press('ArrowDown')
    target=page.locator('#site-content a[href]').first;target.focus();closed(page);be(target).to_be_focused()
    expect(True,'Focusing content dismisses the non-modal menu without stealing that focus')
    toggle.focus();page.keyboard.press('Enter')
    page.set_viewport_size({'width':1440,'height':900})
    be(toggle).to_be_hidden();be(toggle).to_have_attribute('aria-expanded','false');be(nav).to_be_visible()
    be(nav.locator('a[aria-current]')).to_be_focused()
    page.set_viewport_size({'width':width,'height':680})
    closed(page);be(toggle).to_be_focused()
    expect(True,'Desktop/phone transitions reset stale open state and preserve visible focus')
    toggle.click();be(nav).to_be_visible()
    be(nav).not_to_have_attribute('role','menu')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),'Disclosure and controls fit the viewport')
    page.keyboard.press('Escape');closed(page)
    target.focus()
    page.set_viewport_size({'width':1440,'height':900})
    be(target).to_be_focused()
    page.set_viewport_size({'width':width,'height':680})
    be(target).to_be_focused();closed(page)
    expect(True,'Responsive reset never pulls focus away from page content')


def checkbox_geometry(page):
    result=page.locator('label.checkbox-label:has(>input[type=checkbox])').evaluate_all('''labels=>labels.map(label=>{
      const input=label.querySelector('input'),r=input.getBoundingClientRect(),l=label.getBoundingClientRect();
      const walker=document.createTreeWalker(label,NodeFilter.SHOW_TEXT);let text;
      while(text=walker.nextNode())if(text.textContent.trim())break;
      const range=document.createRange();if(text)range.selectNodeContents(text);
      const first=text?range.getClientRects()[0]:null;
      return {width:r.width,height:r.height,labelHeight:l.height,alignment:first?Math.abs(r.top-first.top):999,
        separated:first?first.left>=r.right+6:false,contained:r.left>=l.left&&r.right<=l.right,
        labelText:label.textContent.trim().slice(0,80)};
    })''')
    expect(bool(result),'A real native checkbox is present on this admin page')
    for row in result:
        expect(row['width']==row['height'] and row['width'] in (16,20) and row['labelHeight']>=44 and row['contained'],
               'Native checkbox has bounded square geometry and a full clickable label: '+row['labelText'])
        expect(row['separated'] and row['alignment']<=8,'Checkbox aligns with its first text line: '+row['labelText'])
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),'Admin controls reflow without horizontal overflow')


def admin_checks(browser,s,width,pid,engine,area):
    context=browser.new_context(viewport={'width':width,'height':900},reduced_motion='reduce',bypass_csp=False)
    page=context.new_page();page.goto(s.base+'admin/login.php')
    page.locator('[name=email]').fill(s.owner);page.locator('[name=password]').fill(s.password)
    page.get_by_role('button',name='Enter backstage',exact=True).click();page.wait_for_url(lambda u:dashboard(str(u)))
    before=snapshot(s)
    routes=[f'admin/shop/edit.php?id={pid}',f'admin/shop/variants.php?id={pid}',
            'admin/shop/categories.php','admin/shop/settings.php','admin/media/','admin/branding/']
    for route in routes:
        response=page.goto(s.base+route,wait_until='networkidle');expect(response.status==200,'Admin control screen opens: '+route)
        checkbox_geometry(page)
        control=page.locator('label.checkbox-label input[type=checkbox]').first
        original=control.is_checked();control.focus();page.keyboard.press('Space')
        be(control).to_be_checked(checked=not original)
        page.keyboard.press('Space');be(control).to_be_checked(checked=original)
        expect(True,'Space toggles the actual native control and returns it to its original state')
        if route.startswith('admin/shop/edit') and area=='root':screenshot(page,f'navigation-{engine}-{width}-product-controls')
    expect(snapshot(s)==before,'Reading and toggling unsent controls does not mutate saved business data')
    page.emulate_media(forced_colors='active');checkbox_geometry(page)
    expect(True,'Native checkbox layout remains usable with forced colors')
    context.close()


def audio_checks(browser,s,engine,area):
    context=browser.new_context(viewport={'width':320,'height':568},reduced_motion='reduce',bypass_csp=False)
    page=context.new_page();page.goto(s.base+'music/release.php?slug=fixture-release',wait_until='networkidle')
    page.locator('.play-track').first.click();wait_for_audio(page)
    audio=page.locator('#site-audio').element_handle()
    toggle=page.locator('.nav-toggle');toggle.focus();page.keyboard.press('ArrowUp')
    be(page.locator('#public-nav a').last).to_be_focused()
    bounds=page.locator('#public-nav').evaluate('''n=>{const r=n.getBoundingClientRect(),p=document.getElementById('persistent-player').getBoundingClientRect(),a=n.lastElementChild.getBoundingClientRect();return {scroll:n.scrollHeight>n.clientHeight,visible:a.top>=r.top&&a.bottom<=r.bottom+1,bottom:r.bottom,player:p.top};}''')
    expect(bounds['scroll'] and bounds['visible'] and bounds['bottom']<=bounds['player']-10,'Short phone menu scrolls its final link into view above the actual player')
    # Use the native key to return to the trigger, then select a real public page.
    page.keyboard.press('Escape');toggle.click();page.locator('#public-nav').get_by_role('link',name='Shows',exact=True).click()
    page.wait_for_url('**/shows/');be(page.locator('#site-content')).to_be_focused();closed(page)
    expect(page.locator('#site-audio').evaluate('(n,old)=>n===old',audio),'Using the menu preserves the existing audio node')
    wait_for_audio(page)
    toggle.focus();page.keyboard.press('ArrowUp')
    if area=='root':screenshot(page,f'navigation-{engine}-320-player-menu')
    expect(True,'Audio continues while the bounded phone menu and destination change')
    context.close()


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK')!='1':
        raise RuntimeError('Non-root disposable SQL environment and payment network kill switch required.')
    name=os.environ.get('SPIRIT_BROWSER','chromium')
    if name not in ('chromium','firefox','webkit'):raise RuntimeError('Unsupported browser')
    with tempfile.TemporaryDirectory(prefix='spirit-nav-polish-') as directory,sync_playwright() as driver:
        base=Path(directory);archive,code=build_package(base/'packages');browser=getattr(driver,name).launch()
        try:
            for folder,area in [('', 'root'),('band','subfolder')]:
                with site(base/area,archive,code,folder) as s:
                    s.claim();s.client.form('install/',s.input());s.login();seed_media_and_music(s);pid,_,_=seed_catalog(s)
                    before=snapshot(s)
                    desktop_header_checks(browser,s,name,area)
                    for width in (820,390,320):
                        context=browser.new_context(viewport={'width':width,'height':680},reduced_motion='reduce',bypass_csp=False)
                        page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
                        response=page.goto(s.base,wait_until='networkidle')
                        expect(response.status==200 and "script-src 'self'" in response.headers.get('content-security-policy',''),'Actual public site and original CSP are active')
                        menu_checks(page,width)
                        page.locator('.nav-toggle').click();page.locator('#public-nav').get_by_role('link',name='Music',exact=True).click()
                        page.wait_for_url('**/music/');be(page.locator('#site-content')).to_be_focused();closed(page)
                        expect(not errors,'No uncaught public-menu script errors')
                        context.close()
                    audio_checks(browser,s,name,area)
                    for width in (1440,390,320):admin_checks(browser,s,width,pid,name,area)
                    context=browser.new_context(viewport={'width':320,'height':568},java_script_enabled=False,bypass_csp=False)
                    page=context.new_page();page.goto(s.base,wait_until='load')
                    be(page.locator('.nav-toggle')).to_be_hidden()
                    for link in page.locator('#public-nav a').all():be(link).to_be_visible()
                    page.locator('#public-nav').get_by_role('link',name='Music',exact=True).click();page.wait_for_url('**/music/')
                    expect(True,'Native no-JavaScript phone navigation retains every destination')
                    context.close();expect(snapshot(s)==before,'Read-only public navigation and unsent UI interactions preserve database state')
        finally:browser.close()
    print('Public navigation, short-screen player and native admin controls passed for '+name+'.',flush=True)

if __name__=='__main__':main()
