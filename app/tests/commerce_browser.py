#!/usr/bin/env python3
"""Actual browser product/bag/customer journeys and owner screen rendering."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from commerce_integration import seed_catalog, signup
from communication_integration import enable_fixture_smtp
from public_browser import screenshot


def rendered(page,label):
    expect(page.locator('h1').count()==1 and page.locator('h1').is_visible(),label+': visible heading')
    expect(page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1'),label+': no horizontal overflow')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':
        raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-commerce-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login()
            pid,vid,image=seed_catalog(s);enable_fixture_smtp(s)
            s.client.form('admin/shop/settings.php',{'enabled':'1','accounts_enabled':'1','intro':'Synthetic test store'})
            _,password=signup(s)
            with sync_playwright() as engine:
                browser=engine.chromium.launch()
                for width,height in [(1440,1000),(390,844),(360,800)]:
                    context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False)
                    page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
                    for route in ['shop/','shop/product.php?slug=fixture-shirt','account/login.php','account/register.php','account/forgot.php']:
                        response=page.goto(s.base+route,wait_until='networkidle')
                        expect(response.status==200,f'{width}px renders '+route);rendered(page,route)
                        if route=='shop/':
                            expect(page.locator('#public-nav a').filter(has_text='Merch').count()==1,f'{width}px enabled store remains present in global navigation markup')
                            expect(page.get_by_role('navigation',name='Footer').get_by_role('link',name='Your bag',exact=True).count()==1,f'{width}px enabled store exposes the bag link')
                        if route=='account/login.php':
                            expect(page.locator('#public-nav a').filter(has_text='Merch').count()==1,f'{width}px enabled-store account shell keeps Merch available')
                            expect(page.locator('.shop-tools').get_by_role('link',name='Merchandise',exact=True).count()==1,f'{width}px enabled-store customer navigation keeps Merchandise')
                            expect(page.locator('.shop-tools').get_by_role('link',name='Your bag',exact=True).count()==1,f'{width}px enabled-store customer navigation keeps Your bag')
                            expect('Guest checkout remains available' in page.locator('.account-shell').inner_text(),f'{width}px enabled-store login keeps checkout guidance')
                            expect('Your details. Your collection.' in page.locator('.account-shell').inner_text(),f'{width}px enabled-store account subtitle keeps collection wording')
                        screenshot(page,f'commerce-{width}-'+route.split('?')[0].strip('/').replace('/','-').replace('.php',''))
                    page.goto(s.base+'shop/product.php?slug=fixture-shirt')
                    page.locator('[name="quantity"]').fill('2');page.get_by_role('button',name='Add to bag').click();page.wait_for_url('**/shop/cart.php')
                    expect('$50.02' in page.locator('.summary-total').inner_text(),'Browser adds a real variant and receives server-calculated subtotal')
                    rendered(page,f'{width}px bag');screenshot(page,f'commerce-{width}-bag')
                    page.goto(s.base+'account/login.php');page.locator('.account-shell [name="email"]').fill('customer@example.test');page.locator('.account-shell [name="password"]').fill(password)
                    page.get_by_role('button',name='Sign in to your account').click();page.wait_for_url('**/account/')
                    rendered(page,f'{width}px signed-in account');screenshot(page,f'commerce-{width}-account')
                    page.goto(s.base+'account/addresses.php');rendered(page,f'{width}px address book')
                    page.goto(s.base+'admin/login.php');page.locator('[name="email"]').fill(s.owner);page.locator('[name="password"]').fill(s.password);page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')
                    for route in ['admin/shop/',f'admin/shop/edit.php?id={pid}',f'admin/shop/variants.php?id={pid}','admin/shop/categories.php','admin/shop/settings.php','admin/shop/customers.php']:
                        response=page.goto(s.base+route,wait_until='networkidle');expect(response.status==200,f'{width}px owner '+route);rendered(page,route)
                        screenshot(page,f'commerce-{width}-'+route.split('?')[0].strip('/').replace('/','-').replace('.php',''))
                    expect(errors==[],f'{width}px commerce has no uncaught JavaScript errors');context.close()
                browser.close()
    print('Commerce browser journeys passed at 1440px, 390px and 360px.',flush=True)

if __name__=='__main__':
    main()
