#!/usr/bin/env python3
"""Exercise structured publishing, media authorization, local audio and venue links.
Only disposable SPIRIT_TESTING databases and generated test media are used.
"""
from __future__ import annotations
import io
import math
import os
import re
import secrets
import struct
import tempfile
import urllib.error
import urllib.parse
import urllib.request
import wave
import zlib
from pathlib import Path
from http_integration import Client, build_package, expect, site


def token(body: str) -> str:
    match = re.search(r'name="csrf" value="([a-f0-9]{64})"', body)
    if not match:
        raise AssertionError('Expected a CSRF-protected form')
    return match.group(1)


def edit_revision(s, kind: str, item_id: int) -> int:
    body = s.client.request(f'admin/content/edit.php?kind={kind}&id={item_id}')[1]
    match = re.search(r'name="revision" value="(\d+)"', body)
    if not match:
        raise AssertionError('Expected revision field')
    return int(match.group(1))


def save_content(s, kind: str, title: str, fields: dict, action: str = 'publish', item_id: int = 0, slug: str = '') -> int:
    path = f'admin/content/edit.php?kind={kind}' + (f'&id={item_id}' if item_id else '')
    values = {'action': action, 'title': title, 'slug': slug, 'sort_order': '0', 'revision': str(edit_revision(s, kind, item_id) if item_id else 0), **fields}
    status, body, _, url = s.client.form(path, values)
    found = urllib.parse.parse_qs(urllib.parse.urlparse(url).query).get('id', ['0'])[0]
    if status != 200 or int(found) < 1 or 'role="alert"' in body:
        raise AssertionError(f'Could not {action} fixture content: {kind}/{title}')
    return int(found)


def png_bytes() -> bytes:
    def chunk(name: bytes, payload: bytes) -> bytes:
        return struct.pack('!I', len(payload)) + name + payload + struct.pack('!I', zlib.crc32(name + payload) & 0xffffffff)
    return (b'\x89PNG\r\n\x1a\n' + chunk(b'IHDR', struct.pack('!2I5B', 2, 2, 8, 2, 0, 0, 0))
            + chunk(b'tEXt', b'Comment\x00private-fixture-metadata')
            + chunk(b'IDAT', zlib.compress(b'\x00\x90\x70\x20\x90\x70\x20' * 2)) + chunk(b'IEND', b''))


def wav_bytes() -> bytes:
    output = io.BytesIO()
    with wave.open(output, 'wb') as handle:
        handle.setnchannels(1); handle.setsampwidth(2); handle.setframerate(8000)
        handle.writeframes(b''.join(struct.pack('<h', int(800 * math.sin(i * 2 * math.pi * 220 / 8000))) for i in range(8000 * 8)))
    return output.getvalue()


def upload(s, name: str, mime: str, body: bytes, rights: bool = True):
    page = s.client.request('admin/media/')[1]
    boundary = 'SpiritFixture' + secrets.token_hex(12)
    values = {'csrf': token(page), 'alt_text': 'Generated integration-test image'}
    if rights:
        values['rights_confirmed'] = '1'
    parts = []
    for key, value in values.items():
        parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="file"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n'.encode() + body + b'\r\n')
    parts.append(f'--{boundary}--\r\n'.encode())
    request = urllib.request.Request(s.base + 'admin/media/', b''.join(parts), {'Content-Type': f'multipart/form-data; boundary={boundary}'})
    try:
        response = s.client.opener.open(request, timeout=30)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        text = response.read().decode()
        match = re.search(r'File #(\d+) uploaded', text)
        return int(match.group(1)) if match else 0, response.status, text


def raw_request(url: str, headers: dict | None = None, method: str = 'GET'):
    try:
        response = urllib.request.urlopen(urllib.request.Request(url, headers=headers or {}, method=method), timeout=30)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        return response.status, response.read(), response.headers


def seed_media_and_music(s):
    image_id, status, _ = upload(s, 'fixture.png', 'image/png', png_bytes())
    expect(status == 200 and image_id > 0, 'Owner uploads an image through the real multipart handler')
    audio_id, status, _ = upload(s, 'fixture.wav', 'audio/wav', wav_bytes())
    expect(status == 200 and audio_id > 0, 'Owner uploads an authorized generated WAV recording')
    release_id = save_content(s, 'release', 'Fixture Release', {'release_type': 'album', 'cover_id': str(image_id), 'description': 'Isolated test release', 'release_date': '2026-10-13'}, slug='fixture-release')
    track_id = save_content(s, 'track', 'Fixture Track', {'release_id': str(release_id), 'audio_id': str(audio_id), 'duration': '0:08'}, slug='fixture-track')
    return image_id, audio_id, release_id, track_id


def journey(s):
    s.claim()
    expect(s.client.form('install/', s.input())[3].endswith('/admin/login.php'), 'Content test installation completes')
    expect(s.login()[0] == 200, 'Owner signs in for content management')
    for path in ['', 'music/', 'shows/', 'band/', 'media/', 'booking/', 'press/', 'shop/']:
        expect(s.client.request(path)[0] == 200, 'Public route loads: ' + (path or '/'))
    expect(s.client.request('pages/?slug=not-published')[0] == 404, 'Generic page controller is packaged and returns a real missing-page status')
    expect(s.database('content-count') == {'content': 0, 'media': 0}, 'Fresh installation contains no invented public content')
    page_id = save_content(s, 'page', 'Draft Only Title', {'body': 'PRIVATE DRAFT TEXT'}, action='save', slug='fixture-page')
    anon = Client(s.base)
    expect(anon.request('pages/?slug=fixture-page')[0] == 404, 'Private draft does not have a public page')
    response = anon.request(f'admin/content/preview.php?id={page_id}')
    expect('PRIVATE DRAFT TEXT' not in response[1] and response[3].endswith('/admin/login.php'), 'Anonymous visitors cannot preview a private draft')
    status, body, headers, _ = s.client.request(f'admin/content/preview.php?id={page_id}')
    expect(status == 200 and 'PRIVATE DRAFT TEXT' in body and 'no-store' in headers.get('Cache-Control', '') and 'noindex' in headers.get('X-Robots-Tag', ''), 'Owner draft preview is private, uncached and excluded from indexing')
    image_id, status, _ = upload(s, 'fixture.png', 'image/png', png_bytes())
    expect(image_id > 0 and status == 200, 'Image upload is accepted with rights confirmation')
    expect(anon.request(f'media/file.php?id={image_id}')[0] == 404, 'Unreferenced upload remains private')
    private_body = s.client.request(f'media/file.php?id={image_id}')[1]
    expect('private-fixture-metadata' not in private_body, 'Image re-encoding strips fixture metadata')
    bad, _, body = upload(s, 'script.svg', 'image/svg+xml', b'<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')
    expect(bad == 0 and 'not accepted' in body, 'Active SVG upload is refused')
    bad, _, body = upload(s, 'fixture.png', 'image/png', png_bytes(), rights=False)
    expect(bad == 0 and 'permission to use' in body, 'Upload requires the owner to confirm usage rights')
    page_id = save_content(s, 'page', 'Published Title', {'body': '<script>fixture()</script> Published body', 'image_id': str(image_id)}, item_id=page_id, slug='fixture-page')
    body = anon.request('pages/?slug=fixture-page')[1]
    expect('Published Title' in body and '&lt;script&gt;fixture()&lt;/script&gt;' in body and '<script>fixture()' not in body, 'Published content is visible and HTML is escaped')
    expect(anon.request(f'media/file.php?id={image_id}')[0] == 200, 'Image becomes public only after a published page references it')
    old_revision = edit_revision(s, 'page', page_id)
    save_content(s, 'page', 'Unpublished Edit', {'body': 'NEW PRIVATE BODY', 'image_id': str(image_id)}, action='save', item_id=page_id, slug='fixture-page')
    body = anon.request('pages/?slug=fixture-page')[1]
    expect('Published Title' in body and 'NEW PRIVATE BODY' not in body, 'Saving a draft does not modify the published snapshot')
    expect('NEW PRIVATE BODY' in s.client.request(f'admin/content/preview.php?id={page_id}')[1], 'Preview shows the newly saved private draft')
    path = f'admin/content/edit.php?kind=page&id={page_id}'
    status, body, _, _ = s.client.form(path, {'action': 'save', 'revision': str(old_revision), 'title': 'Stale edit', 'slug': 'fixture-page', 'body': 'Lost update'})
    expect(status == 200 and 'changed in another session' in body, 'Stale editing session cannot silently overwrite new content')
    expect(s.client.request(path, {'csrf': 'wrong', 'action': 'publish'})[0] == 403, 'Publishing rejects invalid CSRF')
    save_content(s, 'page', 'Unpublished Edit', {'body': 'NEW PRIVATE BODY', 'image_id': str(image_id)}, action='unpublish', item_id=page_id, slug='fixture-page')
    expect(anon.request('pages/?slug=fixture-page')[0] == 404 and anon.request(f'media/file.php?id={image_id}')[0] == 404, 'Unpublishing removes the page and its otherwise-private media from public access')
    expect(s.client.request(f'media/file.php?id={image_id}')[0] == 200, 'Owner still retains the media after unpublishing')
    image_id, audio_id, release_id, _ = seed_media_and_music(s)
    body = anon.request('music/release.php?slug=fixture-release')[1]
    expect('Fixture Track' in body and f'data-audio="' in body and 'Fixture Release' in body, 'Published release renders a real track playback control')
    status, body, headers = raw_request(s.base + f'media/file.php?id={audio_id}', {'Range': 'bytes=0-3'})
    expect(status == 206 and body == b'RIFF' and headers.get('Content-Length') == '4', 'Audio byte-range request returns the exact requested recording bytes')
    expect(raw_request(s.base + f'media/file.php?id={audio_id}', {'Range': 'bytes=0-3,8-10'})[0] == 416, 'Unsupported multipart range is rejected safely')
    status, body, headers = raw_request(s.base + f'media/file.php?id={audio_id}', method='HEAD')
    expect(status == 200 and body == b'' and int(headers.get('Content-Length', '0')) > 0, 'HEAD returns recording metadata without file bytes')
    event_fields = {'starts_at': '2099-11-08T20:00', 'timezone': 'America/Chicago', 'venue': 'Fixture Venue', 'city': 'Fixture City', 'address': 'Testing only', 'event_state': 'scheduled', 'ticket_url': 'https://tickets.example.test/show/fixture', 'description': 'Isolated fixture, never production content'}
    event_id = save_content(s, 'event', 'Fixture Future Show', event_fields, slug='fixture-show')
    body = anon.request('shows/')[1]
    expect('Fixture Future Show' in body and '2099' in body and 'https://tickets.example.test/show/fixture' in body and 'noopener noreferrer' in body, 'Show listing includes full year and the configured external venue ticket seller')
    save_content(s, 'event', 'Fixture Future Show', {**event_fields, 'event_state': 'sold_out'}, item_id=event_id, slug='fixture-show')
    body = anon.request('shows/event.php?slug=fixture-show')[1]
    expect('Sold out' in body and 'href="https://tickets.example.test' not in body, 'Sold-out show does not display an active ticket-purchase button')
    save_content(s, 'event', 'Fixture Future Show', {**event_fields, 'ticket_url': ''}, item_id=event_id, slug='fixture-show')
    expect('Ticket details soon' in anon.request('shows/event.php?slug=fixture-show')[1], 'Missing ticket URL is an honest unavailable state, not a dead purchase link')
    save_content(s, 'event', 'Fixture Past Show', {**event_fields, 'starts_at': '2020-01-12T19:30'}, slug='fixture-past')
    expect('Fixture Past Show' not in anon.request('shows/')[1] and 'Fixture Past Show' in anon.request('shows/?view=past')[1], 'Past shows are archived automatically')
    save_content(s, 'gallery', 'Fixture Photograph', {'image_id': str(image_id), 'caption': 'Fixture photo caption'}, slug='fixture-photograph')
    expect('photo-open' in anon.request('media/')[1], 'Published photograph receives an accessible lightbox control')
    s.database('editor')
    expect(s.client.request('admin/content/?kind=event')[0] == 403 and s.client.request('admin/media/')[0] == 403, 'Non-owner cannot manage the owner-only content or media module')
    s.database('owner')
    s.client.form('admin/settings.php', {'site_name': 'Preserved Fixture Name', 'tagline': 'Preserved tagline', 'welcome': 'Preserved welcome'})
    s.database('legacy-schema')
    expect(s.client.request('admin/content/')[3].endswith('/admin/updates.php'), 'Older foundation schema is directed to an owner-only additive update')
    expect(s.client.form('admin/updates.php', {})[0] == 200, 'Owner applies the additive schema update without reinstalling')
    expect(s.database('count')['users'] == 1 and 'Preserved Fixture Name' in anon.request('')[1], 'Additive schema update preserves the owner and saved settings')


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable integration environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-content-') as directory:
        base = Path(directory)
        archive, code = build_package(base / 'packages')
        with site(base / 'website', archive, code, 'band') as current:
            journey(current)
    print('Content, media, music and show integration completed.', flush=True)


if __name__ == '__main__':
    main()
