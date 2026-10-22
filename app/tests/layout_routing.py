#!/usr/bin/env python3
"""Real Apache routing contract with disposable JSON handler probes.
Uses shipped .htaccess, index.php, dispatcher, route map, bootstrap and Paths.
Only business handlers are replaced in the isolated test deployment; this is
not a substitute for the separate database/application integration suites.
"""
from __future__ import annotations
import json, os, re, shutil, socket, ssl, subprocess, tempfile, time, urllib.error, urllib.request, zipfile
from pathlib import Path
from apache_access import configuration
from http_integration import build_package, expect

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, request, fp, code, msg, headers, newurl):
        return None

def main():
    if os.geteuid() == 0:
        raise RuntimeError('Run as an unprivileged user; no production data is used.')
    apache = shutil.which('apache2')
    module = Path(os.environ.get('SPIRIT_APACHE_PHP_MODULE','/usr/lib/apache2/modules/libphp8.4.so'))
    if not apache or not module.exists():
        raise RuntimeError('Real Apache/PHP is required for this test.')
    with tempfile.TemporaryDirectory(prefix='spirit-route-contract-') as temporary:
        area=Path(temporary); archive,_=build_package(area/'packages')
        doc=area/'public_html'; doc.mkdir()
        # A neutral folder name avoids occupying the real /band/ route.
        for root in [doc,doc/'subsite']:
            root.mkdir(exist_ok=True)
            with zipfile.ZipFile(archive) as bundle: bundle.extractall(root)
            for handler in (root/'app/http').rglob('*.php'):
                name=handler.relative_to(root).as_posix()
                handler.write_text("<?php\nrequire dirname(__DIR__,3).'/includes/bootstrap.php';\nheader('Content-Type: application/json');\necho json_encode(['handler'=>"+repr(name)+",'method'=>$_SERVER['REQUEST_METHOD'],'base'=>Spirit\\Paths::basePath(),'query'=>$_GET,'body'=>file_get_contents('php://input')]);\n")
            env={**os.environ,'SPIRIT_ACCOUNT_HOME':str(area),'SPIRIT_FIXTURE_ROOT':str(root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(doc)}
            setup="$_SERVER['DOCUMENT_ROOT']=getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');require getenv('SPIRIT_FIXTURE_ROOT').'/includes/bootstrap.php';Spirit\\Filesystem::prepare(Spirit\\Paths::runtime(),$_SERVER['DOCUMENT_ROOT']);Spirit\\UpdateManager::initialize();"
            subprocess.run(['php','-r',setup],env=env,check=True,capture_output=True,timeout=10)
        routes=json.loads(subprocess.check_output(['php','-r',"echo json_encode(require $argv[1]);",str(doc/'includes/public-routes.php')],text=True))
        expect(len(routes)==44,'All 44 existing public URL aliases are explicit')
        subprocess.run(['openssl','req','-x509','-newkey','rsa:2048','-nodes','-days','1','-subj','/CN=localhost','-addext','subjectAltName=DNS:localhost','-addext','basicConstraints=critical,CA:TRUE','-keyout',str(area/'key.pem'),'-out',str(area/'cert.pem')],check=True,capture_output=True,timeout=15)
        (area/'key.pem').chmod(0o600)
        context=ssl.create_default_context(cafile=str(area/'cert.pem'))
        opener=urllib.request.build_opener(NoRedirect(),urllib.request.HTTPSHandler(context=context))
        with socket.socket() as sock: sock.bind(('127.0.0.1',0)); port=sock.getsockname()[1]
        conf=area/'httpd.conf';conf.write_text(configuration(area,doc,port,module)+f'SetEnv SPIRIT_ACCOUNT_HOME "{area}"\nSetEnv SPIRIT_TESTING 1\n')
        def fetch(path,body=None):
            request=urllib.request.Request(f'https://localhost:{port}'+path,data=body,headers={'Content-Type':'application/x-www-form-urlencoded'})
            try:response=opener.open(request,timeout=5)
            except urllib.error.HTTPError as error:response=error
            with response:return response.status,response.read().decode('utf-8',errors='replace'),response.headers
        with (area/'process.log').open('w') as log:
            server=subprocess.Popen([apache,'-f',str(conf),'-DFOREGROUND'],stdout=log,stderr=log,start_new_session=True)
            try:
                for _ in range(80):
                    try:fetch('/assets/css/app.css');break
                    except OSError:
                        if server.poll() is not None:raise AssertionError((area/'process.log').read_text())
                        time.sleep(.05)
                for prefix in ['', '/subsite']:
                    for path,handler in routes.items():
                        query='slug=example-release&probe=one%26two&value=%2B'
                        code,text,headers=fetch(prefix+path+'?'+query)
                        expect(code==200,f'GET resolves through Apache: {prefix+path}')
                        result=json.loads(text)
                        expect(result=={'handler':handler,'method':'GET','base':prefix,'query':{'slug':'example-release','probe':'one&two','value':'+'},'body':''},f'Exact handler, query and base preserved: {prefix+path}')
                        code,text,_=fetch(prefix+path+'?keep=1',b'csrf=test-only&payload=a%26b')
                        result=json.loads(text) if code==200 else {}
                        expect(result.get('handler')==handler and result.get('method')=='POST' and result.get('body')=='csrf=test-only&payload=a%26b' and result.get('query')=={'keep':'1'},f'POST method/body are not redirected or altered: {prefix+path}')
                    for path in [p.rstrip('/') for p in routes if p.endswith('/')]:
                        code,_,headers=fetch(prefix+path+'?keep=1',b'payload=still-post')
                        expect(code==308 and headers.get('Location')==prefix+path+'/?keep=1','Directory-slash canonical redirect preserves method contract: '+prefix+path)
                    for path in ['/unknown','/music/not-real.php','/payments/','/pages/%61nalytics.php','/music/%2e%2e/secret.php']:
                        code,text,_=fetch(prefix+path)
                        expect(code==404 and '<?php' not in text,'Unmapped or encoded route rejected: '+prefix+path)
                    for path in ['/app/http/music/index.php','/app/views/public/header.php','/config/database/migrations/001.sql','/includes/public-routes.php','/docs/DIRECTORY_LAYOUT.md']:
                        code,text,_=fetch(prefix+path)
                        expect(code==403 and '<?php' not in text,'Private grouped source denied by Apache: '+prefix+path)
                    code,_,headers=fetch(prefix+'/assets/css/app.css')
                    expect(code==200 and headers.get('X-Content-Type-Options')=='nosniff','Static assets bypass front controller safely')
            finally:
                server.terminate()
                try:server.wait(timeout=5)
                except subprocess.TimeoutExpired:server.kill();server.wait()
        print('Actual Apache HTTPS exact routing passed at root and subdirectory. Business handlers were disposable probes.',flush=True)
if __name__=='__main__':main()
