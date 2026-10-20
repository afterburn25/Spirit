#!/usr/bin/env python3
"""Logo draft/publication and existing media authorization on disposable sites."""
from __future__ import annotations
import argparse
import hashlib
import os
import json
import re
import subprocess
import tempfile
from html.parser import HTMLParser
import urllib.error
import urllib.request
from pathlib import Path
import http_integration as harness
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import upload, png_bytes, wav_bytes, save_content
from content_history_integration import fixture as history_fixture
from launch_integration import fixture
from backup_integration import create as backup_create, action as backup_action

PATH='admin/branding/'
PREVIEW=PATH+'preview.php'

def branding_fixture(s,action,values=None):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
    result=subprocess.run(['php',str(ROOT/'tests/branding_fixture.php'),action],input=json.dumps(values or {}),env=env,capture_output=True,text=True,timeout=20)
    if result.returncode:raise AssertionError('Branding fixture failed: '+action+' '+result.stderr)
    return json.loads(result.stdout)

def revision(s):
    match=re.search(r'name="revision" value="(\d+)"',s.client.request(PATH)[1])
    if not match:raise AssertionError('Missing branding revision')
    return int(match.group(1))

def epoch(s):
    match=re.search(r'name="restore_epoch" value="(\d+)"',s.client.request(PATH)[1])
    if not match:raise AssertionError('Missing restoration epoch')
    return int(match.group(1))

def mutate(s,action,logo=0,expected=None,approved=True,restore_epoch=None):
    values={'action':action,'logo_id':str(logo),'revision':str(revision(s) if expected is None else expected),'restore_epoch':str(epoch(s) if restore_epoch is None else restore_epoch)}
    if approved:values['approved']='1'
    return s.client.form(PATH,values)

def logo_count(body):
    class Counter(HTMLParser):
        def __init__(self):super().__init__();self.inert=0;self.count=0
        def handle_starttag(self,tag,attrs):
            if tag=='template':self.inert+=1
            if tag=='img' and not self.inert and 'site-logo' in dict(attrs).get('class','').split():self.count+=1
        def handle_endtag(self,tag):
            if tag=='template':self.inert-=1
    parser=Counter();parser.feed(body);return parser.count

def logo_present(body,id):
    return 'class="site-logo"' in body and f'file.php?id={id}' in body

def journey(s):
    anon=Client(s.base);before=fixture(s,'fingerprint')
    expect('Manage site logo' in s.client.request('admin/settings.php')[1],'Website settings links to the actual logo editor')
    for path in [PATH,PREVIEW]:
        status,body,headers,_=s.client.request(path)
        expect(status==200 and 'no-store' in headers.get('Cache-Control',''),'Owner page is uncached: '+path)
        expect(anon.request(path)[3].endswith('/admin/login.php'),'Anonymous visitor cannot see private logo controls or preview')
    expect(fixture(s,'fingerprint')==before,'Opening editor/preview creates no configuration, content or publication')
    expect(s.client.request(PATH,{'csrf':'bad','action':'publish'})[0]==403,'Logo mutation requires valid CSRF')
    expect(s.client.request(PREVIEW,{'action':'publish'})[0]==405,'Preview rejects POST rather than submitting a live action')
    one,_,_=upload(s,'synthetic-logo-one.png','image/png',png_bytes());two,_,_=upload(s,'synthetic-logo-two.png','image/png',png_bytes());audio,_,_=upload(s,'synthetic-audio.wav','audio/wav',wav_bytes())
    legacy_backup=backup_create(s)
    legacy=branding_fixture(s,'legacy',{'revision':17,'draft_logo_id':two,'published_logo_id':one})
    before=fixture(s,'fingerprint')
    editor=s.client.request(PATH)[1];preview=s.client.request(PREVIEW)[1]
    expect('Approved animated 3D logo' in editor and f'earlier custom image #{one}' in editor,'Legacy upgrade clearly identifies the new approved public source and retained custom image')
    expect(logo_present(preview,two),'Legacy saved private draft remains available for owner preview')
    expect(fixture(s,'fingerprint')==before and branding_fixture(s,'snapshot')==legacy,'Opening upgraded editor and preview never rewrites legacy branding or adds a source setting')
    public=anon.request('')[1]
    expect('class="site-logo"' not in public and public.count('class="bundled-site-logo"')==3,'Absent source defaults to the approved 3D artwork even when an old custom publication is retained')
    expect(anon.request(f'media/file.php?id={one}')[0]==404 and anon.request(f'media/file.php?id={one}&width=320')[0]==404,'An inactive retained custom logo does not authorize its original or derivatives publicly')
    expect(branding_fixture(s,'snapshot')==legacy,'Public rendering and media checks preserve the original legacy record byte for byte')
    before=fixture(s,'fingerprint')
    for action,logo in [('save',audio),('publish',9999999),('unknown',one)]:
        response=mutate(s,action,logo)
        expect('role="alert"' in response[1] and fixture(s,'fingerprint')==before,'Invalid action/media rolls back all branding writes: '+action)
    expect('Logo draft saved privately' in mutate(s,'save',one)[1],'Real owner form saves a private logo')
    before=fixture(s,'fingerprint');draft=revision(s)
    status,body,headers,_=s.client.request(PREVIEW)
    expect(logo_present(body,one) and 'PRIVATE PREVIEW' in body,'Saved logo appears inside the real private homepage preview')
    expect(headers.get('X-Robots-Tag')=='noindex, nofollow' and headers.get('Referrer-Policy')=='no-referrer','Private preview cannot be indexed or leak a referrer')
    expect(fixture(s,'fingerprint')==before,'Preview is read-only after a saved draft too')
    # This separate public observation may legitimately record a visitor pageview.
    # Keep the private preview assertion above scoped to the complete database state.
    expect(not logo_present(anon.request('')[1],one) and anon.request(f'media/file.php?id={one}')[0]==404,'Saving and previewing do not expose the draft image')
    before=fixture(s,'fingerprint')
    expect('Confirm that you approve' in mutate(s,'publish',one,approved=False)[1] and fixture(s,'fingerprint')==before,'Missing publication approval preserves the private state')
    expect('Approved logo published' in mutate(s,'publish',one)[1],'Explicit approved publication completes through the real form')
    old_logo_revision=revision(s);old_logo_epoch=epoch(s)
    logo_backup=backup_create(s)
    for route in ['', 'music/', 'shows/', 'band/', 'media/', 'press/', 'booking/', 'shop/', 'shop/cart.php', 'account/login.php']:
        response=anon.request(route)
        expect(response[0]==200 and logo_count(response[1])==2,'Public header and footer show published logo: '+route)
    expect(anon.request(f'media/file.php?id={one}')[0]==200 and anon.request(f'media/file.php?id={one}&width=320')[0]==200,'Published original and derivative use the existing media authorization')
    before=fixture(s,'fingerprint')
    expect('changed in another session' in mutate(s,'save',two,expected=draft)[1] and fixture(s,'fingerprint')==before,'Stale logo form cannot overwrite a later publication')
    mutate(s,'save',two)
    expect(logo_present(s.client.request(PREVIEW)[1],two) and logo_present(anon.request('')[1],one),'Replacement draft and current public logo stay separate')
    expect(anon.request(f'media/file.php?id={two}')[0]==404,'Replacement logo is private before its own publication')
    custom=branding_fixture(s,'snapshot');custom_state=json.loads(custom['record'])
    before=fixture(s,'fingerprint')
    expect(s.client.request(PATH,{'csrf':'bad','action':'builtin'})[0]==403 and fixture(s,'fingerprint')==before,'Switching to the approved identity requires valid CSRF and leaves rejected requests unchanged')
    expect('Animated 3D logo is active' in mutate(s,'builtin',9999999,approved=False)[1],'The explicit approved-logo action succeeds without publishing an unsaved custom selection')
    builtin=branding_fixture(s,'snapshot');builtin_state=json.loads(builtin['record'])
    expect(custom['source']=='custom' and builtin['source']=='bundled' and builtin_state=={**custom_state,'revision':custom_state['revision']+1},'Switching source preserves both saved IDs and advances the guarded revision')
    expect(not logo_present(anon.request('')[1],one) and logo_present(s.client.request(PREVIEW)[1],two),'Switching public source keeps the saved custom draft available privately')
    expect(anon.request(f'media/file.php?id={one}')[0]==404 and anon.request(f'media/file.php?id={two}')[0]==404,'Inactive custom references grant no branding-only public media access')
    before=fixture(s,'fingerprint')
    expect('changed in another session' in mutate(s,'publish',one,expected=custom_state['revision'])[1] and fixture(s,'fingerprint')==before,'A stale custom Publish cannot silently undo an approved-source change')
    mutate(s,'save',two)
    expect(branding_fixture(s,'snapshot')['source']=='bundled' and not logo_present(anon.request('')[1],two),'Saving a custom draft does not reactivate custom branding')
    mutate(s,'publish',one)
    expect(branding_fixture(s,'snapshot')['source']=='custom' and logo_present(anon.request('')[1],one),'Only deliberate approved Publish reactivates the retained custom identity')
    mutate(s,'publish',two)
    expect(anon.request(f'media/file.php?id={one}')[0]==404 and anon.request(f'media/file.php?id={one}&width=320')[0]==404,'Replaced otherwise-unreferenced logo and cached derivatives are withdrawn')
    mutate(s,'withdraw')
    withdrawn_body=anon.request('')[1]
    expect('class="site-logo"' not in withdrawn_body and withdrawn_body.count('class="bundled-site-logo"')==3 and logo_present(s.client.request(PREVIEW)[1],two),'Withdrawal restores the bundled scalable fallback without deleting the private draft')
    expect(anon.request(f'media/file.php?id={two}')[0]==404,'Withdrawn otherwise-private logo is no longer public')
    # A different explicit publication continues to authorize a shared image.
    page=save_content(s,'page','Synthetic logo reference',{'body':'Test only','image_id':str(two)},slug='logo-reference')
    mutate(s,'publish',two);mutate(s,'withdraw')
    expect(anon.request(f'media/file.php?id={two}')[0]==200,'Withdrawal never hides an image still used by an approved page')
    save_content(s,'page','Synthetic logo reference',{'body':'Test only','image_id':str(two)},action='unpublish',item_id=page,slug='logo-reference')
    expect(anon.request(f'media/file.php?id={two}')[0]==404,'Removing the final public reference withdraws the shared image')
    old_epoch=epoch(s)
    restored=backup_action(s,'restore',id=logo_backup,scope='content',confirm_restore='RESTORE')
    expect('Restore committed' in restored[1] and epoch(s)>old_epoch and logo_present(anon.request('')[1],one),'Supported backup restore recovers the saved logo with a fresh restoration epoch')
    expect(branding_fixture(s,'snapshot')['source']=='custom','Supported restore recovers the explicit custom source with its publication')
    expect('Site data was restored' in mutate(s,'save',two,expected=old_logo_revision,restore_epoch=old_logo_epoch)[1],'A pre-restore form cannot overwrite recovered branding')
    expect('Site data was restored' in mutate(s,'builtin',expected=old_logo_revision,restore_epoch=old_logo_epoch)[1],'A pre-restore source-selection form cannot overwrite recovered branding')
    old_epoch=epoch(s)
    restored=backup_action(s,'restore',id=legacy_backup,scope='content',confirm_restore='RESTORE')
    restored_body=anon.request('')[1]
    expect('Restore committed' in restored[1] and epoch(s)>old_epoch and 'class="site-logo"' not in restored_body and restored_body.count('class="bundled-site-logo"')==3,'A pre-branding backup returns to the bundled fallback without resurrecting stale forms')
    history_fixture(s,'media-break',{'media_id':one});before=fixture(s,'fingerprint')
    expect('undamaged image' in mutate(s,'publish',one)[1] and fixture(s,'fingerprint')==before,'Damaged image cannot be published and does not destroy the saved draft')
    for role in ['content_editor','store_manager']:
        fixture(s,'role',{'role':role})
        for path in [PATH,PREVIEW]:expect(s.client.request(path)[0]==403,role+' cannot access owner branding: '+path)
        expect(s.client.request(PATH,{'action':'publish','logo_id':str(two)})[0]==403,'Role boundary applies before a direct POST')
        expect(s.client.request(PATH,{'action':'builtin'})[0]==403,'Role boundary also protects the approved-source action')
    fixture(s,'role',{'role':'owner'})
    mutate(s,'publish','')
    empty_body=anon.request('')[1]
    expect('class="site-logo"' not in empty_body and empty_body.count('class="bundled-site-logo"')==3,'Explicit empty selection uses the bundled scalable fallback')


def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive',type=Path,help='Use an existing candidate Full ZIP instead of building another package.')
    parser.add_argument('--report',type=Path,help='Write the focused current-package result as JSON.')
    args=parser.parse_args()
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable SQL environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-branding-') as directory:
        base=Path(directory);archive,code=(args.archive.resolve(),None) if args.archive else build_package(base/'packages')
        archive_sha256=hashlib.sha256(archive.read_bytes()).hexdigest()
        for folder,label in [('', 'root'),('band','subfolder')]:
            with site(base/label,archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s)
    report={'status':'passed','assertions_passed':harness.CHECKS,'archive_sha256':archive_sha256,'scope':'Installed root/subfolder approved-logo default, retained legacy state, explicit source actions, private preview, public media authorization, revision/CSRF/owner guards and supported restore','production_site_accessed':False}
    if args.report:args.report.write_text(json.dumps(report,indent=2)+'\n')
    print(json.dumps(report,indent=2),flush=True)

if __name__=='__main__':main()
