#!/usr/bin/env python3
"""Real root/subfolder published projections, SEO isolation and sitemap HTTP tests."""
from __future__ import annotations
import html
import json
import os
import re
import tempfile
import urllib.error
import urllib.request
import urllib.parse
import xml.etree.ElementTree as ET
from pathlib import Path
from http_integration import Client, build_package, expect, site
from content_integration import save_content, upload, png_bytes
from content_history_integration import fixture as history_fixture
from commerce_integration import seed_catalog as make_product, product_values

NS={'s':'http://www.sitemaps.org/schemas/sitemap/0.9'}


def meta(body, name, attribute='name'):
    match=re.search(r'<meta '+attribute+'="'+re.escape(name)+r'" content="([^"]*)"',body)
    return html.unescape(match.group(1)) if match else None


def structured(body):
    match=re.search(r'<script id="page-structured-data" type="application/ld\+json">(.*?)</script>',body,re.S)
    return json.loads(match.group(1)) if match else None


def seed(s):
    image,_,_=upload(s,'discovery-image.png','image/png',png_bytes())
    id=save_content(s,'page','Approved constellation',{'body':'Published melody text','image_id':str(image),'seo_title':'Approved search title','seo_description':'Approved sharing description','share_image_id':str(image)},slug='approved-story')
    event=save_content(s,'event','Synthetic public stage',{'starts_at':'2099-11-08T20:00','venue':'Synthetic Hall','city':'Synthetic City','address':'123 Test Avenue','country':'United States','event_access':'public','event_state':'cancelled','description':'Generated fixture, not a real performance.','image_id':str(image)},slug='synthetic-stage')
    return id,event,image


def xml_tree(s, path):
    status,body,headers,_=s.client.request(path)
    expect(status==200 and 'application/xml' in headers.get('Content-Type',''),'Sitemap is a real XML response: '+path)
    return ET.fromstring(body),body,headers


def journey(s):
    id,event,image=seed(s);anon=Client(s.base)
    status,body,headers,_=anon.request('pages/?slug=approved-story')
    expect(status==200 and meta(body,'og:title','property')=='Approved search title · Spiritual Gorillaz','Published share title is used in real metadata')
    expect(meta(body,'description')=='Approved sharing description' and meta(body,'twitter:description')=='Approved sharing description','Search and social descriptions agree')
    expect(meta(body,'og:image','property')==s.base+f'media/file.php?id={image}' and meta(body,'og:image:width','property')=='2','Sharing image is public and has actual dimensions')
    expect(f'width="2" height="2" src="{urllib.parse.urlparse(s.base).path}media/file.php?id={image}"' in body,'Public image has explicit intrinsic dimensions')
    save_content(s,'page','Secret constellation draft',{'body':'NEVER-PUBLIC-DRAFT-SENTINEL','seo_title':'NEVER-PUBLIC-META-SENTINEL','seo_description':'Private description'},action='save',item_id=id,slug='never-published-slug')
    public=anon.request('pages/?slug=approved-story')[1]
    expect('NEVER-PUBLIC' not in public and meta(public,'og:title','property').startswith('Approved search title'),'Saving private metadata leaves the published preview untouched')
    expect(anon.request('pages/?slug=never-published-slug')[0]==404,'Draft-only URL is neither public content nor a redirect')
    results=anon.request('pages/search.php?q=constellation')[1]
    expect('Approved constellation' in results and 'Secret constellation draft' not in results,'Public search reads the published title, not its latest private column')
    expect(meta(results,'robots')=='noindex, nofollow','Internal search-result pages are not search-engine landing pages')
    expect('0 result(s)' in anon.request('pages/search.php?q=NEVER-PUBLIC')[1],'Private body and metadata cannot be discovered through public search')
    excluded=save_content(s,'page','Excluded constellation',{'body':'Hidden from discovery','seo_visibility':'exclude'},slug='excluded-story')
    page=anon.request('pages/?slug=excluded-story')
    expect(page[0]==200 and meta(page[1],'robots')=='noindex, nofollow' and 'noindex' in page[2].get('X-Robots-Tag',''),'Discovery exclusion does not pretend to be access control')
    expect('Excluded constellation' not in anon.request('pages/search.php?q=constellation')[1],'Excluded pages do not appear in site search')
    tree,_,_=xml_tree(s,'?sitemap=1')
    urls=[x.text for x in tree.findall('s:sitemap/s:loc',NS)]
    expect(all(x.startswith(s.base+'?sitemap=1&part=') for x in urls),'Index references absolute installation-root sitemap parts')
    content,raw,headers=xml_tree(s,'?sitemap=1&part=content&page=1')
    locations=[x.text for x in content.findall('s:url/s:loc',NS)]
    expect(s.base+'pages/?slug=approved-story' in locations and not any('excluded-story' in x or 'never-published-slug' in x for x in locations),'Sitemap includes approved URLs only, excluding drafts and noindex content')
    expect(not any('/admin/' in x or '/account/' in x or 'booking' in x or 'search.php' in x for x in locations),'Sitemap does not expose private forms, accounts or administration')
    for bad in ['?sitemap=1&page[]=1','?sitemap=1&part=bad','pages/search.php?q[]=x','pages/search.php?page=-1','pages/search.php?kind=users']:
        expect(anon.request(bad)[0]==400,'Malformed discovery input is rejected: '+bad)
    expect(anon.request('?sitemap=1&part=content&page=999')[0]==404,'Missing sitemap page is a true 404')
    request=urllib.request.Request(s.base+'?sitemap=1&part=content&page=1',headers={'If-None-Match':headers['ETag']})
    try:response=urllib.request.urlopen(request)
    except urllib.error.HTTPError as e:response=e
    with response:expect(response.status==304 and response.read()==b'','Sitemap conditional GET validates unchanged bytes')
    with urllib.request.urlopen(urllib.request.Request(s.base+'?sitemap=1',method='HEAD')) as response:expect(response.status==200 and response.read()==b'','Sitemap HEAD returns metadata without body')
    expect(anon.request('?sitemap=1',{'x':'y'})[0]==405,'Sitemap rejects mutation methods')
    event_page=anon.request('shows/event.php?slug=synthetic-stage')[1];data=structured(event_page)
    expect(data['eventStatus']=='https://schema.org/EventCancelled' and data['startDate'].endswith('-06:00'),'Published single-show markup has supplied status, date and offset')
    expect('offers' not in data and 'aggregateRating' not in data,'Event markup does not invent prices or ratings')
    private=save_content(s,'event','Invitation fixture',{'starts_at':'2099-11-08T20:00','venue':'Synthetic Hall','city':'Synthetic City','event_access':'invitation'},slug='invitation-fixture')
    expect(structured(anon.request('shows/event.php?slug=invitation-fixture')[1]) is None,'Invitation-only show does not emit public event markup')
    # Former approved names redirect; unpublished/draft-only names never do.
    save_content(s,'page','Renamed approved story',{'body':'Moved public text'},item_id=id,slug='new-approved-story')
    response=anon.request('pages/?slug=approved-story')
    expect(response[0]==200 and response[3].endswith('/pages/?slug=new-approved-story'),'Old published leaf link reaches the current approved page')
    expect(anon.request('pages/?slug=never-published-slug')[0]==404,'Draft name is still not a redirect after a different name is published')
    replacement=save_content(s,'page','Reclaimed exact URL',{'body':'New exact match'},slug='approved-story')
    response=anon.request('pages/?slug=approved-story')
    expect('Reclaimed exact URL' in response[1] and response[3].endswith('slug=approved-story'),'Current exact URL wins over an older redirect candidate')
    save_content(s,'page','Replacement moved',{'body':'Second historic owner'},item_id=replacement,slug='replacement-moved')
    expect(anon.request('pages/?slug=approved-story')[0]==404,'Ambiguous old URL never redirects to an arbitrary item')
    # Existing catalog/category gates are also applied to public discovery.
    product,variant,product_image=make_product(s)
    s.client.form(f'admin/shop/edit.php?id={product}',product_values(s,product,product_image,name='PRIVATE-MERCH-DISCOVERY'))
    result=anon.request('pages/search.php?q=Fixture+shirt&kind=product')[1]
    expect('Fixture shirt' in result,'Published active catalog merchandise is searchable')
    expect('0 result(s)' in anon.request('pages/search.php?q=PRIVATE-MERCH-DISCOVERY')[1],'Private product name is not searchable')
    tree,_,_=xml_tree(s,'?sitemap=1&part=content')
    expect(any('shop/product.php?slug=' in x.text for x in tree.findall('s:url/s:loc',NS)),'Active product URLs appear in the sitemap')
    s.client.form('admin/shop/settings.php',{'intro':'Closed fixture'})
    expect('Fixture shirt' not in anon.request('pages/search.php?q=Fixture+shirt')[1],'Closing the catalog withdraws products from discovery')
    # Global controls never change credentials or claim visibility is privacy.
    expect(anon.request('admin/discovery.php')[3].endswith('/admin/login.php'),'Anonymous visitors cannot configure indexing')
    expect(s.client.request('admin/discovery.php',{'csrf':'bad','indexing':'disabled'})[0]==403,'Global indexing change requires valid CSRF')
    s.client.form('admin/discovery.php',{'indexing':'disabled'})
    expect(meta(anon.request('')[1],'robots')=='noindex, nofollow','Owner staging setting emits page-level noindex')
    tree,_,_=xml_tree(s,'?sitemap=1')
    expect(tree.tag.endswith('urlset') and len(tree)==0,'Disabled indexing serves no sitemap entries')
    expect(structured(anon.request('shows/event.php?slug=synthetic-stage')[1]) is None,'Staging setting suppresses public event structured data')
    s.client.form('admin/discovery.php',{'indexing':'enabled'})
    expect(meta(anon.request('')[1],'robots')=='index, follow','Explicit owner re-enablement restores public discovery metadata')
    roles=history_fixture(s,'roles');editor=Client(s.base);editor.form('admin/login.php',{'email':'content_editor@example.test','password':'History-fixture-Password-42!'})
    expect(editor.request('admin/discovery.php')[0]==403,'Content editor cannot change global indexing controls')
    for i in range(24):save_content(s,'page',f'Paging constellation {i}',{'body':'Published page fixture'},slug=f'public-paging-{i}')
    first=anon.request('pages/search.php?q=Paging+constellation')[1];second=anon.request('pages/search.php?q=Paging+constellation&page=2')[1]
    expect(first.count('<article>')==20 and second.count('<article>')==4,'Public search uses bounded real pagination')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-discovery-') as d:
        base=Path(d);archive,code=build_package(base/'packages')
        for sub in ['', 'band']:
            with site(base/('nested' if sub else 'root'),archive,code,sub) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s)
    print('Published discovery, metadata, redirects and sitemap SQL/HTTP checks passed.',flush=True)
if __name__=='__main__':main()
