#!/usr/bin/env python3
"""Real Chromium rendering, navigation, gallery and persistent-audio checks."""
from __future__ import annotations
import os
import tempfile
import time
from pathlib import Path
from playwright.sync_api import sync_playwright
from http_integration import build_package, expect, site
from content_integration import seed_media_and_music, save_content


def decoded_images(page, label: str):
    failures = page.locator('img[src]').evaluate_all('''async images => {
      return (await Promise.all(images.map(async image => {
        image.loading = 'eager';
        try { await image.decode(); }
        catch { return image.getAttribute('src'); }
        return image.naturalWidth > 0 ? null : image.getAttribute('src');
      }))).filter(Boolean);
    }''')
    expect(failures == [], label + ': all sourced images decode ' + repr(failures))


def wait_for_audio(page, seconds: float = 15):
    # wait_for_function performs string evaluation in the page's main world.
    # Poll the actual media node through the automation context instead. Do not
    # weaken production CSP, bypass it, fake currentTime, or skip playback.
    deadline = time.monotonic() + seconds
    while time.monotonic() < deadline:
        state = page.locator('#site-audio').evaluate('''a => ({
          currentTime: a.currentTime, paused: a.paused, readyState: a.readyState,
          networkState: a.networkState, error: a.error ? a.error.code : null
        })''')
        if state['error']:
            raise AssertionError('Real audio failed: ' + repr(state))
        if state['currentTime'] > .15 and not state['paused']:
            return
        time.sleep(.05)
    raise AssertionError('Playback did not advance: ' + repr(state))


def screenshot(page, name: str):
    destination = os.environ.get('SPIRIT_SCREENSHOT_DIR')
    if destination:
        path = Path(destination); path.mkdir(parents=True, exist_ok=True)
        page.screenshot(path=str(path / (name + '.png')), full_page=True)


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Use a non-root disposable database environment.')
    with tempfile.TemporaryDirectory(prefix='spirit-public-browser-') as directory:
        base = Path(directory)
        archive, code = build_package(base / 'packages')
        with site(base / 'website', archive, code, 'spirit') as current:
            current.claim(); current.client.form('install/', current.input()); current.login()
            image, _, _, _ = seed_media_and_music(current)
            save_content(current, 'gallery', 'Fixture Photo', {'image_id': str(image), 'caption': 'Fixture caption'}, slug='fixture-photo')
            save_content(current, 'event', 'Browser Fixture Show', {'starts_at': '2099-11-08T20:00', 'venue': 'Fixture Venue', 'city': 'Fixture City', 'timezone': 'America/Chicago', 'ticket_url': 'https://tickets.example.test/fixture'}, slug='browser-fixture')
            with sync_playwright() as engine:
                browser = engine.chromium.launch()
                for width, height in [(1440, 1000), (390, 844), (360, 800)]:
                    context = browser.new_context(viewport={'width': width, 'height': height}, reduced_motion='reduce', bypass_csp=False)
                    page = context.new_page(); errors = []
                    page.on('pageerror', lambda error: errors.append(str(error)))
                    for route in ['', 'music/', 'music/release.php?slug=fixture-release', 'shows/', 'band/', 'media/', 'press/', 'booking/', 'shop/', 'newsletter/']:
                        response = page.goto(current.base + route, wait_until='networkidle')
                        expect(response.status == 200, f'Chromium {width}px renders {route or "home"}')
                        csp = response.headers.get('content-security-policy', '')
                        expect("script-src 'self'" in csp and 'unsafe-eval' not in csp, 'Production script CSP remains enforced')
                        expect(page.locator('h1').count() == 1 and page.locator('h1').is_visible(), f'Chromium {width}px has a visible primary heading')
                        expect(page.evaluate('document.documentElement.scrollWidth <= window.innerWidth + 1'), f'Chromium {width}px has no horizontal page overflow')
                        decoded_images(page, f'Chromium {width}px {route or "home"}')
                        if route == 'band/':
                            expect(page.locator('.member-card-factual').count() == 3, f'Chromium {width}px shows the three confirmed lineup cards when member profiles are unpublished')
                            expect(page.locator('.member-card-factual a').count() == 0, f'Chromium {width}px factual lineup does not link to unpublished member pages')
                            lineup_text=page.locator('.member-grid').inner_text().casefold()
                            expect(all(name.casefold() in lineup_text for name in ('Chad Lindsey','Ron Green','Charlie Savell')), f'Chromium {width}px factual lineup names are present')
                        if route == '':
                            expect(page.locator('#public-nav a').filter(has_text='Merch').count()==0, f'Chromium {width}px closed store is absent from global navigation markup')
                            expect(page.get_by_role('navigation',name='Footer').get_by_role('link',name='Your bag',exact=True).count()==0, f'Chromium {width}px closed store does not advertise an empty bag')
                            hero=page.locator('.fallback-hero')
                            expect(hero.count()==1, f'Chromium {width}px uses the bundled fallback only when no CMS hero is published')
                            geometry=hero.evaluate('(n)=>{const r=n.getBoundingClientRect();return {w:r.width,h:r.height,fit:getComputedStyle(n).objectFit};}')
                            expect(geometry['fit'] in ['cover','contain'] and geometry['w'] > 0 and geometry['h'] > 0, f'Chromium {width}px hero artwork renders with a valid fit and geometry')
                            expect(r'\n\n' not in page.locator('.purpose-section .prose').inner_text(), f'Chromium {width}px home fallback renders paragraph breaks instead of escape text')
                        screenshot_routes={'':'home','music/':'music','shows/':'shows','band/':'band','media/':'media','press/':'press','booking/':'booking','shop/':'merch'}
                        if route in screenshot_routes:
                            screenshot(page, f'public-{width}-' + screenshot_routes[route])
                    if width == 1440:
                        chad_id=save_content(current, 'member', 'Chad L.', {'role': 'Guitar, keyboards and vocals', 'bio': 'Published fixture profile.'}, slug='chad-lindsey')
                        page.goto(current.base + 'band/', wait_until='networkidle')
                        expect(page.locator('.member-card:not(.member-card-factual) a').filter(has_text='Chad L.').count() == 1, 'Published confirmed member keeps a linked profile card')
                        expect(page.locator('.member-card-factual').count() == 2, 'Only missing confirmed members use factual fallback cards during partial publishing')
                        names=page.locator('.member-grid').inner_text().casefold()
                        expect('chad l.' in names and 'ron green' in names and 'charlie savell' in names and 'chad lindsey' not in names, 'Canonical slug suppresses a duplicate fallback even when the published profile title is edited')
                        save_content(current, 'member', 'Chad L.', {'role': 'Guitar, keyboards and vocals', 'bio': 'Published fixture profile.'}, action='unpublish', item_id=chad_id, slug='chad-lindsey')
                    page.goto(current.base, wait_until='networkidle')
                    if width <= 390:
                        page.locator('.nav-toggle').click()
                        expect(page.locator('#public-nav').is_visible(), 'Phone navigation opens')
                        page.locator('#public-nav a').filter(has_text='Music').first.click()
                        page.wait_for_url('**/music/')
                        expect(page.locator('.nav-toggle').get_attribute('aria-expanded') == 'false', 'Phone navigation closes after changing page')
                    page.goto(current.base + 'pages/search.php', wait_until='networkidle')
                    expect(page.get_by_role('option',name='Merchandise',exact=True).count()==0, f'Chromium {width}px closed-store search hides Merchandise filter')
                    search_text=page.locator('.search-section').inner_text()
                    expect('available merchandise' not in search_text.lower(), f'Chromium {width}px closed-store search copy does not advertise merchandise')
                    expect('product name' not in search_text.lower(), f'Chromium {width}px closed-store search empty state does not mention products')
                    page.goto(current.base + 'account/login.php', wait_until='networkidle')
                    expect(page.locator('#public-nav a').filter(has_text='Merch').count()==0, f'Chromium {width}px closed-store account shell hides Merch')
                    expect(page.locator('.shop-tools').get_by_role('link',name='Merchandise',exact=True).count()==0, f'Chromium {width}px closed-store customer navigation hides Merchandise')
                    expect(page.locator('.shop-tools').get_by_role('link',name='Your bag',exact=True).count()==0, f'Chromium {width}px closed-store customer navigation hides Your bag')
                    expect('Guest checkout remains available' not in page.locator('.account-shell').inner_text(), f'Chromium {width}px closed-store login does not advertise checkout')
                    expect('Your details. Your account.' in page.locator('.account-shell').inner_text(), f'Chromium {width}px closed-store account subtitle is non-commerce')
                    page.goto(current.base + 'account/register.php', wait_until='networkidle')
                    expect(page.locator('.details-panel').get_by_role('link',name='Return to merchandise',exact=True).count()==0, f'Chromium {width}px closed-store registration fallback does not point to merchandise')
                    expect(page.locator('.details-panel').get_by_role('link',name='Return to sign in',exact=True).count()==1, f'Chromium {width}px closed-store registration fallback returns to sign in')
                    page.goto(current.base + 'music/release.php?slug=fixture-release', wait_until='networkidle')
                    expect(page.locator('#site-audio').evaluate('(audio) => audio.paused && !audio.getAttribute("src")'), 'Audio does not autoplay on page load')
                    page.locator('.play-track').first.click(); wait_for_audio(page)
                    page.evaluate('window.fixtureAudioNode = document.getElementById("site-audio")')
                    expect(page.locator('#persistent-player').is_visible(), 'Selecting a track opens the functioning audio player')
                    if width <= 390:
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
