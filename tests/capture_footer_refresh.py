#!/usr/bin/env python3
"""Focused footer, copyright and naturally animated identity ZIP browser review.

Run only against a frozen candidate in the existing disposable nonroot SQL
environment. Primary captures contain no seeded band content. Optional-link,
maximum-length copyright and custom-logo exercises are labeled synthetic.
Videos begin with ordinary page entry: no seeking, acceleration or CSS edits.
"""
from __future__ import annotations

import argparse
import io
import json
import math
import os
import shutil
import subprocess
import tempfile
import time
import urllib.parse
import zipfile
from datetime import datetime, timezone
from pathlib import Path

from PIL import Image, ImageChops
from playwright.sync_api import sync_playwright, expect as be

import http_integration
from http_integration import Client, dashboard, expect, site
from capture_branding_refresh import digest, public_geometry, favicon
from capture_background_smoke import smoke, paint
from capture_stage_background import ART, background
from content_integration import save_content
from gallery_browser import upload_image, open_nav
from branding_integration import mutate, logo_present, branding_fixture
from branding_browser import loaded_logo
from launch_integration import fixture


WIDTHS = [1920, 1440, 1280, 1100, 820, 390, 360, 320]
SETTINGS = 'admin/settings.php'
CREDIT = 'https://cajunwebpro.com/'
LIMIT = 240


def checkpoint(output, manifest):
    manifest['assertions_passed'] = http_integration.CHECKS
    target = output / 'footer-refresh-verification.json'
    temporary = target.with_suffix('.tmp')
    temporary.write_text(json.dumps(manifest, indent=2) + '\n')
    temporary.replace(target)
    print('EVIDENCE_CHECKPOINT', flush=True)


def capture(page, output, manifest, name, note, synthetic=False, account=False):
    page.evaluate('document.fonts.ready')
    if page.viewport_size['width'] <= 800 and page.locator('.admin-nav-enhanced #sidebar:not(.is-open)').count():
        # A desktop-to-phone resize starts the native drawer closing transition.
        # Two paint frames alone can still capture its edge; wait for actual
        # off-canvas geometry and hidden visibility without changing page CSS.
        page.wait_for_function('''()=>{const n=document.querySelector('#sidebar');
            return getComputedStyle(n).visibility==='hidden'&&n.getBoundingClientRect().right<=0}''')
    for image in page.locator('.bundled-site-logo').all():
        if image.is_visible(): image.evaluate('n=>n.decode()')
    paint(page)
    filename = name + '.png'
    data = page.screenshot(path=str(output / filename), animations='allow', full_page=False)
    painted = None
    if name.endswith('-admin-sidebar'):
        box = page.locator('#sidebar .bundled-site-logo').bounding_box()
        pixels = Image.open(io.BytesIO(data)).convert('RGB').crop((int(box['x']), int(box['y']),
            int(box['x'] + box['width']), int(box['y'] + box['height'])))
        painted = sum(r > 100 and g > 65 and r > b * 1.3 for r, g, b in pixels.get_flattened_data())
        expect(painted > 100, name + ': approved logo is visibly painted in the drawer screenshot')
    manifest['screenshots'].append({'file': filename, 'sha256': digest(data),
        'route': urllib.parse.urlsplit(page.url).path, 'viewport': page.viewport_size,
        'scroll_y': page.evaluate('scrollY'), 'full_page': False,
        'synthetic_content': synthetic, 'disposable_test_account': account,
        'painted_gold_logo_pixels': painted, 'note': note})
    checkpoint(output, manifest)
    return data


def logo(page, current, selector, animated, label, manifest):
    image = page.locator(selector + ' .bundled-site-logo')
    be(image).to_have_count(1)
    deadline = time.monotonic() + 10
    while True:
        value = image.evaluate('''n=>{const r=n.getBoundingClientRect(),root=n.closest('.wordmark,.backstage-brand')||n;return {src:n.currentSrc,
          complete:n.complete,width:n.naturalWidth,height:n.naturalHeight,displayWidth:r.width,
          displayHeight:r.height,fit:getComputedStyle(n).objectFit,
          cssAnimations:root.getAnimations({subtree:true}).filter(a=>a.animationName).map(a=>({name:a.animationName,
            playState:a.playState,playbackRate:a.playbackRate,currentTime:a.currentTime}))}}''')
        url = urllib.parse.urlsplit(value['src'])
        relative = url.path[len(urllib.parse.urlsplit(current.base).path):]
        metadata = manifest['brand_assets'].get(relative)
        moving = bool(metadata and (metadata['frames'] > 1 or value['cssAnimations']))
        if value['complete'] and metadata and moving == animated: break
        if time.monotonic() > deadline: raise AssertionError(label + ': expected approved logo source did not decode')
        time.sleep(.05)
    expect('sculpted' in relative and 'Concept' not in relative, label + ': browser selects the current approved sculpted identity')
    expect((value['width'], value['height']) == (metadata['width'], metadata['height']), label + ': native logo dimensions match the packaged approved asset')
    expect(not url.query or urllib.parse.parse_qs(url.query).get('v') == [manifest['version']],
           label + ': logo uses its immutable approved path or current release version')
    response = page.request.get(value['src'])
    expect(response.status == 200 and digest(response.body()) == manifest['runtime_payload_sha256'][relative], label + ': actual image bytes match the frozen candidate')
    expect(value['fit'] == 'contain' and value['displayWidth'] > 0 and value['displayHeight'] > 0,
           label + ': image is visible and contained')
    manifest['logo_checks'].append({'label': label, 'selector': selector, 'animated': animated,
        'state': {**value, 'src': url.path + '?' + url.query}, 'packaged_asset': relative})


def footer(page, label, manifest, expected=None, all_links=False):
    page.locator('.site-footer').scroll_into_view_if_needed()
    paint(page)
    value = page.locator('.site-footer').evaluate('''n=>{
      const s=getComputedStyle(n),r=n.getBoundingClientRect(),p=getComputedStyle(n,'::before');
      const children=[...n.children].map(c=>{const q=c.getBoundingClientRect();return {left:q.left,right:q.right,top:q.top,bottom:q.bottom,width:q.width,height:q.height}});
      const credit=n.querySelector('.footer-credit'),copyright=n.querySelector('.footer-copyright');
      const cr=credit.getBoundingClientRect(),co=copyright.getBoundingClientRect();
      const links=[...n.querySelectorAll('nav a')].map(a=>({text:a.textContent.trim(),href:a.href,opacity:getComputedStyle(a).opacity}));
      const left=r.left+parseFloat(s.borderLeftWidth)+parseFloat(p.left);
      const walker=document.createTreeWalker(n,NodeFilter.SHOW_TEXT);let t,overflow=[];
      while(t=walker.nextNode()){if(!t.textContent.trim())continue;const range=document.createRange();range.selectNodeContents(t);
        for(const b of range.getClientRects())if(b.width&&(b.left<-1||b.right>innerWidth+1))overflow.push(t.textContent.trim().slice(0,70));}
      return {width:r.width,height:r.height,minHeight:s.minHeight,maxHeight:s.maxHeight,
        paddingTop:parseFloat(s.paddingTop),paddingBottom:parseFloat(s.paddingBottom),borderTop:parseFloat(s.borderTopWidth),
        children,background:p.backgroundColor,surfaceLeft:left,surfaceRight:left+parseFloat(p.width),
        pointerEvents:p.pointerEvents,opacity:s.opacity,links,overflow,
        documentOverflow:document.documentElement.scrollWidth>innerWidth+1,
        copyright:copyright.textContent.trim(),credit:credit.textContent.trim(),creditHref:credit.querySelector('a')?.href,
        creditBelowCopyright:cr.top>=co.bottom-1,removedTagline:!n.querySelector('.footer-brand p')&&!n.textContent.includes('Music. People. Purpose.')};
    }''')
    width = page.viewport_size['width']
    expect(not value['documentOverflow'] and not value['overflow'], label + ': footer text and links fit without horizontal overflow')
    expect(value['minHeight'] == '0px' and value['maxHeight'] == 'none', label + ': footer has no forced minimum or maximum height')
    extent = max(row['bottom'] for row in value['children']) - min(row['top'] for row in value['children'])
    expect(value['height'] - extent <= value['paddingTop'] + value['paddingBottom'] + value['borderTop'] + 8,
           label + ': footer height follows its actual content without an empty height floor')
    expect(value['surfaceLeft'] <= .5 and value['surfaceRight'] >= width - .5, label + ': footer background reaches both viewport edges')
    expect(value['background'].startswith('rgba(') and abs(float(value['background'].rstrip(')').split(',')[-1]) - .75) < .001,
           label + ': footer background is exactly 25 percent transparent')
    expect(value['pointerEvents'] == 'none' and value['opacity'] == '1'
           and all(row['opacity'] == '1' for row in value['links']), label + ': decorative surface leaves links fully opaque and interactive')
    expect(value['removedTagline'], label + ': duplicate footer tagline and Music.People.Purpose line are removed')
    expect(value['creditHref'] == CREDIT and 'Cajun Web Pro LLC' in value['credit']
           and value['creditBelowCopyright'], label + ': fixed linked designer credit sits below copyright')
    if expected is not None: expect(value['copyright'] == expected, label + ': public copyright matches the saved plain text exactly')
    if all_links:
        labels = {row['text'] for row in value['links']}
        expect({'Search', 'Listen', 'Shows', 'Press kit', 'Your bag', 'Customer account', 'Booking',
                'Privacy', 'Terms', 'Shipping', 'Returns'} <= labels, label + ': all conditional footer links are present and wrap naturally')
    manifest['footer_checks'].append({'label': label, 'viewport': page.viewport_size, **value})
    return value


def motion(page, label, manifest):
    first = smoke(page); stage = background(page)
    expect(first['animation'] is not None and first['animation']['playState'] == 'running'
           and first['animation']['playbackRate'] == 1, label + ': smoke runs naturally at its packaged timing')
    expect(first['position'] == 'fixed' and first['pointerEvents'] == 'none'
           and stage['position'] == 'fixed' and ART in stage['image'], label + ': smoke and retained stage remain noninteractive fixed decoration')
    page.wait_for_timeout(1000)
    second = smoke(page)
    expect(second['animation']['currentTime'] > first['animation']['currentTime'] + 750
           and (second['transform'], second['opacity']) != (first['transform'], first['opacity']),
           label + ': actual smoke motion advances during ordinary elapsed time')
    manifest['smoke_checks'].append({'label': label, 'before': first, 'after': second})


def private_review(browser, current, label, route, output, manifest):
    for width in [1440, 390]:
        context = browser.new_context(viewport={'width': width, 'height': 1000 if width == 1440 else 844}, reduced_motion='no-preference')
        page = context.new_page(); page.goto(current.base + route, wait_until='networkidle')
        prefix = label + '-' + str(width)
        logo(page, current, '.simple-header .backstage-brand', True, prefix + ' ' + route, manifest)
        expect(smoke(page)['animation'] is None and ART not in background(page)['image'], prefix + ': staff/setup stays outside public smoke and stage')
        if label == 'root': capture(page, output, manifest, prefix + ('-installer' if route == 'install/' else '-login'), 'Actual unseeded setup/sign-in surface, ordinary motion preference.')
        if route == 'admin/login.php':
            page.locator('[name=email]').fill(current.owner); page.locator('[name=password]').fill(current.password)
            page.get_by_role('button', name='Enter backstage', exact=True).click(); page.wait_for_url(lambda url: dashboard(str(url)))
            if width == 390: page.locator('.admin-menu-toggle').click()
            be(page.locator('#sidebar')).to_be_visible()
            logo(page, current, '#sidebar .backstage-brand', True, prefix + ' admin sidebar', manifest)
            if label == 'root': capture(page, output, manifest, prefix + '-admin-sidebar', 'Disposable test owner; no seeded band content.', account=True)
        context.close()


def public_review(browser, current, label, output, manifest):
    for width in WIDTHS:
        context = browser.new_context(viewport={'width': width, 'height': 1080 if width == 1920 else (1000 if width >= 820 else 844)}, reduced_motion='no-preference')
        page = context.new_page(); page.on('pageerror', lambda error: manifest['page_errors'].append(str(error)))
        page.goto(current.base, wait_until='networkidle'); prefix = label + '-' + str(width)
        logo(page, current, '.site-header .wordmark', True, prefix + ' header', manifest)
        logo(page, current, '.site-footer .wordmark', True, prefix + ' footer', manifest)
        public_geometry(page, prefix, manifest)
        if width in [1920, 390]:
            motion(page, prefix, manifest)
            if label == 'root': capture(page, output, manifest, prefix + '-home-top', 'Actual unseeded Home; ordinary motion, no animation seeking.')
        initial = background(page); page.evaluate('scrollTo({top:600,behavior:"instant"})'); later = background(page)
        expect(all(initial[key] == later[key] for key in ['position', 'top', 'left', 'width', 'height', 'image', 'size', 'placement']),
               prefix + ': stage stays fixed as content scrolls')
        footer(page, prefix + ' default', manifest)
        if label == 'root' and width in [1920, 1440, 820, 390, 320]:
            capture(page, output, manifest, prefix + '-footer', 'Actual compact footer, default copyright and fixed designer credit.')
        page.evaluate('scrollTo({top:0,behavior:"instant"})')
        page.get_by_role('button', name='Open site search', exact=True).click()
        be(page.locator('#header-search-query')).to_be_focused(); page.keyboard.press('Escape')
        be(page.locator('#header-search-panel')).to_be_hidden()
        audio = page.locator('#site-audio').element_handle(); open_nav(page, 'Music'); page.wait_for_url('**/music/')
        expect(page.locator('#site-audio').evaluate('(n,old)=>n===old', audio), prefix + ': navigation and search retain the persistent audio node')
        footer(page, prefix + ' after client navigation', manifest)
        if width in [1920, 390]:
            page.emulate_media(reduced_motion='reduce')
            logo(page, current, '.site-header .wordmark', False, prefix + ' reduced-motion header', manifest)
            expect(smoke(page)['display'] == 'none' and smoke(page)['animation'] is None, prefix + ': reduced motion omits smoke')
            page.emulate_media(reduced_motion='no-preference', media='print')
            expect(smoke(page)['display'] == 'none', prefix + ': print omits smoke')
            page.emulate_media(media='screen', forced_colors='active')
            expect(smoke(page)['display'] == 'none', prefix + ': forced colors omit smoke')
        if width == 1440: favicon(page, current, prefix + ' icons', manifest)
        context.close(); checkpoint(output, manifest)


def fields(page):
    return page.locator('form').filter(has=page.locator('[name=footer_copyright]')).evaluate(
        'form=>Object.fromEntries(new FormData(form).entries())')


def settings_review(browser, current, label, output, manifest):
    current.login()
    context = browser.new_context(viewport={'width': 1440, 'height': 1000}, reduced_motion='no-preference')
    page = context.new_page(); page.goto(current.base + 'admin/login.php')
    page.locator('[name=email]').fill(current.owner); page.locator('[name=password]').fill(current.password)
    page.get_by_role('button', name='Enter backstage', exact=True).click(); page.wait_for_url(lambda url: dashboard(str(url)))
    page.goto(current.base + SETTINGS, wait_until='networkidle')
    original = fields(page)
    be(page.locator('[name=footer_copyright]')).to_have_attribute('maxlength', str(LIMIT))
    expect(not page.locator('[name=footer_copyright]').get_attribute('required'), label + ': copyright is optional with its stated limit')
    expect(page.locator('input[name*=credit],textarea[name*=credit],select[name*=credit]').count() == 0,
           label + ': owner form exposes no editable or hideable designer credit')
    if label == 'root':
        for width in [1440, 390]:
            page.set_viewport_size({'width': width, 'height': 1000 if width == 1440 else 844})
            page.locator('#footer-copyright-help').scroll_into_view_if_needed()
            be(page.locator('[name=footer_copyright]')).to_be_visible()
            capture(page, output, manifest, 'root-' + str(width) + '-footer-settings',
                    'Actual owner Website settings with the Footer copyright field and help visible; disposable test account.', account=True)
        page.set_viewport_size({'width': 1440, 'height': 1000})
    value = 'Synthetic copyright test: <script id="copyright-injection">window.fixtureExecuted=1</script> & "quotes"'
    page.locator('[name=footer_copyright]').fill(value)
    page.get_by_role('button', name='Save website settings', exact=True).click()
    page.reload(wait_until='networkidle'); be(page.locator('[name=footer_copyright]')).to_have_value(value)
    public = context.new_page(); public.goto(current.base, wait_until='networkidle')
    footer(public, label + ' saved escaped copyright', manifest, value)
    expect(public.locator('#copyright-injection').count() == 0 and public.evaluate('typeof window.fixtureExecuted') == 'undefined',
           label + ': copyright markup remains escaped plain text in the real public DOM')
    values = fields(page)
    bad = page.request.post(current.base + SETTINGS, form={**values, 'csrf': 'bad', 'footer_copyright': 'Rejected CSRF'})
    expect(bad.status == 403, label + ': actual settings POST rejects invalid CSRF')
    page.reload(); be(page.locator('[name=footer_copyright]')).to_have_value(value)
    values = fields(page)
    response = page.request.post(current.base + SETTINGS, form={key: item for key, item in values.items() if key != 'footer_copyright'})
    expect(response.status == 200, label + ': older settings POST remains accepted')
    page.reload(); be(page.locator('[name=footer_copyright]')).to_have_value(value)
    values = fields(page)
    response = page.request.post(current.base + SETTINGS, form={**values, 'footer_credit': 'Unapproved credit',
        'footer_credit_url': 'https://example.test/', 'hide_footer_credit': '1'})
    expect(response.status == 200, label + ': unknown credit fields cannot replace the fixed template identity')
    public.reload(); footer(public, label + ' fixed credit after injected POST', manifest, value)
    expect('Unapproved credit' not in public.locator('.site-footer').inner_text(), label + ': submitted designer-credit text is never rendered')
    anonymous = Client(current.base)
    expect(anonymous.request(SETTINGS)[3].endswith('/admin/login.php'), label + ': anonymous settings access requires sign-in')
    for role in ['content_editor', 'store_manager']:
        fixture(current, 'role', {'role': role})
        try:
            expect(current.client.request(SETTINGS)[0] == 403 and current.client.request(SETTINGS,
                {'csrf': 'irrelevant', 'footer_copyright': 'Unauthorized change'})[0] == 403,
                label + ': ' + role + ' cannot read or mutate owner copyright settings')
        finally: fixture(current, 'role', {'role': 'owner'})
    page.reload(); be(page.locator('[name=footer_copyright]')).to_have_value(value)
    page.locator('[name=footer_copyright]').fill(''); page.get_by_role('button', name='Save website settings', exact=True).click()
    public.reload(); fallback = '© ' + datetime.now(timezone.utc).strftime('%Y') + ' ' + original['site_name'] + '.'
    footer(public, label + ' blank fallback', manifest, fallback)
    manifest['settings_checks'].append({'installation': label, 'save_reload_public_render': True,
        'escaped_plain_text': True, 'csrf_denied': True, 'anonymous_denied': True,
        'nonowner_roles_denied': ['content_editor', 'store_manager'], 'fixed_credit_post_ignored': True,
        'missing_field_preserved': True, 'blank_restores_automatic': True, 'synthetic_test_only': True})
    context.close(); checkpoint(output, manifest)


def maximum_links_review(browser, current, label, output, manifest):
    current.login()
    current.client.form('admin/shop/settings.php', {'enabled': '1', 'intro': 'Synthetic footer-link fixture; no products or payments.'})
    for slug in ['privacy', 'terms', 'shipping', 'returns']:
        save_content(current, 'page', 'Synthetic ' + slug, {'body': 'Disposable layout-test text only.'}, slug=slug)
    context = browser.new_context(viewport={'width': 390, 'height': 844}, reduced_motion='reduce')
    page = context.new_page(); page.goto(current.base + 'admin/login.php')
    page.locator('[name=email]').fill(current.owner); page.locator('[name=password]').fill(current.password)
    page.get_by_role('button', name='Enter backstage', exact=True).click(); page.wait_for_url(lambda url: dashboard(str(url)))
    page.goto(current.base + SETTINGS); original = fields(page)
    text = ('Synthetic copyright layout fixture with a long organization name and reserved-rights statement. ' * 4)[:LIMIT]
    expect(len(text.encode()) == LIMIT, label + ': copyright wrapping fixture reaches the exact byte limit')
    page.locator('[name=footer_copyright]').fill(text); page.get_by_role('button', name='Save website settings', exact=True).click()
    public = context.new_page()
    for width in [1920, 1440, 820, 390, 320]:
        public.set_viewport_size({'width': width, 'height': 1080 if width == 1920 else (1000 if width >= 820 else 844)})
        public.goto(current.base, wait_until='networkidle')
        value = footer(public, label + '-' + str(width) + ' maximum copyright and all links', manifest, text, True)
        if label == 'root' and width in [1440, 390, 320]: capture(public, output, manifest,
            'synthetic-root-' + str(width) + '-maximum-footer', 'Synthetic240-byte copyright and four disposable policy pages; all conditional footer links enabled.', synthetic=True)
        if width == 390:
            for link in value['links']:
                expect(link['href'].startswith(current.base) and public.request.get(link['href']).status == 200,
                       label + ': actual footer link resolves inside the root/subfolder installation: ' + link['text'])
    page.goto(current.base + SETTINGS); values = fields(page)
    response = page.request.post(current.base + SETTINGS, form={**values, 'footer_copyright': 'X' * LIMIT})
    expect(response.status == 200, label + ': longest unbroken plain-text copyright is accepted')
    public.set_viewport_size({'width': 320, 'height': 844}); public.reload()
    footer(public, label + ' unbroken maximum copyright', manifest, 'X' * LIMIT, True)
    page.reload(); values = fields(page)
    page.request.post(current.base + SETTINGS, form={**values, 'footer_copyright': 'X' * (LIMIT + 1)})
    public.reload(); footer(public, label + ' rejected over-limit preserves value', manifest, 'X' * LIMIT, True)
    page.reload(); values = fields(page)
    page.request.post(current.base + SETTINGS, form={**values, 'footer_copyright': ''})
    manifest['maximum_link_checks'].append({'installation': label, 'copyright_bytes': LIMIT,
        'viewports': [1920, 1440, 820, 390, 320], 'all_optional_links': True,
        'unbroken_text_wraps': True, 'over_limit_preserves_previous_value': True, 'synthetic_test_only': True})
    context.close(); checkpoint(output, manifest)


def custom_logo_review(browser, current, label, manifest):
    current.login(); wide = upload_image(current, 900, 300, 11); tall = upload_image(current, 200, 650, 12)
    branding_fixture(current, 'legacy', {'revision': 17, 'draft_logo_id': tall, 'published_logo_id': wide})
    legacy = branding_fixture(current, 'snapshot')
    context = browser.new_context(viewport={'width': 390, 'height': 844}, reduced_motion='reduce')
    public = context.new_page(); public.goto(current.base)
    logo(public, current, '.site-header .wordmark', False, label + ' old record defaults to approved identity', manifest)
    expect(not logo_present(Client(current.base).request('')[1], wide)
           and branding_fixture(current, 'snapshot') == legacy,
           label + ': old v1 logo IDs remain byte-for-byte saved while the approved bundled source is shown')
    owner = context.new_page(); owner.goto(current.base + 'admin/login.php')
    owner.locator('[name=email]').fill(current.owner); owner.locator('[name=password]').fill(current.password)
    owner.get_by_role('button', name='Enter backstage', exact=True).click(); owner.wait_for_url(lambda url: dashboard(str(url)))
    owner.goto(current.base + 'admin/branding/', wait_until='networkidle')
    be(owner.get_by_role('button', name='Use animated 3D logo', exact=True)).to_be_disabled()
    owner.get_by_label('Logo image', exact=True).select_option(str(wide))
    owner.get_by_role('button', name='Save private logo draft', exact=True).click()
    expect(not logo_present(Client(current.base).request('')[1], wide), label + ': new custom-logo draft remains private')
    with owner.expect_popup() as event: owner.get_by_role('link', name='Preview saved logo').click()
    preview = event.value; preview.wait_for_load_state('networkidle'); loaded_logo(preview, wide, (900, 300))
    be(preview.locator('.preview-banner')).to_be_visible(); preview.close()
    owner.locator('[name=approved]').check(); owner.get_by_role('button', name='Publish approved logo', exact=True).click()
    public.reload(); loaded_logo(public, wide, (900, 300))
    expect(branding_fixture(current, 'snapshot')['source'] == 'custom', label + ': explicit new publication selects the saved custom source')
    footer(public, label + ' custom uploaded logo footer', manifest)
    mutate(current, 'save', tall); public.reload(); loaded_logo(public, wide, (900, 300))
    prior = json.loads(branding_fixture(current, 'snapshot')['record'])
    owner.reload(); owner.get_by_role('button', name='Use animated 3D logo', exact=True).click()
    selected = branding_fixture(current, 'snapshot'); preserved = json.loads(selected['record'])
    expect(selected['source'] == 'bundled'
           and all(preserved[key] == prior[key] for key in ['draft_logo_id', 'published_logo_id'])
           and preserved['revision'] == prior['revision'] + 1,
           label + ': native builtin action selects approved artwork without clearing either saved media ID')
    be(owner.get_by_role('button', name='Use animated 3D logo', exact=True)).to_be_disabled()
    audio = public.locator('#site-audio').element_handle()
    open_nav(public, 'Music'); public.wait_for_url('**/music/')
    logo(public, current, '.site-header .wordmark', False, label + ' bundled identity after native source choice', manifest)
    expect(public.locator('#site-audio').evaluate('(n,old)=>n===old', audio), label + ': builtin source choice and PJAX refresh preserve persistent audio node')
    mutate(current, 'publish', wide); public.reload(); loaded_logo(public, wide, (900, 300)); mutate(current, 'withdraw')
    open_nav(public, 'Shows'); public.wait_for_url('**/shows/')
    logo(public, current, '.site-header .wordmark', False, label + ' bundled identity after withdrawal', manifest)
    footer(public, label + ' restored bundled footer', manifest)
    manifest['custom_logo_checks'].append({'installation': label, 'draft_private': True, 'publish_preserved': True,
        'legacy_record_preserved': True, 'legacy_defaults_to_bundle': True, 'private_preview': True,
        'builtin_action_preserves_saved_ids': True, 'explicit_republish_selects_custom': True,
        'withdraw_restores_bundle': True, 'pjax_audio_node_preserved': True, 'synthetic_test_only': True})
    context.close()


def record_entry(browser, current, width, output, manifest):
    viewport = {'width': width, 'height': 1080 if width == 1920 else 844}
    raw = output / 'raw-videos'; raw.mkdir(exist_ok=True)
    context = browser.new_context(viewport=viewport, reduced_motion='no-preference', device_scale_factor=1,
        record_video_dir=str(raw), record_video_size=viewport)
    page = context.new_page(); page.goto(current.base, wait_until='networkidle')
    logo(page, current, '.site-header .wordmark', True, 'entry video ' + str(width), manifest)
    page.evaluate('document.fonts.ready'); paint(page)
    capture(page, output, manifest, 'root-' + str(width) + '-video-poster',
            'Actual ordinary page-entry recording viewport; no animation seeking or acceleration.')
    box = page.locator('.site-header .wordmark').bounding_box()
    clip = {'x': math.floor(box['x']), 'y': math.floor(box['y']),
            'width': math.ceil(box['width']), 'height': math.ceil(box['height'])}
    first = smoke(page); frames = []; samples = []; started = time.monotonic()
    sample_folder = output / 'logo-motion-samples'; sample_folder.mkdir(exist_ok=True)
    for index in range(12):
        filename = 'root-' + str(width) + '-natural-logo-' + str(index).zfill(2) + '.png'
        data = page.screenshot(path=str(sample_folder / filename), clip=clip, animations='allow')
        frames.append(Image.open(io.BytesIO(data)).convert('RGB'))
        samples.append({'file': 'logo-motion-samples/' + filename, 'sha256': digest(data),
            'elapsed_seconds': round(time.monotonic() - started, 3), 'actual_viewport_crop': clip})
        if index < 11: page.wait_for_timeout(1000)
    last = smoke(page)
    mask = [r > 100 and g > 65 and r > b * 1.3 for r, g, b in frames[0].get_flattened_data()]
    changes = []
    for frame in frames[1:]:
        pixels = list(ImageChops.difference(frames[0], frame).get_flattened_data())
        changes.append(sum(keep and max(pixel) >= 3 for keep, pixel in zip(mask, pixels)))
    expect(max(changes) > 100, 'entry video ' + str(width) + ': actual gold logo pixels animate over natural elapsed time')
    expect(last['animation']['currentTime'] > first['animation']['currentTime'] + 10000
           and last['animation']['playbackRate'] == 1 and first['transform'] != last['transform'],
           'entry video ' + str(width) + ': smoke naturally drifts during the unmodified entry recording')
    video = page.video; context.close(); raw_path = Path(video.path())
    target = output / ('Spiritual-Gorillaz-rc8-Natural-Motion-' + ('Desktop' if width == 1920 else 'Phone') + '.mp4')
    subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-sseof', '-10', '-i', str(raw_path),
        '-t', '10', '-an', '-c:v', 'libx264', '-preset', 'fast', '-crf', '18', '-pix_fmt', 'yuv420p',
        '-movflags', '+faststart', str(target)], check=True, capture_output=True)
    probe = json.loads(subprocess.check_output(['ffprobe', '-v', 'error', '-show_entries',
        'format=duration:stream=codec_name,width,height,r_frame_rate', '-of', 'json', str(target)]))
    expect(9.5 <= float(probe['format']['duration']) <= 10.2, 'entry video ' + str(width) + ': normal-speed clip is approximately ten seconds')
    manifest['videos'].append({'file': target.name, 'sha256': digest(target.read_bytes()),
        'raw_file': raw_path.relative_to(output).as_posix(), 'raw_sha256': digest(raw_path.read_bytes()),
        'viewport': viewport, 'probe': probe, 'synthetic_content': False, 'normal_speed': True,
        'ordinary_page_entry': True, 'animation_seek_performed': False,
        'trim': 'Last10seconds of the actual browser recording; initial page-load time omitted, no speed adjustment or imagery alteration.',
        'logo_samples': samples, 'gold_pixels_changed_from_first_sample': changes,
        'smoke_before': first, 'smoke_after': last})
    checkpoint(output, manifest)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive', required=True, type=Path); parser.add_argument('--output', required=True, type=Path)
    args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK') != '1':
        raise RuntimeError('Use a nonroot disposable SQL fixture and the payment-network kill switch.')
    if not shutil.which('ffmpeg') or not shutil.which('ffprobe'): raise RuntimeError('Actual preview recording requires ffmpeg and ffprobe.')
    archive = args.archive.resolve(); output = args.output.resolve(); output.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(archive) as bundle:
        version = bundle.read('VERSION').decode().strip()
        runtime = {name: digest(bundle.read(name)) for name in sorted(bundle.namelist())
            if not name.endswith('/') and name not in ['README.md', 'CHANGELOG.md', 'manifest.json'] and not name.startswith('docs/')}
        assets = {}
        for name in runtime:
            if name.startswith('images/branding/') and 'sculpted' in name and name.endswith(('.png', '.webp')):
                image = Image.open(io.BytesIO(bundle.read(name)))
                assets[name] = {'width': image.width, 'height': image.height, 'frames': getattr(image, 'n_frames', 1), 'format': image.format}
    manifest = {'version': version, 'status': 'running', 'scope': 'actual-installed-footer-and-natural-motion',
        'captured_at_utc': datetime.now(timezone.utc).isoformat(), 'package_name': archive.name,
        'package_sha256': digest(archive.read_bytes()), 'runtime_payload_sha256': runtime, 'brand_assets': assets,
        'test_script_sha256': digest(Path(__file__).read_bytes()), 'browser': 'Chromium', 'device_scale_factor': 1,
        'production_site_accessed': False, 'runtime_code_modified': False,
        'primary_public_captures_unseeded': True, 'secondary_fixture_phases_explicitly_synthetic': True,
        'screenshots': [], 'videos': [], 'logo_checks': [], 'geometry_checks': [], 'favicon_checks': [],
        'footer_checks': [], 'smoke_checks': [], 'settings_checks': [], 'maximum_link_checks': [],
        'custom_logo_checks': [], 'page_errors': []}
    checkpoint(output, manifest)
    try:
        with tempfile.TemporaryDirectory(prefix='spirit-footer-refresh-') as directory, sync_playwright() as engine:
            executable = os.environ.get('SPIRIT_CHROMIUM_EXECUTABLE')
            browser = engine.chromium.launch(**({'executable_path': executable} if executable else {}))
            manifest['browser_version'] = browser.version
            try:
                for subfolder, label in [('', 'root'), ('band', 'subfolder')]:
                    with site(Path(directory) / label, archive, None, subfolder) as current:
                        current.claim(); private_review(browser, current, label, 'install/', output, manifest)
                        status, body, _, _ = current.client.form('install/', current.input())
                        expect(status == 200 and 'id="install-complete"' in body, label + ': actual ZIP installs normally')
                        private_review(browser, current, label, 'admin/login.php', output, manifest)
                        public_review(browser, current, label, output, manifest)
                        if label == 'root':
                            record_entry(browser, current, 1920, output, manifest)
                            record_entry(browser, current, 390, output, manifest)
                        settings_review(browser, current, label, output, manifest)
                        maximum_links_review(browser, current, label, output, manifest)
                        custom_logo_review(browser, current, label, manifest)
            finally: browser.close()
        expect(not manifest['page_errors'], 'No uncaught public browser errors during the focused review')
        manifest['status'] = 'passed'
    except Exception as error:
        manifest['status'] = 'failed'; manifest['failure'] = str(error); raise
    finally: checkpoint(output, manifest)
    print(str(http_integration.CHECKS) + ' focused footer/identity checks passed; '
        + str(len(manifest['screenshots'])) + ' actual screenshots and ' + str(len(manifest['videos'])) + ' natural-entry clips.', flush=True)


if __name__ == '__main__': main()
