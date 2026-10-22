#!/usr/bin/env python3
"""Real Apache/HTTPS access-rule checks; no database or live hosting deployment.
The built-in PHP HTTP server does not interpret .htaccess. These assertions run
against actual mod_rewrite/authz/SSL/PHP rather than a test-only route simulator.
"""
from __future__ import annotations
import grp
import os
import pwd
import shutil
import socket
import ssl
import subprocess
import tempfile
import time
import urllib.error
import urllib.request
import zipfile
from pathlib import Path
from http_integration import build_package, expect


def request(url, context):
    try:
        response = urllib.request.urlopen(url, context=context, timeout=5)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        return response.status, response.read().decode('utf-8', errors='replace'), response.headers


def configuration(base: Path, docroot: Path, port: int, php_module: Path):
    modules = ['mpm_prefork', 'authz_core', 'authz_host', 'mime', 'dir', 'rewrite', 'headers', 'env', 'ssl']
    lines = [f'ServerRoot "{base}"', f'PidFile "{base}/httpd.pid"', f'Listen 127.0.0.1:{port}', 'ServerName localhost',
             f'User {pwd.getpwuid(os.geteuid()).pw_name}', f'Group {grp.getgrgid(os.getegid()).gr_name}']
    for name in modules:
        module = Path('/usr/lib/apache2/modules') / f'mod_{name}.so'
        if not module.is_file():
            raise RuntimeError(f'Required Apache test module missing: {module}')
        lines.append(f'LoadModule {name}_module "{module}"')
    lines += [f'LoadModule php_module "{php_module}"', 'TypesConfig /etc/mime.types', 'DirectoryIndex index.php',
              f'ErrorLog "{base}/error.log"', 'LogLevel warn', f'DocumentRoot "{docroot}"',
              'SSLSessionCache none', 'SSLEngine on', 'SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1',
              f'SSLCertificateFile "{base}/cert.pem"', f'SSLCertificateKeyFile "{base}/key.pem"',
              '<Directory />', '    Require all denied', '</Directory>',
              f'<Directory "{docroot}">', '    Options -Indexes +FollowSymLinks', '    AllowOverride All', '    Require all granted', '</Directory>',
              '<FilesMatch "\\.php$">', '    SetHandler application/x-httpd-php', '</FilesMatch>']
    return '\n'.join(lines) + '\n'


def main():
    if os.geteuid() == 0:
        raise RuntimeError('Apache verification runs as a non-root user on a loopback high port.')
    binary = shutil.which('apache2')
    php_module = Path(os.environ.get('SPIRIT_APACHE_PHP_MODULE', '/usr/lib/apache2/modules/libphp8.4.so'))
    if not binary or not php_module.is_file():
        raise RuntimeError('Install test-only Apache and its PHP module before this suite; do not skip these checks.')
    with tempfile.TemporaryDirectory(prefix='spirit-apache-access-') as directory:
        base = Path(directory)
        archive, _ = build_package(base / 'packages')
        docroot = base / 'public_html'; docroot.mkdir()
        roots = [docroot, docroot / 'band']
        for root in roots:
            root.mkdir(exist_ok=True)
            with zipfile.ZipFile(archive) as bundle:
                bundle.extractall(root)
            env = {**os.environ, 'SPIRIT_ACCOUNT_HOME': str(base), 'SPIRIT_FIXTURE_ROOT': str(root), 'SPIRIT_FIXTURE_DOCUMENT_ROOT': str(docroot)}
            script = r'''$_SERVER['DOCUMENT_ROOT']=getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
require getenv('SPIRIT_FIXTURE_ROOT').'/includes/bootstrap.php';
Spirit\Filesystem::prepare(Spirit\Paths::runtime(),$_SERVER['DOCUMENT_ROOT']);
Spirit\UpdateManager::initialize();
echo Spirit\Paths::runtime();'''
            result = subprocess.run(['php', '-r', script], env=env, text=True, capture_output=True, timeout=10)
            if result.returncode:
                raise AssertionError(result.stderr)
            expect(Path(result.stdout).is_dir(), 'Private recovery location provisioned without needing public secrets')
            (root / 'server-check.php').write_text('<?php header("Content-Type: text/plain"); echo "PHP executes rather than exposing source";')
        subprocess.run(['openssl', 'req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-days', '1', '-subj', '/CN=localhost',
                        '-addext', 'subjectAltName=DNS:localhost', '-addext', 'basicConstraints=critical,CA:TRUE',
                        '-keyout', str(base/'key.pem'), '-out', str(base/'cert.pem')], check=True, capture_output=True, timeout=15)
        (base/'key.pem').chmod(0o600)
        context = ssl.create_default_context(cafile=str(base/'cert.pem'))
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0)); port = sock.getsockname()[1]
        config = base/'httpd.conf'; config.write_text(configuration(base, docroot, port, php_module))
        subprocess.run([binary, '-t', '-f', str(config)], check=True, capture_output=True, timeout=10)
        with (base/'process.log').open('w') as log:
            server = subprocess.Popen([binary, '-f', str(config), '-DFOREGROUND'], stdout=log, stderr=log, start_new_session=True)
            try:
                url = f'https://localhost:{port}/'
                for _ in range(80):
                    try:
                        request(url+'server-check.php', context); break
                    except OSError:
                        if server.poll() is not None:
                            raise AssertionError((base/'process.log').read_text() + (base/'error.log').read_text())
                        time.sleep(.05)
                else:
                    raise AssertionError('Apache HTTPS listener did not start')
                for prefix in ['', 'band/']:
                    status, body, _ = request(url+prefix+'server-check.php', context)
                    expect(status == 200 and body == 'PHP executes rather than exposing source', 'Actual Apache PHP handler executes under verified HTTPS: '+(prefix or 'root'))
                    status, body, headers = request(url+prefix+'assets/css/app.css', context)
                    expect(status == 200 and 'nosniff' == headers.get('X-Content-Type-Options'), 'Public stylesheet remains accessible with deployed response headers')
                    for path in ['admin/upgrades/RecoveryEngine.php', 'admin/upgrades/location.php', 'app/UpdateManager.php', 'config/README.md', 'storage/README.md', 'uploads/README.md', 'config/database/migrations/001.sql', 'includes/bootstrap.php', 'docs/UPGRADES.md', 'VERSION', 'install/access.php']:
                        status, body, _ = request(url+prefix+path, context)
                        expect(status == 403 and '<?php' not in body and str(base) not in body, 'Deployed Apache rules deny private/support file: '+prefix+path)
                    status, body, headers = request(url+prefix+'admin/upgrades/recover.php', context)
                    expect(status == 200 and 'Restore previous code' in body and 'no-store' in headers.get('Cache-Control', ''), 'Independent recovery form opens through Apache HTTPS at '+(prefix or 'root'))
            finally:
                server.terminate()
                try:
                    server.wait(timeout=5)
                except subprocess.TimeoutExpired:
                    server.kill(); server.wait()
    print('Actual Apache/HTTPS/PHP access rules passed at root and subfolder.', flush=True)


if __name__ == '__main__':
    main()
