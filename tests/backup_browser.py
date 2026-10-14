#!/usr/bin/env python3
"""Rendered owner creation, verification, download and actual content restoration."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from content_integration import save_content
from staff_browser import signin, rendered
from backup_integration import snapshot
from public_browser import screenshot


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-backup-browser-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login()
            with sync_playwright() as engine:
                browser=engine.chromium.launch();page_id=0
                for width,height in [(1440,1000),(390,844)]:
                    page_id=save_content(s,'page','Browser backup story',{'body':'Original browser story'},item_id=page_id,slug='browser-backup-story')
                    context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',bypass_csp=False)
                    page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
                    signin(s,page,s.owner,s.password);page.goto(s.base+'admin/backups/',wait_until='networkidle');before={r['id'] for r in snapshot(s)['backups']}
                    create=page.locator('form').filter(has=page.locator('input[name="action"][value="create"]'))
                    create.locator('[name="owner_password"]').fill(s.password);create.get_by_role('button',name='Create encrypted snapshot').click();page.wait_for_load_state('networkidle')
                    id=next(r['id'] for r in snapshot(s)['backups'] if r['id'] not in before)
                    rendered(page,f'{width}px backup administration');screenshot(page,f'backup-{width}-created')
                    card=page.locator('[data-backup-id="'+id+'"]');form=card.locator('form.backup-actions');form.locator('[name="owner_password"]').fill(s.password);form.get_by_role('button',name='Verify archive').click();page.wait_for_load_state('networkidle')
                    expect(any(r['id']==id and r['status']=='verified' for r in snapshot(s)['backups']),'Browser verifies its actual encrypted archive')
                    card=page.locator('[data-backup-id="'+id+'"]');form=card.locator('form.backup-actions');form.locator('[name="owner_password"]').fill(s.password)
                    with page.expect_download() as event:form.get_by_role('button',name='Download encrypted backup').click()
                    download=event.value;destination=base/f'fixture-{width}.sgbackup';download.save_as(destination)
                    expect(destination.read_bytes().startswith(b'SPIRIT-BACKUP-1\n'),'Owner browser downloads a real encrypted archive')
                    with page.expect_download() as event:form.get_by_role('button',name='Download separate recovery key').click()
                    receipt=event.value;receipt.save_as(base/f'fixture-{width}-private.txt');form.locator('[name="owner_password"]').fill('')
                    expect('Recovery key: ' in (base/f'fixture-{width}-private.txt').read_text(),'Browser receives a separate recovery receipt, never bundled in archive')
                    save_content(s,'page','Later browser story',{'body':'Later edited text'},item_id=page_id,slug='browser-backup-story')
                    detail=card.locator('details').filter(has=card.get_by_text('Restore this snapshot',exact=True));detail.locator('summary').click()
                    detail.locator('[name="scope"]').select_option('content');detail.locator('[name="confirm_restore"]').fill('RESTORE');detail.locator('[name="owner_password"]').fill(s.password)
                    detail.get_by_role('button',name='Restore selected site data').click();page.wait_for_load_state('networkidle')
                    expect('Restore committed' in page.locator('main').inner_text(),'Owner browser completes a real transactional restore')
                    rendered(page,f'{width}px restored backup administration');screenshot(page,f'backup-{width}-restored')
                    page.goto(s.base+'pages/?slug=browser-backup-story',wait_until='networkidle')
                    expect('Original browser story' in page.locator('main').inner_text() and 'Later edited text' not in page.locator('main').inner_text(),'Restored database content is visible through the public controller')
                    expect(errors==[],f'{width}px backup workflow has no uncaught JavaScript errors');context.close()
                browser.close()
    print('Backup browser creation/verification/download/restore passed at 1440px and390px.',flush=True)


if __name__=='__main__':main()
