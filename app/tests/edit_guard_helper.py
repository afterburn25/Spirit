#!/usr/bin/env python3
"""Isolated DOM tests of the shipped warning script, not application acceptance."""
from __future__ import annotations
import os
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as be
ROOT=Path(__file__).resolve().parents[2]

def main():
    engine=os.environ.get('SPIRIT_BROWSER','chromium')
    options={}
    if os.environ.get('SPIRIT_LOCAL_CHROMIUM')=='1':options['executable_path']='/usr/bin/chromium'
    with sync_playwright() as p:
        browser=getattr(p,engine).launch(**options)
        page=browser.new_page()
        page.set_content('''<form method="post" data-edit-guard>
          <input name="title" value="Original" required><textarea name="body">Original body</textarea>
          <select name="kind"><option value="one">One</option><option value="two">Two</option></select>
          <select name="images[]" multiple><option value="7" selected>7</option><option value="8">8</option></select>
          <input name="flag" type="checkbox"><input name="approved" type="checkbox" data-edit-guard-ignore>
          <input name="csrf" type="hidden" value="token"><input name="password" type="password">
          <input name="file" type="file"><button type="submit">Save</button><button type="reset">Reset</button></form>
          <form id="private" method="post"><input name="api_secret" type="text"></form>''')
        page.evaluate('''() => {
          for (const n of ['localStorage','sessionStorage']) Object.defineProperty(window,n,{get(){throw Error('Storage must not be accessed');}});
          window.fetch=()=>{throw Error('No network');}; navigator.sendBeacon=()=>{throw Error('No beacon');};
          for(const selector of ['[name=password]','[name=csrf]','[name=file]','#private input']) Object.defineProperty(document.querySelector(selector),'value',{get(){throw Error('Do not inspect this field');}});
        }''')
        errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
        page.add_script_tag(path=str(ROOT/'assets/js/edit-guard.js'))
        form=page.locator('form[data-edit-guard]');status=form.locator('[role=status]');n=0
        def state(value):
            nonlocal n
            be(form).to_have_attribute('data-edit-guard-state',value);n+=1
        def blocked(expected):
            nonlocal n
            value=page.evaluate("() => {const e=new Event('beforeunload',{cancelable:true});window.dispatchEvent(e);return e.defaultPrevented;}")
            assert value==expected,(value,expected);n+=1
        state('clean');blocked(False)
        page.locator('[name=title]').fill('Changed');state('dirty');blocked(True);be(status).to_be_visible()
        page.locator('[name=title]').fill('Original');state('clean');blocked(False)
        page.locator('[name=kind]').select_option('two');state('dirty')
        page.locator('[name=kind]').select_option('one');state('clean')
        page.locator('[name="images[]"]').select_option(['7','8']);state('dirty')
        page.locator('[name="images[]"]').select_option('7');state('clean')
        page.locator('[name=flag]').check();state('dirty')
        page.get_by_role('button',name='Reset',exact=True).click();state('clean')
        page.locator('[name=approved]').check();state('clean');blocked(False)
        page.locator('[name=title]').fill('');page.get_by_role('button',name='Save',exact=True).click();state('dirty');blocked(True)
        page.locator('[name=title]').fill('New title')
        page.evaluate("document.querySelector('form').addEventListener('submit',e=>e.preventDefault(),{once:true})")
        page.get_by_role('button',name='Save',exact=True).click();state('dirty');blocked(True)
        # Firefox performs native default submission even for this dispatched
        # event. Observe the exempt state synchronously, then cancel deliberately
        # to keep the isolated DOM fixture on the page. Real saves run separately.
        probe=page.evaluate("""() => {
          const f=document.querySelector('form');let result;
          f.addEventListener('submit',e=>{
            const leave=new Event('beforeunload',{cancelable:true});window.dispatchEvent(leave);
            result={state:f.dataset.editGuardState,blocked:leave.defaultPrevented};
            e.preventDefault();
          },{once:true});
          f.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));return result;
        }""")
        assert probe=={'state':'submitting','blocked':False},probe;n+=2
        page.evaluate("window.dispatchEvent(new Event('pageshow'))")
        state('dirty');blocked(True)
        page.get_by_role('button',name='Reset',exact=True).click();state('clean');blocked(False)
        # A restored form's live value differs from its server default before enhancement.
        page.set_content('<form method="post" data-edit-guard><input name="title" value="Saved"></form>')
        page.locator('[name=title]').fill('Restored unsaved entry')
        page.add_script_tag(path=str(ROOT/'assets/js/edit-guard.js'))
        be(page.locator('form')).to_have_attribute('data-edit-guard-state','dirty');n+=1
        # A rejected server response is not clean simply because its field values
        # are now the defaults in returned HTML.
        page.set_content('<form method="post" data-edit-guard data-edit-guard-dirty="true"><input name="title" value="Unsent"></form>')
        page.add_script_tag(path=str(ROOT/'assets/js/edit-guard.js'))
        be(page.locator('form')).to_have_attribute('data-edit-guard-state','dirty');n+=1
        be(page.locator('[role=status]')).to_contain_text('not saved')
        assert errors==[],errors;n+=1
        browser.close()
        print(f'{n} isolated editor guard checks passed for {engine}.')
if __name__=='__main__':main()
