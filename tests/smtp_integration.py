#!/usr/bin/env python3
"""Exercise the real TLS SMTP transport against disposable loopback servers.
No SMTP provider, internet mailbox, production credential, or TLS bypass is used.
"""
from __future__ import annotations
import base64
import email
import os
import socket
import ssl
import subprocess
import tempfile
import threading
import time
from pathlib import Path
from http_integration import build_package, expect, site
from communication_integration import fixture


def certificates(path: Path):
    path.mkdir()
    commands = [
        ['req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-days', '1', '-subj', '/CN=Spirit disposable test CA', '-addext', 'basicConstraints=critical,CA:TRUE', '-keyout', 'ca.key', '-out', 'ca.pem'],
        ['req', '-new', '-newkey', 'rsa:2048', '-nodes', '-subj', '/CN=localhost', '-keyout', 'server.key', '-out', 'server.csr'],
        ['x509', '-req', '-in', 'server.csr', '-CA', 'ca.pem', '-CAkey', 'ca.key', '-CAcreateserial', '-days', '1', '-extfile', 'server.ext', '-out', 'server.pem'],
    ]
    (path / 'server.ext').write_text('subjectAltName=DNS:localhost\nbasicConstraints=critical,CA:FALSE\nkeyUsage=digitalSignature,keyEncipherment\nextendedKeyUsage=serverAuth\n')
    for command in commands:
        subprocess.run(['openssl', *command], cwd=path, capture_output=True, check=True, timeout=15)
    for key in path.glob('*.key'):
        key.chmod(0o600)
    return path / 'ca.pem', path / 'server.pem', path / 'server.key'


class Server:
    def __init__(self, cert: Path, key: Path, mode='accept', implicit=False):
        self.mode = mode; self.implicit = implicit; self.message = b''; self.authenticated = False
        self.context = ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER)
        self.context.load_cert_chain(str(cert), str(key))
        self.listener = socket.socket(); self.listener.bind(('127.0.0.1', 0)); self.listener.listen(1); self.listener.settimeout(5)
        self.port = self.listener.getsockname()[1]
        self.thread = threading.Thread(target=self.run, daemon=True); self.thread.start()

    def run(self):
        connection = reader = None
        try:
            connection, _ = self.listener.accept(); connection.settimeout(5)
            if self.implicit:
                connection = self.context.wrap_socket(connection, server_side=True)
            reader = connection.makefile('rb')
            if self.mode == 'slow':
                time.sleep(1); return
            connection.sendall(b'220 localhost disposable SMTP\r\n')
            stage = 'command'; username = ''
            while True:
                line = reader.readline(4096)
                if not line:
                    return
                line = line.rstrip(b'\r\n')
                if stage == 'user':
                    username = base64.b64decode(line).decode(); stage = 'password'
                    connection.sendall(b'334 UGFzc3dvcmQ6\r\n'); continue
                if stage == 'password':
                    password = base64.b64decode(line).decode(); stage = 'command'
                    self.authenticated = username == 'fixture-user' and password == 'fixture-password'
                    code = b'535 Authentication refused\r\n' if self.mode == 'auth_fail' or not self.authenticated else b'235 Authenticated\r\n'
                    connection.sendall(code); continue
                command = line.decode('ascii')
                if command.startswith('EHLO '):
                    connection.sendall(b'250-localhost\r\n250-STARTTLS\r\n250 AUTH LOGIN\r\n')
                elif command == 'STARTTLS':
                    connection.sendall(b'220 Start TLS\r\n'); reader.close()
                    connection = self.context.wrap_socket(connection, server_side=True)
                    reader = connection.makefile('rb')
                elif command == 'AUTH LOGIN':
                    if not isinstance(connection, ssl.SSLSocket):
                        raise AssertionError('Credentials were attempted before TLS')
                    stage = 'user'; connection.sendall(b'334 VXNlcm5hbWU6\r\n')
                elif command.startswith('MAIL FROM:') or command.startswith('RCPT TO:'):
                    if self.mode == 'predata_drop':
                        return
                    connection.sendall(b'250 Accepted\r\n')
                elif command == 'DATA':
                    connection.sendall(b'354 Send message\r\n'); parts = []
                    while True:
                        part = reader.readline(4096)
                        if not part:
                            return
                        if part == b'.\r\n':
                            break
                        parts.append(part[1:] if part.startswith(b'..') else part)
                    self.message = b''.join(parts)
                    if self.mode == 'lost_ack':
                        return
                    connection.sendall(b'451 Temporary refusal\r\n' if self.mode == 'data_reject' else b'250 Queued\r\n')
                elif command == 'QUIT':
                    if self.mode != 'lost_quit':
                        connection.sendall(b'221 Goodbye\r\n')
                    return
                else:
                    connection.sendall(b'500 Unknown command\r\n')
        except (OSError, ssl.SSLError):
            # Expected on certificate rejection and deliberately dropped sockets.
            pass
        finally:
            if reader:
                reader.close()
            if connection:
                connection.close()
            self.listener.close()

    def close(self):
        self.thread.join(7)
        if self.thread.is_alive():
            self.listener.close()
            raise AssertionError('Disposable SMTP fixture did not stop')


def main():
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Non-root disposable integration environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-smtp-') as directory:
        base = Path(directory); ca, cert, key = certificates(base / 'certificates')
        archive, code = build_package(base / 'packages')
        with site(base / 'website', archive, code, 'band') as current:
            current.claim(); current.client.form('install/', current.input())
            for mode, security, trust, host, success, uncertain in [
                ('accept', 'starttls', True, 'localhost', True, False),
                ('accept', 'tls', True, 'localhost', True, False),
                ('accept', 'starttls', False, 'localhost', False, False),
                ('accept', 'starttls', True, '127.0.0.1', False, False),
                ('auth_fail', 'starttls', True, 'localhost', False, False),
                ('predata_drop', 'starttls', True, 'localhost', False, False),
                ('data_reject', 'starttls', True, 'localhost', False, False),
                ('lost_ack', 'starttls', True, 'localhost', False, True),
                ('lost_quit', 'starttls', True, 'localhost', True, False),
                ('slow', 'starttls', True, 'localhost', False, False),
            ]:
                server = Server(cert, key, mode, security == 'tls')
                try:
                    result = fixture(current, 'smtp', {'port': server.port, 'security': security, 'host': host, 'seconds': .3 if mode == 'slow' else 3}, ca if trust else None)
                finally:
                    server.close()
                label = f'{mode}/{security}/trusted={trust}/host={host}'
                expect(result['ok'] == success, 'TLS SMTP result: ' + label)
                expect((result.get('class') == 'Spirit\\DeliveryUncertain') == uncertain, 'SMTP acceptance certainty: ' + label)
                if not trust or host != 'localhost':
                    expect(not server.authenticated, 'Invalid certificate or hostname is rejected before credentials')
                if success:
                    parsed = email.message_from_bytes(server.message)
                    expect(server.authenticated and parsed['To'] == '<fan@example.test>' and parsed.is_multipart(), 'Real authenticated transport preserves envelope and MIME')
                    expect('Generated test body' in parsed.get_payload(1).get_payload(decode=True).decode(), 'SMTP fixture receives actual HTML body bytes')
                if mode == 'data_reject':
                    expect(result['error'] == 'smtp_rejected_451', 'Explicit post-DATA rejection is definite, not uncertain')
                if mode == 'slow':
                    expect(result['elapsed'] < 1.5, 'Network deadline stops a stalled SMTP greeting')
    print('Real loopback STARTTLS/implicit-TLS, certificate and SMTP failure tests passed.', flush=True)


if __name__ == '__main__':
    main()
