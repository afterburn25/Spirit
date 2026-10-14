#!/usr/bin/env python3
"""Real checkout UI and token-only browser submission. SDK/network are fixtures.
This is NOT a live Authorize.net SDK test or a sandbox merchant acceptance test.
"""
from __future__ import annotations
import json
import os
import tempfile
import urllib.parse
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from commerce_integration import seed_catalog, adjust
from payment_integration import configure, fixture, snapshot, order
from public_browser import screenshot

SDK='https://jstest.authorize.net/v1/Accept.js'
GOOD='''window.Accept={dispatchData:(data,callback)=>{
 window.syntheticTokenizationCalls=(window.syntheticTokenizationCalls||0)+1;
 callback({messages:{resultCode:'Ok'},opaqueData:{dataDescriptor:'COMMON.ACCEPT.INAPP.PAYMENT',dataValue:'SYNTHETIC_BROWSER_OPAQUE_TOKEN_0000001'}});
}};'''
BAD="window.Accept={dispatchData:(data,callback)=>callback({messages:{resultCode:'Error'}})};"


def rendered(page,label):
    expect(page.locator('h1').count()==1 and page.locator('h1').is_visible(),label+': visible heading')
    expect(page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1'),label+': no horizontal overflow')


def prepare_in_browser(s,page):
    page.goto(s.base+'shop/product.php?slug=fixture-shirt',wait_until='networkidle')
    page.locator('[name="quantity"]').fill('2')
    page.get_by_role('button',name='Add to bag').click();page.wait_for_url('**/shop/cart.php')
    page.get_by_role('link',name='Continue to checkout').click();page.wait_for_url('**/shop/checkout.php')
    for name,value in {'email':'browser-guest@example.test','recipient':'Synthetic Guest','line_one':'123 Fixture Street','city':'Lafayette','postal_code':'70501'}.items():
        page.locator('.checkout-page [name="'+name+'"]').fill(value)
    page.locator('[name="region"]').select_option('LA');page.locator('[name="order_consent"]').check()
    rendered(page,'Filled checkout form')
    page.get_by_role('button',name='Review order and total').click();page.wait_for_url('**/shop/order.php?reference=*')
    reference=urllib.parse.parse_qs(urllib.parse.urlparse(page.url).query)['reference'][0]
    row=next(o for o in snapshot(s)['orders'] if o['reference']==reference)
    expect(int(row['total_cents'])==6002 and row['payment_state']=='awaiting_payment','Browser sees stored final total before payment')
    return int(row['id'])


def fill_card(page):
    for field,value in {'card-holder':'Synthetic Guest','card-number':'4111111111111111','card-month':'12','card-year':'2099','card-code':'123'}.items():
        page.locator('#'+field).fill(value)


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK')!='1':
        raise RuntimeError('Disposable non-root database and payment-network kill switch required.')
    with tempfile.TemporaryDirectory(prefix='spirit-payment-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();pid,vid,image=seed_catalog(s);adjust(s,pid,vid,20);configure(s)
            with sync_playwright() as engine:
                browser=engine.chromium.launch()
                for width,height in [(1440,1000),(390,844)]:
                    context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False)
                    # Replace only the external test SDK at its official URL. Never
                    # add a production SDK override endpoint or weaken application CSP.
                    context.route(SDK,lambda route:route.fulfill(status=200,content_type='application/javascript',body=GOOD))
                    page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
                    oid=prepare_in_browser(s,page);receipt=page.url
                    rendered(page,f'{width}px private order review');screenshot(page,f'payment-{width}-review')
                    expect('$60.02' in page.locator('.checkout-totals').inner_text(),'Rendered total matches reserved order')
                    expect(page.locator('#card-pay-button').is_enabled(),'Successful test SDK loading enables tokenization')
                    expect(page.locator('#card-payment input[name]').count()==4,'Only CSRF, nonce and opaque-token fields have names')
                    # A failed SDK callback never posts a charge, and clears the card.
                    context.unroute(SDK);context.route(SDK,lambda route:route.fulfill(status=200,content_type='application/javascript',body=BAD))
                    page.reload(wait_until='networkidle');fill_card(page);page.locator('#card-pay-button').click()
                    expect('not tokenized' in page.locator('#payment-message').inner_text(),'Tokenization failure is visible without payment submission')
                    expect(page.locator('#card-number').input_value()=='' and order(s,oid)['payment_state']=='awaiting_payment','Failed tokenization clears sensitive values and leaves the order uncharged')
                    # Missing library fails closed.
                    context.unroute(SDK);context.route(SDK,lambda route:route.abort())
                    page.reload(wait_until='networkidle')
                    expect(page.locator('#card-pay-button').is_disabled() and 'unavailable' in page.locator('#payment-message').inner_text(),'Missing payment library cannot submit raw card data')
                    context.unroute(SDK);context.route(SDK,lambda route:route.fulfill(status=200,content_type='application/javascript',body=GOOD))
                    page.reload(wait_until='networkidle');submitted=[]
                    def capture(route):
                        submitted.append(urllib.parse.parse_qs(route.request.post_data or '',keep_blank_values=True))
                        route.fulfill(status=200,content_type='text/html',body='<h1>Synthetic token boundary captured</h1>')
                    context.route('**/shop/pay.php?*',capture)
                    fill_card(page);page.locator('#card-pay-button').click()
                    page.wait_for_url('**/shop/pay.php?*')
                    expect(len(submitted)==1 and set(submitted[0])=={'csrf','nonce','data_descriptor','data_value'},'Browser posts only the token envelope once')
                    expect('4111111111111111' not in json.dumps(submitted) and 'cardCode' not in json.dumps(submitted),'Browser request contains no card number or security code')
                    # Independently exercise the real backend with its CLI-only
                    # synthetic gateway boundary. This does not claim PSP end-to-end.
                    result=fixture(s,'pay',{'id':oid})
                    expect(result.get('ok') and result['mutating_calls']==1 and order(s,oid)['payment_state']=='paid','Backend records one synthetic provider acceptance')
                    page.goto(receipt,wait_until='networkidle');rendered(page,f'{width}px paid receipt')
                    expect(page.locator('#card-payment').count()==0 and 'Payment accepted' in page.locator('.order-reference').inner_text(),'Paid receipt no longer exposes a charge form')
                    screenshot(page,f'payment-{width}-paid')
                    page.goto(s.base+'admin/login.php');page.locator('[name="email"]').fill(s.owner);page.locator('[name="password"]').fill(s.password)
                    page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')
                    for route in ['admin/orders/',f'admin/orders/view.php?id={oid}','admin/orders/payments.php','admin/orders/policy.php','admin/orders/webhooks.php']:
                        response=page.goto(s.base+route,wait_until='networkidle')
                        expect(response.status==200 and 'no-store' in response.headers.get('cache-control',''),f'{width}px private owner '+route)
                        rendered(page,route);screenshot(page,f'payment-{width}-'+route.split('?')[0].strip('/').replace('/','-').replace('.php',''))
                    expect(errors==[],f'{width}px checkout has no uncaught JavaScript errors');context.close()
                browser.close()
    print('Rendered order/tokenization boundaries passed at 1440px and 390px; actual provider acceptance not tested.',flush=True)

if __name__=='__main__':
    main()
