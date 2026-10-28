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
                        if route == 'press/':
                            press_copy = page.locator('.press-story .prose').inner_text()
                            if width == 1440:
                                expect('Ron Green' in press_copy and 'Charlie Savell' in press_copy, 'Untouched Press copy retains the original factual lineup')
                            else:
                                expect('Ron Green' not in press_copy and 'Charlie Savell' not in press_copy, 'Updated Press copy does not resurrect historical lineup names on mobile')
                        if route == 'band/':
                            rows = page.locator('.band-profile-list .band-profile')
                            if width == 1440:
                                # No member has ever been published: approved historical names remain
                                # as unlinked placeholders in the new vertically stacked layout.
                                expect(rows.count() == 3, 'Initial Band view shows three confirmed, unlinked fallback profiles')
                                expect(rows.locator('a').count() == 0, 'Fallback profiles never link to unpublished member pages')
                                names = page.locator('.band-profile-list').inner_text()
                                expect(all(name in names for name in ('Chad Lindsey', 'Ron Green', 'Charlie Savell')), 'Initial factual lineup names remain visible')
                            else:
                                # The 1440px test below published, then unpublished and republished
                                # Chad. Historical placeholders must not return after first publication.
                                expect(rows.count() == 1, f'Chromium {width}px shows the one republished CMS profile, not legacy placeholders')
                                expect('Chad L.' in rows.inner_text() and 'Ron Green' not in rows.inner_text(), f'Chromium {width}px does not resurrect omitted factual profiles')
                                expect(rows.get_by_role('link', name='Member profile').count() == 1, f'Chromium {width}px links only the published profile')
                            photo = rows.first.locator('.band-profile-photo').bounding_box()
                            bio = rows.first.locator('.band-profile-copy').bounding_box()
                            expect(photo is not None and bio is not None, f'Chromium {width}px renders a portrait and biography region')
                            if width == 1440:
                                expect(photo['x'] + photo['width'] <= bio['x'] + 2, 'Desktop Band profile has photograph on the left and biography on the right')
                            else:
                                expect(photo['y'] + photo['height'] <= bio['y'] + 2, f'Chromium {width}px stacks Band photograph above the biography')
                        if route == '':
                            expect(page.locator('#public-nav a').filter(has_text='Merch').count()==0, f'Chromium {width}px closed store is absent from global navigation markup')
                            expect(page.get_by_role('navigation',name='Footer').get_by_role('link',name='Your bag',exact=True).count()==0, f'Chromium {width}px closed store does not advertise an empty bag')
                            hero=page.locator('.fallback-hero')
                            expect(hero.count()==1, f'Chromium {width}px uses the bundled fallback only when no CMS hero is published')
                            geometry=hero.evaluate('(n)=>{const r=n.getBoundingClientRect();return {w:r.width,h:r.height,fit:getComputedStyle(n).objectFit};}')
                            expect(geometry['fit']=='cover' and geometry['w'] > 0 and geometry['h'] > 0, f'Chromium {width}px native production hero fills its responsive artwork frame')
                        screenshot_routes={'':'home','music/':'music','shows/':'shows','band/':'band','booking/':'booking','shop/':'merch'}
                        if route in screenshot_routes:
                            screenshot(page, f'public-{width}-' + screenshot_routes[route])
                    if width == 1440:
                        member_fields={'role': 'Guitar, keyboards and vocals', 'bio': 'Published fixture profile.', 'image_id': str(image), 'portrait_alt': 'Chad playing a gold guitar on stage', 'website_url': 'https://member.example.test/chad'}
                        chad_id=save_content(current, 'member', 'Chad L.', member_fields, slug='chad-lindsey')
                        page.goto(current.base + 'band/', wait_until='networkidle')
                        rows = page.locator('.band-profile-list .band-profile')
                        expect(rows.count() == 1 and rows.get_by_role('link', name='Member profile').count() == 1, 'First published member replaces all provisional historical placeholders')
                        names = rows.inner_text()
                        expect('Chad L.' in names and 'Published fixture profile.' in names and 'Ron Green' not in names and 'Charlie Savell' not in names and 'Chad Lindsey' not in names, 'Only approved CMS biographies appear when one member is published')
                        expect('Ron Green' not in page.locator('.band-hero').inner_text() and 'Charlie Savell' not in page.locator('.band-hero').inner_text(), 'Default Band introduction stops naming removed or unpublished historical members')
                        expect('Ron Green' not in (page.locator('meta[name="description"]').get_attribute('content') or ''), 'Default Band metadata stops naming removed historical members')
                        page.goto(current.base + 'press/', wait_until='networkidle')
                        initial_press = page.locator('.press-story .prose').inner_text()
                        expect('Ron Green' not in initial_press and 'Charlie Savell' not in initial_press and 'For event inquiries' in initial_press, 'Press fallback stops advertising former members after roster publication')
                        # A partial Page edit must not erase the designed Home/Band/Press introduction.
                        save_content(current, 'page', 'Home introduction', {'headline': 'Edited Home headline'}, slug='home')
                        save_content(current, 'page', 'Band introduction', {'headline': 'Edited Band headline'}, slug='band')
                        save_content(current, 'page', 'Press introduction', {'headline': 'Edited Press headline'}, slug='press')
                        page.goto(current.base, wait_until='networkidle')
                        expect('Music can turn an ordinary moment' in page.locator('.purpose-section .prose').inner_text(), 'Empty Home Page body preserves the approved purpose copy')
                        page.goto(current.base + 'band/', wait_until='networkidle')
                        band_copy = page.locator('.band-hero .prose').inner_text()
                        expect('Meet the musicians behind Spiritual Gorillaz' in band_copy and 'Ron Green' not in band_copy, 'Empty Band Page body preserves updated roster-safe default copy')
                        page.goto(current.base + 'press/', wait_until='networkidle')
                        press_copy = page.locator('.press-story .prose').inner_text()
                        expect('For event inquiries' in press_copy and 'Ron Green' not in press_copy and 'Charlie Savell' not in press_copy, 'Empty Press Page body falls back through empty Band copy to roster-safe editorial text')
                        page.goto(current.base + 'band/', wait_until='networkidle')
                        expect(rows.locator('.band-profile-photo img').first.get_attribute('alt')=='Chad playing a gold guitar on stage', 'Member portrait description overrides Media library alt text')
                        member_website=rows.get_by_role('link', name='Member website')
                        expect(member_website.count()==1 and member_website.get_attribute('href')=='https://member.example.test/chad' and member_website.get_attribute('target')=='_blank' and 'noopener' in (member_website.get_attribute('rel') or ''), 'Member website is explicitly HTTPS and safely opens a new tab')
                        screenshot(page, 'band-published-1440')
                        page.goto(current.base+'band/member.php?slug=chad-lindsey',wait_until='networkidle')
                        expect(page.locator('.member-detail .portrait').get_attribute('alt')=='Chad playing a gold guitar on stage', 'Member detail uses the same approved portrait description')
                        expect(page.locator('.member-copy').get_by_role('link',name='Member website').count()==1, 'Member detail includes the approved optional website')
                        page.goto(current.base+'band/',wait_until='networkidle')
                        save_content(current, 'member', 'Chad L.', member_fields, action='unpublish', item_id=chad_id, slug='chad-lindsey')
                        page.goto(current.base + 'band/', wait_until='networkidle')
                        expect(page.locator('.band-profile').count() == 0 and page.get_by_text('Member profiles will appear here as soon as they are published.').count() == 1, 'Unpublishing the last member gives an honest empty state instead of resurrecting the prior lineup')
                        expect('Ron Green' not in page.locator('.band-hero').inner_text(), 'An unpublished lineup does not restore historical names in the page introduction')
                        save_content(current, 'member', 'Chad L.', member_fields, item_id=chad_id, slug='chad-lindsey')
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
