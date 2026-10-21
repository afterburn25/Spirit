#!/usr/bin/env python3
"""Capture the real cumulative ZIP with unseeded public content in a disposable site.

Requires the same non-root SPIRIT_TESTING SQL/browser environment as browser tests.
The output is review evidence, not a mockup or production visitor dataset.
"""
from __future__ import annotations
import argparse
import hashlib
import json
import os
import tempfile
import zipfile
from datetime import datetime, timezone
from pathlib import Path

from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from public_browser import decoded_images
from media_quality_browser import home_artwork_check

ROUTES = [('', 'home'), ('music/', 'music'), ('shows/', 'shows'),
          ('band/', 'band'), ('media/', 'media'), ('press/', 'press'),
          ('booking/', 'booking'), ('shop/', 'merch')]
HUMAN_UA = ('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 '
            '(KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36')


def digest(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def record(page, output: Path, name: str, route: str, viewport: dict, full_page=True):
    expect(page.locator('h1').count() == 1, name + ': one primary heading')
    expect(page.evaluate('document.documentElement.scrollWidth <= innerWidth + 1'),
           name + ': no horizontal document overflow')
    decoded_images(page, name)
    if route in ['', '/'] and 'menu' not in name:
        home_artwork_check(page, viewport['width'])
    backgrounds = page.locator('main, main *').evaluate_all('''async nodes => {
      const sources = new Set();
      for (const node of nodes) for (const pseudo of [null, '::before', '::after']) {
        const value = getComputedStyle(node, pseudo).backgroundImage;
        for (const match of value.matchAll(/url\\(["']?([^"')]+)["']?\\)/g)) sources.add(match[1]);
      }
      return await Promise.all([...sources].map(async source => {
        const image = new Image(); image.src = source;
        try { await image.decode(); }
        catch { return {source: new URL(source, location.href).pathname, decoded: false}; }
        return {source: new URL(source, location.href).pathname, decoded: true,
                natural_width: image.naturalWidth, natural_height: image.naturalHeight};
      }));
    }''')
    expect(all(item['decoded'] for item in backgrounds), name + ': CSS artwork backgrounds decode')
    page.evaluate('document.fonts.ready')
    destination = output / (name + '.png')
    page.screenshot(path=str(destination), full_page=full_page, animations='disabled')
    images = page.locator('img[src]').evaluate_all('''images => images.map(image => ({
      source: new URL(image.currentSrc || image.src, location.href).pathname,
      natural_width: image.naturalWidth, natural_height: image.naturalHeight,
      displayed_width: Math.round(image.getBoundingClientRect().width),
      displayed_height: Math.round(image.getBoundingClientRect().height)
    }))''')
    return {'file': destination.name, 'sha256': digest(destination), 'route': route or '/',
            'viewport': viewport, 'full_page': full_page,
            'page_title': page.title(), 'images': images, 'background_images': backgrounds}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', required=True, type=Path)
    parser.add_argument('--archive', type=Path,
                        help='Capture this exact final ZIP; otherwise build in disposable staging.')
    args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root disposable SQL test environment.')
    output = args.output.resolve(); output.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='spirit-review-capture-') as directory:
        area = Path(directory)
        archive = args.archive.resolve() if args.archive else build_package(area / 'package')[0]
        with zipfile.ZipFile(archive) as bundle:
            version = bundle.read('VERSION').decode().strip()
            generated = [name for name in bundle.namelist()
                         if name.startswith('images/extended/') and name.endswith('.webp')]
        expect(len(generated) == 6, 'The captured ZIP includes all six generated wide artwork files')
        manifest = {'version': version, 'captured_at_utc': datetime.now(timezone.utc).isoformat(),
                    'package_name': archive.name, 'package_sha256': digest(archive),
                    'public_content_seeded': False, 'device_scale_factor': 1,
                    'reduced_motion': 'reduce', 'browser': 'Chromium', 'screenshots': []}
        with site(area / 'website', archive, None) as current:
            current.claim()
            status, body, _, _ = current.client.form('install/', current.input())
            expect(status == 200 and 'id="install-complete"' in body,
                   'Captured application was installed from the complete ZIP')
            # No content, products, shows, metrics or provider credentials are seeded.
            with sync_playwright() as engine:
                browser = engine.chromium.launch()
                manifest['browser_version'] = browser.version
                for width, height, label in [(1440, 1000, 'desktop'), (390, 844, 'phone')]:
                    viewport = {'width': width, 'height': height}
                    context = browser.new_context(viewport=viewport, device_scale_factor=1,
                                                  reduced_motion='reduce', user_agent=HUMAN_UA)
                    page = context.new_page(); errors = []
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    for route, title in ROUTES:
                        response = page.goto(current.base + route, wait_until='networkidle')
                        expect(response is not None and response.status == 200,
                               label + ': public route renders ' + (route or '/'))
                        name = f'public-{label}-{width}-{title}'
                        manifest['screenshots'].append(record(page, output, name, route, viewport))
                    expect(errors == [], label + ': no uncaught browser exceptions')
                    context.close()
                for width in [1180, 1024, 900, 821]:
                    viewport = {'width': width, 'height': 1000}
                    context = browser.new_context(viewport=viewport, device_scale_factor=1,
                                                  reduced_motion='reduce', user_agent=HUMAN_UA)
                    page = context.new_page(); page.goto(current.base, wait_until='networkidle')
                    manifest['screenshots'].append(record(page, output, f'public-tablet-{width}-home',
                                                           '/', viewport))
                    context.close()
                viewport = {'width': 320, 'height': 568}
                context = browser.new_context(viewport=viewport, device_scale_factor=1,
                                              reduced_motion='reduce', user_agent=HUMAN_UA)
                page = context.new_page(); page.goto(current.base, wait_until='networkidle')
                page.locator('.nav-toggle').click()
                expect(page.locator('#public-nav').is_visible(), 'Small-phone menu opens')
                manifest['screenshots'].append(record(page, output, 'public-phone-320-menu',
                                                       '/', viewport, full_page=False))
                page.locator('#public-nav a').filter(has_text='Music').first.click()
                page.wait_for_url('**/music/')
                expect(page.locator('.nav-toggle').get_attribute('aria-expanded') == 'false',
                       'Small-phone menu navigates and closes after a real click')
                context.close(); browser.close()
        (output / 'public-capture-manifest.json').write_text(json.dumps(manifest, indent=2) + '\n')
    print(f'Captured {len(manifest["screenshots"])} actual public screenshots for {version}.', flush=True)
    print('Manifest: ' + str(output / 'public-capture-manifest.json'), flush=True)


if __name__ == '__main__':
    main()
