#!/usr/bin/env python3
"""Logo draft/publication and existing media authorization on disposable sites."""
from __future__ import annotations
import os
import re
import tempfile
from html.parser import HTMLParser
import urllib.error
import urllib.request
from pathlib import Path
from http_integration import Client, build_package, expect, site
from content_integration import upload, png_bytes, wav_bytes, save_content
from content_history_integration import fixture as history_fixture
from launch_integration import fixture
from backup_integration import create as backup_create, action as backup_action

PATH='admin/branding/'
PREVIEW=PATH+'preview.php'

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
    before=fixture(s,'fingerprint')
    for action,logo in [('save',audio),('publish',9999999),('unknown',one)]:
        response=mutate(s,action,logo)
        expect('role="alert"' in response[1] and fixture(s,'fingerprint')==before,'Invalid action/media rolls back all branding writes: '+action)
    expect('Logo draft saved privately' in mutate(s,'save',one)[1],'Real owner form saves a private logo')
    before=fixture(s,'fingerprint');draft=revision(s)
    status,body,headers,_=s.client.request(PREVIEW)
    expect(logo_present(body,one) and 'PRIVATE PREVIEW' in body,'Saved logo appears inside the real private homepage preview')
    expect(headers.get('X-Robots-Tag')=='noindex, nofollow' and headers.get('Referrer-Policy')=='no-referrer','Private preview cannot be indexed or leak a referrer')
    expect(not logo_present(anon.request('')[1],one) and anon.request(f'media/file.php?id={one}')[0]==404,'Saving and previewing do not expose the draft image')
    expect(fixture(s,'fingerprint')==before,'Preview is read-only after a saved draft too')
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
    mutate(s,'publish',two)
    expect(anon.request(f'media/file.php?id={one}')[0]==404 and anon.request(f'media/file.php?id={one}&width=320')[0]==404,'Replaced otherwise-unreferenced logo and cached derivatives are withdrawn')
    mutate(s,'withdraw')
    expect('class="site-logo"' not in anon.request('')[1] and logo_present(s.client.request(PREVIEW)[1],two),'Withdrawal returns to text wordmark without deleting the private draft')
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
    expect('Site data was restored' in mutate(s,'save',two,expected=old_logo_revision,restore_epoch=old_logo_epoch)[1],'A pre-restore form cannot overwrite recovered branding')
    old_epoch=epoch(s)
    restored=backup_action(s,'restore',id=legacy_backup,scope='content',confirm_restore='RESTORE')
    expect('Restore committed' in restored[1] and epoch(s)>old_epoch and 'class="site-logo"' not in anon.request('')[1],'A pre-branding backup returns to text without resurrecting stale forms')
    history_fixture(s,'media-break',{'media_id':one});before=fixture(s,'fingerprint')
    expect('undamaged image' in mutate(s,'publish',one)[1] and fixture(s,'fingerprint')==before,'Damaged image cannot be published and does not destroy the saved draft')
    for role in ['content_editor','store_manager']:
        fixture(s,'role',{'role':role})
        for path in [PATH,PREVIEW]:expect(s.client.request(path)[0]==403,role+' cannot access owner branding: '+path)
        expect(s.client.request(PATH,{'action':'publish','logo_id':str(two)})[0]==403,'Role boundary applies before a direct POST')
    fixture(s,'role',{'role':'owner'})
    mutate(s,'publish','')
    expect('class="site-logo"' not in anon.request('')[1],'Explicit empty selection uses native text wordmark')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable SQL environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-branding-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder,label in [('', 'root'),('band','subfolder')]:
            with site(base/label,archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s)
    print('Owner branding/private preview/public media SQL/HTTP checks passed.',flush=True)

if __name__=='__main__':main()
