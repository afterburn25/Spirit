#!/usr/bin/env python3
"""Real Chromium rendering, navigation, gallery and persistent-audio checks."""
from __future__ import annotations
import os
import tempfile
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from content_integration import seed_media_and_music, save_content


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root disposable database environment.')
    with tempfile.TemporaryDirectory(prefix='spirit-public-browser-') as directory:
        base = Path(directory)
        archive, code = build_package(base / 'packages')
        with site(base / 'website', archive, code, 'spirit') as current:
            current.claim()
            current.client.form('install/', current.input())
            current.login()
            image, _, _, _ = seed_media_and_music(current)
            save_content(current, 'gallery', 'Fixture Photo', {'image_id': str(image), 'caption': 'Fixture caption'}, slug='fixture-photo')
            save_content(current, 'event', 'Browser Fixture Show', {'starts_at': '2099-11-08T20:00', 'venue': 'Fixture Venue', 'city': 'Fixture City', 'timezone': 'America/Chicago', 'ticket_url': 'https://tickets.example.test/fixture'}, slug='browser-fixture')
            with sync_playwright() as engine:
                browser = engine.chromium.launch()
                for width, height in [(1440, 1000), (390, 844)]:
                    context = browser.new_context(viewport={'width': width, 'height': height}, reduced_motion='reduce')
                    page = context.new_page(); errors = []
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    for route in ['', 'music/', 'music/release.php?slug=fixture-release', 'shows/', 'band/', 'media/', 'press/', 'booking/', 'shop/']:
                        response = page.goto(current.base + route, wait_until='networkidle')
                        expect(response.status == 200, f'Chromium {width}px renders {route or "home"}')
                        expect(page.locator('h1').count() == 1 and page.locator('h1').is_visible(), f'Chromium {width}px has a visible primary heading')
                        expect(page.evaluate('document.documentElement.scrollWidth <= window.innerWidth + 1'), f'Chromium {width}px has no horizontal page overflow')
                        expect(page.locator('img').evaluate_all('(images) => images.every(image => image.complete && image.naturalWidth > 0)'), f'Chromium {width}px images decode successfully')
                    page.goto(current.base, wait_until='networkidle')
                    if width == 390:
                        page.locator('.nav-toggle').click()
                        expect(page.locator('#public-nav').is_visible(), 'Phone navigation opens')
                        page.locator('#public-nav a').filter(has_text='Music').first.click()
                        page.wait_for_url('**/music/')
                        expect(page.locator('.nav-toggle').get_attribute('aria-expanded') == 'false', 'Phone navigation closes after changing page')
                    page.goto(current.base + 'music/release.php?slug=fixture-release', wait_until='networkidle')
                    expect(page.locator('#site-audio').evaluate('(audio) => audio.paused && !audio.getAttribute("src")'), 'Audio does not autoplay on page load')
                    page.locator('.play-track').first.click()
                    page.wait_for_function('document.getElementById("site-audio").currentTime > 0.15')
                    page.evaluate('window.fixtureAudioNode = document.getElementById("site-audio")')
                    expect(page.locator('#persistent-player').is_visible(), 'Selecting a track opens the functioning audio player')
                    if width == 390:
                        page.locator('.nav-toggle').click()
                    page.locator('#public-nav a').filter(has_text='Shows').first.click()
                    page.wait_for_url('**/shows/')
                    expect(page.evaluate('window.fixtureAudioNode === document.getElementById("site-audio") && !window.fixtureAudioNode.paused && window.fixtureAudioNode.currentTime > 0'), 'The same recording continues while navigating to shows')
                    page.locator('#player-toggle').click()
                    expect(page.locator('#site-audio').evaluate('(audio) => audio.paused'), 'Player pause control works')
                    page.locator('#player-close').click()
                    expect(not page.locator('#persistent-player').is_visible(), 'Player close stops playback and removes its panel')
                    page.goto(current.base + 'media/', wait_until='networkidle')
                    page.locator('.photo-open').first.click()
                    expect(page.locator('#photo-dialog').is_visible(), 'Photo gallery opens its native dialog')
                    page.keyboard.press('Escape')
                    expect(not page.locator('#photo-dialog').is_visible(), 'Keyboard Escape closes the photo dialog')
                    expect(errors == [], f'Chromium {width}px has no uncaught JavaScript errors')
                    context.close()
                browser.close()
    print('Public responsive browser and playback checks completed.', flush=True)


if __name__ == '__main__':
    main()
