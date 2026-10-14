#!/usr/bin/env python3
"""Actual cURL TLS/POST cPanel transport; loopback server, no live credentials."""
import json
import os
import ssl
import subprocess
import tempfile
import threading
import urllib.parse
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from http_integration import ROOT, expect
from smtp_integration import certificates


def main():
    if os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Disposable testing environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-cpanel-tls-') as directory:
        base=Path(directory); ca,_,_=certificates(base/'ca')
        (base/'server.ext').write_text('subjectAltName=DNS:cpanel.example.com\nbasicConstraints=critical,CA:FALSE\nkeyUsage=digitalSignature,keyEncipherment\nextendedKeyUsage=serverAuth\n')
        for cmd in [
            ['req','-new','-newkey','rsa:2048','-nodes','-subj','/CN=cpanel.example.com','-keyout',str(base/'server.key'),'-out',str(base/'server.csr')],
            ['x509','-req','-in',str(base/'server.csr'),'-CA',str(ca),'-CAkey',str(ca.parent/'ca.key'),'-CAcreateserial','-days','1','-extfile',str(base/'server.ext'),'-out',str(base/'server.pem')],
        ]:subprocess.run(['openssl',*cmd],check=True,capture_output=True,timeout=15)
        for mode in ['ok','wrapped','reject','unauthorized','redirect','malformed','oversize','untrusted','mismatch']:
            received=[]
            class Handler(BaseHTTPRequestHandler):
                def log_message(self,*args):pass
                def do_POST(self):
                    body=self.rfile.read(int(self.headers.get('Content-Length',0)))
                    received.append((self.path,dict(self.headers),body))
                    if mode=='unauthorized':self.send_response(401)
                    elif mode=='redirect':self.send_response(302);self.send_header('Location','https://cpanel.example.com:2083/steal')
                    else:self.send_response(200)
                    data={'status':0 if mode=='reject' else 1,'data':None,'errors':['PRIVATE-PROVIDER-ERROR'] if mode=='reject' else None}
                    if mode=='wrapped':data={'apiversion':3,'module':'Mysql','func':'create_user','result':data}
                    content=b'bad' if mode=='malformed' else b'x'*1048577 if mode=='oversize' else json.dumps(data).encode()
                    # A complete HTTP response has explicit framing. Otherwise a
                    # bare TLS socket close is correctly treated as transport loss.
                    self.send_header('Content-Type','application/json')
                    self.send_header('Content-Length',str(len(content)))
                    self.end_headers()
                    try:self.wfile.write(content)
                    except (OSError,ssl.SSLError):pass
            server=ThreadingHTTPServer(('127.0.0.1',0),Handler)
            context=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);context.load_cert_chain(str(base/'server.pem'),str(base/'server.key'))
            server.socket=context.wrap_socket(server.socket,server_side=True)
            thread=threading.Thread(target=server.serve_forever,daemon=True);thread.start()
            try:
                result=subprocess.run(['php',str(ROOT/'tests/cpanel_transport.php')],input=json.dumps({'port':server.server_port,'ca':str(ca) if mode!='untrusted' else None,'mismatch':mode=='mismatch'}),env=os.environ.copy(),text=True,capture_output=True,check=True,timeout=12)
                report=json.loads(result.stdout)
                expect(report['ok']==(mode in ['ok','wrapped']),'Real TLS cPanel transport result: '+mode+'; safe outcome='+json.dumps(report)+'; received='+str(len(received)))
                expect('PRIVATE-PROVIDER-ERROR' not in result.stdout and 'Synthetic+Password' not in result.stdout and 'T'*32 not in result.stdout,'Transport never reflects provider secrets')
                if mode in ['untrusted','mismatch']:expect(not received,'TLS identity rejection precedes token transmission')
                else:
                    path,headers,body=received[0]
                    expect(path=='/execute/Mysql/create_user' and len(received)==1,'No credential query string or redirected request')
                    expect(headers.get('Authorization')=='cpanel cpuser:'+'T'*32 and urllib.parse.parse_qs(body.decode())['password']==['Synthetic+Password&975!'],'Token header and form-encoded password bytes preserved over TLS')
            finally:server.shutdown();server.server_close();thread.join(3)
    print('cPanel TLS/certificate/body/redaction tests passed; no A2 API contacted.',flush=True)


if __name__=='__main__':main()
