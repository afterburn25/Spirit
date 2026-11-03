#!/usr/bin/env python3
"""Private history/restore and bounded large-library SQL/HTTP regressions."""
from __future__ import annotations
import json
import os
import re
import subprocess
import tempfile
from pathlib import Path
from types import SimpleNamespace
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import save_content, upload, png_bytes, edit_revision


def fixture(s, action, values=None):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
    r=subprocess.run(['php',str(ROOT/'app/tests/content_history_fixture.php'),action],input=json.dumps(values or {}),env=env,text=True,capture_output=True,timeout=30)
    if r.returncode:raise AssertionError('History fixture failed: '+r.stderr)
    return json.loads(r.stdout)


def snapshot(s, id):return fixture(s,'snapshot',{'id':id})


def seed_story(s):
    old,_,_=upload(s,'history-old.png','image/png',png_bytes())
    new,_,_=upload(s,'history-new.png','image/png',png_bytes())
    id=save_content(s,'page','History Alpha',{'body':'Original <script>text</script>','image_id':str(old),'sort_order':'17'},slug='history-story')
    save_content(s,'page','History Beta',{'body':'Currently live body','image_id':str(new),'sort_order':'5'},item_id=id,slug='history-story')
    save_content(s,'page','Private Gamma',{'body':'Private unreleased text','image_id':str(new),'sort_order':'99'},action='save',item_id=id,slug='history-story')
    return id,old,new


def restore(s, id, source, expected=None, client=None):
    expected=edit_revision(s,'page',id) if expected is None else expected
    return (client or s.client).form(f'admin/content/history.php?id={id}',{'action':'restore','source_revision':str(source),'revision':str(expected)})


def journey(s):
    id,old,new=seed_story(s);path=f'admin/content/history.php?id={id}&revision=1';before=snapshot(s,id)
    expect('"sort_order":5' in before['row']['published_json'],'Private ordering changes do not change the published snapshot')
    anon=Client(s.base);status,body,headers,url=anon.request(path)
    expect(url.endswith('/admin/login.php') and 'Original &lt;script&gt;' not in body,'Anonymous visitors cannot retrieve private revision content')
    status,body,headers,_=s.client.request(path)
    expect(status==200 and 'no-store' in headers.get('Cache-Control','') and headers.get('X-Robots-Tag')=='noindex, nofollow','History is private, uncached and excluded from indexing')
    expect('Original &lt;script&gt;text&lt;/script&gt;' in body and '<script>text</script>' not in body,'Historical active markup is rendered as escaped text')
    expect('Private unreleased text' in body and 'Restore as private draft' in body,'History compares saved content with the current private draft')
    expect(s.client.request(path,{'csrf':'bad','action':'restore','source_revision':'1','revision':'3'})[0]==403,'Restore requires valid CSRF')
    expect(snapshot(s,id)==before,'Rejected restore leaves content, history and media references unchanged')
    response=restore(s,id,1,2)
    expect('changed in another session' in response[1] and snapshot(s,id)==before,'Stale restore cannot overwrite a later draft')
    response=restore(s,id,1)
    state=snapshot(s,id)
    expect('restored as a new private draft' in response[1] and state['row']['revision']==4,'Restore creates a new numbered draft')
    expect(state['row']['published_json']==before['row']['published_json'] and state['row']['published_at']==before['row']['published_at'],'Restoration never republishes or changes the live snapshot')
    expect(state['row']['title']=='History Alpha' and state['history'][-1]['action']=='restore' and len(state['history'])==4,'History remains append-only with a recorded restore action')
    expect(state['row']['sort_order']==17,'Recorded historical sort order is restored privately')
    expect(anon.request(f'media/file.php?id={old}')[0]==404 and anon.request(f'media/file.php?id={new}')[0]==200,'Restored old media stays private until explicitly published')
    expect('History Beta' in anon.request('pages/?slug=history-story')[1] and 'Original &lt;script&gt;' not in anon.request('pages/?slug=history-story')[1],'Public page remains the approved version after restoration')
    restore(s,id,1,3)
    expect(snapshot(s,id)==state,'Replaying a restore with the old revision is harmless')
    other=save_content(s,'page','Other document',{'body':'Unrelated'},action='save',slug='other-document')
    expect(s.client.request(f'admin/content/history.php?id={other}&revision=4')[0]==404,'Revision numbers are scoped to their content item')
    response=restore(s,other,4)
    expect('does not belong' in response[1] and snapshot(s,other)['row']['revision']==1,'A foreign item revision cannot be restored')
    save_content(s,'page','History Alpha',{'body':'Original <script>text</script>','image_id':str(old),'sort_order':'17'},item_id=id,slug='history-story')
    expect(anon.request(f'media/file.php?id={old}')[0]==200 and anon.request(f'media/file.php?id={new}')[0]==404,'Explicit publication changes media visibility, unlike draft restoration')
    save_content(s,'page','New live again',{'body':'Safe live','image_id':str(new)},item_id=id,slug='history-story')
    fixture(s,'media-break',{'media_id':old});before=snapshot(s,id)
    expect('missing or damaged' in restore(s,id,1)[1] and snapshot(s,id)==before,'Damaged old media blocks restore atomically without deleting history')
    # Legacy sort metadata: use a version with valid new media, not the damaged old file.
    fixture(s,'legacy',{'id':id,'revision':2});before_order=before['row']['sort_order']
    restore(s,id,2)
    expect(snapshot(s,id)['row']['sort_order']==before_order,'A legacy revision without sort metadata preserves the current ordering')
    roles=fixture(s,'roles');editor=Client(s.base);manager=Client(s.base)
    for client,role in [(editor,'content_editor'),(manager,'store_manager')]:
        client.form('admin/login.php',{'email':role+'@example.test','password':'History-fixture-Password-42!'})
    expect(manager.request(path)[0]==403 and manager.request('admin/content/')[0]==403,'Store manager cannot read private content history or drafts')
    expect(not fixture(s,'direct-denied',{'actor':roles['store_manager']})['ok'],'Domain save rejects a store-manager actor independently of UI hiding')
    expect(editor.request(path)[0]==200,'Content editor can inspect permitted history')
    expected=edit_revision(s,'page',id)
    expect('restored as a new private draft' in restore(s,id,2,expected,editor)[1],'Content editor can perform a permitted private restore')
    for route in ['admin/content/?kind[]=page','admin/content/?page[]=1','admin/content/?sort=DROP','admin/content/history.php?id[]=1','admin/content/history.php?id=1&revision[]=1','admin/content/edit.php?id[]=1']:
        expect(s.client.request(route)[0]==400,'Malformed typed query is rejected: '+route)
    edit=f'admin/content/edit.php?kind=page&id={id}';before=snapshot(s,id)
    response=s.client.form(edit,{'action':'save','title':'Malformed field','slug':'history-story','revision':str(before['row']['revision']),'body[]':'not text'})
    expect('Invalid Page text' in response[1] and snapshot(s,id)==before,'Array field cannot silently erase saved content')
    save_content(s,'page','Literal 100%_tag!',{'body':'Literal'},action='save',slug='literal-search')
    expect('Literal 100%_tag!' in s.client.request('admin/content/?q=100%25_tag%21')[1],'Search treats percent, underscore and exclamation as literal text')
    fixture(s,'seed',{'kind':'page','count':1100})
    first=s.client.request('admin/content/?q=Large+library&sort=title')[1]
    last=s.client.request('admin/content/?q=Large+library&sort=title&page=44')[1]
    expect(first.count('<tr>')==26 and '1,100 matching item(s)' in first and 'Page 1 of 44' in first,'Large content library is bounded to 25 results per page')
    expect('Large library 1099' in last and 'Page 44 of 44' in last,'Content after the old 1000-record limit is reachable')
    expect('Page 44 of 44' in s.client.request('admin/content/?q=Large+library&sort=title&page=99999')[1],'Out-of-range valid pages clamp without unbounded retrieval')
    private=s.client.request('admin/content/?q=Large+library&state=published')[1]
    expect('0 matching item(s)' in private,'Visibility filter is applied before pagination')
    release=save_content(s,'release','Selected old release',{},action='save',slug='selected-old-release')
    track=save_content(s,'track','Selected release track',{'release_id':str(release)},action='save',slug='selected-release-track')
    fixture(s,'seed',{'kind':'release','count':1001});fixture(s,'seed',{'kind':'media','count':1001})
    expect(f'value="{release}" selected' in s.client.request(f'admin/content/edit.php?kind=track&id={track}')[1],'Old selected release remains in a bounded selector beyond 1000 newer records')
    # An image preview source attribute may sit between value and selected.
    # Match an actual selected option; do not require adjacent attributes.
    option_html=s.client.request(edit)[1]
    expect(bool(re.search(r'<option\b[^>]*\bvalue="'+str(new)+r'"[^>]*\bselected\b',option_html)),
           'Old selected media remains selected beyond 1000 newer uploads')
    fixture(s,'duplicate-history',{'id':other})
    history=s.client.request(f'admin/content/history.php?id={other}')[1]
    expect('Page 1 of 2' in history and history.count('Compare #')==25,'Version metadata is paginated without loading every snapshot')
    expect('Compare #1' in s.client.request(f'admin/content/history.php?id={other}&page=2')[1],'Older revisions remain accessible through history pagination')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-history-') as d:
        base=Path(d);archive,code=build_package(base/'packages')
        for sub in ['', 'band']:
            with site(base/('nested' if sub else 'root'),archive,code,sub) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s)
    print('Content history, large-library and role SQL/HTTP checks passed.',flush=True)
if __name__=='__main__':main()
