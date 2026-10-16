#!/usr/bin/env python3
"""Read-only diagnostics and actual PHP oversized-body failure with no live services."""
from __future__ import annotations
import contextlib
import hashlib
import json
import os
import subprocess
import tempfile
import urllib.error
import urllib.request
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import token, save_content


def fixture(s, action, values=None):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
    result=subprocess.run(['php',str(ROOT/'tests/launch_fixture.php'),action],input=json.dumps(values or {}),env=env,capture_output=True,text=True,timeout=20)
    if result.returncode:raise AssertionError('Launch fixture failed: '+action+' '+result.stderr)
    return json.loads(result.stdout)


@contextlib.contextmanager
def limited_php(base):
    info=json.loads(subprocess.run(['php','-r','echo json_encode([php_ini_loaded_file(),php_ini_scanned_files()]);'],text=True,capture_output=True,check=True).stdout)
    base.mkdir();scan=base/'scan';scan.mkdir()
    (base/'php.ini').write_text(Path(info[0]).read_text() if info[0] else '')
    for i,source in enumerate(filter(None,(p.strip() for p in (info[1] or '').split(',')))):
        (scan/f'{i:03}.ini').write_text(Path(source).read_text())
    (scan/'zzz-launch.ini').write_text('post_max_size=32K\nupload_max_filesize=16K\n')
    before={k:os.environ.get(k) for k in ['PHPRC','PHP_INI_SCAN_DIR']}
    os.environ['PHPRC']=str(base/'php.ini');os.environ['PHP_INI_SCAN_DIR']=str(scan)
    try:yield
    finally:
        for k,v in before.items():
            if v is None:os.environ.pop(k,None)
            else:os.environ[k]=v


def report(s):
    status,body,headers,_=s.client.form('admin/launch/',{'action':'export'})
    expect(status==200 and 'application/json' in headers.get('Content-Type',''),'Owner receives a JSON support download')
    expect('no-store' in headers.get('Cache-Control','') and headers.get('Referrer-Policy')=='no-referrer' and 'attachment' in headers.get('Content-Disposition',''),'Support download is private, uncached and has a fixed attachment name')
    return json.loads(body)


def oversized(s, route, field, action):
    csrf=token(s.client.request(route)[1]);boundary='spirit-disposable-boundary'
    parts=[]
    for key,value in {'csrf':csrf,'action':action,'owner_password':s.password,'rights_confirmed':'1','trust_package':'1'}.items():
        parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{field}"; filename="synthetic.bin"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode()+b'x'*100000+b'\r\n')
    body=b''.join(parts)+f'--{boundary}--\r\n'.encode()
    request=urllib.request.Request(s.base+route,body,{'Content-Type':'multipart/form-data; boundary='+boundary})
    try:response=s.client.opener.open(request,timeout=20)
    except urllib.error.HTTPError as error:response=error
    with response:
        text=response.read().decode()
        expect(response.status==413 and 'PHP discarded' in text and 'no requested action' in text,'Oversized '+route+' explains PHP body rejection without executing an action')
        expect('This form expired' not in text and 'multipart/form-data' in text and s.password not in text,'Oversized response preserves usable page and never reflects owner password')


def journey(s):
    s.claim();s.client.form('install/',s.input());s.login()
    expect(Client(s.base).request('admin/launch/')[3].endswith('/admin/login.php'),'Anonymous visitors cannot access launch checks')
    expect(s.client.request('admin/launch/',{'action':'export','csrf':'bad'})[0]==403,'Export enforces existing CSRF protection')
    expect(s.client.form('admin/launch/',{'action':'enable_live_payments'})[0]==400,'Diagnostic endpoint cannot become a settings or live-enablement action')
    dormant=report(s)
    expect(dormant['check_states']['store']=='observed' and dormant['check_states']['payments']=='observed' and dormant['check_states']['policy']=='observed','Closed commerce is reported as a safe dormant launch state, not a false blocker')
    expect(dormant['check_states']['public_contact']=='observed' and dormant['check_states']['accounts']=='observed' and dormant['check_states']['sandbox_keys']=='observed' and dormant['check_states']['payment_mode']=='observed' and dormant['check_states']['catalog_count']=='observed' and dormant['check_states']['external_merchant']=='observed','Optional contact and dormant commerce extras do not create false launch warnings')
    expect(dormant['check_states']['mail_worker']=='review' and dormant['check_states']['payment_worker']=='observed','Fresh install distinguishes missing mail execution evidence from intentionally dormant payment maintenance')
    for role in ['content_editor','store_manager']:
        fixture(s,'role',{'role':role})
        expect(s.client.request('admin/launch/')[0]==403 and s.client.request('admin/launch/',{'action':'export'})[0]==403,role+' cannot see or export owner diagnostics')
    fixture(s,'role',{'role':'owner'})
    save_content(s,'page','PRIVATE-PAGE-SENTINEL',{'body':'PRIVATE-TEXT-SENTINEL'},slug='private-diagnostics',action='save')
    fixture(s,'configure')
    before=fixture(s,'fingerprint');config_hash=hashlib.sha256((s.runtime/'config.php').read_bytes()).hexdigest()
    status,body,headers,_=s.client.request('admin/launch/')
    expect(status==200 and 'Observations are not launch approval' in body and 'no-store' in headers.get('Cache-Control',''),'Launch page separates observations from acceptance')
    result=report(s);serialized=json.dumps(result)
    for forbidden in ['PRIVATE-CONFIG-SENTINEL','PRIVATE-PAGE-SENTINEL','PRIVATE-TEXT-SENTINEL','private-host.example.test','private-sender@example.test',s.env['SPIRIT_DB_PASSWORD'],s.env['SPIRIT_DB_NAME'],s.prefix,str(s.runtime),s.owner]:
        expect(forbidden not in serialized,'Support report omits private configuration, identities, paths and content')
    expect(result['check_states']['smtp']=='review' and result['check_states']['store']=='review' and result['check_states']['payments']=='review' and result['check_states']['policy']=='review','Configured SMTP and enabled commerce remain review-required rather than provider-verified')
    expect(result['check_states']['mail_worker']=='review' and result['check_states']['payment_worker']=='review','Enabled services require actual worker execution evidence')
    fixture(s,'heartbeats');result=report(s)
    expect(result['check_states']['mail_worker']=='observed' and result['check_states']['payment_worker']=='observed','Recent private worker heartbeats become observed without claiming mailbox or provider acceptance')
    expect(result['limits']['post_max_bytes']==32768 and result['limits']['upload_max_bytes']==16384,'Report reads the actual child PHP limits')
    expect(fixture(s,'fingerprint')==before,'Refreshing/exporting diagnostics leaves every database table unchanged')
    for route,field,action in [('admin/media/','file','upload'),('admin/backups/','backup','import'),('admin/upgrades/','package','stage')]:
        expect(s.client.request(route,{'csrf':'invalid'})[0]==403,'Normal-size '+route+' retains CSRF denial')
        oversized(s,route,field,action)
    expect(fixture(s,'fingerprint')==before and hashlib.sha256((s.runtime/'config.php').read_bytes()).hexdigest()==config_hash,'Oversized uploads cannot alter database content or private configuration')
    expect(not list((s.runtime/'backups').glob('backup-*')) and not (s.runtime/'updates').exists(),'Oversized import/update creates no snapshot, stage or recovery operation')
    fixture(s,'schema_bad')
    result=report(s)
    expect(result['check_states']['schema']=='attention' and result['check_states']['smtp']=='unavailable' and result['check_states']['content_page']=='unavailable','Missing or changed migration evidence does not certify dependent checks')
    fixture(s,'schema_restore');fixture(s,'service_offline')
    try:
        result=report(s);body=s.client.request('admin/launch/')[1]
        expect(result['check_states']['smtp']=='unavailable' and result['check_states']['payments']=='unavailable','A failing service query is reported as not checked')
        expect('SQLSTATE' not in body and s.prefix not in body and 'PRIVATE-CONFIG-SENTINEL' not in body,'Failed diagnostic queries do not expose driver or configuration data')
    finally:fixture(s,'service_restore')
    expect(fixture(s,'fingerprint')==before,'Failure diagnostics leave repaired original records unchanged')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Use non-root disposable SQL services.')
    with tempfile.TemporaryDirectory(prefix='spirit-launch-') as directory:
        base=Path(directory);archive,unused=build_package(base/'packages')
        with limited_php(base/'php'):
            for folder in ['', 'band']:
                with site(base/('root' if not folder else 'nested'),archive,unused,folder) as s:journey(s)
    print('Owner-only redacted diagnostics, read-only SQL and oversized PHP-body regressions passed.',flush=True)


if __name__=='__main__':main()
