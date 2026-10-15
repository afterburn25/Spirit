#!/usr/bin/env python3
"""Compare real reload entry points on a loopback fixture, not fake unload events.

The required application suite independently tests its real authenticated editor.
The protocol reload observation is diagnostic only. Native Location.reload must
show the real dialog when dirty, cancel it, and reload cleanly when not dirty.
"""
from __future__ import annotations
import json
import os
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from playwright.sync_api import sync_playwright, expect as be, Error

ROOT = Path(__file__).resolve().parents[1]
HTML = b'''<!doctype html><html><head><script defer src="/guard.js"></script></head>
<body><form method="post" data-edit-guard><label>Title<input name="title" value="Saved"></label></form></body></html>'''


class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        body = (ROOT / 'assets/js/edit-guard.js').read_bytes() if self.path == '/guard.js' else HTML
        self.send_response(200)
        self.send_header('Content-Type', 'text/javascript' if self.path == '/guard.js' else 'text/html')
        self.send_header('Cache-Control', 'no-store')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def log_message(self, *args):
        pass


def main():
    name = os.environ.get('SPIRIT_BROWSER', 'chromium')
    if name not in ('chromium', 'firefox', 'webkit'):
        raise RuntimeError('Unknown browser engine.')
    server = ThreadingHTTPServer(('127.0.0.1', 0), Handler)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    rows = []
    try:
        with sync_playwright() as p:
            options = {'executable_path': '/usr/bin/chromium'} if os.environ.get('SPIRIT_LOCAL_CHROMIUM') == '1' and name == 'chromium' else {}
            browser = getattr(p, name).launch(**options)
            try:
                for method in ('protocol', 'location'):
                    context = browser.new_context()
                    page = context.new_page()
                    url = f'http://127.0.0.1:{server.server_port}/'
                    page.goto(url, wait_until='load')
                    field = page.get_by_label('Title')
                    field.click()
                    field.press('End')
                    field.press_sequentially(' changed')
                    be(page.locator('form')).to_have_attribute('data-edit-guard-state', 'dirty')
                    active = page.evaluate('navigator.userActivation?.hasBeenActive ?? null')
                    assert active is not False, 'Real input gesture did not activate the page'
                    seen = []
                    def dialog(d):
                        seen.append(d.type)
                        d.dismiss()
                    page.on('dialog', dialog)
                    error = None
                    if method == 'protocol':
                        try:
                            page.reload(timeout=3000, wait_until='load')
                        except Error as e:
                            error = type(e).__name__
                    else:
                        with page.expect_event('dialog', timeout=5000) as result:
                            page.evaluate('() => { setTimeout(() => window.location.reload(), 0); }')
                        assert result.value.type == 'beforeunload' and seen == ['beforeunload']
                        be(field).to_have_value('Saved changed')
                        be(page.locator('form')).to_have_attribute('data-edit-guard-state', 'dirty')
                    rows.append({'engine': name, 'method': method, 'activated': active,
                                 'dialogs': list(seen), 'automation_error': error,
                                 'retained_input': field.input_value() == 'Saved changed'})
                    if method == 'location':
                        field.fill('Saved')
                        be(page.locator('form')).to_have_attribute('data-edit-guard-state', 'clean')
                        seen.clear()
                        with page.expect_navigation(wait_until='load'):
                            page.evaluate('() => { setTimeout(() => window.location.reload(), 0); }')
                        assert seen == [] and page.url == url
                        be(page.locator('form')).to_have_attribute('data-edit-guard-state', 'clean')
                        print('PASS Real Location.reload cancels dirty departure and reloads cleanly without a prompt.', flush=True)
                    context.close()
            finally:
                browser.close()
    finally:
        server.shutdown()
        server.server_close()
        thread.join()
    print(json.dumps(rows, sort_keys=True), flush=True)


if __name__ == '__main__':
    main()
