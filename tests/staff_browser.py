#!/usr/bin/env python3
"""Actual owner invitation and restricted staff sessions, desktop and mobile."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from communication_integration import enable_fixture_smtp
from staff_integration import PASSWORD, mail_token, snapshot
from public_browser import screenshot


def rendered(page, label):
    expect(page.locator('h1').count()==1 and page.locator('h1').is_visible(),label+': visible heading')
    expect(page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1'),label+': no horizontal overflow')
    expect(page.locator('input[type="password"][value]').count()==0,label+': no password reflection')


def signin(s,page,email,password):
    page.goto(s.base+'admin/login.php',wait_until='networkidle')
    page.locator('[name="email"]').fill(email);page.locator('[name="password"]').fill(password)
    page.get_by_role('button',name='Enter backstage').click();page.wait_for_url('**/admin/')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':
        raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-staff-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();enable_fixture_smtp(s)
            with sync_playwright() as engine:
                browser=engine.chromium.launch()
                for width,height in [(1440,1000),(390,844)]:
                    owner_context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False)
                    owner=owner_context.new_page();errors=[];owner.on('pageerror',lambda error:errors.append(str(error)))
                    signin(s,owner,s.owner,s.password);owner.goto(s.base+'admin/staff/',wait_until='networkidle')
                    form=owner.locator('form').filter(has=owner.locator('input[name="action"][value="invite"]'))
                    email=f'editor-{width}@example.test'
                    form.locator('[name="name"]').fill('Synthetic browser editor');form.locator('[name="email"]').fill(email)
                    form.locator('[name="role"]').select_option('content_editor');form.locator('[name="owner_password"]').fill(s.password)
                    form.get_by_role('button',name='Send staff invitation').click();owner.wait_for_load_state('networkidle')
                    expect(any(t['email']==email for t in snapshot(s)['tokens']),'Owner browser stores a real invitation')
                    rendered(owner,f'{width}px owner staff access');screenshot(owner,f'staff-{width}-owner-access')
                    owner.goto(s.base+'admin/activity/',wait_until='networkidle');rendered(owner,f'{width}px owner audit');screenshot(owner,f'staff-{width}-activity')
                    staff_context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False)
                    staff=staff_context.new_page();staff.on('pageerror',lambda error:errors.append(str(error)))
                    raw=mail_token(s,email);staff.goto(s.base+'admin/staff/complete.php?token='+raw,wait_until='networkidle')
                    rendered(staff,f'{width}px staff invitation');screenshot(staff,f'staff-{width}-invitation')
                    staff.locator('[name="password"]').fill(PASSWORD);staff.locator('[name="password_confirm"]').fill(PASSWORD)
                    staff.get_by_role('button',name='Save staff password').click();staff.wait_for_load_state('networkidle')
                    expect('Password saved.' in staff.locator('main').inner_text(),'Staff browser activation succeeds')
                    signin(s,staff,email,PASSWORD);rendered(staff,f'{width}px editor workspace');screenshot(staff,f'staff-{width}-editor-workspace')
                    expect(staff.locator('#sidebar a[href*="admin/staff/"]').count()==0 and staff.locator('#sidebar a[href*="admin/orders/"]').count()==0,'Editor sidebar contains no owner or payment routes')
                    for route in ['admin/content/?kind=page','admin/media/','admin/account.php']:
                        response=staff.goto(s.base+route,wait_until='networkidle');expect(response.status==200,'Staff browser opens allowed '+route);rendered(staff,f'{width}px '+route)
                    response=staff.goto(s.base+'admin/staff/',wait_until='networkidle');expect(response.status==403,'Direct browser navigation cannot escalate to owner access')
                    staff.goto(s.base+'admin/staff/recover.php',wait_until='networkidle');rendered(staff,f'{width}px recovery');screenshot(staff,f'staff-{width}-recovery')
                    expect(errors==[],f'{width}px staff workflows have no uncaught JavaScript errors')
                    staff_context.close();owner_context.close()
                browser.close()
    print('Staff owner-invitation and scoped browser journeys passed at 1440px/390px.',flush=True)


if __name__=='__main__':main()
