#!/usr/bin/env python3
"""Real authoring pages and actual before-unload dialogs, no live provider calls."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright,expect as be,Error as BrowserError
from http_integration import Client,build_package,expect,site,dashboard
from content_integration import save_content
from commerce_integration import seed_catalog,snapshot
from public_browser import screenshot

def dirty(page,expected):
    be(page.locator('form[data-edit-guard]')).to_have_attribute('data-edit-guard-state',expected)

def cancel_navigation(page,action,label):
    seen=[]
    def on_dialog(dialog):
        seen.append(dialog.type);dialog.dismiss()
    page.on('dialog',on_dialog)
    try:
        # A no-wait click may return before the dialog event is delivered, most
        # noticeably in WebKit. Await the actual event rather than inspecting an
        # immediate Python list. The dialog itself is still required to occur.
        with page.expect_event('dialog',timeout=10000) as event:
            try:action()
            except BrowserError:
                # Native cancellation may surface ERR_ABORTED/NS_BINDING_ABORTED.
                # The real dialog requirement prevents hiding unrelated failure.
                if not seen:raise
        expect(event.value.type=='beforeunload' and seen==['beforeunload'],label+': browser actually displayed a before-unload dialog')
    finally:page.remove_listener('dialog',on_dialog)

def login(page,s):
    page.goto(s.base+'admin/login.php');page.locator('[name=email]').fill(s.owner);page.locator('[name=password]').fill(s.password)
    page.get_by_role('button',name='Enter backstage',exact=True).click();page.wait_for_url(lambda u:dashboard(str(u)))

def journey(browser,s,engine,width,area,pid,item,image):
    context=browser.new_context(viewport={'width':width,'height':960},reduced_motion='reduce',bypass_csp=False)
    page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
    try:
        login(page,s)
        route=s.base+f'admin/content/edit.php?kind=page&id={item}'
        page.goto(route,wait_until='networkidle');dirty(page,'clean')
        baseline=page.locator('[name=title]').input_value()
        page.locator('[name=title]').fill('Unsent page '+str(width));dirty(page,'dirty')
        be(page.locator('.edit-guard-notice')).to_be_visible()
        expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),'Editor warning fits '+str(width)+'px')
        before=snapshot(s)
        cancel_navigation(page,lambda:page.get_by_role('link',name='Back to list').click(no_wait_after=True),'Content navigation')
        expect(page.url==route and page.locator('[name=title]').input_value()=='Unsent page '+str(width),'Cancelling departure preserves current page and entered title')
        cancel_navigation(page,lambda:page.reload(timeout=10000),'Editor refresh')
        expect(page.url==route and snapshot(s)==before,'Cancelling refresh changes no stored product, identity or stock state')
        # A saved-preview popup does not unload or autosave the dirty editor.
        with page.expect_popup() as event:page.get_by_role('link',name='Preview saved draft').click()
        preview=event.value;preview.wait_for_load_state('domcontentloaded')
        expect('Unsent page '+str(width) not in preview.locator('h1').inner_text(),'Preview remains the saved draft, not unsent text')
        preview.close();dirty(page,'dirty')
        page.locator('[name=title]').fill(baseline);dirty(page,'clean')
        expect(page.locator('.edit-guard-notice').is_hidden(),'Restoring original field value removes unnecessary warning')
        page.locator('[name=title]').fill('Saved private page '+str(width));dirty(page,'dirty')
        dialogs=[];page.on('dialog',lambda d:(dialogs.append(d.type),d.dismiss()))
        page.get_by_role('button',name='Save private draft',exact=True).click();page.wait_for_load_state('networkidle')
        dirty(page,'clean');expect(not dialogs,'Legitimate draft save is not interrupted by the warning')
        expect('Saved private page' not in Client(s.base).request('pages/?slug=editor-fixture')[1],'Warning feature does not publish the saved private draft')
        # Actual rejected product POST retains authored values in the response.
        page.goto(s.base+f'admin/shop/edit.php?id={pid}',wait_until='networkidle');dirty(page,'clean')
        page.locator('[name=name]').fill('UNSENT PRODUCT '+str(width))
        page.locator('[name=description]').fill('Keep this description after validation.')
        page.locator('[name=slug]').fill('invalid slug')
        page.get_by_role('button',name='Save private draft',exact=True).click();page.wait_for_load_state('networkidle')
        be(page.locator('.notice.error')).to_be_visible();dirty(page,'dirty')
        be(page.locator('[name=name]')).to_have_value('UNSENT PRODUCT '+str(width))
        be(page.locator('[name=description]')).to_have_value('Keep this description after validation.')
        be(page.locator('.edit-guard-notice')).to_contain_text('not saved')
        expect(not dialogs,'Rejected submission reaches its real error response without a false leave warning')
        if area=='root':screenshot(page,f'editor-{engine}-{width}-correction')
        page.locator('[name=slug]').fill('fixture-shirt')
        page.get_by_role('button',name='Save private draft',exact=True).click();page.wait_for_load_state('networkidle');dirty(page,'clean')
        expect('UNSENT PRODUCT' not in Client(s.base).request('shop/product.php?slug=fixture-shirt')[1],'Corrected product draft does not alter published merchandise')
        page.goto(s.base+'admin/branding/',wait_until='networkidle');dirty(page,'clean')
        page.locator('[name=approved]').check();dirty(page,'clean')
        page.locator('[name=logo_id]').select_option(str(image));dirty(page,'dirty')
        if area=='root':screenshot(page,f'editor-{engine}-{width}-logo-warning')
        page.get_by_role('button',name='Save private logo draft',exact=True).click();page.wait_for_load_state('networkidle');dirty(page,'clean')
        expect(not dialogs,'Logo selection saves normally and approval checkbox alone is not treated as authored draft')
        expect(not errors,'No uncaught script errors in the editor workflow')
    finally:context.close()

def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    engine=os.environ.get('SPIRIT_BROWSER','chromium')
    if engine not in ('chromium','firefox','webkit'):raise RuntimeError('Invalid browser')
    with tempfile.TemporaryDirectory(prefix='spirit-editor-browser-') as directory,sync_playwright() as p:
        base=Path(directory);archive,code=build_package(base/'packages');browser=getattr(p,engine).launch()
        try:
            for folder,area in [('', 'root'),('band','subfolder')]:
                with site(base/area,archive,code,folder) as s:
                    s.claim();s.client.form('install/',s.input());s.login();pid,_,image=seed_catalog(s)
                    item=save_content(s,'page','Published editor fixture',{'body':'Published content remains unchanged.'},slug='editor-fixture')
                    for width in [1440,390,320]:
                        # Clear logo draft using its existing protected form so
                        # every context starts from the same authored selection.
                        from branding_integration import mutate
                        mutate(s,'save',0)
                        journey(browser,s,engine,width,area,pid,item,image)
                    context=browser.new_context(viewport={'width':390,'height':960},java_script_enabled=False,bypass_csp=False)
                    page=context.new_page();login(page,s);page.goto(s.base+f'admin/shop/edit.php?id={pid}')
                    page.locator('[name=name]').fill('Native unsent product');page.locator('[name=slug]').fill('invalid slug')
                    page.get_by_role('button',name='Save private draft',exact=True).click();page.wait_for_load_state('domcontentloaded')
                    be(page.locator('[name=name]')).to_have_value('Native unsent product')
                    expect(page.locator('.edit-guard-notice').count()==0,'No-JavaScript correction preserves text without pretending to provide a browser warning')
                    page.locator('[name=slug]').fill('fixture-shirt');page.get_by_role('button',name='Save private draft',exact=True).click()
                    page.wait_for_load_state('domcontentloaded');expect(page.locator('.notice.error').count()==0,'Native product correction remains usable')
                    context.close()
        finally:browser.close()
    print('Actual editor departure, correction and save workflows passed for '+engine+'.',flush=True)
if __name__=='__main__':main()
