#!/usr/bin/env python3
"""Real account/category/reload/URL/scroll checks; no provider calls or live data."""
from __future__ import annotations
import json
import os
import subprocess
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as be
from http_integration import ROOT, build_package, expect, site, dashboard
from launch_integration import fixture
from public_browser import screenshot


def signin(s,page,email=None,password=None):
    page.goto(s.base+'admin/login.php',wait_until='networkidle')
    page.locator('[name="email"]').fill(email or s.owner);page.locator('[name="password"]').fill(password or s.password)
    page.get_by_role('button',name='Enter backstage').click();page.wait_for_url(lambda url:dashboard(str(url)))
    ready(page)


def ready(page):
    be(page.locator('#sidebar')).to_have_attribute('data-nav-ready','true')
    page.evaluate('()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))')


def open_menu(page,width):
    if width<=800 and page.locator('.admin-menu-toggle').get_attribute('aria-expanded')!='true':page.locator('.admin-menu-toggle').click()


def states(page):return page.locator('[data-nav-group]').evaluate_all('(nodes)=>Object.fromEntries(nodes.map(n=>[n.dataset.navGroup,n.open]))')


def shape(page,label):
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),label+': no page overflow')
    expect(page.locator('#sidebar a[aria-current="page"]').count()==1,label+': exactly one current destination')
    expect(page.locator('[data-nav-group]').count()==6,label+': six owner categories')
    expect(page.locator('#sidebar nav a').count()==34,label+': all 34 authorized destinations present')
    expect(page.locator('[role="menu"], [role="menuitem"]').count()==0,label+': ordinary disclosure/navigation semantics retained')


def journey(browser,s,width,engine,area):
    c=browser.new_context(viewport={'width':width,'height':900},reduced_motion='reduce',bypass_csp=False)
    page=c.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)));signin(s,page)
    before=fixture(s,'fingerprint')
    route='admin/content/?kind=page&q=Menu%20check&page=2#main'
    page.goto(s.base+route,wait_until='networkidle');ready(page);open_menu(page,width)
    shape(page,f'{engine}/{area}/{width}')
    expect(all(states(page).values()),'First visit starts with every permitted category expanded')
    page.locator('[data-nav-action="collapse"]').click()
    page.locator('[data-nav-group="content"] > summary').focus();page.keyboard.press('Enter')
    page.locator('[data-nav-group="music"] > summary').focus();page.keyboard.press('Space')
    expected={'content':True,'music':True,'audience':False,'commerce':False,'settings':False,'system':False}
    expect(states(page)==expected,'Keyboard Enter/Space selects independent expanded categories')
    exact=page.url;page.reload(wait_until='networkidle');ready(page)
    expect(page.url==exact and states(page)==expected,'Refresh preserves exact query/hash URL and category choices')
    if width<=800:expect(page.locator('.admin-menu-toggle').get_attribute('aria-expanded')=='true','Refreshing an open phone menu keeps it open')
    page.locator('[data-nav-group="content"] > summary').click();page.reload(wait_until='networkidle');ready(page)
    expect(not states(page)['content'] and page.locator('[data-nav-group="content"]').get_attribute('class').find('has-current')>=0,'An intentionally closed current category stays closed and remains marked current')
    expect(page.locator('.admin-breadcrumb strong').inner_text()!='Overview','Current-page context remains visible without redirecting to Overview')
    page.locator('[data-nav-group="content"] > summary').click()
    page.locator('#sidebar [data-nav-link="library"]').click();page.wait_for_url('**/admin/media/');ready(page)
    expect(states(page)['content'] and not states(page)['system'],'Following a page link preserves category preferences')
    if width<=800:
        expect(page.locator('.admin-menu-toggle').get_attribute('aria-expanded')=='false','Choosing a phone destination closes only the drawer, not its categories')
        expect(page.locator('#sidebar').evaluate('(n)=>n.inert') and not page.locator('.workspace').evaluate('(n)=>n.inert'),'Closed phone menu is out of keyboard focus and page remains usable')
        open_menu(page,width);page.keyboard.press('Escape')
        be(page.locator('.admin-menu-toggle')).to_be_focused()
        open_menu(page,width)
        page.locator('.admin-sidebar-actions button').focus();page.keyboard.press('Tab');be(page.locator('.admin-menu-toggle')).to_be_focused()
        page.keyboard.press('Shift+Tab');be(page.locator('.admin-sidebar-actions button')).to_be_focused()
        expect(page.locator('.workspace').evaluate('(n)=>n.inert'),'Open phone drawer isolates page focus')
    # Do not store or restore page URLs/form fields; only sidebar pixel offset.
    page.locator('[data-nav-action="expand"]').click();ready(page)
    page.locator('#admin-nav-scroll').evaluate('(n)=>{n.scrollTop=240;}');ready(page)
    offset=page.locator('#admin-nav-scroll').evaluate('(n)=>n.scrollTop')
    expect(offset>100,'The expanded sidebar has real independent overflow to exercise')
    page.reload(wait_until='networkidle');ready(page)
    expect(abs(page.locator('#admin-nav-scroll').evaluate('(n)=>n.scrollTop')-offset)<3,'Reload restores the sidebar scroll position')
    page.locator('[data-nav-action="collapse"]').click();page.reload(wait_until='networkidle');ready(page)
    expect(not any(states(page).values()),'Collapse all survives a page refresh')
    page.locator('[data-nav-group="content"] > summary').click()
    page.locator('#admin-nav-scroll').evaluate('(n)=>{n.scrollTop=0;}')
    screenshot(page,f'admin-nav-{engine}-{area}-{width}')
    # Per-tab drawer/scroll and per-account group namespace never take over another URL.
    newpage=c.new_page();newpage.goto(s.base+'admin/bookings/?status=reviewing&q=Menu&page=2',wait_until='networkidle');ready(newpage)
    expect(newpage.url.endswith('admin/bookings/?status=reviewing&q=Menu&page=2'),'A new tab keeps its independently chosen page')
    expect(states(newpage)==states(page),'Same account in this browser retains its category choices')
    if width<=800:expect(newpage.locator('.admin-menu-toggle').get_attribute('aria-expanded')=='false','New tabs do not inherit another tab\'s open drawer')
    newpage.close()
    expect(fixture(s,'fingerprint')==before,'Navigation toggles, reloads, links and preferences do not modify database records')
    expect(not errors,'Navigation journey has no uncaught JavaScript errors: '+repr(errors));c.close()


def identity_and_failure(browser,s,engine,area):
    c=browser.new_context(viewport={'width':1024,'height':900},reduced_motion='reduce',bypass_csp=False)
    page=c.new_page();signin(s,page);owner_scope=page.locator('#sidebar').get_attribute('data-nav-scope')
    page.locator('[data-nav-action="collapse"]').click();page.locator('.admin-sidebar-actions button').click();page.wait_for_url('**/admin/login.php')
    password='Menu-fixture-only-Password-492'
    for role,links,groups in [('owner',34,6),('content_editor',11,3),('store_manager',6,3)]:
        signin(s,page,'menu-'+role+'@example.test',password)
        expect(page.locator('#sidebar').get_attribute('data-nav-scope')!=owner_scope,'Different accounts have independent opaque preference scopes')
        expect(all(states(page).values()),'One account\'s collapsed layout does not spill into another')
        expect(page.locator('#sidebar nav a').count()==links and len(states(page))==groups,'Server filters links and empty categories for '+role)
        if role!='owner':
            expect(page.locator('#sidebar a[href*="admin/orders/payments"],#sidebar a[href*="admin/shop/customers"],#sidebar a[href*="admin/staff/"]').count()==0,'Staff do not receive owner financial/customer/access links')
            expect(page.goto(s.base+'admin/orders/payments.php').status==403,'A direct forbidden URL is still rejected by server permissions')
            page.goto(s.base+'admin/');ready(page)
        page.locator('.admin-sidebar-actions button').click();page.wait_for_url('**/admin/login.php')
    signin(s,page);expect(not any(states(page).values()),'Returning to the first owner restores that owner\'s layout')
    # Active store subsections must be more precise than the old generic store key.
    for route,key in [('admin/shop/categories.php','store-categories'),('admin/shop/customers.php','customers'),('admin/shop/settings.php','store-settings'),('admin/orders/payments.php?environment=sandbox','payment-settings'),('admin/orders/policy.php','delivery-policy'),('admin/orders/webhooks.php','payment-notifications')]:
        page.goto(s.base+route,wait_until='networkidle');ready(page)
        be(page.locator('#sidebar a[aria-current="page"]')).to_have_attribute('data-nav-link',key)
        expect(not any(states(page).values()),'Direct subsection entry does not reopen deliberately collapsed categories')
    key='spirit:admin-nav:v1:'+owner_scope
    for bad in ['not-json',json.dumps({'version':1,'groups':{'content':'false','music':0,'system':{},'untrusted':True},'lastPage':'https://example.invalid'}),'x'*5000]:
        page.evaluate('([key,value])=>localStorage.setItem(key,value)',[key,bad])
        probe=c.new_page();probe.goto(page.url,wait_until='networkidle');ready(probe)
        expect(all(states(probe).values()) and probe.url==page.url,'Corrupt/oversized/unknown preference values safely fall back without URL redirection')
        probe.close()
    stored=page.evaluate('()=>({local:Object.values(localStorage),session:Object.values(sessionStorage)})')
    expect(all('lastPage' not in value for value in stored['session']),'The tab-state writer never stores page URLs')
    c.close()
    # Browser privacy settings may forbid storage. Actual property access throws.
    c=browser.new_context(viewport={'width':390,'height':900},bypass_csp=False)
    c.add_init_script("for (const key of ['localStorage','sessionStorage']) Object.defineProperty(window,key,{get(){throw new DOMException('Disabled','SecurityError')}});")
    page=c.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)));signin(s,page);open_menu(page,390)
    page.locator('[data-nav-action="collapse"]').click();page.locator('[data-nav-group="content"] > summary').click()
    be(page.locator('#admin-nav-memory')).to_contain_text('cannot save')
    page.locator('[data-nav-link="library"]').click();page.wait_for_url('**/admin/media/');ready(page)
    expect(not errors,'Unavailable storage does not crash or disable navigation');c.close()
    c=browser.new_context(viewport={'width':390,'height':900},java_script_enabled=False,bypass_csp=False)
    page=c.new_page();page.goto(s.base+'admin/login.php');page.locator('[name="email"]').fill(s.owner);page.locator('[name="password"]').fill(s.password);page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')
    expect(page.locator('#sidebar').is_visible() and page.locator('.admin-menu-toggle').is_hidden(),'Without JavaScript, native mobile navigation stays visible')
    summary=page.locator('[data-nav-group="music"] > summary');summary.click();expect(page.locator('[data-nav-link="track"]').is_hidden(),'Native details collapse without page JavaScript');summary.click()
    page.locator('[data-nav-link="track"]').click();page.wait_for_url('**/admin/content/?kind=track')
    expect(page.locator('h1').is_visible(),'Native links still navigate to the intended page without JavaScript')
    screenshot(page,f'admin-nav-{engine}-{area}-native');c.close()


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK')!='1':raise RuntimeError('Use a non-root disposable database and disabled payment network.')
    engine=os.environ.get('SPIRIT_BROWSER','chromium')
    if engine not in ['chromium','firefox','webkit']:raise RuntimeError('Unknown engine')
    with tempfile.TemporaryDirectory(prefix='spirit-admin-navigation-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with sync_playwright() as driver:
            browser=getattr(driver,engine).launch()
            try:
                for folder,area in [('', 'root'),('band','subfolder')]:
                    with site(base/area,archive,code,folder) as s:
                        s.claim();s.client.form('install/',s.input());s.login()
                        env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
                        subprocess.run(['php',str(ROOT/'tests/navigation_fixture.php')],env=env,check=True,timeout=15)
                        for width in [1440,820,390,320]:journey(browser,s,width,engine,area)
                    # Separate disposable instance keeps repeated login tests below
                    # the real per-account limit without weakening rate limiting.
                    with site(base/(area+'-identities'),archive,code,folder) as s:
                        s.claim();s.client.form('install/',s.input());s.login()
                        env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
                        subprocess.run(['php',str(ROOT/'tests/navigation_fixture.php')],env=env,check=True,timeout=15)
                        identity_and_failure(browser,s,engine,area)
            finally:browser.close()
    print('Grouped administration navigation, roles, same-page refresh and stored layout passed for '+engine+'.',flush=True)

if __name__=='__main__':main()
