#!/usr/bin/env python3
"""Verify/capture fixed stage artwork on an actual disposable ZIP installation.

The primary page pass is unseeded. Supplemental, explicitly labeled synthetic
content is created only in the disposable installation to inspect populated
copy panels. No fixture content enters the package. Readiness/provider acceptance
is outside this focused presentation check. Requires the existing non-root SQL
test environment, Playwright Chromium and Pillow (pixel inspection only).
"""
from __future__ import annotations

import argparse
import hashlib
import io
import json
import os
import tempfile
import zipfile
from datetime import datetime, timezone
from pathlib import Path

from PIL import Image
from playwright.sync_api import sync_playwright, expect as browser_expect

import http_integration
from http_integration import expect, site
from public_browser import decoded_images
from content_integration import seed_media_and_music, save_content, upload
from commerce_integration import seed_catalog


ROUTES = [('', 'home'), ('music/', 'music'), ('shows/', 'shows'),
          ('band/', 'band'), ('media/', 'media'), ('press/', 'press'),
          ('booking/', 'booking'), ('shop/', 'merch'),
          ('pages/search.php', 'search'), ('newsletter/', 'newsletter'),
          ('account/login.php', 'account-login'), ('account/register.php', 'account-register'),
          ('shop/cart.php', 'cart'), ('shop/checkout.php', 'checkout')]
CAPTURE_ROUTES = {'', 'music/', 'shows/', 'band/', 'media/', 'press/', 'booking/', 'pages/search.php', 'account/login.php'}
ART = 'images/backgrounds/spiritual-gorillaz-stage-background.webp'
PANELS = '.content-panel,.section-heading:not(.stage-section .section-heading),.release-card,.booking-callout,.newsletter-callout,.download-card,.gallery-grid figure,.cart-line,.order-reference,.music-section>.tabs,.release-cover>.eyebrow,.search-section>p[role="status"],.search-pagination,.product-photo-status,.detail-back'


def digest(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def background(page):
    return page.evaluate('''() => {
      const p=getComputedStyle(document.body,'::before'), s=document.querySelector('.site-shell');
      return {position:p.position,top:p.top,left:p.left,width:p.width,height:p.height,
        image:p.backgroundImage,size:p.backgroundSize,placement:p.backgroundPosition,
        pointerEvents:p.pointerEvents,zIndex:p.zIndex,display:p.display,
        bodyClass:document.body.className,shellBackground:s?getComputedStyle(s).backgroundColor:null,
        isolation:getComputedStyle(document.body).isolation,
        scrollY,headingTop:document.querySelector('h1')?.getBoundingClientRect().top,
        headerTop:document.querySelector('.site-header')?.getBoundingClientRect().top};
    }''')


def capture(page, output, manifest, name, route, note='', synthetic=False):
    page.evaluate('document.fonts.ready')
    data=page.screenshot(path=str(output/(name+'.png')), animations='disabled', full_page=False)
    manifest['screenshots'].append({'file':name+'.png','sha256':digest(data),
        'route':route or '/', 'viewport':page.viewport_size, 'scroll_y':round(page.evaluate('scrollY'),2),
        'full_page':False,'synthetic_content':synthetic,'note':note})
    return data


def header_check(page, baseline, manifest, label):
    current=page.evaluate('''() => {
      const rect=s=>{const n=document.querySelector(s),r=n.getBoundingClientRect();
        return{x:r.x,y:r.y,width:r.width,height:r.height,display:getComputedStyle(n).display}};
      const header=document.querySelector('.site-header'),h=header.getBoundingClientRect(),
        p=getComputedStyle(header,'::before'),style=getComputedStyle(header);
      const left=h.left+parseFloat(style.borderLeftWidth)+parseFloat(p.left);
      return{header:rect('.site-header'),logo:rect('.site-header .wordmark'),
        navigation:rect('#public-nav'),search:rect('.nav-search'),book:rect('.nav-book'),toggle:rect('.nav-toggle'),
        backplate:{left,right:left+parseFloat(p.width),width:parseFloat(p.width),height:parseFloat(p.height),
          top:p.top,bottom:p.bottom,pointerEvents:p.pointerEvents,position:p.position,display:p.display},
        overflow:document.documentElement.scrollWidth>innerWidth+1};
    }''')
    reference=next(r for r in baseline['viewports'] if r['viewport']['width']==page.viewport_size['width'])
    for selector in ['header','logo','navigation','search','book','toggle']:
        expect(all(abs(current[selector][key]-reference[selector][key])<=.05 for key in ['x','y','width','height']),
               label+': '+selector+' position/size exactly retains pre-extension layout')
    plate=current['backplate'];width=page.viewport_size['width']
    expect(plate['left']<=.5 and plate['right']>=width-.5 and abs(plate['width']-width)<=1,
           label+': header backplate reaches both viewport edges')
    expect(plate['pointerEvents']=='none' and not current['overflow'],label+': full-width header does not intercept controls or cause overflow')
    manifest['header_checks'].append({'label':label,'viewport':page.viewport_size,'geometry':current,'matches_baseline':True})


def readable_panels(page, manifest, label):
    result=page.locator(PANELS).evaluate_all(r'''nodes => nodes.map(n=>{
      const s=getComputedStyle(n),r=n.getBoundingClientRect();
      const colors=[...s.backgroundImage.matchAll(/rgba?\(([^)]+)\)/g)].map(m=>m[1].split(',').map(Number));
      const dark=colors.length>0&&colors.every(c=>Math.max(...c.slice(0,3))<80&&(c[3]??1)>=.95);
      const walker=document.createTreeWalker(n,NodeFilter.SHOW_TEXT);let t,overflow=[];
      while(t=walker.nextNode()){
        if(!t.textContent.trim())continue;
        const range=document.createRange();range.selectNodeContents(t);
        for(const textRect of range.getClientRects())if(textRect.width&&
          (textRect.left<r.left-1||textRect.right>r.right+1))overflow.push(t.textContent.trim().slice(0,80));
      }
      return {tag:n.tagName,classes:n.className,width:r.width,height:r.height,
        dark_surface:dark,visible:s.display!=='none'&&r.width>0&&r.height>0,text_overflow:[...new Set(overflow)]};
    })''')
    shown=[item for item in result if item['visible']]
    expect(all(item['dark_surface'] for item in shown),label+': exposed copy groups have near-opaque dark surfaces')
    failures=[item for item in shown if item['text_overflow']]
    expect(not failures,label+': copy stays inside its readable panel '+json.dumps(failures))
    manifest['panel_checks'].append({'label':label,'viewport':page.viewport_size,'visible_panels':len(shown),
        'dark_surfaces':True,'text_within_panels':True,'panels':shown})


def check_page(page, current, route, label, manifest=None):
    response=page.goto(current.base+route, wait_until='networkidle')
    expect(response is not None and response.status==200, label+': actual route renders '+(route or '/'))
    expect(page.locator('body.public-site').count()==1, label+': public layout is active')
    expect(page.locator('h1').count()==1 and page.locator('h1').is_visible(), label+': primary heading readable')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), label+': no horizontal overflow')
    decoded_images(page,label)
    value=background(page)
    expect(value['position']=='fixed' and value['top']=='0px' and value['left']=='0px', label+': decoration is anchored to viewport')
    expect(value['pointerEvents']=='none' and value['isolation']=='isolate', label+': background cannot intercept controls')
    expect(ART in value['image'] and value['shellBackground']=='rgba(0, 0, 0, 0)', label+': selected artwork exposed through shell')
    loaded=page.evaluate(r'''async () => {
      const match=getComputedStyle(document.body,'::before').backgroundImage.match(/url\(["']?([^"')]+)["']?\)/);
      const image=new Image(); image.src=match[1]; await image.decode();
      return {path:new URL(image.src,location.href).pathname,width:image.naturalWidth,height:image.naturalHeight};
    }''')
    expect(loaded['path'].endswith('/'+ART) and (loaded['width'],loaded['height'])==(1672,941), label+': original-aspect native stage image decodes')
    if manifest is not None:readable_panels(page,manifest,label)
    return value


def scroll_check(page, current, output, manifest, label, take_screens):
    first=check_page(page,current,'',label,manifest)
    top=capture(page,output,manifest,label+'-home-top','') if take_screens else page.screenshot(animations='disabled')
    page.evaluate('scrollTo({top:600,behavior:"instant"})')
    page.evaluate('() => new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))')
    after=background(page)
    expect(after['scrollY']>=300,label+': document content actually scrolls')
    expect(abs((after['headingTop']-first['headingTop'])+after['scrollY'])<=2,label+': heading moves with the document')
    for key in ['position','top','left','width','height','image','size','placement']:
        expect(first[key]==after[key],label+': background '+key+' is unchanged during scroll')
    expect(abs(after['headerTop'])<=1,label+': header remains sticky above content')
    scrolled=capture(page,output,manifest,label+'-home-scrolled','') if take_screens else page.screenshot(animations='disabled')
    # Compare actual outer-gutter pixels at the same screen coordinates. This
    # catches a scrolling or hidden stage even if a CSS property looks correct.
    before_image=Image.open(io.BytesIO(top)).convert('RGB')
    after_image=Image.open(io.BytesIO(scrolled)).convert('RGB')
    width,height=before_image.size
    points=[(x,y) for x in [2,3,width-4,width-3] for y in range(180,min(height-110,650),20)]
    deltas=[max(abs(a-b) for a,b in zip(before_image.getpixel(p),after_image.getpixel(p))) for p in points]
    # Existing scrolling hero shadows can change gutter luminance slightly;
    # allow 6/255 while requiring the scene's screen-space detail to stay put.
    stable=sum(d<=6 for d in deltas)
    expect(stable>=len(points)*.9,label+': visible gutter pixels remain stationary during scrolling')
    unique=len({before_image.getpixel(p) for p in points})
    expect(unique>=10,label+': visible gutters contain image detail, not a flat fill')
    manifest['scroll_checks'].append({'label':label,'scroll_y':after['scrollY'],
        'background_placement':after['placement'],'fixed_properties_unchanged':True,
        'stable_gutter_samples':stable,'gutter_samples':len(points),'distinct_gutter_colors':unique,
        'maximum_gutter_rgb_delta':max(deltas),'gutter_rgb_tolerance':6})


def populated_review(browser, current, output, manifest):
    """Exercise populated copy surfaces only inside the disposable installation."""
    current.login()
    image_id, _, _, _ = seed_media_and_music(current)
    save_content(current,'gallery','Synthetic Review Photograph',{
        'image_id':str(image_id),'caption':'Synthetic gallery caption used to inspect the readable caption container.',
        'credit':'Disposable browser fixture'},slug='synthetic-review-photo')
    save_content(current,'video','Synthetic Review Video',{
        'image_id':str(image_id),'url':'https://www.youtube.com/watch?v=00000000000',
        'description':'Synthetic video description; this placeholder is never played or published outside the disposable test.'},slug='synthetic-review-video')
    # A complete blank one-page PDF is sufficient for the real upload/publish
    # path; its contents are not a band document or a visual deliverable.
    document=bytearray(b'%PDF-1.4\n'); offsets=[0]
    objects=[b'<< /Type /Catalog /Pages 2 0 R >>',
        b'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        b'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 72 72] /Resources <<>> >>']
    for number,body in enumerate(objects,1):
        offsets.append(len(document));document.extend(f'{number} 0 obj\n'.encode()+body+b'\nendobj\n')
    xref=len(document)
    document.extend(b'xref\n0 4\n0000000000 65535 f \n')
    for offset in offsets[1:]:document.extend(f'{offset:010} 00000 n \n'.encode())
    document.extend(f'trailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n{xref}\n%%EOF\n'.encode())
    pdf_id,status,_=upload(current,'synthetic-review.pdf','application/pdf',bytes(document))
    expect(status==200 and pdf_id>0,'Synthetic press fixture uploaded through the real protected media form')
    save_content(current,'press','Synthetic Review Download',{
        'file_id':str(pdf_id),'description':'Synthetic download description used to inspect readable press cards.'},slug='synthetic-review-download')
    save_content(current,'event','Synthetic Review Show',{
        'starts_at':'2099-11-08T20:00','venue':'Synthetic Test Venue','city':'Fixture City',
        'timezone':'America/Chicago','ticket_url':'https://tickets.example.test/review',
        'description':'Synthetic event description used only to verify the readable content panel.\n\nA second paragraph exercises realistic multiline copy.'},slug='synthetic-review-show')
    save_content(current,'member','Synthetic Review Member',{
        'role':'Fixture musician','bio':'Synthetic member biography used only in this disposable visual check.\n\nThis text verifies readable paragraphs over the global stage artwork.'},slug='synthetic-review-member')
    save_content(current,'page','Synthetic Review Page',{
        'body':'Synthetic custom-page content used only for browser verification.\n\nA separate paragraph checks that normal published copy remains in a readable container.'},slug='synthetic-review-page')
    seed_catalog(current)
    routes=[('music/','music'),('music/release.php?slug=fixture-release','release-detail'),
        ('shows/','shows'),('shows/event.php?slug=synthetic-review-show','show-detail'),
        ('band/member.php?slug=synthetic-review-member','member-detail'),
        ('pages/?slug=synthetic-review-page','custom-page'),
        ('media/','media'),('press/','press'),
        ('shop/product.php?slug=fixture-shirt','product-detail'),
        ('pages/search.php?q=Fixture&kind=all','search-results')]
    for width,height,label in [(1440,1000,'desktop'),(390,844,'phone')]:
        context=browser.new_context(viewport={'width':width,'height':height},reduced_motion='reduce',device_scale_factor=1)
        page=context.new_page();errors=[];page.on('pageerror',lambda error:errors.append(str(error)))
        for route,name in routes:
            check_page(page,current,route,'Synthetic '+label+' '+name,manifest)
            if route in ['music/','shows/']:
                page.evaluate('scrollTo({top:480,behavior:"instant"})')
            if route=='media/':page.locator('.video-grid').scroll_into_view_if_needed()
            if route=='press/':page.locator('.press-layout').scroll_into_view_if_needed()
            capture(page,output,manifest,'synthetic-'+label+'-'+name+'-Synthetic',route,
                note='Synthetic content in a disposable test installation; not approved band content or a real performance/product.',synthetic=True)
            if route=='media/':
                page.locator('.gallery-grid').scroll_into_view_if_needed()
                capture(page,output,manifest,'synthetic-'+label+'-gallery-Synthetic',route,
                    note='Synthetic gallery caption and credit in a disposable test installation.',synthetic=True)
            if route.startswith('shop/product.php'):
                page.get_by_role('button',name='Add to bag',exact=True).click()
                page.wait_for_url('**/shop/cart.php')
                check_page(page,current,'shop/cart.php','Synthetic '+label+' populated cart',manifest)
                expect(page.locator('.cart-line').count()==1,'Synthetic '+label+': actual cart contains the fixture item')
                page.locator('.cart-layout').scroll_into_view_if_needed()
                capture(page,output,manifest,'synthetic-'+label+'-cart-Synthetic','shop/cart.php',
                    note='Synthetic item added using the real form in a disposable test; checkout remains closed and no order/payment is created.',synthetic=True)
        expect(errors==[],'Synthetic '+label+': no uncaught browser errors')
        context.close()


def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive',required=True,type=Path)
    parser.add_argument('--output',required=True,type=Path)
    parser.add_argument('--header-baseline',type=Path,
                        help='Optional prior header geometry to verify exact logo/content preservation.')
    args=parser.parse_args()
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK')!='1':
        raise RuntimeError('Use the non-root disposable test environment and payment network kill switch.')
    output=args.output.resolve(); output.mkdir(parents=True,exist_ok=True)
    archive=args.archive.resolve()
    with zipfile.ZipFile(archive) as bundle:
        version=bundle.read('VERSION').decode().strip()
        runtime={n:digest(bundle.read(n)) for n in sorted(bundle.namelist())
                 if not n.endswith('/') and n not in ['README.md','CHANGELOG.md','manifest.json'] and not n.startswith('docs/')}
        expect(ART in runtime,'Supplied candidate ZIP contains the selected global background')
    manifest={'version':version,'captured_at_utc':datetime.now(timezone.utc).isoformat(),
        'package_name':archive.name,'package_sha256':digest(archive.read_bytes()),
        'provenance':'Actual installed candidate ZIP. Runtime/code/asset hashes are supplied for comparison with the final delivery ZIP; documentation may subsequently change.',
        'primary_public_review_unseeded':True,'additional_readability_review_uses_synthetic_fixtures':True,
        'production_site_accessed':False,'runtime_code_modified':False,
        'browser':'Chromium','device_scale_factor':1,'screenshots':[],'scroll_checks':[],'header_checks':[],'panel_checks':[],
        'runtime_payload_sha256':runtime,'page_errors':[],'favicon_checks':[]}
    baseline=json.loads(args.header_baseline.read_text()) if args.header_baseline else None
    if baseline:manifest['header_baseline']=baseline
    with tempfile.TemporaryDirectory(prefix='spirit-stage-background-') as area, sync_playwright() as engine:
        browser=engine.chromium.launch()
        manifest['browser_version']=browser.version
        try:
            for subfolder,label in [('', 'root'),('band','subfolder')]:
                with site(Path(area)/label,archive,None,subfolder) as current:
                    current.claim()
                    status,body,_,_=current.client.form('install/',current.input())
                    expect(status==200 and 'id="install-complete"' in body,label+': candidate installed normally')
                    for width,height,size in [(1440,1000,'desktop'),(1920,1080,'wide'),(390,844,'phone')]:
                        context=browser.new_context(viewport={'width':width,'height':height},device_scale_factor=1,
                            reduced_motion='reduce',bypass_csp=False)
                        page=context.new_page();errors=[]
                        page.on('pageerror',lambda error:errors.append(str(error)))
                        prefix=label+'-'+size
                        scroll_check(page,current,output,manifest,prefix,label=='root')
                        if label=='root': print('EARLY_SCREENSHOTS '+prefix,flush=True)
                        page.evaluate('scrollTo({top:0,behavior:"instant"})')
                        if baseline:header_check(page,baseline,manifest,prefix)
                        for route,name in ([] if width==1920 else ROUTES):
                            check_page(page,current,route,prefix+' '+name,manifest)
                            if label=='root' and route in CAPTURE_ROUTES and route:
                                capture(page,output,manifest,prefix+'-'+name,route)
                        page.goto(current.base,wait_until='networkidle')
                        if size=='desktop':
                            icon=page.locator('link[rel="icon"][sizes="32x32"]').evaluate('(n)=>n.href')
                            response=page.request.get(icon)
                            expect(response.status==200 and digest(response.body())==runtime['images/icons/favicon-32.png'],
                                prefix+': preserved gorilla favicon resolves to the exact packaged PNG')
                            manifest['favicon_checks'].append({'label':prefix,'status':200,'matches_package':True})
                        search=page.get_by_role('button',name='Open site search',exact=True)
                        search.click();browser_expect(page.locator('#header-search-panel')).to_be_visible()
                        browser_expect(page.locator('#header-search-query')).to_be_focused()
                        expect(True,prefix+': search opens and accepts focus above background')
                        page.keyboard.press('Escape');browser_expect(page.locator('#header-search-panel')).to_be_hidden()
                        if width<821:
                            page.locator('.nav-toggle').click();browser_expect(page.locator('#public-nav')).to_be_visible()
                            if label=='root':capture(page,output,manifest,prefix+'-menu','',note='Actual open mobile navigation.')
                            page.locator('#public-nav').get_by_role('link',name='Music',exact=True).click()
                            page.wait_for_url('**/music/');browser_expect(page.locator('.nav-toggle')).to_have_attribute('aria-expanded','false')
                            expect(ART in background(page)['image'],prefix+': background persists after real menu navigation')
                        page.goto(current.base+'admin/login.php',wait_until='networkidle')
                        expect(ART not in background(page)['image'] and page.locator('body.public-site').count()==0,prefix+': staff access remains outside global public artwork')
                        expect(errors==[],prefix+': no uncaught browser errors')
                        context.close()
                    context=browser.new_context(viewport={'width':390,'height':844},java_script_enabled=False,reduced_motion='reduce')
                    page=context.new_page();check_page(page,current,'music/',label+' no-JS',manifest)
                    expect(page.get_by_role('link',name='Search',exact=True).first.is_visible(),label+': native search remains available without JS')
                    if label=='root':capture(page,output,manifest,'root-phone-no-js-music','music/',note='JavaScript disabled; fixed CSS background remains.')
                    context.close()
                    context=browser.new_context(viewport={'width':1440,'height':1000},reduced_motion='reduce')
                    page=context.new_page();page.goto(current.base,wait_until='networkidle')
                    page.emulate_media(forced_colors='active')
                    expect(background(page)['display']=='none',label+': forced-colors omits decorative art')
                    page.emulate_media(forced_colors='none',media='print')
                    expect(background(page)['display']=='none',label+': print omits decorative art')
                    context.close()
                    if label=='root':populated_review(browser,current,output,manifest)
        finally:
            browser.close()
    manifest['assertions_passed']=http_integration.CHECKS
    (output/'stage-background-verification.json').write_text(json.dumps(manifest,indent=2)+'\n')
    print(f'{http_integration.CHECKS} focused checks passed; {len(manifest["screenshots"])} actual screenshots.',flush=True)


if __name__=='__main__':
    main()
