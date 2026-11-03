#!/usr/bin/env python3
"""Existing CMS section artwork, private previews and publication, no live providers."""
from __future__ import annotations
import os
import re
import tempfile
from pathlib import Path
from http_integration import Client, build_package, expect, site
from content_integration import save_content, upload, png_bytes
from content_history_integration import fixture as history_fixture, restore, snapshot
from launch_integration import fixture as launch_fixture

SECTIONS={'home':'','music':'music/','shows':'shows/','band':'band/','media':'media/','booking':'booking/','press':'press/','merch':'shop/'}
HEADINGS={'home':'MUSIC<br>WITH A HIGHER','music':'OUR MUSIC','shows':'UPCOMING SHOWS','band':'THE BAND','media':'WATCH &amp; EXPLORE','booking':'BOOK<br>THE BAND','press':'PRESS KIT','merch':'WEAR THE MOVEMENT'}

def journey(s):
    anon=Client(s.base)
    before=launch_fixture(s,'fingerprint')
    for slug in SECTIONS:
        status,body,_,_=s.client.request('admin/content/edit.php?kind=page&placement='+slug)
        expect(status==200 and f'name="slug" value="{slug}"' in body,'Placement opens a prefilled existing Page editor: '+slug)
    expect(launch_fixture(s,'fingerprint')==before,'Opening eight preparation links creates no content or publication')
    directory=s.client.request('admin/content/?kind=page')[1]
    expect(directory.count('section-directory-card')==8,'Pages directory shows eight placements without adding navigation destinations')
    for invalid in ['placement[]=home','placement=../private','kind=event&placement=music']:
        expect(s.client.request('admin/content/edit.php?'+invalid)[0]==400,'Invalid placement rejected: '+invalid)
    for slug,route in SECTIONS.items():
        first,_,_=upload(s,slug+'-first.png','image/png',png_bytes())
        second,_,_=upload(s,slug+'-private.png','image/png',png_bytes())
        fields={'subtitle':'FIRST '+slug,'body':'FIRST BODY '+slug+' <script>not code</script>','image_id':str(first),'focal':'top'}
        item=save_content(s,'page',slug.title(),fields,action='save',slug=slug)
        edit=f'admin/content/edit.php?kind=page&id={item}';preview=f'admin/content/preview.php?id={item}'
        before=snapshot(s,item)
        status,body,headers,_=s.client.request(preview)
        expect(status==200 and HEADINGS[slug] in body and 'PRIVATE PREVIEW' in body,'Draft previews the actual section layout: '+slug)
        expect('FIRST '+slug in body and '&lt;script&gt;not code&lt;/script&gt;' in body and '<script>not code</script>' not in body,'Draft copy renders escaped in its designed location: '+slug)
        expect('no-store' in headers.get('Cache-Control','') and 'noindex' in headers.get('X-Robots-Tag','') and headers.get('Referrer-Policy')=='no-referrer','Draft response remains private and unindexed: '+slug)
        expect(anon.request(preview)[3].endswith('/admin/login.php') and anon.request(f'media/file.php?id={first}')[0]==404,'Anonymous visitors cannot read the preview or its new image: '+slug)
        expect('FIRST '+slug not in anon.request(route)[1],'Unpublished section Page does not replace the public fallback: '+slug)
        expect(s.client.request(preview,{'action':'publish','csrf':'ignored'})[0]==405,'Preview endpoint rejects POST instead of processing forms: '+slug)
        expect(snapshot(s,item)==before,'Preview and rejected POST do not change content/history/media references: '+slug)
        expect(s.client.request('admin/content/edit.php?kind=page&placement='+slug)[3].endswith(edit),'Prepare link reopens the exact existing Page instead of creating a duplicate: '+slug)
        inspection=s.client.request(edit)[1]
        expect('2 × 2 pixels' in inspection and 'Below the suggested' in inspection,'Editor reports real image resolution without blocking publication: '+slug)
        save_content(s,'page',slug.title(),fields,item_id=item,slug=slug)
        live=anon.request(route)[1]
        expect('FIRST '+slug in live and 'FIRST BODY '+slug in live and 'PRIVATE PREVIEW' not in live,'Explicit publication updates both introduction fields: '+slug)
        expect(anon.request(f'media/file.php?id={first}')[0]==200 and anon.request(f'media/file.php?id={first}&width=320')[0]==200,'Published artwork and bounded derivative access use the normal media endpoint: '+slug)
        alias=anon.request('pages/?slug='+slug)[1]
        expect(HEADINGS[slug] in alias and 'FIRST '+slug in alias,'Existing Page alias renders the same designed section: '+slug)
        changed={'subtitle':'NEW PRIVATE '+slug,'body':'NEW PRIVATE BODY '+slug,'image_id':str(second),'focal':'right'}
        save_content(s,'page','Changed title',changed,action='save',item_id=item,slug=slug)
        expect('NEW PRIVATE '+slug in s.client.request(preview)[1] and 'NEW PRIVATE '+slug not in anon.request(route)[1],'Private replacement never alters the current published section: '+slug)
        expect(anon.request(f'media/file.php?id={second}')[0]==404,'Replacement image remains private before publication: '+slug)
        restore(s,item,1)
        expect('NEW PRIVATE '+slug not in s.client.request(preview)[1] and 'FIRST '+slug in anon.request(route)[1],'History restores only a draft without changing public content: '+slug)
        save_content(s,'page',slug.title(),fields,action='unpublish',item_id=item,slug=slug)
        expect('FIRST '+slug not in anon.request(route)[1],'Unpublishing restores the default section presentation: '+slug)
        expect(anon.request(f'media/file.php?id={first}')[0]==404 and anon.request(f'media/file.php?id={first}&width=320')[0]==404,'Unpublishing withdraws otherwise-private original and derivative: '+slug)
        if slug=='booking':
            # Booking uses its dedicated inquiry-form template, not the generic sections.php branch.
            custom={'headline':'PRIVATE BOOKING <script>unsafe</script>',
                    'eyebrow':'PRIVATE BOOKING KICKER','subtitle':'PRIVATE BOOKING SUBTITLE',
                    'body':'PRIVATE BOOKING BODY','slot_1':'PRIVATE BOOKING DETAILS',
                    'button_label':'Booking press guide','button_url':'https://example.com/booking-press'}
            save_content(s,'page','Booking page',custom,action='save',item_id=item,slug=slug)
            draft=s.client.request(preview)[1]
            expect('PRIVATE BOOKING &lt;script&gt;unsafe&lt;/script&gt;' in draft
                   and '<script>unsafe</script>' not in draft
                   and all(word in draft for word in ['PRIVATE BOOKING KICKER','PRIVATE BOOKING SUBTITLE','PRIVATE BOOKING DETAILS','Booking press guide'])
                   and 'href="https://example.com/booking-press"' in draft
                   and 'rel="noopener noreferrer"' in draft
                   and 'class="preview-controls" disabled' in draft,
                   'Dedicated Booking preview escapes editable headings/CTA and disables inquiry submission')
            expect('PRIVATE BOOKING KICKER' not in anon.request(route)[1],
                   'Private Booking draft never changes the public inquiry page')
            save_content(s,'page','Booking page',custom,item_id=item,slug=slug)
            live=anon.request(route)[1]
            expect('PRIVATE BOOKING &lt;script&gt;unsafe&lt;/script&gt;' in live
                   and all(word in live for word in ['PRIVATE BOOKING KICKER','PRIVATE BOOKING DETAILS','Booking press guide'])
                   and 'href="https://example.com/booking-press"' in live
                   and 'name="name"' in live,
                   'Publishing Booking fields updates the real inquiry page without removing its form')
            save_content(s,'page','Booking page',custom,action='unpublish',item_id=item,slug=slug)
    # Existing page records outside the bounded listing remain discoverable.
    history_fixture(s,'seed',{'kind':'page','count':1100})
    directory=s.client.request('admin/content/?kind=page')[1]
    expect('DEFAULT PRESENTATION' not in directory and directory.count('section-directory-card')==8,'Exact placement lookup reaches older records beyond the general 1000-row ceiling')
    # A published slug and another record's draft slug can disagree: never auto-merge.
    music=next_id(s,'music')
    save_content(s,'page','Live music record',{'body':'Live canonical body'},item_id=music,slug='music')
    save_content(s,'page','Renamed music draft',{'body':'Unpublished rename'},action='save',item_id=music,slug='music-renamed')
    competing=save_content(s,'page','Competing private page',{'body':'Unpublished competing draft'},action='save',slug='music')
    before=launch_fixture(s,'fingerprint')
    status,body,_,url=s.client.request('admin/content/edit.php?kind=page&placement=music')
    expect(status==200 and 'q=music' in url and 'More than one saved page refers to this section' in body,'Conflicting placement records are exposed for review instead of silently merged')
    expect(launch_fixture(s,'fingerprint')==before and 'Live canonical body' in anon.request('music/')[1],'Conflict review preserves database and the existing publication')
    save_content(s,'page','Competing private page',{'body':'Still private'},action='save',item_id=competing,slug='other-private-page')
    # Role boundaries are those of the existing content editor and media library.
    roles=history_fixture(s,'roles');editor=Client(s.base);manager=Client(s.base)
    for client,role in [(editor,'content_editor'),(manager,'store_manager')]:
        client.form('admin/login.php',{'email':role+'@example.test','password':'History-fixture-Password-42!'})
    expect(editor.request('admin/content/?kind=page')[0]==200 and manager.request('admin/content/?kind=page')[0]==403,'Directory retains existing content-editor/store-manager boundary')
    expect(editor.request(f'admin/content/preview.php?id={music}')[0]==200 and manager.request(f'admin/content/preview.php?id={music}')[0]==403,'Designed previews enforce the existing server permission')
    # Compare canonical discovery: section pages use static routes once, not duplicates.
    music_page=save_content(s,'page','Search music',{'body':'Published music SEO','seo_visibility':'exclude'},item_id=next_id(s,'music'),slug='music')
    static=anon.request('?sitemap=1&part=static')[1];dynamic=anon.request('?sitemap=1&part=content')[1]
    expect(s.base+'music/' not in static and 'pages/?slug=music' not in dynamic,'Excluded section page is not resurrected by static sitemap or an alias')


def next_id(s,slug):
    url=s.client.request('admin/content/edit.php?kind=page&placement='+slug)[3]
    return int(re.search(r'[?&]id=(\d+)',url).group(1))


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable SQL environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-sections-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder,label in [('', 'root'),('band','subfolder')]:
            with site(base/label,archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s)
    print('Section Page publication/private-preview/authorization checks passed.',flush=True)

if __name__=='__main__':main()
