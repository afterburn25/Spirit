#!/usr/bin/env python3
"""Verify and record subtle smoke on real disposable root/subfolder ZIP installs.

Screenshots and clips contain the installed unseeded application. Controlled
animation phases are used only for deterministic pixel assertions; video runs
at playbackRate 1 and begins partway through the existing 48-second CSS loop.
No source artwork, runtime stylesheet or application content is modified.
"""
from __future__ import annotations

import argparse
import io
import json
import os
import shutil
import subprocess
import tempfile
import time
import zipfile
from datetime import datetime, timezone
from pathlib import Path

from PIL import Image, ImageChops
from playwright.sync_api import sync_playwright, expect as be

import http_integration
from http_integration import dashboard, expect, site
from capture_branding_refresh import digest, mark, public_geometry, navigate_and_search
from capture_stage_background import ART, background, readable_panels


NAME = 'stage-smoke-drift'
WIDTHS = [1920, 1440, 390, 320]


def checkpoint(output, manifest):
    manifest['assertions_passed'] = http_integration.CHECKS
    target = output / 'background-smoke-verification.json'
    temporary = target.with_suffix('.tmp')
    temporary.write_text(json.dumps(manifest, indent=2) + '\n')
    temporary.replace(target)
    print('EVIDENCE_CHECKPOINT', flush=True)


def smoke(page):
    return page.evaluate('''() => {
      const s=getComputedStyle(document.body,'::after');
      const a=document.getAnimations().find(a=>a.animationName==='stage-smoke-drift');
      return {bodyClass:document.body.className,content:s.content,display:s.display,
        position:s.position,top:s.top,left:s.left,width:s.width,height:s.height,
        zIndex:s.zIndex,pointerEvents:s.pointerEvents,backgroundImage:s.backgroundImage,
        transform:s.transform,opacity:Number(s.opacity),filter:s.filter,willChange:s.willChange,
        animationName:s.animationName,animationDuration:s.animationDuration,
        animationTimingFunction:s.animationTimingFunction,animationIterationCount:s.animationIterationCount,
        animation:a?{currentTime:a.currentTime,playState:a.playState,playbackRate:a.playbackRate,
          pseudoElement:a.effect.pseudoElement,keyframes:a.effect.getKeyframes()}:null,
        scrollY,overflow:document.documentElement.scrollWidth>innerWidth+1};
    }''')


def paint(page):
    page.evaluate('()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))')


def capture(page, output, manifest, name, note, phase=None):
    page.evaluate('document.fonts.ready')
    paint(page)
    filename = name + '.png'
    data = page.screenshot(path=str(output / filename), animations='allow', full_page=False)
    manifest['screenshots'].append({'file': filename, 'sha256': digest(data),
        'viewport': page.viewport_size, 'route': page.url.split('127.0.0.1:')[-1].split('/', 1)[-1] or '/',
        'scroll_y': page.evaluate('scrollY'), 'full_page': False,
        'synthetic_content': False, 'disposable_test_account': False,
        'controlled_animation_phase_ms': phase, 'note': note})
    checkpoint(output, manifest)
    return data


def active_check(page, label, manifest):
    state = smoke(page)
    stage = background(page)
    expect(state['display'] != 'none' and state['content'] == '""', label + ': smoke is present with ordinary motion preferences')
    expect(state['position'] == 'fixed' and state['pointerEvents'] == 'none', label + ': decorative smoke is fixed and cannot intercept input')
    expect(stage['zIndex'] == '-2' and state['zIndex'] == '-1', label + ': smoke is above the stage and behind the content')
    expect(state['animationName'] == NAME and state['animationDuration'] == '48s'
           and state['animationIterationCount'] == 'infinite', label + ': browser runs the slow 48-second continuous loop')
    expect(state['animation'] is not None and state['animation']['playState'] == 'running'
           and state['animation']['playbackRate'] == 1, label + ': animation runs at its native speed')
    expect(state['filter'] == 'none' and state['backgroundImage'].count('radial-gradient') == 6,
           label + ': soft wisps use gradients without animated blur or raster downloads')
    expect(not state['overflow'], label + ': smoke overscan creates no horizontal document overflow')
    manifest['smoke_checks'].append({'label': label, 'state': state, 'stage': stage})
    return state


def hidden_check(page, label, manifest):
    state = smoke(page)
    expect(state['display'] == 'none' and state['animationName'] == 'none'
           and state['animation'] is None, label + ': smoke is hidden and its animation is absent')
    manifest['suppression_checks'].append({'label': label, 'state': state})


def private_check(browser, current, label, route, manifest):
    context = browser.new_context(viewport={'width': 390, 'height': 844}, reduced_motion='no-preference')
    page = context.new_page()
    page.goto(current.base + route, wait_until='networkidle')
    state = smoke(page)
    expect('public-site' not in state['bodyClass'] and state['animation'] is None
           and state['animationName'] == 'none', label + ': private/setup page has no public smoke animation')
    expect(ART not in background(page)['image'], label + ': private/setup page has no public stage decoration')
    expect(not state['overflow'], label + ': private/setup layout has no horizontal overflow')
    if route == 'admin/login.php':
        mark(page, '.simple-header .backstage-brand', True, label + ' approved login identity', manifest)
        page.locator('[name=email]').fill(current.owner)
        page.locator('[name=password]').fill(current.password)
        page.get_by_role('button', name='Enter backstage', exact=True).click()
        page.wait_for_url(lambda url: dashboard(str(url)))
        page.locator('.admin-menu-toggle').click()
        be(page.locator('#sidebar')).to_be_visible()
        state = smoke(page)
        expect('public-site' not in state['bodyClass'] and state['animation'] is None,
               label + ': signed-in Overview and drawer remain outside smoke decoration')
        mark(page, '#sidebar .backstage-brand', True, label + ' approved sidebar identity', manifest)
    manifest['private_checks'].append({'label': label, 'route': route, 'state': state})
    context.close()


def phases(page, label, output, manifest):
    samples = []
    images = []
    # Pause existing CSS animations only in this inspection page. Keeping
    # animations='allow' in screenshot avoids resetting the infinite smoke.
    page.evaluate('''() => {for(const a of document.getAnimations())a.pause()}''')
    for milliseconds in [0, 24000]:
        page.evaluate('''t => {const a=document.getAnimations().find(a=>a.animationName==='stage-smoke-drift');a.currentTime=t}''', milliseconds)
        paint(page)
        state = smoke(page)
        expect(state['animation']['playState'] == 'paused'
               and abs(state['animation']['currentTime'] - milliseconds) < 1,
               label + ': deterministic phase remains at ' + str(milliseconds) + 'ms')
        samples.append(state)
        images.append(capture(page, output, manifest, label + '-phase-' + str(milliseconds),
            'Actual installed viewport at a controlled CSS phase; this is a verification image, not a speed-adjusted preview.', milliseconds))
    expect(samples[0]['transform'] != samples[1]['transform']
           and abs(samples[0]['opacity'] - .65) < .001 and abs(samples[1]['opacity'] - .9) < .001,
           label + ': actual browser reaches both defined gentle drift extremes')
    first, second = [Image.open(io.BytesIO(data)).convert('RGB') for data in images]
    header_bottom = int(page.locator('.site-header').bounding_box()['height']) + 2
    difference = ImageChops.difference(first.crop((0, header_bottom, first.width, first.height)),
                                      second.crop((0, header_bottom, second.width, second.height)))
    pixels = list(difference.get_flattened_data())
    changed = sum(max(pixel) > 1 for pixel in pixels)
    mean = sum(sum(pixel) for pixel in pixels) / (len(pixels) * 3)
    expect(changed > 100, label + ': viewport pixels visibly differ between smoke phases outside the animated logo')
    manifest['phase_checks'].append({'label': label, 'samples': samples,
        'pixel_comparison_excludes_header': True, 'changed_pixels_above_one_level': changed,
        'mean_absolute_rgb_difference': round(mean, 6), 'application_content_modified': False})


def public_review(browser, current, label, output, manifest):
    for width in WIDTHS:
        height = 1080 if width == 1920 else (1000 if width == 1440 else 844)
        context = browser.new_context(viewport={'width': width, 'height': height}, reduced_motion='no-preference', device_scale_factor=1)
        page = context.new_page()
        page.on('pageerror', lambda error: manifest['page_errors'].append(str(error)))
        prefix = label + '-' + str(width)
        response = page.goto(current.base, wait_until='networkidle')
        expect(response.status == 200, prefix + ': unseeded installed Home loads')
        expect("script-src 'self'" in response.headers.get('content-security-policy', ''), prefix + ': production CSP stays active')
        mark(page, '.site-header .wordmark', True, prefix + ' header', manifest)
        mark(page, '.site-footer .wordmark', False, prefix + ' footer', manifest)
        public_geometry(page, prefix, manifest)
        readable_panels(page, manifest, prefix)
        before = active_check(page, prefix, manifest)
        page.wait_for_timeout(1100)
        after = smoke(page)
        expect(after['animation']['currentTime'] - before['animation']['currentTime'] >= 900
               and after['transform'] != before['transform'], prefix + ': natural drift is observed over a real short interval')
        manifest['natural_motion_checks'].append({'label': prefix, 'before': before, 'after': after})
        if label == 'root' and width in [1920, 390]:
            capture(page, output, manifest, prefix + '-smoke-home-top', 'Ordinary motion preference; naturally running subtle smoke on the actual unseeded site.')
        initial = background(page)
        initial_smoke = smoke(page)
        page.evaluate('scrollTo({top:600,behavior:"instant"})')
        final = background(page)
        final_smoke = smoke(page)
        expect(final['scrollY'] >= 300 and abs((final['headingTop'] - initial['headingTop']) + final['scrollY']) <= 2,
               prefix + ': content scrolls independently of the stage')
        expect(all(initial[key] == final[key] for key in ['position', 'top', 'left', 'width', 'height', 'image', 'size', 'placement']),
               prefix + ': stage geometry remains stationary during scroll')
        expect(all(initial_smoke[key] == final_smoke[key] for key in ['position', 'top', 'left', 'width', 'height'])
               and not final_smoke['overflow'], prefix + ': smoke remains viewport-fixed with no scroll overflow')
        if width in [1920, 390]:
            phases(page, prefix, output, manifest)
        page.locator('.site-footer').scroll_into_view_if_needed()
        if label == 'root' and width in [1920, 390]:
            capture(page, output, manifest, prefix + '-smoke-footer', 'Actual unseeded footer; approved identity and full-width background retained.')
        page.evaluate('scrollTo({top:0,behavior:"instant"})')
        navigate_and_search(page, current, prefix)
        expect(smoke(page)['animation'] is not None, prefix + ': smoke survives real client navigation and leaves menu/search usable')
        page.emulate_media(reduced_motion='reduce')
        hidden_check(page, prefix + ' reduced motion', manifest)
        mark(page, '.site-header .wordmark', False, prefix + ' reduced-motion identity', manifest)
        if label == 'root' and width in [1920, 390]:
            capture(page, output, manifest, prefix + '-reduced-motion', 'Actual reduced-motion rendering: moving smoke omitted and approved static logo selected.')
        page.emulate_media(reduced_motion='no-preference', media='print')
        hidden_check(page, prefix + ' print', manifest)
        expect(background(page)['display'] == 'none', prefix + ': print omits the decorative stage too')
        page.emulate_media(media='screen', forced_colors='active')
        hidden_check(page, prefix + ' forced colors', manifest)
        expect(background(page)['display'] == 'none', prefix + ': forced colors omit decorative stage artwork')
        page.emulate_media(forced_colors='none')
        active_check(page, prefix + ' restored ordinary motion', manifest)
        context.close()
        checkpoint(output, manifest)


def record_video(browser, current, width, output, manifest):
    viewport = {'width': width, 'height': 1080 if width == 1920 else 844}
    raw = output / 'raw-videos'
    raw.mkdir(exist_ok=True)
    context = browser.new_context(viewport=viewport, reduced_motion='no-preference',
        device_scale_factor=1, record_video_dir=str(raw), record_video_size=viewport)
    page = context.new_page()
    page.goto(current.base, wait_until='networkidle')
    mark(page, '.site-header .wordmark', True, 'video ' + str(width) + ' header', manifest)
    page.evaluate('document.fonts.ready')
    if width == 390:
        page.evaluate('scrollTo({top:600,behavior:"instant"})')
    # Start near the middle of the same CSS cycle so a short preview clearly
    # shows its restrained drift. Playback rate stays 1; no time compression.
    page.evaluate('''()=>{const a=document.getAnimations().find(a=>a.animationName==='stage-smoke-drift');a.currentTime=12000;a.play()}''')
    paint(page)
    capture(page, output, manifest, 'root-' + str(width) + '-video-poster',
        'Actual recording viewport; normal-speed playback begins about 12 seconds into the existing 48-second loop.')
    first = smoke(page)
    observations = [first]
    started = time.monotonic()
    for _ in range(22):
        page.wait_for_timeout(500)
        observations.append(smoke(page))
    elapsed = time.monotonic() - started
    last = observations[-1]
    expect(elapsed >= 10 and last['animation']['currentTime'] - first['animation']['currentTime'] >= 10000
           and all(item['animation']['playbackRate'] == 1 for item in observations)
           and first['transform'] != last['transform'], 'video ' + str(width) + ': actual drift recorded continuously at normal speed')
    video = page.video
    context.close()
    raw_path = Path(video.path())
    target = output / ('Spiritual-Gorillaz-rc7-Smoke-' + ('Desktop' if width == 1920 else 'Phone') + '.mp4')
    subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-sseof', '-10', '-i', str(raw_path),
        '-t', '10', '-an', '-c:v', 'libx264', '-preset', 'fast', '-crf', '18', '-pix_fmt', 'yuv420p',
        '-movflags', '+faststart', str(target)], check=True, capture_output=True)
    probe = json.loads(subprocess.check_output(['ffprobe', '-v', 'error', '-show_entries',
        'format=duration:stream=codec_name,width,height,r_frame_rate', '-of', 'json', str(target)]))
    expect(9.5 <= float(probe['format']['duration']) <= 10.2
           and probe['streams'][0]['width'] == width and probe['streams'][0]['height'] == viewport['height'],
           'video ' + str(width) + ': native-size MP4 is approximately ten seconds')
    manifest['videos'].append({'file': target.name, 'sha256': digest(target.read_bytes()),
        'raw_file': raw_path.relative_to(output).as_posix(), 'raw_sha256': digest(raw_path.read_bytes()),
        'viewport': viewport, 'probe': probe, 'synthetic_content': False,
        'normal_speed': True, 'playback_rate': 1, 'css_cycle_ms': 48000,
        'initial_phase_ms': 12000, 'trim': 'Last 10 seconds of actual browser recording; no speed adjustment, overlays or retouching.',
        'scroll_y': 600 if width == 390 else 0, 'real_observation_seconds': round(elapsed, 3),
        'motion_observations': observations})
    checkpoint(output, manifest)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive', required=True, type=Path)
    parser.add_argument('--output', required=True, type=Path)
    args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1' or os.environ.get('SPIRIT_DISABLE_PAYMENT_NETWORK') != '1':
        raise RuntimeError('Use a non-root disposable SQL fixture and the payment-network kill switch.')
    if not shutil.which('ffmpeg') or not shutil.which('ffprobe'):
        raise RuntimeError('ffmpeg and ffprobe are required for the actual normal-speed previews.')
    output = args.output.resolve(); output.mkdir(parents=True, exist_ok=True)
    archive = args.archive.resolve()
    with zipfile.ZipFile(archive) as bundle:
        version = bundle.read('VERSION').decode().strip()
        runtime = {name: digest(bundle.read(name)) for name in sorted(bundle.namelist())
            if not name.endswith('/') and name not in ['README.md', 'CHANGELOG.md', 'manifest.json'] and not name.startswith('docs/')}
    manifest = {'version': version, 'status': 'running', 'scope': 'actual-installed-background-smoke',
        'captured_at_utc': datetime.now(timezone.utc).isoformat(), 'package_name': archive.name,
        'package_sha256': digest(archive.read_bytes()), 'runtime_payload_sha256': runtime,
        'test_script_sha256': digest(Path(__file__).read_bytes()), 'browser': 'Chromium',
        'device_scale_factor': 1, 'production_site_accessed': False, 'runtime_code_modified': False,
        'synthetic_band_content': False, 'disposable_test_owner_accounts': True,
        'provenance': 'Actual candidate ZIP installed at root and /band; compare all runtime hashes to final delivery.',
        'screenshots': [], 'videos': [], 'logo_checks': [], 'geometry_checks': [], 'panel_checks': [],
        'smoke_checks': [], 'natural_motion_checks': [], 'suppression_checks': [], 'private_checks': [],
        'phase_checks': [], 'page_errors': []}
    checkpoint(output, manifest)
    try:
        with tempfile.TemporaryDirectory(prefix='spirit-smoke-') as directory, sync_playwright() as engine:
            executable = os.environ.get('SPIRIT_CHROMIUM_EXECUTABLE')
            browser = engine.chromium.launch(**({'executable_path': executable} if executable else {}))
            manifest['browser_version'] = browser.version
            try:
                for subfolder, label in [('', 'root'), ('band', 'subfolder')]:
                    with site(Path(directory) / label, archive, None, subfolder) as current:
                        current.claim()
                        private_check(browser, current, label + ' installer', 'install/', manifest)
                        status, body, _, _ = current.client.form('install/', current.input())
                        expect(status == 200 and 'id="install-complete"' in body, label + ': normal single-page installation completes')
                        expect(current.database('count') == {'users': 1, 'state': 'installed'}, label + ': isolated installation has one test owner')
                        private_check(browser, current, label + ' private login and Overview', 'admin/login.php', manifest)
                        public_review(browser, current, label, output, manifest)
                        if label == 'root':
                            record_video(browser, current, 1920, output, manifest)
                            record_video(browser, current, 390, output, manifest)
            finally:
                browser.close()
        expect(manifest['page_errors'] == [], 'No uncaught browser errors during smoke review')
        manifest['status'] = 'passed'
    except Exception as error:
        manifest['status'] = 'failed'; manifest['failure'] = str(error)
        raise
    finally:
        checkpoint(output, manifest)
    print(str(http_integration.CHECKS) + ' smoke checks passed; ' + str(len(manifest['screenshots']))
          + ' actual screenshots and ' + str(len(manifest['videos'])) + ' normal-speed clips.', flush=True)


if __name__ == '__main__':
    main()
