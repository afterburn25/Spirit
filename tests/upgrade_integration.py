#!/usr/bin/env python3
"""Actual upload/staging/replacement/recovery, with no live deployment or payment."""
from __future__ import annotations
import hashlib
import io
import json
import os
import re
import secrets
import subprocess
import tempfile
import urllib.request
import urllib.error
import zipfile
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import token, save_content


def fixture(s, action, values=None):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
    result=subprocess.run(['php',str(ROOT/'tests/upgrade_fixture.php'),action],input=json.dumps(values or {}),text=True,capture_output=True,env=env,timeout=30)
    if result.returncode:raise AssertionError(result.stderr)
    return json.loads(result.stdout or '{}')


# A fixture must remain newer as the application moves from dev to RC/final.
_core=re.match(r'^(\d+)\.(\d+)\.(\d+)',(ROOT/'VERSION').read_text().strip())
if not _core:raise RuntimeError('Invalid source version for update fixture')
TEST_UPDATE_VERSION=f'{_core[1]}.{_core[2]}.{int(_core[3])+1}-dev.999'

def package_bytes(archive, mode='good'):
    with zipfile.ZipFile(archive) as z:files={name:z.read(name) for name in z.namelist() if name!='docs/PACKAGE-MANIFEST.json'}
    version=TEST_UPDATE_VERSION;files['VERSION']=(version+'\n').encode()
    files['app/UpgradeFixture.php']=b'<?php declare(strict_types=1); namespace Spirit; final class UpgradeFixture {}\n'
    files['docs/UPGRADE-FIXTURE.txt']=b'Isolated test version, not a real release.\n'
    if mode=='broken':files['includes/bootstrap.php']=files['includes/bootstrap.php'].replace(b"const SPIRIT_ROOT",b"throw new RuntimeException('Synthetic failed application startup');\nconst SPIRIT_ROOT")
    if mode=='schema':files['database/migrations/007.sql']=b'CREATE TABLE IF NOT EXISTS `{{prefix}}not_for_browser` (id INT PRIMARY KEY);\n'
    if mode=='kernel':files['admin/upgrades/RecoveryEngine.php']+=b'\n// Changed pinned kernel.\n'
    if mode=='php':files['app/UpgradeFixture.php']=b'<?php broken syntax ( ;'
    if mode=='bootstrap':files['includes/bootstrap.php']=files['includes/bootstrap.php'].replace(b"require_once __DIR__ . '/maintenance-entry.php';\n",b'')
    if mode=='secret':files['admin/upgrades/location.php']=b'<?php return "/tmp/attacker";'
    if mode=='traversal':files['../outside.php']=b'<?php echo 1;'
    if mode=='same':version=files['VERSION']=Path(ROOT/'VERSION').read_bytes();version=version.decode().strip()
    manifest={'version':version,'files':{n:hashlib.sha256(b).hexdigest() for n,b in files.items()}}
    if mode=='checksum':files['app/UpgradeFixture.php']+=b'\n// Unmanifested edit'
    buffer=io.BytesIO()
    with zipfile.ZipFile(buffer,'w',zipfile.ZIP_DEFLATED) as z:
        for name,body in files.items():
            if mode=='symlink' and name=='app/UpgradeFixture.php':
                entry=zipfile.ZipInfo(name);entry.create_system=3;entry.external_attr=(0o120777<<16);z.writestr(entry,body)
            else:z.writestr(name,body)
        if mode=='duplicate':z.writestr('APP/UpgradeFixture.php',files['app/UpgradeFixture.php'])
        z.writestr('docs/PACKAGE-MANIFEST.json',json.dumps(manifest))
    return buffer.getvalue()


def upload(s, body, *, trust='1', password=None):
    csrf=token(s.client.request('admin/upgrades/')[1]);boundary='SpiritUpgrade'+secrets.token_hex(12);parts=[]
    for k,v in {'csrf':csrf,'action':'stage','owner_password':s.password if password is None else password,'trust_package':trust}.items():parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode())
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="package"; filename="Spirit-test-only.zip"\r\nContent-Type: application/zip\r\n\r\n'.encode()+body+b'\r\n');parts.append(f'--{boundary}--\r\n'.encode())
    req=urllib.request.Request(s.base+'admin/upgrades/',b''.join(parts),{'Content-Type':'multipart/form-data; boundary='+boundary})
    try:r=s.client.opener.open(req,timeout=40)
    except urllib.error.HTTPError as e:r=e
    with r:return r.status,r.read().decode(),r.headers


def action(s, kind, **values):return s.client.form('admin/upgrades/',{'action':kind,'owner_password':s.password,**values})


def recovery_receipt(s):
    job=fixture(s,'state');result=action(s,'key',id=job['id']);key=re.search(r'Recovery key: ([a-f0-9]{64})',result[1]).group(1)
    expect(key not in json.dumps(job) and 'no-store' in result[2].get('Cache-Control',''),'Independent update key is encrypted at rest and downloaded privately')
    return job,key


def rescue(s, job, key):
    client=Client(s.base);return client.form('admin/upgrades/recover.php',{'checkpoint':job['id'],'recovery_key':key,'confirm':'ROLLBACK'})


def journey(s, archive):
    body=package_bytes(archive);original=(s.root/'VERSION').read_text();save_content(s,'page','Persistent story',{'body':'Business content remains current'},slug='update-story')
    expect(Client(s.base).request('admin/upgrades/')[3].endswith('/admin/login.php'),'Anonymous visitor cannot stage or install code')
    expect(s.client.request('admin/upgrades/',{'action':'apply','csrf':'bad'})[0]==403,'Update mutations require CSRF')
    expect('trust' in upload(s,body,trust='0')[1] and fixture(s,'state')=={},'Executable ZIP needs explicit publisher-trust acknowledgement')
    upload(s,body,password='incorrect');expect(fixture(s,'state')=={},'Owner password required before update preparation')
    reasons={'traversal':'unsafe path','symlink':'special files','duplicate':'unsafe path','secret':'unsafe path','kernel':'pinned recovery layer','schema':'pinned recovery layer','php':'PHP syntax','bootstrap':'early recovery guard','same':'newer version','checksum':'checksum or length'}
    for mode, reason in reasons.items():
        if mode!='traversal':fixture(s,'advance-owner-window')
        response=upload(s,package_bytes(archive,mode));state=fixture(s,'state')
        message=re.sub('<[^>]+>',' ',response[1])
        expect(reason in message and state=={},'Unsafe/incompatible package rejected by its actual validator: '+mode+'; '+message[-1400:])
    fixture(s,'advance-owner-window')
    expect((s.root/'VERSION').read_text()==original and not (s.root/'install').exists(),'Rejected packages preserve version and removed-installer state')
    response=upload(s,body);expect('Package validated and staged' in response[1],'Owner stages a complete newer package through actual multipart upload')
    job,key=recovery_receipt(s);expect((s.root/'VERSION').read_text()==original and job['state']=='staged','Staging does not replace live code')
    action(s,'apply',id=job['id'],saved_receipt='0');expect(fixture(s,'state')['state']=='staged','Installation requires recovery-receipt acknowledgement')
    action(s,'apply',id=job['id'],saved_receipt='1');job=fixture(s,'state')
    expect(job['state']=='awaiting_confirmation' and (s.root/'VERSION').read_text().strip()==TEST_UPDATE_VERSION,'Actual files are replaced and new application opens in verification mode')
    expect(job['pre_backup'] is not None,'A real encrypted pre-code-update data snapshot is retained')
    expect(Client(s.base).request('')[0]==503,'Persistent update marker blocks public requests after applying process exits')
    expect(rescue(s,job,'0'*64)[0]==200 and fixture(s,'state')['state']=='awaiting_confirmation','Wrong recovery key cannot restore code')
    action(s,'confirm',id=job['id']);expect(fixture(s,'state')['state']=='committed' and Client(s.base).request('')[0]==200,'Separate owner verification reopens the website')
    save_content(s,'page','Later preserved page',{'body':'New business data after code update'},slug='after-update')
    action(s,'rollback',id=job['id'])
    expect((s.root/'VERSION').read_text()==original and not (s.root/'app/UpgradeFixture.php').exists(),'Code rollback restores original version and removes update-added files')
    expect('New business data after code update' in Client(s.base).request('pages/?slug=after-update')[1],'Code rollback preserves newer database content')
    expect(not (s.root/'install').exists(),'Code upgrades never recreate the installer directory')
    expect('already been restored' in rescue(s,job,key)[1],'Completed code rollback consumes its recovery capability')
    # Apache .htaccess access assertions execute in apache_access.py against
    # real Apache/PHP/HTTPS, not this lightweight PHP development server.
    s.database('editor');expect(s.client.request('admin/upgrades/')[0]==403,'Non-owner content staff cannot replace application code');s.database('owner')


def failure_recovery(s, archive):
    original=(s.root/'VERSION').read_text();good=package_bytes(archive)
    upload(s,good);job,key=recovery_receipt(s);fixture(s,'damage-stage');action(s,'apply',id=job['id'],saved_receipt='1')
    expect((s.root/'VERSION').read_text()==original and fixture(s,'state')['state']=='staged','Tampered staged bytes cannot become executable code')
    fixture(s,'advance-owner-window');upload(s,good);job,key=recovery_receipt(s)
    (s.root/'docs/UPGRADE-FIXTURE.txt').write_text('Unrelated administrator change')
    action(s,'apply',id=job['id'],saved_receipt='1')
    expect('Unrelated administrator change'==(s.root/'docs/UPGRADE-FIXTURE.txt').read_text() and (s.root/'VERSION').read_text()==original,'File changed after staging is not overwritten')
    (s.root/'docs/UPGRADE-FIXTURE.txt').unlink()
    fixture(s,'advance-owner-window');upload(s,good);job,key=recovery_receipt(s);fixture(s,'interrupt')
    expect(Client(s.base).request('')[0]==503 and fixture(s,'state')['state']=='applying','Hard exit during partial copy retains a durable maintenance gate')
    expect('Previous code restored' in rescue(s,job,key)[1] and (s.root/'VERSION').read_text()==original,'Independent endpoint recovers a partially copied update after process death')
    fixture(s,'advance-owner-window');response=upload(s,package_bytes(archive,'broken'));job,key=recovery_receipt(s)
    action(s,'apply',id=job['id'],saved_receipt='1')
    expect(s.client.request('admin/upgrades/')[0]>=500,'Syntax-valid failing application startup is not mistaken for an accepted update')
    expect('Previous code restored' in rescue(s,job,key)[1] and s.client.request('admin/')[0]==200,'Pinned recovery works without loading a broken application bootstrap')
    fixture(s,'advance-owner-window');upload(s,good);job,key=recovery_receipt(s);action(s,'apply',id=job['id'],saved_receipt='1');action(s,'confirm',id=job['id'])
    fixture(s,'schema-change')
    try:expect('schema changed' in rescue(s,job,key)[1] and fixture(s,'state')['state']=='committed','Later schema changes block unsafe code downgrade')
    finally:fixture(s,'schema-revert')
    rescue(s,job,key);expect((s.root/'VERSION').read_text()==original,'Original schema permits verified code recovery again')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-upgrades-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for subfolder in ['', 'band']:
            with site(base/('root' if not subfolder else 'nested'),archive,code,subfolder) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s,archive)
        with site(base/'faults',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();failure_recovery(s,archive)
        # Use a separate installation for the real sensitive-action quota.
        with site(base/'rate-limit',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login()
            body=package_bytes(archive)
            for _ in range(12):upload(s,body,trust='0')
            response=upload(s,body)
            expect('Too many requests' in response[1] and fixture(s,'state')=={},'Sensitive owner rate limit blocks the thirteenth request without staging')
    print('Browser ZIP upgrade SQL/HTTP, isolation and independent recovery checks passed.',flush=True)


if __name__=='__main__':main()
