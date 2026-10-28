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
from analytics_admin_integration import admin_fixture
from analytics_integration import fixture as analytics_fixture


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
    expect(page.locator('[data-nav-group]').count()==8,label+': eight owner categories')
    expect(page.locator('#sidebar nav a').count()==38,label+': all 38 authorized destinations present')
    expect(page.locator('[data-nav-group="analytics"] [data-nav-link]').count()==3,label+': Analytics has its own three destinations')
    expect(page.locator('[data-nav-group="audience"] [data-nav-link="analytics"]').count()==0,label+': Analytics is outside Bookings and communication')
    expect(page.locator('[data-nav-group="help"] [data-nav-link="help"]').count()==1,label+': Help Center is a dedicated permitted destination')
    expect(page.locator('[role="menu"], [role="menuitem"]').count()==0,label+': ordinary disclosure/navigation semantics retained')
    if page.viewport_size['width'] <= 600:
        heading=page.locator('.admin-breadcrumb span')
        expect(heading.evaluate('(n)=>n.getBoundingClientRect().height <= parseFloat(getComputedStyle(n).lineHeight)*1.25'),label+': mobile administration category is not broken mid-word')
        help_link=page.locator('.admin-context-help')
        expect(help_link.count()==1 and help_link.is_visible() and help_link.evaluate('(n)=>n.getBoundingClientRect().height >= 43.5'),label+': contextual Help retains a 44px phone target')
        expect(page.evaluate('''() => {
            const a=document.querySelector('.admin-context-help').getBoundingClientRect();
            const b=document.querySelector('.topbar .user-chip').getBoundingClientRect();
            return a.right <= b.left+1 || b.right <= a.left+1 || a.bottom <= b.top+1 || b.bottom <= a.top+1;
        }'''),label+': contextual Help and account identity never overlap')


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
    expected={'content':True,'music':True,'analytics':False,'audience':False,'commerce':False,'settings':False,'help':False,'system':False}
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
        page.locator('.admin-menu-toggle').focus();page.keyboard.press('Tab');be(page.locator('#sidebar .brand')).to_be_focused()
        expect(page.locator('.admin-nav-backdrop').get_attribute('tabindex')=='-1','Keyboard entry goes to navigation, not the pointer-only backdrop')
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
    open_menu(newpage,width)
    newpage.locator('[data-nav-group="music"] > summary').click()
    ready(newpage)
    expect(states(newpage)['music'] and not states(page)['music'],'Separate open pages exercise a genuinely newer category preference')
    page.reload(wait_until='networkidle');ready(page)
    expect(states(page)['music'],'Reloading an older tab cannot overwrite a newer explicit category choice')
    newpage.close()
    # Observe the actual Help Center, not only its sidebar link. Capture owner
    # topic/search screenshots at desktop, tablet and phone sizes and require
    # native keyboard navigation without changing owner data.
    response=page.goto(s.base+'admin/help/?topic=members',wait_until='networkidle')
    expect(response.status==200,f'{engine}/{area}/{width}: Help Center serves the protected topic')
    if width<=800 and page.locator('.admin-menu-toggle').get_attribute('aria-expanded')=='true':
        page.locator('.admin-menu-toggle').click()
    be(page.get_by_role('navigation',name='Help topics')).to_be_visible()
    be(page.get_by_role('heading',name='Band members',exact=True)).to_be_visible()
    be(page.locator('.help-topics a[href*="topic=members"]')).to_have_attribute('aria-current','page')
    expect(page.locator('.help-guide').count()==5,'Owner Help Center documents all five member-management tasks')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'Help topic fits {width}px without horizontal overflow')
    if engine=='chromium' and area=='root':screenshot(page,f'help-{width}-members')
    field=page.get_by_label('Search help articles')
    if width<=600:
        # In a column flex container, desktop flex-basis:300px must not become
        # a 300px-tall search input (caught in actual 320/390px screenshots).
        rect=field.bounding_box()
        expect(rect is not None and 32<=rect['height']<=64,
               f'{engine}/{area}/{width}: mobile Help search input remains a single-line control')
        submit=page.get_by_role('button',name='Search guides',exact=True).bounding_box()
        expect(submit is not None and submit['y']>=rect['y']+rect['height'],
               f'{engine}/{area}/{width}: mobile Help submit follows the field without overlap')
    field.focus()
    page.keyboard.press('Tab')
    be(page.get_by_role('button',name='Search guides',exact=True)).to_be_focused()
    page.keyboard.press('Shift+Tab')
    be(field).to_be_focused()
    field.fill('biography')
    with page.expect_navigation(wait_until='domcontentloaded'):
        field.press('Enter')
    be(page.get_by_role('heading',name='Results for “biography”')).to_be_visible()
    expect(page.locator('.help-guide').count()>0,'Native keyboard Help search returns real matching guides')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'Help search fits {width}px without horizontal overflow')
    if engine=='chromium' and area=='root':screenshot(page,f'help-{width}-search')
    guide=page.locator('.help-guide .text-link').first
    guide.focus()
    be(guide).to_be_focused()
    with page.expect_navigation(wait_until='domcontentloaded'):
        guide.press('Enter')
    expect(page.url.endswith('admin/content/?kind=member'),'Keyboard activates an authorized Help guide destination')
    expect(fixture(s,'fingerprint')==before,'Navigation, Help reading and Help search do not modify database records')
    expect(not errors,'Navigation journey has no uncaught JavaScript errors: '+repr(errors));c.close()


def identity_and_failure(browser,s,engine,area):
    c=browser.new_context(viewport={'width':1024,'height':900},reduced_motion='reduce',bypass_csp=False)
    page=c.new_page();signin(s,page);owner_scope=page.locator('#sidebar').get_attribute('data-nav-scope')
    page.locator('[data-nav-action="collapse"]').click();page.locator('.admin-sidebar-actions button').click();page.wait_for_url('**/admin/login.php')
    password='Menu-fixture-only-Password-492'
    for role,links,groups in [('owner',38,8),('content_editor',12,4),('store_manager',7,4)]:
        signin(s,page,'menu-'+role+'@example.test',password)
        expect(page.locator('#sidebar').get_attribute('data-nav-scope')!=owner_scope,'Different accounts have independent opaque preference scopes')
        expect(all(states(page).values()),'One account\'s collapsed layout does not spill into another')
        expect(page.locator('#sidebar nav a').count()==links and len(states(page))==groups,'Server filters links and empty categories for '+role)
        expect(page.locator('#sidebar [data-nav-link="analytics"]').count()==int(role=='owner'),'Audience analytics navigation is owner-only for '+role)
        expect(page.locator('#sidebar [data-nav-link="help"]').count()==1,'Help Center is available to every authorized staff role: '+role)
        page.goto(s.base+'admin/help/?topic=analytics',wait_until='networkidle')
        expect(page.locator('.help-search').count()==1,'Permitted '+role+' can read Help without a forbidden response')
        if role=='owner':
            expect(page.locator('.help-topics a[href*="topic=analytics"]').count()==1,'Owner retains Analytics Help topic')
        else:
            expect(page.locator('.help-topics a[href*="topic=analytics"]').count()==0 and
                   page.locator('.help-guide a[href*="/admin/analytics/"]').count()==0,
                   'Staff Help navigation excludes owner-only Analytics topics and guides: '+role)
        page.goto(s.base+'admin/',wait_until='networkidle');ready(page)
        expect(page.locator('[data-analytics-snapshot]').count()==int(role=='owner'),'The Overview analytics snapshot is owner-only for '+role)
        if role!='owner':
            expect(page.locator('#sidebar a[href*="admin/orders/payments"],#sidebar a[href*="admin/shop/customers"],#sidebar a[href*="admin/staff/"]').count()==0,'Staff do not receive owner financial/customer/access links')
            expect(page.goto(s.base+'admin/orders/payments.php').status==403,'A direct forbidden URL is still rejected by server permissions')
            expect(page.goto(s.base+'admin/analytics/').status==403,'Direct audience analytics access is rejected for '+role)
            expect(page.goto(s.base+'admin/analytics/visitors.php').status==403,'Direct visitor exploration is rejected for '+role)
            expect(page.goto(s.base+'admin/analytics/geography.php').status==403,'Direct geography access is rejected for '+role)
            page.goto(s.base+'admin/');ready(page)
        page.locator('.admin-sidebar-actions button').click();page.wait_for_url('**/admin/login.php')
    signin(s,page);expect(not any(states(page).values()),'Returning to the first owner restores that owner\'s layout')
    # Active store subsections must be more precise than the old generic store key.
    for route,key in [('admin/shop/categories.php','store-categories'),('admin/shop/customers.php','customers'),('admin/shop/settings.php','store-settings'),('admin/orders/payments.php?environment=sandbox','payment-settings'),('admin/orders/policy.php','delivery-policy'),('admin/orders/webhooks.php','payment-notifications'),('admin/analytics/','analytics'),('admin/analytics/visitors.php','analytics-visitors'),('admin/analytics/geography.php','analytics-geography')]:
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
    page.goto(s.base+'admin/help/',wait_until='domcontentloaded')
    field=page.get_by_label('Search help articles')
    be(field).to_be_visible()
    rect=field.bounding_box()
    expect(rect is not None and 32<=rect['height']<=64,
           'No-JavaScript 390px Help search input remains a normal single-line control')
    field.fill('biography')
    with page.expect_navigation(wait_until='domcontentloaded'):
        page.get_by_role('button',name='Search guides',exact=True).press('Enter')
    expect(page.locator('.help-guide').count()>0,'No-JavaScript mobile Help search returns matching guides')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),'No-JavaScript Help search fits 390px')
    screenshot(page,f'help-{engine}-{area}-native-390')
    c.close()


def audience_overview(browser,s,engine,area):
    """A real owner snapshot must show stored data while preserving the database."""
    c=browser.new_context(viewport={'width':1440,'height':960},reduced_motion='reduce',bypass_csp=False)
    page=c.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)));signin(s,page)
    be(page.locator('[data-overview-metric="visits"]')).to_have_text('0')
    expect('No public traffic recorded' in page.locator('[data-analytics-snapshot]').inner_text(),'Fresh Overview honestly shows an empty audience snapshot')
    admin_fixture(s,'seed')
    before=fixture(s,'fingerprint')
    response=page.reload(wait_until='networkidle');ready(page)
    expect(response.status==200 and 'no-store' in response.headers.get('cache-control',''),'Owner Overview snapshot is a private uncached report')
    be(page.locator('[data-overview-metric="visits"]')).to_have_text('67')
    be(page.locator('[data-overview-metric="pageviews"]')).to_have_text('131')
    be(page.locator('[data-overview-metric="bounce"]')).to_have_text('35.8%')
    expect('Last 7 days' in page.locator('.overview-period').inner_text() and 'UTC' in page.locator('.overview-period').inner_text(),'Snapshot states its seven-day UTC reporting window')
    expect('at page load' in page.locator('.tone-live').inner_text() and 'reload to refresh' in page.locator('.overview-audience-footer').inner_text(),'At-load live count is not presented as a continuously polling metric')
    for width in [1440,390,320]:
        page.set_viewport_size({'width':width,'height':960})
        expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),'Overview analytics fits '+str(width)+'px without document overflow')
        expect(page.locator('.overview-audience-metrics .overview-metric').count()==6,'Six colored analytics snapshots are present at '+str(width)+'px')
        chart=page.locator('.overview-traffic .analytics-trend')
        expect(chart.count()==1 and chart.locator('.chart-visits').count()==1 and chart.locator('.chart-pageviews').count()==1,'Overview renders both measured traffic series at '+str(width)+'px')
        if width==1440:
            box=chart.bounding_box()
            expect(box is not None and box['y']+box['height']<=960,'Overview traffic chart is visible in the first desktop screen')
        if width in [1440,390]:screenshot(page,f'analytics-overview-{engine}-{area}-{width}-synthetic')
    page.locator('.overview-daily summary').click()
    expect(page.locator('.overview-daily tbody tr').count()==7,'Native daily data reveals the same seven-day snapshot')
    expect('google.com' in page.locator('.overview-audience-highlights').inner_text() and 'United States' in page.locator('.overview-audience-highlights').inner_text(),'Overview highlights reflect real synthetic source and country aggregates')
    expect(fixture(s,'fingerprint')==before,'Overview read, resize and daily disclosure do not write database records')
    page.locator('.overview-audience-heading a').click();page.wait_for_url('**/admin/analytics/?range=7')
    expect(page.locator('#sidebar a[aria-current="page"]').get_attribute('data-nav-link')=='analytics','Overview links into the real seven-day Analytics dashboard')
    analytics_fixture(s,'settings',{'enabled':'0'})
    try:
        before=fixture(s,'fingerprint')
        page.goto(s.base+'admin/',wait_until='networkidle')
        be(page.locator('.overview-audience-notice')).to_contain_text('Collection is paused')
        be(page.locator('[data-overview-metric="visits"]')).to_have_text('67')
        expect(fixture(s,'fingerprint')==before,'Paused collection preserves the existing report and Overview performs no writes')
    finally: analytics_fixture(s,'settings',{'enabled':'1'})
    analytics_fixture(s,'break')
    try:
        response=page.reload(wait_until='networkidle')
        expect(response.status==200 and page.locator('.overview-audience-unavailable').is_visible(),'An unavailable analytics table does not break the owner Overview')
        expect(page.locator('[data-overview-metric]').count()==0 and page.locator('.stats .stat').count()==3,'Unavailable reporting shows no invented zero metrics and retains the operational summaries')
    finally: analytics_fixture(s,'restore')
    expect(not errors,'Overview analytics has no uncaught browser errors');c.close()
    native=browser.new_context(viewport={'width':390,'height':960},java_script_enabled=False,bypass_csp=False)
    page=native.new_page();page.goto(s.base+'admin/login.php');page.locator('[name="email"]').fill(s.owner);page.locator('[name="password"]').fill(s.password);page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')
    be(page.locator('[data-overview-metric="visits"]')).to_have_text('67')
    expect(page.locator('.overview-traffic .chart-visits').count()==1,'Overview chart renders without JavaScript')
    page.locator('.overview-daily summary').click();expect(page.locator('.overview-daily tbody').is_visible(),'Overview daily data disclosure works without JavaScript')
    page.locator('.overview-audience-footer a').filter(has_text='Explore visitors').click();page.wait_for_url('**/admin/analytics/visitors.php?range=7')
    expect(page.locator('h1').is_visible(),'Native Overview visitor-explorer link opens the real report')
    native.close()


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
                        subprocess.run(['php',str(ROOT/'app/tests/navigation_fixture.php')],env=env,check=True,timeout=15)
                        audience_overview(browser,s,engine,area)
                        for width in [1440,820,390,320]:journey(browser,s,width,engine,area)
                    # Separate disposable instance keeps repeated login tests below
                    # the real per-account limit without weakening rate limiting.
                    with site(base/(area+'-identities'),archive,code,folder) as s:
                        s.claim();s.client.form('install/',s.input());s.login()
                        env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
                        subprocess.run(['php',str(ROOT/'app/tests/navigation_fixture.php')],env=env,check=True,timeout=15)
                        identity_and_failure(browser,s,engine,area)
            finally:browser.close()
    print('Grouped administration navigation, roles, same-page refresh and stored layout passed for '+engine+'.',flush=True)

if __name__=='__main__':main()
