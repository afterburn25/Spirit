#!/usr/bin/env python3
"""Real populated storefront photos in Chromium, Firefox and WebKit.
All product/media fixtures are synthetic and created through the actual owner UI.
This does not claim coverage of every OS, mobile device or payment provider.
"""
from __future__ import annotations
import os
import json
import secrets
import struct
import tempfile
import urllib.request
import urllib.parse
import zlib
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as browser_expect
from http_integration import Client, build_package, expect, site
from content_integration import token, raw_request, seed_media_and_music
from commerce_integration import seed_catalog, product_values, snapshot
from public_browser import screenshot, wait_for_audio


def image_bytes(width, height, variant):
    def chunk(kind, data):
        return struct.pack('>I',len(data))+kind+data+struct.pack('>I',zlib.crc32(kind+data)&0xffffffff)
    rows=[]
    for y in range(height):
        row=bytearray(b'\0')
        for x in range(width):
            value=(x//32+y//32+variant*3)%8
            row.extend(((value*31+variant*53)%256,(100+value*17)%256,(200-value*19)%256,0 if x<16 or y<16 else 255))
        rows.append(row)
    return b'\x89PNG\r\n\x1a\n'+chunk(b'IHDR',struct.pack('>IIBBBBB',width,height,8,6,0,0,0))+chunk(b'IDAT',zlib.compress(b''.join(rows)))+chunk(b'IEND',b'')


def upload_image(s,width,height,index):
    boundary='GalleryFixture'+secrets.token_hex(12)
    values={'csrf':token(s.client.request('admin/media/')[1]),'rights_confirmed':'1','alt_text':f'Synthetic gallery photograph {index}'}
    parts=[f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode() for k,v in values.items()]
    parts += [f'--{boundary}\r\nContent-Disposition: form-data; name="file"; filename="synthetic-{index}.png"\r\nContent-Type: image/png\r\n\r\n'.encode()+image_bytes(width,height,index)+b'\r\n',f'--{boundary}--\r\n'.encode()]
    request=urllib.request.Request(s.base+'admin/media/',b''.join(parts),{'Content-Type':f'multipart/form-data; boundary={boundary}'})
    with s.client.opener.open(request,timeout=30) as response:
        import re
        found=re.search(r'File #(\d+) uploaded',response.read().decode())
        if not found:raise AssertionError('Actual gallery upload failed')
        return int(found.group(1))


def photo(page, ids, index, dimensions):
    links=page.locator('.product-photo')
    browser_expect(links.nth(index)).to_have_attribute('aria-current','true')
    expect(page.locator('.product-photo[aria-current]').count()==1,'Exactly one photograph is selected')
    state=page.locator('.product-main-image').evaluate('''async image => {
      await image.decode(); return {src:image.currentSrc, srcset:image.getAttribute('srcset'),
        width:image.getAttribute('width'),height:image.getAttribute('height'),alt:image.alt,
        fit:getComputedStyle(image).objectFit,complete:image.complete,naturalWidth:image.naturalWidth};
    }''')
    expect(urllib.parse.parse_qs(urllib.parse.urlsplit(state['src']).query)['id']==[str(ids[index])],'Browser actually displays selected media, not stale responsive candidate')
    expect(state['complete'] and state['naturalWidth']>0 and state['fit']=='contain','Selected photograph decodes and is not cropped or stretched')
    expect((int(state['width']),int(state['height']))==dimensions[index],'Selected image carries its own intrinsic dimensions')
    expect(state['alt']==f'Synthetic gallery photograph {index+1}','Selected image carries its own approved alternative text')
    if dimensions[index][0]>320:
        expect(state['srcset'] and all(urllib.parse.parse_qs(urllib.parse.urlsplit(c.strip().split()[0]).query)['id']==[str(ids[index])] for c in state['srcset'].split(',')),'Every responsive candidate belongs to selected image')
    else:
        expect(state['srcset'] is None,'Small-image selection removes the previous large-image srcset')
    return state


def open_nav(page,label):
    link=page.locator('#public-nav a').filter(has_text=label).first
    if not link.is_visible():page.locator('.nav-toggle').click()
    link.click()


def journey(browser,s,ids,dimensions,name):
    for width in [1440,390,320]:
        context=browser.new_context(viewport={'width':width,'height':900},reduced_motion='reduce',bypass_csp=False)
        page=context.new_page();errors=[];page.on('pageerror',lambda e:errors.append(str(e)))
        page.goto(s.base+'music/release.php?slug=fixture-release',wait_until='networkidle')
        page.locator('.play-track').first.click();wait_for_audio(page)
        page.evaluate('window.galleryAudio=document.getElementById("site-audio")')
        open_nav(page,'Merch');page.wait_for_url('**/shop/')
        page.get_by_role('link',name='Fixture shirt',exact=True).click();page.wait_for_url('**/shop/product.php?slug=fixture-shirt')
        expect(page.evaluate('window.galleryAudio===document.getElementById("site-audio")&&!window.galleryAudio.paused'),'Real audio continues from release through catalog to product')
        expect(page.locator('.product-photo').count()==3,'Published product exposes all three actual photographs')
        photo(page,ids,0,dimensions)
        # Measure an unselected thumbnail before its larger source can be in the
        # decoded-image cache. A warm browser may legitimately reuse a larger
        # already-cached candidate, even with the correct 72px sizes hint.
        thumbnail=page.locator('.product-photo img').nth(1)
        thumbnail.scroll_into_view_if_needed()
        thumbnail.evaluate('(image)=>image.decode()')
        thumb_query=urllib.parse.parse_qs(urllib.parse.urlsplit(thumbnail.evaluate('(image)=>image.currentSrc')).query)
        print('MEASURED '+json.dumps({'engine':name,'viewport':width,'cold_thumbnail':thumb_query}),flush=True)
        expect(thumbnail.get_attribute('sizes')=='72px' and thumb_query.get('id')==[str(ids[1])] and thumb_query.get('width')==['320'],'Cold unselected thumbnail requests its small responsive candidate')
        quantity=page.locator('[name=quantity]');quantity.fill('3')
        history_length=page.evaluate('history.length')
        for index in [1,2,0]:
            selected=page.locator('.product-photo').nth(index);selected.focus();page.keyboard.press('Enter')
            photo(page,ids,index,dimensions);browser_expect(selected).to_be_focused()
            expect(page.evaluate('history.length')==history_length,'Photo selection does not add a history entry')
            expect(quantity.input_value()=='3' and page.locator('#product-add').is_enabled(),'Photo switch preserves purchasing controls and entered quantity')
            expect(page.evaluate('window.galleryAudio===document.getElementById("site-audio")'),'Gallery preserves the original audio node')
        expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'),f'{name}/{width}px populated storefront has no horizontal overflow')
        expect(page.locator('link[rel=canonical]').get_attribute('href')==s.base+'shop/product.php?slug=fixture-shirt','Photo URL does not change the canonical product')
        page.evaluate('window.scrollTo(0,0)');screenshot(page,f'gallery-{name}-{width}-product')
        expect(errors==[],f'{name}/{width}px gallery has no uncaught errors')
        context.close()

    # Native links still select only approved photos without JavaScript.
    context=browser.new_context(viewport={'width':390,'height':900},java_script_enabled=False)
    page=context.new_page();page.goto(s.base+'shop/product.php?slug=fixture-shirt')
    page.locator('.product-photo').nth(1).click();page.wait_for_url('**/shop/product.php?slug=fixture-shirt&photo=2')
    photo(page,ids,1,dimensions)
    expect(page.locator('#product-add').is_visible(),'No-JavaScript photo selection leaves a usable product form')
    screenshot(page,f'gallery-{name}-390-nojs');context.close()


def failure_and_race(browser,s,ids,dimensions,name):
    context=browser.new_context(viewport={'width':390,'height':900},reduced_motion='reduce',bypass_csp=False)
    page=context.new_page();mode={'value':'fail'};held=[]
    def intercept(route):
        query=urllib.parse.parse_qs(urllib.parse.urlsplit(route.request.url).query)
        if query.get('id')==[str(ids[1])] and query.get('width')!=['320']:
            if mode['value']=='fail':route.fulfill(status=503,content_type='text/plain',body='Synthetic image failure');return
            if mode['value']=='hold':held.append(route);return
        route.continue_()
    page.route('**/media/file.php?*',intercept)
    page.goto(s.base+'shop/product.php?slug=fixture-shirt',wait_until='networkidle')
    page.locator('.product-photo').nth(1).click()
    browser_expect(page.locator('.product-photo-status')).to_contain_text('could not load')
    photo(page,ids,0,dimensions)
    browser_expect(page.locator('.product-main')).to_have_attribute('aria-busy','false')
    expect('photo=2' not in page.url,'Failed image selection does not claim the new photo in the URL')
    screenshot(page,f'gallery-{name}-390-failed-photo')
    mode['value']='ok';page.locator('.product-photo').nth(1).click();photo(page,ids,1,dimensions)
    context.close()
    # A fresh context has not already decoded the requested main photo. This
    # guarantees a real pending network request rather than an immediate cache hit.
    context=browser.new_context(viewport={'width':390,'height':900},reduced_motion='reduce',bypass_csp=False)
    page=context.new_page();mode['value']='hold';held.clear()
    page.route('**/media/file.php?*',intercept)
    page.goto(s.base+'shop/product.php?slug=fixture-shirt',wait_until='networkidle')
    with page.expect_request(lambda request: urllib.parse.parse_qs(urllib.parse.urlsplit(request.url).query).get('id')==[str(ids[1])] and urllib.parse.parse_qs(urllib.parse.urlsplit(request.url).query).get('width')!=['320']):
        page.locator('.product-photo').nth(1).click()
    browser_expect(page.locator('.product-photo-status')).to_contain_text('Loading photograph 2')
    page.locator('.product-photo').nth(2).click();photo(page,ids,2,dimensions)
    expect(bool(held),'A real earlier image request remains pending for the race test')
    mode['value']='ok'
    for route in held:
        with page.expect_response(lambda r:r.url==route.request.url) as event:route.continue_()
        event.value.finished()
    page.locator('.product-main').evaluate('node=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))')
    photo(page,ids,2,dimensions)
    browser_expect(page.locator('.product-photo-status')).to_contain_text('Showing photograph 3')
    expect('photo=3' in page.url,'Delayed earlier response cannot replace the latest chosen photograph or URL')
    context.close()


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    name=os.environ.get('SPIRIT_BROWSER','chromium')
    if name not in ['chromium','firefox','webkit']:raise RuntimeError('Unsupported test engine')
    with tempfile.TemporaryDirectory(prefix='spirit-gallery-') as directory:
        base=Path(directory);archive,unused=build_package(base/'packages')
        with site(base/'website',archive,unused,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();pid,_,_=seed_catalog(s)
            dimensions=[(1200,800),(800,1200),(160,100)]
            ids=[upload_image(s,w,h,i+1) for i,(w,h) in enumerate(dimensions)]
            values=product_values(s,pid,ids[0],'publish')
            values.update({f'image_ids[{i}]':str(id) for i,id in enumerate(ids)})
            response=s.client.form(f'admin/shop/edit.php?id={pid}',values)
            expect(response[0]==200 and all(f'photo={i}' in Client(s.base).request('shop/product.php?slug=fixture-shirt')[1] for i in [1,2,3]),'Actual published product has three gallery positions')
            seed_media_and_music(s);before=snapshot(s)
            with sync_playwright() as driver:
                browser=getattr(driver,name).launch()
                try:journey(browser,s,ids,dimensions,name);failure_and_race(browser,s,ids,dimensions,name)
                finally:browser.close()
            expect(snapshot(s)==before,'Reading and switching photos do not alter catalog, inventory, accounts or audit data')
            # Withdrawal still applies to originals and generated sizes; a query cannot select arbitrary IDs.
            s.client.form(f'admin/shop/edit.php?id={pid}',product_values(s,pid,ids[0],'unpublish'))
            for id in ids:
                expect(raw_request(s.base+f'media/file.php?id={id}')[0]==404 and raw_request(s.base+f'media/file.php?id={id}&width=320')[0]==404,'Unpublishing withdraws selected originals and responsive copies')
            expect(Client(s.base).request('shop/product.php?slug=fixture-shirt&photo=2')[0]==404,'No-JavaScript selection cannot resurrect an unpublished product')
    print('Populated storefront gallery, native fallback, failure/race and audio navigation passed: '+name,flush=True)

if __name__=='__main__':main()
