#!/usr/bin/env python3
"""Real packaged PHP/SQL/HTTP tests; disposable databases only, non-root runner.
This is not proof of Apache rules, production TLS, or live shared hosting.
"""
from __future__ import annotations
import contextlib
import fcntl
import hashlib
import http.cookiejar
import json
import os
import re
import secrets
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
CHECKS = 0

def expect(condition: bool, label: str) -> None:
    global CHECKS
    if not condition:
        raise AssertionError(label)
    CHECKS += 1
    print('PASS ' + label, flush=True)

def dashboard(url: str) -> bool:
    return urllib.parse.urlparse(url).path.endswith(('/admin/', '/admin/index.php'))

class Client:
    def __init__(self, base: str):
        self.base = base
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies))
    def request(self, path: str, data: dict[str, str] | None = None):
        encoded = None if data is None else urllib.parse.urlencode(data).encode()
        request = urllib.request.Request(self.base + path, encoded)
        try:
            response = self.opener.open(request, timeout=30)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            return response.status, response.read().decode('utf-8', errors='replace'), response.headers, response.url
    def form(self, path: str, data: dict[str, str]):
        status, body, _, _ = self.request(path)
        token = re.search(r'name="csrf" value="([a-f0-9]{64})"', body)
        if status != 200 or token is None:
            raise AssertionError('Expected usable form: ' + path)
        return self.request(path, {**data, 'csrf': token.group(1)})

class Site:
    def __init__(self, area: Path, archive: Path, code: str, subfolder: str):
        self.home = area
        self.docroot = area / 'public_html'
        self.root = self.docroot / subfolder if subfolder else self.docroot
        self.root.mkdir(parents=True)
        with zipfile.ZipFile(archive) as bundle:
            bundle.extractall(self.root)
        self.code = code
        self.prefix = 'sgci_' + secrets.token_hex(4) + '_'
        self.env = {**os.environ, 'SPIRIT_TESTING':'1', 'SPIRIT_DB_PREFIX':self.prefix, 'SPIRIT_ACCOUNT_HOME':str(area)}
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0))
            port = sock.getsockname()[1]
        self.base = f'http://127.0.0.1:{port}/' + (subfolder + '/' if subfolder else '')
        self.log = (area / 'php.log').open('w+')
        self.process = subprocess.Popen(['php','-d','display_errors=0','-S',f'127.0.0.1:{port}','-t',str(self.docroot)], cwd=self.root, env=self.env, stdout=self.log, stderr=self.log)
        self.client = Client(self.base)
        self.runtime = area / ('.spirit-' + hashlib.sha256(str(self.root.resolve()).encode()).hexdigest()[:20])
        self.password = secrets.token_urlsafe(24)
        self.owner = 'owner@example.test'
        for _ in range(100):
            try:
                self.client.request('install/')
                break
            except (OSError, urllib.error.URLError):
                time.sleep(.1)
        else:
            self.process.terminate(); self.process.wait(timeout=10); self.log.close()
            raise AssertionError('PHP server did not start')
    def database(self, command: str):
        result = subprocess.run(['php', str(ROOT/'tests/db_fixture.php'), command], env=self.env, capture_output=True, text=True, timeout=30)
        if result.returncode:
            raise RuntimeError('Database fixture failed: ' + command)
        return json.loads(result.stdout)
    def input(self):
        return {'action':'install','site_url':self.base.rstrip('/'),'db_host':self.env.get('SPIRIT_DB_HOST','127.0.0.1'),'db_port':self.env.get('SPIRIT_DB_PORT','3306'),'db_name':self.env['SPIRIT_DB_NAME'],'db_user':self.env['SPIRIT_DB_USER'],'db_password':self.env['SPIRIT_DB_PASSWORD'],'db_prefix':self.prefix,'email':self.owner,'owner_name':'Test Owner','password':self.password,'password_confirm':self.password}
    def claim(self):
        status, body, _, _ = self.client.form('install/', {'action':'claim','setup_code':self.code})
        expect(status == 200 and 'Database connection' in body, 'Correct package code opens hosting configuration')
    def login(self, client: Client | None = None, password: str | None = None):
        client = client or self.client
        return client.form('admin/login.php', {'email':self.owner,'password':password or self.password})
    def close(self):
        self.process.terminate()
        try:
            self.process.wait(timeout=10)
        except subprocess.TimeoutExpired:
            self.process.kill(); self.process.wait()
        self.log.close()
        if (self.root/'install').exists():
            (self.root/'install').chmod(0o700)
        self.database('cleanup')

@contextlib.contextmanager
def site(area: Path, archive: Path, code: str, subfolder: str = ''):
    instance = Site(area, archive, code, subfolder)
    try:
        yield instance
    finally:
        instance.close()

def normal_journey(s: Site, blocked_cleanup: bool):
    status, body, _, _ = s.client.form('install/', {'action':'claim','setup_code':'incorrect'})
    expect(status == 200 and 'incorrect' in body, 'Wrong claim code denied')
    expect(s.client.request('install/', {'action':'claim','setup_code':s.code,'csrf':'wrong'})[0] == 403, 'Invalid setup CSRF denied')
    s.claim()
    with (s.runtime/'install-operation.lock').open('a+b') as lock:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        status, body, _, _ = s.client.form('install/', s.input())
        expect(status == 200 and 'already running' in body, 'Concurrent installer operation is rejected')
    if blocked_cleanup:
        (s.root/'install').chmod(0o500)
    status, _, _, url = s.client.form('install/', s.input())
    expect(status == 200 and url.endswith('/admin/login.php'), 'Fresh database install reaches owner sign-in')
    expect(s.database('count') == {'users':1,'state':'installed'}, 'Exactly one owner and installed database state')
    expect((s.runtime/'config.php').is_file() and (s.runtime/'installed.lock').is_file(), 'Private config and completion lock persisted')
    expect((s.runtime/'config.php').stat().st_mode & 0o077 == 0, 'Database configuration permissions are private')
    expect(not (s.root/'config/local.php').exists(), 'No credentials placed in public source config')
    if blocked_cleanup:
        expect((s.root/'install/index.php').exists(), 'Host-denied deletion is not reported as removal')
        expect(s.client.request('install/')[0] == 410, 'Retained installer is permanently locked')
    else:
        expect(not (s.root/'install').exists(), 'Installer directory physically removed')
    anonymous = Client(s.base)
    status, _, _, url = anonymous.request('admin/index.php')
    expect(status == 200 and url.endswith('/admin/login.php'), 'Anonymous admin access redirects to sign-in')
    status, _, _, url = s.login()
    expect(status == 200 and dashboard(url), 'Owner login reaches dashboard')
    second = Client(s.base)
    expect(dashboard(s.login(second)[3]), 'Second owner session can sign in')
    status, _, headers, _ = s.client.request('admin/settings.php')
    expect(status == 200 and 'no-store' in headers.get('Cache-Control',''), 'Private admin pages prohibit caching')
    expect(all(c.has_nonstandard_attr('HttpOnly') for c in s.client.cookies), 'Session cookie is HttpOnly')
    expect(all(c.has_nonstandard_attr('SameSite') for c in s.client.cookies), 'Session cookie has SameSite policy')
    expect(s.client.request('assets/css/app.css')[0] == 200, 'Stylesheet resolves under the installation base path')
    expect(s.client.request('admin/settings.php', {'csrf':'wrong','site_name':'Unauthorized'})[0] == 403, 'Admin settings reject invalid CSRF')
    status, _, _, _ = s.client.form('admin/settings.php', {'site_name':'Integration Band','tagline':'Verified setting','welcome':'<script>alert(1)</script>'})
    expect(status == 200, 'Owner saves website settings')
    body = s.client.request('')[1]
    expect('Integration Band' in body and '&lt;script&gt;' in body and '<script>alert(1)' not in body, 'Saved public content persists and is escaped')
    s.database('editor')
    expect(s.client.request('admin/settings.php')[0] == 403, 'Non-owner role cannot access owner settings')
    s.database('owner')
    status, _, _, _ = s.client.form('admin/account.php', {'current_password':s.password,'new_password':s.password+'N','confirm_password':s.password+'N'})
    expect(status == 200, 'Owner changes password')
    expect(second.request('admin/index.php')[3].endswith('/admin/login.php'), 'Password change revokes the other session')
    s.password += 'N'
    if blocked_cleanup:
        (s.root/'install').chmod(0o700)
        expect(s.client.form('admin/installation.php', {})[0] == 200, 'Owner retries installer cleanup')
        expect(not (s.root/'install').exists(), 'Cleanup retry physically removes installer after permission repair')
    status, body, _, _ = s.client.request('admin/index.php')
    token = re.search(r'name="csrf" value="([a-f0-9]{64})"', body)
    expect(token is not None, 'Dashboard exposes CSRF-protected logout')
    status, _, _, url = s.client.request('admin/logout.php', {'csrf':token.group(1)})
    expect(status == 200 and url.endswith('/admin/login.php'), 'Logout returns to sign-in')
    expect(s.client.request('admin/index.php')[3].endswith('/admin/login.php'), 'Logged-out session cannot reopen admin')
    expect(dashboard(s.login()[3]), 'New password works after logout')

def recovery_journey(s: Site):
    s.claim()
    s.database('collision')
    status, body, _, _ = s.client.form('install/', s.input())
    expect(status == 200 and 'prefix is already in use' in body, 'Existing database prefix is refused')
    expect(s.database('sentinel')['id'] == 42, 'Existing data survives rejected installation')
    s.database('cleanup')
    schema = s.root/'database/migrations/001.sql'
    original = schema.read_text()
    schema.write_text(original + '\nINVALID SQL TO SIMULATE AN INTERRUPTED MIGRATION;\n')
    status, body, _, _ = s.client.form('install/', s.input())
    expect(status == 200 and 'provisioning failed' in body, 'Interrupted migration reports failure without claiming completion')
    expect((s.runtime/'pending.php').exists() and not (s.runtime/'config.php').exists(), 'Interrupted installation retains recovery state without published config')
    changed = s.input(); changed['db_prefix'] = 'sgci_' + secrets.token_hex(4) + '_'
    expect('different database settings' in s.client.form('install/',changed)[1], 'Retry cannot silently switch databases or prefixes')
    schema.write_text(original)
    status, _, _, url = s.client.form('install/', s.input())
    expect(status == 200 and url.endswith('/admin/login.php'), 'Same-install retry completes after interrupted migration')
    expect(s.database('count')['users'] == 1, 'Recovery does not duplicate the owner')

def build_package(destination: Path):
    subprocess.run(['python3',str(ROOT/'tools/package.py'),'--output',str(destination)],check=True,capture_output=True)
    archive = next(destination.glob('*-Full.zip'))
    receipt = next(destination.glob('*-PRIVATE-SETUP.txt'))
    code = re.search(r'^Setup code: (.+)$',receipt.read_text(),re.MULTILINE).group(1)
    with zipfile.ZipFile(archive) as bundle:
        expect(not any('PRIVATE-SETUP' in name for name in bundle.namelist()), 'Private setup receipt excluded from deployment ZIP')
        manifest = json.loads(bundle.read('docs/PACKAGE-MANIFEST.json'))
        expect(all(hashlib.sha256(bundle.read(name)).hexdigest() == digest for name,digest in manifest['files'].items()), 'Every deployment manifest hash matches its file')
    return archive, code

def main():
    if os.geteuid() == 0:
        raise RuntimeError('Run integration tests as an unprivileged user, not root.')
    if os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Set SPIRIT_TESTING=1 and isolated SPIRIT_DB_* credentials.')
    env = {**os.environ,'SPIRIT_DB_PREFIX':'sgci_00000000_'}
    for _ in range(90):
        result = subprocess.run(['php',str(ROOT/'tests/db_fixture.php'),'ping'],env=env,capture_output=True,text=True)
        if result.returncode == 0:
            print('Database ready: ' + str(json.loads(result.stdout)['database']), flush=True); break
        time.sleep(1)
    else:
        raise RuntimeError('Disposable database did not become ready; driver/credentials/service must be checked.')
    with tempfile.TemporaryDirectory(prefix='spirit-integration-') as directory:
        temp = Path(directory)
        archive, code = build_package(temp/'packages')
        for folder, label, blocked in [('', 'root', False),('band', 'subfolder', True)]:
            print('SCENARIO ' + label, flush=True)
            with site(temp/label,archive,code,folder) as current:
                normal_journey(current,blocked)
        with site(temp/'recovery',archive,code,'recovery') as current:
            recovery_journey(current)
    print(f'{CHECKS} integration assertions passed.', flush=True)

if __name__ == '__main__':
    main()
