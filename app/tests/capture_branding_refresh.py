#!/usr/bin/env python3
"""Capture approved branding on real disposable root/subdirectory installations.

Primary public captures use the unseeded application. A separate, labeled
synthetic phase exercises the existing owner upload/preview/publish/withdraw
workflow and persistent audio during client navigation. No production site,
provider, customer information or real traffic is accessed.
"""
from __future__ import annotations

import argparse
import hashlib
import io
import json
import os
import tempfile
import time
import urllib.parse
import zipfile
from datetime import datetime, timezone
from pathlib import Path

from PIL import Image
from playwright.sync_api import sync_playwright, expect as be

import http_integration
from http_integration import Client, dashboard, expect, site
from public_browser import decoded_images, wait_for_audio
from capture_stage_background import ART, background, readable_panels
from gallery_browser import upload_image, open_nav
from content_integration import seed_media_and_music
from branding_integration import mutate, logo_present
from branding_browser import loaded_logo, fits


STATIC = 'images/branding/spiritual-gorillaz-sculpted-gold.png'
ANIMATED = 'images/branding/spiritual-gorillaz-sculpted-gold-nav.webp'
ICONS = 'images/icons/crowned-gorilla-full-fit/'
WIDTHS = [1920, 1440, 1280, 1100, 1000, 880, 821, 820, 390, 360, 320]
ANCHORS = {1440: 44.1875, 1920: 253, 390: 16}


def digest(data):
    return hashlib.sha256(data).hexdigest()


def checkpoint(output, manifest):
    manifest['assertions_passed'] = http_integration.CHECKS
    target = output / 'branding-refresh-verification.json'
    temporary = target.with_suffix('.tmp')
    temporary.write_text(json.dumps(manifest, indent=2) + '\n')
    temporary.replace(target)
    # The external runner can mirror this checkpoint immediately out of /tmp.
    print('EVIDENCE_CHECKPOINT', flush=True)


def capture(page, output, manifest, name, route, note='', synthetic=False, account=False):
    page.evaluate('document.fonts.ready')
    sidebar_logo = name.endswith('-admin-overview-sidebar')
    if sidebar_logo:
        # A just-opened drawer can have complete network bytes before its async
        # image has reached the compositor. Wait for decode and actual paints.
        page.locator('#sidebar .bundled-site-logo').evaluate('n=>n.decode()')
        page.evaluate('()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))')
    filename = name + '.png'
    data = page.screenshot(path=str(output / filename), animations='disabled', full_page=False)
    painted = None
    if sidebar_logo:
        rectangle = page.locator('#sidebar .bundled-site-logo').bounding_box()
        rendered = Image.open(io.BytesIO(data)).convert('RGB')
        region = rendered.crop((int(rectangle['x']), int(rectangle['y']),
                                int(rectangle['x'] + rectangle['width']), int(rectangle['y'] + rectangle['height'])))
        painted = sum(r > 100 and g > 65 and r > b * 1.3 for r, g, b in region.get_flattened_data())
        expect(painted > 100, name + ': approved gold logo is visibly painted in the actual screenshot')
    manifest['screenshots'].append({'file': filename, 'sha256': digest(data),
        'route': route or '/', 'viewport': page.viewport_size,
        'scroll_y': round(page.evaluate('scrollY'), 2), 'full_page': False,
        'synthetic_content': synthetic, 'disposable_test_account': account,
        'painted_gold_logo_pixels': painted, 'note': note})
    checkpoint(output, manifest)


def mark(page, selector, animated, label, manifest):
    image = page.locator(selector + ' .bundled-site-logo')
    be(image).to_have_count(1)
    target = ANIMATED if animated else STATIC
    deadline = time.monotonic() + 10
    while True:
        state = image.evaluate('''n => {
          const r=n.getBoundingClientRect(),p=n.closest('.bundled-brand-wrap').getBoundingClientRect();
          return {src:n.currentSrc,complete:n.complete,naturalWidth:n.naturalWidth,naturalHeight:n.naturalHeight,
            x:r.x,y:r.y,width:r.width,height:r.height,fit:getComputedStyle(n).objectFit,
            contained:r.left>=p.left-1&&r.right<=p.right+1&&r.top>=p.top-1&&r.bottom<=p.bottom+1,
            pictureSource:n.closest('picture')?.querySelector('source')?.media??null};
        }''')
        if (state['complete'] and state['naturalWidth'] > 0
                and urllib.parse.urlsplit(state['src']).path.endswith('/' + target)):
            break
        if time.monotonic() > deadline:
            raise AssertionError(label + ': expected bundled source failed to decode: ' + state['src'])
        time.sleep(.05)
    url = urllib.parse.urlsplit(state['src'])
    expect(url.path.endswith('/' + target), label + ': browser selects the approved ' + ('animated' if animated else 'static') + ' artwork')
    expect(urllib.parse.parse_qs(url.query).get('v') == [manifest['version']], label + ': actual image URL carries this release version')
    dimensions = (384, 132) if animated else (2172, 724)
    expect((state['naturalWidth'], state['naturalHeight']) == dimensions, label + ': exact approved image dimensions decode')
    expect(state['fit'] == 'contain' and state['contained'] and state['width'] > 0 and state['height'] > 0,
           label + ': complete image stays inside its logo container')
    expect(image.get_attribute('alt') == '', label + ': decorative image defers to the labeled home link')
    manifest['logo_checks'].append({'label': label, 'selector': selector, 'animated': animated,
        'state': {**state, 'src': url.path + '?' + url.query}})
    return state


def public_geometry(page, label, manifest):
    value = page.evaluate(r'''() => {
      const rect=n=>{const r=n.getBoundingClientRect();return {left:r.left,right:r.right,top:r.top,bottom:r.bottom,width:r.width,height:r.height}};
      const plate=selector=>{const n=document.querySelector(selector),r=n.getBoundingClientRect(),p=getComputedStyle(n,'::before'),s=getComputedStyle(n);
        const left=r.left+parseFloat(s.borderLeftWidth)+parseFloat(p.left);
        const background=p.backgroundImage==='none'?p.backgroundColor:p.backgroundImage;
        const alphas=[...background.matchAll(/rgba?\(([^)]+)\)/g)].map(m=>{const c=m[1].split(',').map(Number);return c[3]??1});
        return {left,right:left+parseFloat(p.width),width:parseFloat(p.width),height:parseFloat(p.height),pointerEvents:p.pointerEvents,position:p.position,background,alphas,elementOpacity:s.opacity}};
      const shell=getComputedStyle(document.querySelector('.site-shell'));
      const selectors={logo:'.site-header .wordmark',navigation:'#public-nav',search:'.nav-search',book:'.nav-book',toggle:'.nav-toggle'};
      const boxes=Object.fromEntries(Object.entries(selectors).map(([k,s])=>[k,rect(document.querySelector(s))]));
      const controls=[...document.querySelectorAll('.site-header .bundled-site-logo,.site-header a,.site-header button')].map(n=>({tag:n.tagName,opacity:getComputedStyle(n).opacity}));
      return {boxes,controls,header:plate('.site-header'),footer:plate('.site-footer'),overflow:document.documentElement.scrollWidth>innerWidth+1,
        shell:{background:shell.backgroundColor,borders:[shell.borderTopWidth,shell.borderRightWidth,shell.borderBottomWidth,shell.borderLeftWidth],shadow:shell.boxShadow,outline:shell.outlineWidth,radius:shell.borderRadius}};
    }''')
    width = page.viewport_size['width']
    manifest['geometry_checks'].append({'label': label, 'viewport': page.viewport_size, **value})
    expect(not value['overflow'], label + ': no horizontal document overflow')
    shell = value['shell']
    expect(shell['background'] == 'rgba(0, 0, 0, 0)' and set(shell['borders']) == {'0px'}
           and shell['shadow'] == 'none' and shell['outline'] == '0px' and shell['radius'] == '0px',
           label + ': outer shell has no background, border, shadow, outline or radius')
    for name in ['header', 'footer']:
        plate = value[name]
        expect(plate['left'] <= .5 and plate['right'] >= width - .5 and abs(plate['width'] - width) <= 1,
               label + ': ' + name + ' surface reaches both screen edges')
        expect(plate['pointerEvents'] == 'none', label + ': ' + name + ' surface cannot intercept controls')
    expect(value['header']['alphas'] and all(abs(alpha - .75) <= .001 for alpha in value['header']['alphas']),
           label + ': navigation surface is 25 percent transparent')
    expect(value['header']['elementOpacity'] == '1' and all(item['opacity'] == '1' for item in value['controls']),
           label + ': logo, labels and controls keep full opacity')
    boxes = value['boxes']
    logo = boxes['logo']
    for name in ['navigation', 'search', 'toggle']:
        other = boxes[name]
        if other['width'] and other['height']:
            expect(logo['right'] <= other['left'] + 1 or logo['bottom'] <= other['top'] + 1,
                   label + ': logo does not overlap ' + name)
    if width in ANCHORS:
        expect(abs(logo['left'] - ANCHORS[width]) <= .05, label + ': original logo left anchor is retained')


def favicon(page, current, label, manifest):
    links = page.locator('link[rel="icon"],link[rel="apple-touch-icon"]').evaluate_all(
        "nodes=>nodes.map(n=>({href:n.href,sizes:n.sizes.value,rel:n.rel,type:n.type}))")
    expect(len(links) == 8, label + ': shared icon set exposes all native sizes and touch icon')
    observations = []
    for link in links:
        url = urllib.parse.urlsplit(link['href'])
        relative = url.path[len(urllib.parse.urlsplit(current.base).path):]
        expect(relative.startswith(ICONS) and urllib.parse.parse_qs(url.query).get('v') == [manifest['version']],
               label + ': icon has the active cache path and correct root/subfolder prefix')
        response = page.request.get(link['href'])
        expect(response.status == 200 and digest(response.body()) == manifest['runtime_payload_sha256'][relative],
               label + ': icon response matches the exact packaged asset')
        decoded = page.evaluate('''async url=>{const i=new Image();i.src=url;await i.decode();return {width:i.naturalWidth,height:i.naturalHeight}}''', link['href'])
        if relative.endswith('.png'):
            size = int(link['sizes'].split('x')[0])
            expect((decoded['width'], decoded['height']) == (size, size), label + ': native PNG icon decodes at its advertised dimensions')
        else:
            expect(decoded['width'] in [16, 32, 48, 64] and decoded['width'] == decoded['height'], label + ': multi-size ICO decodes natively')
        observations.append({**link, 'href': url.path + '?' + url.query, 'decoded': decoded})
    manifest['favicon_checks'].append({'label': label, 'links': observations,
        'root_favicon_file_omitted_for_pinned_update_compatibility': True})


def navigate_and_search(page, current, label, output=None, manifest=None):
    page.get_by_role('button', name='Open site search', exact=True).click()
    be(page.locator('#header-search-panel')).to_be_visible()
    be(page.locator('#header-search-query')).to_be_focused()
    if output is not None:
        capture(page, output, manifest, label + '-search-open', '', note='Search remains readable over the 25 percent transparent navigation surface.')
    page.keyboard.press('Escape')
    be(page.locator('#header-search-panel')).to_be_hidden()
    expect(True, label + ': search opens, receives focus and closes with Escape')
    audio = page.locator('#site-audio').element_handle()
    if output is not None and page.locator('.nav-toggle').is_visible():
        page.locator('.nav-toggle').click()
        be(page.locator('#public-nav')).to_be_visible()
        capture(page, output, manifest, label + '-menu-open', '', note='Actual expanded phone menu with readable menu surface.')
    open_nav(page, 'Music')
    page.wait_for_url('**/music/')
    expect(page.locator('#site-audio').evaluate('(n,old)=>n===old', audio), label + ': client navigation preserves the audio node')
    if page.locator('.nav-toggle').is_visible():
        be(page.locator('.nav-toggle')).to_have_attribute('aria-expanded', 'false')
    expect(ART in background(page)['image'], label + ': fixed stage survives real navigation')


def administrative_review(browser, current, label, output, manifest, phase):
    route = 'install/' if phase == 'installer' else 'admin/login.php'
    for width, height in [(1440, 1000), (390, 844), (320, 780)]:
        context = browser.new_context(viewport={'width': width, 'height': height}, reduced_motion='reduce', bypass_csp=False)
        page = context.new_page()
        page.on('pageerror', lambda error: manifest['page_errors'].append(str(error)))
        prefix = label + '-' + str(width)
        response = page.goto(current.base + route, wait_until='networkidle')
        expect(response.status == 200, prefix + ': ' + phase + ' loads')
        expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), prefix + ': ' + phase + ' has no horizontal overflow')
        mark(page, '.simple-header .backstage-brand', False, prefix + ' ' + phase, manifest)
        expect(ART not in background(page)['image'], prefix + ': ' + phase + ' remains outside public stage decoration')
        take = (width in [1440, 390] and label == 'root') or manifest['scope'] == 'admin-paint-recheck'
        if take:
            capture(page, output, manifest, prefix + '-' + phase, route,
                    note='Actual disposable ' + phase + ' page before any band-content fixture.')
        if phase != 'installer':
            page.locator('[name=email]').fill(current.owner)
            page.locator('[name=password]').fill(current.password)
            page.get_by_role('button', name='Enter backstage', exact=True).click()
            page.wait_for_url(lambda url: dashboard(str(url)))
            expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), prefix + ': owner Overview has no horizontal overflow')
            if width < 801:
                page.locator('.admin-menu-toggle').click()
            be(page.locator('#sidebar')).to_be_visible()
            mark(page, '#sidebar .backstage-brand', False, prefix + ' admin sidebar', manifest)
            if take:
                capture(page, output, manifest, prefix + '-admin-overview-sidebar', 'admin/',
                        note='Disposable test owner account; no seeded band content.', account=True)
            page.goto(current.base + 'admin/branding/', wait_until='networkidle')
            be(page.locator('.branding-swatch .bundled-site-logo')).to_have_count(2)
            for index in range(2):
                decoded = page.locator('.branding-swatch .bundled-site-logo').nth(index).evaluate('async n=>{await n.decode();return n.currentSrc}')
                expect(STATIC in decoded, prefix + ': default Branding preview uses the approved static logo')
            fits(page, prefix + ' Branding editor')
            if take:
                page.locator('.branding-comparison').scroll_into_view_if_needed()
                capture(page, output, manifest, prefix + '-branding-defaults', 'admin/branding/',
                        note='Default bundled identity, using a disposable owner account.', account=True)
        context.close()
    checkpoint(output, manifest)


def public_review(browser, current, label, output, manifest):
    for width in WIDTHS:
        height = 1080 if width == 1920 else (1000 if width >= 820 else 844)
        context = browser.new_context(viewport={'width': width, 'height': height}, reduced_motion='reduce', device_scale_factor=1, bypass_csp=False)
        page = context.new_page()
        page.on('pageerror', lambda error: manifest['page_errors'].append(str(error)))
        prefix = label + '-' + str(width)
        response = page.goto(current.base, wait_until='networkidle')
        expect(response.status == 200, prefix + ': unseeded public Home renders')
        expect("script-src 'self'" in response.headers.get('content-security-policy', ''), prefix + ': production CSP remains active')
        decoded_images(page, prefix)
        mark(page, '.site-header .wordmark', False, prefix + ' header', manifest)
        mark(page, '.site-footer .wordmark', False, prefix + ' footer', manifest)
        try:
            public_geometry(page, prefix, manifest)
        except Exception:
            capture(page, output, manifest, prefix + '-geometry-failure', '', note='Diagnostic capture from a failed geometry assertion.')
            raise
        readable_panels(page, manifest, prefix)
        initial = background(page)
        expect(initial['position'] == 'fixed' and initial['pointerEvents'] == 'none' and ART in initial['image'], prefix + ': selected stage remains a fixed non-interactive background')
        take = label == 'root' and width in [1920, 1440, 820, 390, 360, 320]
        if take:
            capture(page, output, manifest, prefix + '-home-top', '')
        page.evaluate('scrollTo({top:600,behavior:"instant"})')
        after = background(page)
        expect(after['scrollY'] >= 300 and abs((after['headingTop'] - initial['headingTop']) + after['scrollY']) <= 2, prefix + ': content scrolls independently of the stage')
        expect(all(after[key] == initial[key] for key in ['position', 'top', 'left', 'width', 'height', 'image', 'size', 'placement']), prefix + ': background geometry stays fixed while content scrolls')
        expect(abs(after['headerTop']) <= 1, prefix + ': full-width header stays sticky')
        if take and width in [1920, 390]:
            capture(page, output, manifest, prefix + '-home-scrolled', '', note='Actual scroll position 600px; stage stays stationary.')
        page.locator('.site-footer').scroll_into_view_if_needed()
        if take:
            capture(page, output, manifest, prefix + '-footer', '', note='Actual footer at the bottom of the unseeded Home page.')
        page.evaluate('scrollTo({top:0,behavior:"instant"})')
        if width == 1440:
            favicon(page, current, prefix + ' public', manifest)
        capture_controls = label == 'root' and width in [1440, 390]
        navigate_and_search(page, current, prefix, output if capture_controls else None, manifest)
        mark(page, '.site-header .wordmark', False, prefix + ' after client navigation', manifest)
        expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), prefix + ': interior Music has no horizontal overflow')
        if take and width in [1440, 390]:
            capture(page, output, manifest, prefix + '-music', 'music/')
        context.close()
        checkpoint(output, manifest)
    # Native reduced-motion source selection and no-JavaScript rendering.
    for width in [1440, 390]:
        context = browser.new_context(viewport={'width': width, 'height': 1000 if width == 1440 else 844}, reduced_motion='no-preference')
        page = context.new_page(); page.goto(current.base, wait_until='networkidle')
        mark(page, '.site-header .wordmark', True, label + ' animated header ' + str(width), manifest)
        mark(page, '.site-footer .wordmark', False, label + ' static footer ' + str(width), manifest)
        page.emulate_media(reduced_motion='reduce')
        mark(page, '.site-header .wordmark', False, label + ' changed motion preference ' + str(width), manifest)
        page.goto(current.base + 'admin/login.php', wait_until='networkidle')
        page.emulate_media(reduced_motion='no-preference')
        mark(page, '.simple-header .backstage-brand', True, label + ' animated login ' + str(width), manifest)
        if width == 1440:
            favicon(page, current, label + ' shared login', manifest)
        context.close()
    context = browser.new_context(viewport={'width': 390, 'height': 844}, reduced_motion='reduce', java_script_enabled=False)
    page = context.new_page(); page.goto(current.base + 'music/', wait_until='networkidle')
    mark(page, '.site-header .wordmark', False, label + ' native no-JS header', manifest)
    expect(page.get_by_role('link', name='Search', exact=True).first.is_visible(), label + ': native Search remains available without JavaScript')
    expect(page.evaluate('document.documentElement.scrollWidth<=innerWidth+1'), label + ': no-JS Music has no horizontal overflow')
    if label == 'root': capture(page, output, manifest, 'root-390-no-js-music', 'music/', note='JavaScript disabled; native static logo and navigation remain usable.')
    context.close()
    checkpoint(output, manifest)


def custom_review(browser, current, label, output, manifest):
    current.login()
    seed_media_and_music(current)
    wide = upload_image(current, 900, 300, 11)
    tall = upload_image(current, 200, 650, 12)
    for width, javascript in [(1440, True), (390, True), (390, False)]:
        mutate(current, 'withdraw')
        context = browser.new_context(viewport={'width': width, 'height': 950}, reduced_motion='reduce', java_script_enabled=javascript, bypass_csp=False)
        page = context.new_page(); errors = []
        page.on('pageerror', lambda error: errors.append(str(error)))
        prefix = label + '-' + str(width) + '-js' + str(int(javascript))
        page.goto(current.base + 'admin/login.php')
        page.locator('[name=email]').fill(current.owner); page.locator('[name=password]').fill(current.password)
        page.get_by_role('button', name='Enter backstage', exact=True).click()
        page.wait_for_url(lambda url: dashboard(str(url)))
        page.goto(current.base + 'admin/branding/', wait_until='networkidle')
        page.get_by_label('Logo image', exact=True).select_option(str(wide))
        page.get_by_role('button', name='Save private logo draft', exact=True).click()
        be(page.locator('body')).to_contain_text('Logo draft saved privately')
        with page.expect_popup() as event:
            page.get_by_role('link', name='Preview saved logo').click()
        preview = event.value; preview.wait_for_load_state('networkidle')
        be(preview.locator('.preview-banner')).to_be_visible()
        loaded_logo(preview, wide, (900, 300)); fits(preview, prefix + ' private preview')
        expect(not logo_present(Client(current.base).request('')[1], wide), prefix + ': private draft has not changed anonymous branding')
        if label == 'root' and width == 1440:
            capture(preview, output, manifest, 'synthetic-private-logo-preview', 'admin/branding/preview.php',
                    note='Synthetic image and audio fixtures in a disposable test only; not approved band content.', synthetic=True, account=True)
        preview.close()
        page.locator('[name=approved]').check()
        page.get_by_role('button', name='Publish approved logo', exact=True).click()
        be(page.locator('body')).to_contain_text('Approved logo published')
        public = context.new_page(); public.goto(current.base, wait_until='networkidle')
        loaded_logo(public, wide, (900, 300))
        if label == 'root' and javascript:
            capture(public, output, manifest, 'synthetic-' + str(width) + '-custom-logo', '',
                    note='Synthetic uploaded logo demonstrates preserved custom-branding behavior.', synthetic=True)
        mutate(current, 'save', tall); public.reload(wait_until='networkidle'); loaded_logo(public, wide, (900, 300))
        expect(True, prefix + ': saving a new private draft preserves the published image')
        if javascript:
            public.goto(current.base + 'music/release.php?slug=fixture-release', wait_until='networkidle')
            public.locator('.play-track').first.click(); wait_for_audio(public)
            audio = public.locator('#site-audio').element_handle()
            mutate(current, 'publish', tall)
            open_nav(public, 'Shows'); public.wait_for_url('**/shows/')
            loaded_logo(public, tall, (200, 650))
            expect(public.locator('#site-audio').evaluate('(n,old)=>n===old', audio), prefix + ': published logo refresh preserves the persistent audio node')
            wait_for_audio(public)
            expect(True, prefix + ': actual audio keeps playing through logo refresh and navigation')
        else:
            mutate(current, 'publish', tall); public.reload(wait_until='networkidle'); loaded_logo(public, tall, (200, 650))
            expect(True, prefix + ': native no-JS request renders the published portrait image')
        fits(public, prefix + ' published portrait logo')
        page.reload(wait_until='networkidle')
        page.get_by_role('button', name='Withdraw published logo', exact=True).click()
        be(page.locator('body')).to_contain_text('Published logo withdrawn')
        if javascript:
            open_nav(public, 'Music'); public.wait_for_url('**/music/')
        else:
            public.reload(wait_until='networkidle')
        be(public.locator('.site-logo')).to_have_count(0)
        be(public.locator('.bundled-site-logo')).to_have_count(2)
        mark(public, '.site-header .wordmark', False, prefix + ' restored bundled header', manifest)
        mark(public, '.site-footer .wordmark', False, prefix + ' restored bundled footer', manifest)
        expect(not errors, prefix + ': no uncaught branding editor errors')
        manifest['custom_override_checks'].append({'installation': label, 'width': width, 'javascript': javascript,
            'draft_private': True, 'publish_preserved': True, 'portrait_ratio_preserved': True,
            'withdraw_restores_approved_bundle': True, 'persistent_audio': javascript,
            'synthetic_content_only': True})
        context.close(); checkpoint(output, manifest)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive', required=True, type=Path)
    parser.add_argument('--output', required=True, type=Path)
    parser.add_argument('--admin-only', action='store_true', help='Focused fresh-install admin paint verification and recapture.')
    args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK') != '1':
        raise RuntimeError('Use a non-root disposable SQL fixture and the payment-network kill switch.')
    output = args.output.resolve(); output.mkdir(parents=True, exist_ok=True)
    archive = args.archive.resolve()
    with zipfile.ZipFile(archive) as bundle:
        version = bundle.read('VERSION').decode().strip()
        runtime = {name: digest(bundle.read(name)) for name in sorted(bundle.namelist())
                   if not name.endswith('/') and name not in ['README.md', 'CHANGELOG.md', 'manifest.json'] and not name.startswith('docs/')}
    expect(all(path in runtime for path in [STATIC, ANIMATED, ICONS + 'favicon.ico', ART]), 'Candidate ZIP contains the approved branding, icons and retained stage')
    manifest = {'version': version, 'status': 'running', 'scope': 'admin-paint-recheck' if args.admin_only else 'complete-brand-refresh',
        'captured_at_utc': datetime.now(timezone.utc).isoformat(),
        'package_name': archive.name, 'package_sha256': digest(archive.read_bytes()),
        'runtime_payload_sha256': runtime, 'browser': 'Chromium', 'device_scale_factor': 1,
        'provenance': 'Actual installed candidate ZIP; compare runtime hashes against final delivery if documentation changes.',
        'primary_public_review_unseeded': not args.admin_only,
        'additional_custom_logo_review_uses_synthetic_fixtures': not args.admin_only,
        'production_site_accessed': False, 'runtime_code_modified': False,
        'screenshots': [], 'logo_checks': [], 'geometry_checks': [], 'favicon_checks': [],
        'panel_checks': [], 'custom_override_checks': [], 'page_errors': []}
    checkpoint(output, manifest)
    try:
        with tempfile.TemporaryDirectory(prefix='spirit-brand-refresh-') as directory, sync_playwright() as engine:
            executable = os.environ.get('SPIRIT_CHROMIUM_EXECUTABLE')
            browser = engine.chromium.launch(**({'executable_path': executable} if executable else {}))
            manifest['browser_version'] = browser.version
            try:
                for subfolder, label in [('', 'root'), ('band', 'subfolder')]:
                    with site(Path(directory) / label, archive, None, subfolder) as current:
                        current.claim()
                        administrative_review(browser, current, label, output, manifest, 'installer')
                        status, body, _, _ = current.client.form('install/', current.input())
                        expect(status == 200 and 'id="install-complete"' in body, label + ': candidate completes the normal single-page installation')
                        expect(current.database('count') == {'users': 1, 'state': 'installed'}, label + ': one owner and installed database state')
                        administrative_review(browser, current, label, output, manifest, 'login')
                        if not args.admin_only:
                            public_review(browser, current, label, output, manifest)
                            custom_review(browser, current, label, output, manifest)
            finally:
                browser.close()
        expect(manifest['page_errors'] == [], 'No uncaught browser errors in primary installed-page review')
        manifest['status'] = 'passed'
    except Exception as error:
        manifest['status'] = 'failed'
        manifest['failure'] = str(error)
        raise
    finally:
        checkpoint(output, manifest)
    print(str(http_integration.CHECKS) + ' focused checks passed; ' + str(len(manifest['screenshots'])) + ' actual screenshots.', flush=True)


if __name__ == '__main__':
    main()
