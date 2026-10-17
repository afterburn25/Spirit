#!/usr/bin/env python3
"""Render the actual installed public site for concept-fidelity review."""
from __future__ import annotations

import os
import tempfile
from pathlib import Path

from playwright.sync_api import sync_playwright

from content_integration import save_content, seed_media_and_music
from http_integration import build_package, site


def main() -> None:
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root disposable database environment.')

    output = Path(os.environ['SPIRIT_SCREENSHOT_DIR'])
    output.mkdir(parents=True, exist_ok=True)

    routes = {
        'home': '',
        'music': 'music/',
        'shows': 'shows/',
        'band': 'band/',
        'booking': 'booking/',
        'merch': 'shop/',
    }

    with tempfile.TemporaryDirectory(prefix='spirit-concept-preview-') as directory:
        base = Path(directory)
        archive, code = build_package(base / 'packages')
        with site(base / 'website', archive, code, 'spirit') as current:
            current.claim()
            current.client.form('install/', current.input())
            current.login()
            seed_media_and_music(current)
            save_content(
                current,
                'event',
                'Browser Fixture Show',
                {
                    'starts_at': '2099-11-08T20:00',
                    'venue': 'Fixture Venue',
                    'city': 'Fixture City',
                    'timezone': 'America/Chicago',
                    'ticket_url': 'https://tickets.example.test/fixture',
                },
                slug='browser-fixture',
            )

            with sync_playwright() as engine:
                browser = engine.chromium.launch()
                for width, height in [(1440, 1000), (390, 844)]:
                    context = browser.new_context(
                        viewport={'width': width, 'height': height},
                        reduced_motion='reduce',
                        bypass_csp=False,
                    )
                    page = context.new_page()
                    for name, route in routes.items():
                        response = page.goto(current.base + route, wait_until='networkidle')
                        if response is None or response.status != 200:
                            raise AssertionError(f'{name} returned an invalid response at {width}px')
                        if page.evaluate('document.documentElement.scrollWidth > window.innerWidth + 1'):
                            raise AssertionError(f'{name} overflows horizontally at {width}px')
                        if name == 'home':
                            metrics = page.locator('.concept-home h1').evaluate("""
                                node => {
                                    const box = node.getBoundingClientRect();
                                    const segments = [];
                                    for (const child of node.childNodes) {
                                        if (child.nodeType === 3 && child.textContent.trim()) {
                                            const range = document.createRange();
                                            range.selectNodeContents(child);
                                            segments.push([...range.getClientRects()].map(rect => ({left: rect.left, right: rect.right})));
                                        } else if (child.nodeType === 1 && child.tagName !== 'BR') {
                                            const range = document.createRange();
                                            range.selectNodeContents(child);
                                            segments.push([...range.getClientRects()].map(rect => ({left: rect.left, right: rect.right})));
                                        }
                                    }
                                    return {
                                        left: box.left,
                                        right: box.right,
                                        clientWidth: node.clientWidth,
                                        scrollWidth: node.scrollWidth,
                                        segments,
                                    };
                                }
                            """)
                            invalid_segments = [
                                segment for segment in metrics['segments']
                                if len(segment) != 1
                                or segment[0]['left'] < metrics['left'] - 1
                                or segment[0]['right'] > metrics['right'] + 1
                            ]
                            if metrics['scrollWidth'] > metrics['clientWidth'] + 1 or len(metrics['segments']) != 3 or invalid_segments:
                                raise AssertionError(f'Home title leaves its copy container at {width}px: {metrics}')
                        if name != 'home':
                            selector = '.concept-booking h1' if name == 'booking' else ('.concept-merch-heading h1' if name == 'merch' else '.page-hero h1')
                            for title in page.locator(selector).all():
                                bounds = title.evaluate('''node => {
                                    const box = node.getBoundingClientRect();
                                    const parent = node.parentElement.getBoundingClientRect();
                                    return {left: box.left, right: box.right, parentLeft: parent.left, parentRight: parent.right, scrollWidth: node.scrollWidth, clientWidth: node.clientWidth};
                                }''')
                                if bounds['left'] < bounds['parentLeft'] - 1 or bounds['right'] > bounds['parentRight'] + 1 or bounds['scrollWidth'] > bounds['clientWidth'] + 1:
                                    raise AssertionError(f'{name} title leaves its container at {width}px: {bounds}')
                        page.screenshot(
                            path=str(output / f'concept-{width}-{name}.png'),
                            full_page=True,
                        )
                    context.close()
                browser.close()


if __name__ == '__main__':
    main()
