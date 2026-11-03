#!/usr/bin/env python3
"""Read-only GET coverage for each visible Admin destination on disposable websites."""
from __future__ import annotations
from html import unescape
import os
import re
import tempfile
from pathlib import Path
from urllib.parse import urlsplit
from http_integration import build_package, dashboard, expect, site

LINK = re.compile(r'<a\s+data-nav-link="[^"]+"\s+href="([^"]+)"')

def verify(s, name):
    status, html, _, _ = s.client.form('install/', s.input())
    expect(status == 200 and 'id="install-complete"' in html, name+': disposable site installed')
    status, _, _, url = s.login()
    expect(status == 200 and dashboard(url), name+': owner signed in')
    status, body, _, _ = s.client.request('admin/')
    expect(status == 200 and 'id="sidebar"' in body, name+': Admin navigation rendered')
    links = list(dict.fromkeys(unescape(x) for x in LINK.findall(body)))
    expect(len(links) >= 35, name+': comprehensive navigation found')
    root = urlsplit(s.base).path
    before = s.database('content-count')
    for href in links:
        path = urlsplit(href)
        expect(not path.scheme and not path.netloc and path.path.startswith(root+'admin/'),
               name+': link stays within disposable Admin')
        relative = href[len(root):]
        code, page, _, url = s.client.request(relative)
        final = urlsplit(url).path
        expect(code == 200 and final.startswith(root+'admin/') and
               not final.endswith('/login.php') and '<main' in page and '<h1' in page,
               name+': permitted page renders: '+relative)
        if path.path.rstrip('/') != (root+'admin/help').rstrip('/'):
            expect('admin-context-help' in page, name+': contextual help: '+relative)
    for kind in ('page', 'member'):
        url = 'admin/content/edit.php?kind=' + kind
        code, page, _, final = s.client.request(url)
        expect(code == 200 and urlsplit(final).path.endswith('/admin/content/edit.php') and
               'enctype="multipart/form-data"' in page and
               'name="direct_image_id"' in page and
               'name="image_rights_confirmed"' in page,
               name+': '+kind+' editor has direct image upload')
    expect(before == s.database('content-count'),
           name+': GET-only review did not create media or content')

def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Only unprivileged disposable CI fixtures may run Admin checks.')
    with tempfile.TemporaryDirectory(prefix='spirit-admin-routes-') as tmp:
        root = Path(tmp)
        archive, code = build_package(root/'packages')
        for folder in ('', 'subfolder'):
            name = folder or 'root'
            with site(root/('site-'+name), archive, code, folder) as current:
                verify(current, name)
    print('All permitted owner Admin destinations passed GET checks in root and subfolder.')

if __name__ == '__main__':
    main()
