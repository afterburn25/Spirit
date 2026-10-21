#!/usr/bin/env python3
"""Owner footer-copyright persistence and fixed credit on isolated installed sites."""
from __future__ import annotations

import argparse
import hashlib
import html
import json
import os
import re
import tempfile
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path

import http_integration as harness
from http_integration import Client, build_package, expect, site
from launch_integration import fixture

PATH = 'admin/settings.php'
CREDIT = 'Website designed &amp; developed by <a href="https://cajunwebpro.com/">Cajun Web Pro LLC</a>'


class Inputs(HTMLParser):
    def __init__(self, source):
        super().__init__()
        self.values = {}
        self.feed(source)

    def handle_starttag(self, tag, attributes):
        values = dict(attributes)
        if tag == 'input' and 'name' in values:
            self.values[values['name']] = values.get('value', '')


def footer(client):
    status, body, _, _ = client.request('')
    match = re.search(r'<footer class="site-footer">(.*?)</footer>', body, re.S)
    expect(status == 200 and match is not None, 'The public page has its real shared footer')
    return match.group(1)


def journey(s):
    public = Client(s.base)
    initial = fixture(s, 'fingerprint')['settings']
    status, editor, headers, _ = s.client.request(PATH)
    expect(status == 200 and 'no-store' in headers.get('Cache-Control', ''), 'Copyright editor retains the private owner settings response')
    fields = Inputs(editor).values
    expect(fields.get('footer_copyright') == '' and '240 bytes maximum' in editor, 'An upgraded installation starts with an optional, bounded copyright field')
    expect(not any(name in fields for name in ['footer_credit', 'footer_credit_url', 'hide_footer_credit']), 'Administration offers no developer-credit edit or visibility control')
    expect(fixture(s, 'fingerprint')['settings'] == initial, 'Reading the new settings form does not create or rewrite a setting')
    default_name = fields['site_name']
    automatic = '© ' + str(datetime.now(timezone.utc).year) + ' ' + default_name + '.'
    body = footer(public)
    expect('<p class="footer-copyright">' + html.escape(automatic, quote=True) + '</p>' in body, 'Absent copyright row renders the prior year-and-name default')
    expect(CREDIT in body and 'Music. People. Purpose.' not in body, 'Fixed linked credit is present and both old footer taglines are removed')
    for route in ['pages/search.php', 'music/', 'shows/', 'press/', 'account/', 'booking/']:
        base = '/' + '/'.join(s.base.split('/')[3:])
        expect('href="' + base + route + '"' in body, 'Existing footer destination remains: ' + route)

    values = {'site_name': default_name, 'tagline': fields['tagline'], 'welcome': 'Synthetic footer settings test introduction.'}
    custom = '© 2020–2026 Test rights holder. All rights reserved.'
    saved = s.client.form(PATH, {**values, 'footer_copyright': '  ' + custom + '  '})
    expect(saved[0] == 200 and 'Website settings saved.' in saved[1], 'The normal owner form inserts the new settings row')
    expect(Inputs(s.client.request(PATH)[1]).values['footer_copyright'] == custom, 'Custom copyright survives a fresh settings request')
    expect(custom in footer(public), 'Saved copyright is used by the public footer')

    s.client.form(PATH, {**values, 'site_name': 'Changed test band'})
    expect(Inputs(s.client.request(PATH)[1]).values['footer_copyright'] == custom, 'An older form without the new field preserves an existing custom copyright')
    values['site_name'] = 'Changed test band'
    before = fixture(s, 'fingerprint')['settings']
    s.client.form(PATH, {**values, 'footer_copyright': custom, 'footer_credit': 'forged credit', 'footer_credit_url': 'javascript:alert(1)', 'hide_footer_credit': '1'})
    expect(fixture(s, 'fingerprint')['settings'] == before, 'Unknown credit and hide fields are not persisted')
    expect(CREDIT in footer(public), 'Attempted credit edits cannot replace or hide the fixed linked line')

    malicious = '<img src=x onerror=alert(1)> & "rights"'
    s.client.form(PATH, {**values, 'footer_copyright': malicious})
    editor = s.client.request(PATH)[1]
    body = footer(public)
    expect(Inputs(editor).values['footer_copyright'] == malicious and malicious not in editor, 'Copyright is escaped in the editable input without losing its plain-text value')
    expect(html.escape(malicious, quote=True) in body and malicious not in body and CREDIT in body, 'Saved markup renders as text and cannot inject or modify the credit')

    for boundary in ['x' * 240, 'é' * 120]:
        response = s.client.form(PATH, {**values, 'footer_copyright': boundary})
        expect('Website settings saved.' in response[1] and Inputs(response[1]).values['footer_copyright'] == boundary, 'Exactly 240 bytes of valid text can be saved')
    before = fixture(s, 'fingerprint')['settings']
    for invalid in ['x' * 241, 'é' * 121, 'first\nsecond', 'first\x00second']:
        response = s.client.form(PATH, {**values, 'site_name': 'Must not be stored', 'footer_copyright': invalid})
        expect('Footer copyright must be one line of plain text' in response[1] and fixture(s, 'fingerprint')['settings'] == before, 'Invalid copyright rejects the whole settings update without data loss')
    response = s.client.form(PATH, {**values, 'footer_copyright[]': 'not a string'})
    expect('Footer copyright must be one line of plain text' in response[1] and fixture(s, 'fingerprint')['settings'] == before, 'An array-shaped optional field is rejected without clearing the saved copyright')
    expect(s.client.request(PATH, {**values, 'footer_copyright': 'forged', 'csrf': 'wrong'})[0] == 403 and fixture(s, 'fingerprint')['settings'] == before, 'Invalid CSRF cannot mutate the saved copyright')

    anonymous = Client(s.base)
    for payload in [None, {**values, 'footer_copyright': 'anonymous', 'csrf': 'wrong'}]:
        expect(anonymous.request(PATH, payload)[3].endswith('/admin/login.php') and fixture(s, 'fingerprint')['settings'] == before, 'Anonymous GET/POST cannot access or change website settings')
    token = Inputs(s.client.request(PATH)[1]).values['csrf']
    for role in ['content_editor', 'store_manager']:
        fixture(s, 'role', {'role': role})
        expect(s.client.request(PATH)[0] == 403 and s.client.request(PATH, {**values, 'footer_copyright': role, 'csrf': token})[0] == 403, role + ' cannot GET or POST the copyright editor')
        expect(fixture(s, 'fingerprint')['settings'] == before, role + ' cannot change saved copyright or other settings')
    fixture(s, 'role', {'role': 'owner'})

    s.client.form(PATH, {**values, 'footer_copyright': ''})
    body = footer(public)
    expect('© ' + str(datetime.now(timezone.utc).year) + ' Changed test band.' in body and CREDIT in body, 'Clearing copyright restores the current automatic default without removing the credit')
    expect(Inputs(s.client.request(PATH)[1]).values['footer_copyright'] == '', 'The blank automatic choice survives settings reload')


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive', type=Path, help='Use an existing candidate Full ZIP instead of building a new test package.')
    parser.add_argument('--report', type=Path, help='Write the current focused assertion count as JSON.')
    args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable SQL environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-footer-settings-') as directory:
        base = Path(directory)
        archive, code = (args.archive.resolve(), None) if args.archive else build_package(base / 'packages')
        archive_sha256 = hashlib.sha256(archive.read_bytes()).hexdigest()
        for folder, label in [('', 'root'), ('band', 'subfolder')]:
            with site(base / label, archive, code, folder) as current:
                current.claim()
                current.client.form('install/', current.input())
                current.login()
                journey(current)
    report = {'status': 'passed', 'assertions_passed': harness.CHECKS, 'archive_sha256': archive_sha256, 'scope': 'Installed root/subfolder footer copyright, escaping, limits, CSRF, owner permissions and fixed developer credit', 'production_site_accessed': False}
    if args.report:
        args.report.write_text(json.dumps(report, indent=2) + '\n')
    print(json.dumps(report, indent=2), flush=True)


if __name__ == '__main__':
    main()
