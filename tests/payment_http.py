#!/usr/bin/env python3
"""The actual bounded cURL HTTPS transport, against isolated TLS fixtures only."""
from __future__ import annotations
import http.server
import os
import ssl
import tempfile
import threading
import time
from pathlib import Path
from http_integration import build_package, expect, site
from smtp_integration import certificates
from payment_integration import fixture

class Server:
    def __init__(self,cert,key,mode):
        self.mode=mode;self.calls=0;self.posted=b''
        parent=self
        class Handler(http.server.BaseHTTPRequestHandler):
            def log_message(self,*args):pass
            def do_POST(self):
                parent.calls+=1;parent.posted=self.rfile.read(int(self.headers.get('Content-Length','0')))
                if parent.mode=='slow':time.sleep(.8)
                if parent.mode=='lost':self.connection.close();return
                status=302 if parent.mode=='redirect' else (503 if parent.mode=='error' else 200)
                body=b'{"messages":{"resultCode":"Ok"},"synthetic":true}'
                if parent.mode=='bom':body=b'\xef\xbb\xbf'+body
                if parent.mode=='invalid':body=b'not json'
                if parent.mode=='oversize':body=b'x'*1100000
                try:
                    self.send_response(status)
                    if status==302:self.send_header('Location','https://example.invalid/must-never-follow')
                    self.send_header('Content-Type','application/json');self.send_header('Content-Length',str(len(body)));self.end_headers();self.wfile.write(body)
                except (OSError,ssl.SSLError):pass
        self.server=http.server.ThreadingHTTPServer(('127.0.0.1',0),Handler)
        context=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);context.load_cert_chain(str(cert),str(key));self.server.socket=context.wrap_socket(self.server.socket,server_side=True)
        self.port=self.server.server_address[1];self.thread=threading.Thread(target=self.server.serve_forever,daemon=True);self.thread.start()
    def close(self):self.server.shutdown();self.server.server_close();self.thread.join(3)

def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-payment-http-') as directory:
        base=Path(directory);ca,cert,key=certificates(base/'certs');archive,code=build_package(base/'packages')
        with site(base/'website',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input())
            for mode,trust,host,success in [('ok',True,'localhost',True),('bom',True,'localhost',True),('ok',False,'localhost',False),('ok',True,'127.0.0.1',False),('redirect',True,'localhost',False),('error',True,'localhost',False),('invalid',True,'localhost',False),('oversize',True,'localhost',False),('lost',True,'localhost',False),('slow',True,'localhost',False)]:
                server=Server(cert,key,mode)
                try:
                    values={'port':server.port,'host':host,'seconds':.15 if mode=='slow' else 3}
                    if trust:values['ca']=str(ca)
                    result=fixture(s,'http',values)
                finally:server.close()
                expect(result['ok']==success,f'Bounded cURL TLS result: {mode}, trusted={trust}, host={host}')
                if success:expect(server.posted==b'{"synthetic":true}' and result['response']['synthetic'],'Actual HTTPS client transmits and parses expected bytes')
                if not trust or host=='127.0.0.1':expect(server.calls==0,'Invalid certificate/hostname is rejected before application POST')
                if mode=='redirect':expect(server.calls==1,'Payment redirects are not followed')
                if mode=='slow':expect(result['elapsed']<.7,'Stalled payment transport respects its overall deadline')
    print('Payment transport TLS, trust, redirect, size, deadline and malformed-response checks passed on loopback; no provider called.',flush=True)

if __name__=='__main__':main()
