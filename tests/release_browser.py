#!/usr/bin/env python3
"""Release-candidate public/form checks on disposable root/subfolder websites.
No real band inventory, mailbox delivery, provider charge or host acceptance.
"""
from __future__ import annotations
import json
import os
import tempfile
import urllib.parse
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import Client, build_package, expect, site
from content_integration import save_content, seed_media_and_music
from commerce_integration import seed_catalog, product_values, snapshot
from gallery_browser import upload_image, open_nav
from payment_integration import configure, checkout_values, snapshot as payment_snapshot
from public_browser import screenshot


def audit(page, label, width):
    expect(page.locator('h1').count()==1 and page.locator('h1').is_visible(), label+': one visible primary heading')
    result=page.evaluate('''() => {
      const visible=n=>n.getClientRects().length>0&&getComputedStyle(n).visibility!=='hidden'&&!n.closest('.honeypot,[aria-hidden="true"]');
      const name=n=>{
        if(n.getAttribute('aria-label'))return n.getAttribute('aria-label').trim();
        if(n.getAttribute('aria-labelledby'))return n.getAttribute('aria-labelledby').split(/\\s+/).map(id=>document.getElementById(id)?.textContent||'').join(' ').trim();
        if(n.labels?.length)return [...n.labels].map(l=>l.textContent).join(' ').trim();
        const copy=n.cloneNode(true);copy.querySelectorAll('[aria-hidden="true"],script,style,noscript').forEach(x=>x.remove());
        return (copy.textContent+[...copy.querySelectorAll('img[alt]')].map(x=>x.alt).join(' ')).trim();
      };
      const controls=[...document.querySelectorAll('a[href],button,input:not([type="hidden"]),select,textarea,summary')].filter(visible);
      const ids=[...document.querySelectorAll('[id]')].map(n=>n.id);
      return {
        overflow:document.documentElement.scrollWidth>innerWidth+1,
        clipped:controls.filter(n=>{const r=n.getBoundingClientRect();return r.left< -1||r.right>innerWidth+1;}).map(n=>n.tagName+':'+name(n).slice(0,60)),
        unnamed:controls.filter(n=>!name(n)).map(n=>n.outerHTML.slice(0,150)),
        duplicates:ids.filter((id,i)=>ids.indexOf(id)!==i),
        small:controls.filter(n=>n.matches('input:not([type="checkbox"]):not([type="range"]),select,textarea')&&parseFloat(getComputedStyle(n).fontSize)<16).map(n=>n.name),
        brokenImages:[...document.querySelectorAll('img[src]')].filter(n=>visible(n)&&n.complete&&n.naturalWidth===0).map(n=>n.src)
      };
    }''')
    expect(not result['overflow'] and not result['clipped'],label+': controls fit the viewport '+repr(result['clipped']))
    expect(not result['unnamed'],label+': visible links and controls have structural names '+repr(result['unnamed']))
    expect(not result['duplicates'],label+': no duplicate element identifiers')
    expect(not result['brokenImages'],label+': no completed broken images')
    if width<=600:expect(not result['small'],label+': phone entry controls use at least 16px text '+repr(result['small']))


def card_geometry(page):
    box=page.locator('.merch-art').first
    box.scroll_into_view_if_needed()
    box.locator('img').evaluate('(n)=>n.decode()')
    state=box.evaluate('''n=>{const r=n.getBoundingClientRect(),image=n.querySelector('img'),i=image.getBoundingClientRect();return {square:Math.abs(r.width-r.height)<1,contained:i.left>=r.left&&i.right<=r.right+1&&i.top>=r.top&&i.bottom<=r.bottom+1,fit:getComputedStyle(image).objectFit};}''')
    expect(state['square'] and state['contained'] and state['fit']=='contain','Portrait catalog photograph stays fully inside a square without cropping')


def browsing(browser,s,width,name,area):
    context=browser.new_context(viewport={'width':width,'height':900},reduced_motion='reduce',bypass_csp=False)
    page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
    routes=['','music/','music/release.php?slug=fixture-release','music/release.php?slug=artwork-pending',
            'shows/','shows/event.php?slug=release-show','band/','band/member.php?slug=release-member',
            'media/','press/','booking/','newsletter/','shop/','shop/product.php?slug=fixture-shirt',
            'pages/search.php?q=Fixture','pages/?slug=privacy','account/login.php','account/register.php',
            'account/forgot.php','shop/cart.php','shop/checkout.php']
    for route in routes:
        response=page.goto(s.base+route,wait_until='networkidle')
        label=f'{name}/{area}/{width}/{route or "home"}'
        expect(response.status==200,label+': successful endpoint')
        policy=response.headers.get('content-security-policy','')
        expect("script-src 'self'" in policy and 'unsafe-eval' not in policy,label+': application script policy is unchanged')
        audit(page,label,width)
        expect(page.get_by_role('navigation',name='Website policies',exact=True).get_by_role('link',name='Privacy',exact=True).count()==1,label+': published privacy link available')
        expect(page.get_by_role('navigation',name='Website policies',exact=True).get_by_role('link',name='Terms',exact=True).count()==0,label+': private policy draft is not advertised')
        if route=='shop/':
            card_geometry(page)
            page.evaluate('window.scrollTo(0,0)');screenshot(page,f'release-{name}-{area}-{width}-catalog')
        if route=='shop/product.php?slug=fixture-shirt':
            expect(page.locator('.product-price').evaluate('(n)=>parseFloat(getComputedStyle(n).fontSize)')>=28,'Product price retains its designed emphasis instead of inheriting small paragraph text')
        if route=='media/':
            photo=page.locator('.photo-open').first;photo.focus();page.keyboard.press('Enter')
            browser_expect(page.get_by_role('dialog',name='Photograph preview')).to_be_visible()
            page.keyboard.press('Escape');browser_expect(photo).to_be_focused()
            expect(photo.get_attribute('href')==photo.get_attribute('data-photo'),'Enhanced gallery retains an authorized native target')
        if route=='music/release.php?slug=fixture-release':
            expect(page.locator('.native-track audio').count()==0 or not page.locator('.native-track audio').first.is_visible(),'JavaScript playback does not display a second native player')
    # Public input errors must not turn unavailable content into successful pages.
    for route in ['pages/?slug=terms','pages/?slug=missing-release-page','shop/product.php?slug=not-published']:
        response=page.goto(s.base+route,wait_until='networkidle')
        expect(response.status==404,'Unavailable or draft-only route returns an actual 404: '+route)
        audit(page,f'{name}/{area}/{width}/404',width)
    if area=='subfolder' and width==320:
        page.goto(s.base+'booking/',wait_until='networkidle')
        page.evaluate('document.documentElement.style.fontSize="200%"')
        # A text-size stress check, not a claim of native browser zoom coverage.
        audit(page,f'{name}/enlarged-text',width)
        screenshot(page,f'release-{name}-{area}-320-enlarged-text')
    expect(not errors,f'{name}/{area}/{width}: no uncaught application exceptions '+repr(errors))
    context.close()


def native_access(browser,s,name,area):
    context=browser.new_context(viewport={'width':390,'height':900},java_script_enabled=False,bypass_csp=False)
    page=context.new_page();page.goto(s.base,wait_until='load')
    nav=page.get_by_role('navigation',name='Main navigation')
    expect(nav.is_visible() and not page.locator('.nav-toggle').is_visible(),'Without JavaScript, mobile navigation is expanded instead of a dead Menu button')
    for link in nav.get_by_role('link').all():expect(link.is_visible(),'A native mobile navigation destination remains visible')
    nav.get_by_role('link',name='Music',exact=True).click();page.wait_for_url('**/music/')
    page.get_by_role('link',name='Fixture Release',exact=True).click();page.wait_for_url('**/music/release.php?slug=fixture-release')
    native=page.locator('.native-track audio').first
    expect(native.is_visible() and native.get_attribute('controls') is not None and native.get_attribute('preload')=='none','No-JavaScript music provides visible user-initiated native audio controls')
    expect(page.locator('.play-track').first.is_hidden(),'Inactive JavaScript-only track controls do not mislead native users')
    expect('/media/file.php?id=' in native.get_attribute('src'),'Native audio uses the existing authorized endpoint')
    screenshot(page,f'release-{name}-{area}-390-native-music')
    nav.get_by_role('link',name='Media',exact=True).click();page.wait_for_url('**/media/')
    href=page.locator('.photo-open').first.get_attribute('href')
    with page.expect_navigation() as event:page.locator('.photo-open').first.click()
    expect('image/' in event.value.headers.get('content-type',''),'Native photograph link opens actual authorized image bytes')
    expect(page.url==urllib.parse.urljoin(s.base,href),'Native photograph keeps the intended media destination')
    page.goto(s.base+'booking/');page.locator('[name="name"]').fill('Native fixture organizer');page.locator('[name="email"]').fill('native@example.test')
    page.locator('[name="location"]').fill('Fixture Louisiana venue');page.locator('[name="contact_consent"]').check()
    page.get_by_role('button',name='Send inquiry').click();page.wait_for_url('**/booking/thanks.php')
    expect(page.locator('.reference-number strong').inner_text().startswith('SG-'),'Native booking form saves a real inquiry without claiming delivery or a reservation')
    # URL completion alone can precede final stylesheet loading after a native POST.
    page.wait_for_load_state('networkidle')
    page.keyboard.press('Control+Home')
    expect(nav.get_by_role('link',name='Book the band',exact=True).evaluate('(n)=>getComputedStyle(n).color')=='rgb(19, 28, 35)','Active gold booking button retains contrasting dark text')
    screenshot(page,f'release-{name}-{area}-390-native-booking');context.close()


def checkout(browser,s,name,area):
    # Exercise all entry controls, review and cancellation; never charge anything.
    configure(s)
    context=browser.new_context(viewport={'width':390,'height':900},reduced_motion='reduce',bypass_csp=False)
    context.route('https://jstest.authorize.net/v1/Accept.js',lambda route:route.abort())
    page=context.new_page();page.goto(s.base+'shop/product.php?slug=fixture-shirt')
    page.get_by_role('button',name='Add to bag').click();page.wait_for_url('**/shop/cart.php')
    audit(page,f'{name}/{area}/filled-cart',390)
    page.get_by_role('link',name='Continue to checkout').click();page.wait_for_url('**/shop/checkout.php')
    audit(page,f'{name}/{area}/delivery-form',390)
    values=checkout_values()
    for key in ['email','recipient','line_one','city','postal_code']:page.locator('.checkout-page [name="'+key+'"]').fill(values[key])
    page.locator('[name="region"]').select_option('LA');page.locator('[name="order_consent"]').check()
    page.evaluate('window.scrollTo(0,0)')
    screenshot(page,f'release-{name}-{area}-390-delivery')
    page.get_by_role('button',name='Review order and total').click();page.wait_for_url('**/shop/order.php?reference=*')
    audit(page,f'{name}/{area}/private-order',390)
    expect(page.locator('#card-pay-button').is_disabled(),'An unavailable provider library leaves card submission disabled')
    expect(page.locator('#card-payment input[name]').count()==4,'Only CSRF, nonce and opaque token fields have submission names')
    screenshot(page,f'release-{name}-{area}-390-order')
    # The cancellation is a native POST/303 to the same receipt URL. A click
    # can return before its new document is visible; observe both the actual
    # response and the authoritative state, not an immediate old-DOM count.
    order_url=page.url
    reference=urllib.parse.parse_qs(urllib.parse.urlsplit(order_url).query)['reference'][0]
    before=payment_snapshot(s)
    matching=[order for order in before['orders'] if order['reference']==reference]
    expect(len(matching)==1,'The browser receipt identifies exactly one persisted order')
    original=matching[0];order_id=int(original['id'])
    expect(original['payment_state']=='awaiting_payment' and original['reservation_state']=='held' and original['transaction_id'] is None,'The cancellation starts with unpaid, actually reserved stock')
    with page.expect_response(lambda response:response.url==order_url and response.request.method=='POST') as event:
        page.get_by_role('button',name='Cancel this checkout and release stock').click()
    expect(event.value.status==303,'Actual cancellation POST redirects after its server transaction')
    browser_expect(page.locator('.payment-panel').get_by_role('heading',name='Checkout cancelled',exact=True)).to_be_visible()
    browser_expect(page.locator('#card-payment')).to_have_count(0)
    expect(page.url==order_url,'Cancellation shows the same private receipt without a payment form')
    after=payment_snapshot(s)
    cancelled=next(order for order in after['orders'] if int(order['id'])==order_id)
    expect(cancelled['payment_state']=='cancelled' and cancelled['reservation_state']=='released' and int(cancelled['revision'])==int(original['revision'])+1,'Cancellation commits one revision and releases the actual reservation')
    expect(cancelled['transaction_id'] is None and after['payment_operations']==before['payment_operations'],'Cancellation creates no payment operation or transaction identifier')
    prior_adjustments={int(row['id']) for row in before['inventory_adjustments']}
    released=[row for row in after['inventory_adjustments'] if int(row['id']) not in prior_adjustments]
    expected=sorted((int(item['variant_id']),int(item['quantity'])) for item in before['order_items'] if int(item['order_id'])==order_id)
    expect(expected and sorted((int(row['variant_id']),int(row['quantity_delta'])) for row in released)==expected and all(row['reason']==f'Order #{order_id} release' for row in released),'Exactly the reserved item quantities return to inventory once')
    screenshot(page,f'release-{name}-{area}-390-cancelled-order')
    # A fresh GET must retain the result, not issue another cancellation/charge.
    page.reload(wait_until='domcontentloaded')
    browser_expect(page.locator('.payment-panel').get_by_role('heading',name='Checkout cancelled',exact=True)).to_be_visible()
    browser_expect(page.locator('#card-payment')).to_have_count(0)
    refreshed=payment_snapshot(s)
    expect(next(order for order in refreshed['orders'] if int(order['id'])==order_id)==cancelled and refreshed['inventory_adjustments']==after['inventory_adjustments'] and refreshed['payment_operations']==after['payment_operations'],'Receipt refresh preserves the cancelled order without releasing stock twice')
    context.close()


def withdrawn_policy(browser,s,privacy,name):
    context=browser.new_context(viewport={'width':390,'height':900},bypass_csp=False)
    page=context.new_page();page.goto(s.base)
    expect(page.locator('.policy-links a').count()==1,'Only the published policy appears in the shell')
    save_content(s,'page','Fixture privacy',{'body':'Synthetic policy for testing only.'},action='unpublish',item_id=privacy,slug='privacy')
    open_nav(page,'Music');page.wait_for_url('**/music/')
    browser_expect(page.locator('.policy-links')).to_have_count(0)
    expect(Client(s.base).request('pages/?slug=privacy')[0]==404,'Withdrawn policy is absent both from refreshed navigation and the public endpoint')
    context.close()


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK')!='1':
        raise RuntimeError('Non-root disposable database and payment-network kill switch required.')
    name=os.environ.get('SPIRIT_BROWSER','chromium')
    if name not in ['chromium','firefox','webkit']:raise RuntimeError('Unknown browser engine')
    with tempfile.TemporaryDirectory(prefix='spirit-release-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with sync_playwright() as driver:
            browser=getattr(driver,name).launch()
            try:
                for folder,area in [('', 'root'),('band','subfolder')]:
                    with site(base/area,archive,code,folder) as s:
                        s.claim();s.client.form('install/',s.input());s.login()
                        image,_,_,_=seed_media_and_music(s);pid,_,_=seed_catalog(s)
                        portrait=upload_image(s,800,1200,2)
                        s.client.form(f'admin/shop/edit.php?id={pid}',product_values(s,pid,portrait,'publish'))
                        save_content(s,'release','Artwork pending',{'release_type':'single','cover_id':'0'},slug='artwork-pending')
                        save_content(s,'member','Fixture musician',{'role':'Fixture instrument','bio':'Synthetic member for layout verification.','image_id':str(portrait)},slug='release-member')
                        save_content(s,'gallery','Fixture photo',{'image_id':str(image),'caption':'Synthetic gallery photograph.'},slug='release-photo')
                        save_content(s,'event','Fixture show',{'starts_at':'2099-11-08T20:00','venue':'Synthetic venue','city':'Lafayette','timezone':'America/Chicago','ticket_url':'https://tickets.example.test/fixture'},slug='release-show')
                        privacy=save_content(s,'page','Fixture privacy',{'body':'Synthetic policy for testing only.'},slug='privacy')
                        save_content(s,'page','PRIVATE POLICY DRAFT',{'body':'Never publish this fixture.'},action='save',slug='terms')
                        before=snapshot(s)
                        for width in [1440,390,320]:browsing(browser,s,width,name,area)
                        expect(snapshot(s)==before,'Reading the release review pages does not mutate catalog, stock, identities or audit history')
                        native_access(browser,s,name,area)
                        checkout(browser,s,name,area)
                        withdrawn_policy(browser,s,privacy,name)
            finally:browser.close()
    print('Release candidate public, native and checkout review passed for '+name+'.',flush=True)

if __name__=='__main__':main()
