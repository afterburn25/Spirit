#!/usr/bin/env python3
"""Encrypted snapshots, authentic reads, safe restores and real lock/failure paths."""
from __future__ import annotations
import hashlib
import json
import os
import re
import secrets
import subprocess
import tempfile
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import save_content, seed_media_and_music, upload, png_bytes, token
from commerce_integration import seed_catalog, adjust


def fixture(s,action,values=None):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
    r=subprocess.run(['php',str(ROOT/'tests/backup_fixture.php'),action],input=json.dumps(values or {}),env=env,text=True,capture_output=True,timeout=30)
    if r.returncode:raise AssertionError('Backup fixture failed: '+r.stderr)
    return json.loads(r.stdout)


def snapshot(s):return fixture(s,'snapshot')


def action(s,kind,**values):
    return s.client.form('admin/backups/',{'action':kind,'owner_password':s.password,**values})


def create(s):
    before={r['id'] for r in snapshot(s)['backups']}
    response=action(s,'create')
    rows=[r for r in snapshot(s)['backups'] if r['id'] not in before]
    expect(response[0]==200 and len(rows)==1 and 'Encrypted snapshot created' in response[1],'Owner creates a real encrypted snapshot through the browser form')
    return rows[0]['id']


def download(s,kind,id):
    csrf=token(s.client.request('admin/backups/')[1]);body=urllib.parse.urlencode({'csrf':csrf,'action':kind,'id':id,'owner_password':s.password}).encode()
    with s.client.opener.open(urllib.request.Request(s.base+'admin/backups/',body),timeout=30) as r:
        return r.status,r.read(),r.headers


def import_archive(s,body,key):
    csrf=token(s.client.request('admin/backups/')[1]);boundary='BackupFixture'+secrets.token_hex(10);parts=[]
    for k,v in {'csrf':csrf,'action':'import','owner_password':s.password,'recovery_key':key}.items():parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode())
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="backup"; filename="owner-snapshot.sgbackup"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode()+body+b'\r\n');parts.append(f'--{boundary}--\r\n'.encode())
    req=urllib.request.Request(s.base+'admin/backups/',b''.join(parts),{'Content-Type':'multipart/form-data; boundary='+boundary})
    try:r=s.client.opener.open(req,timeout=30)
    except urllib.error.HTTPError as e:r=e
    with r:return r.status,r.read().decode(),r.headers


def journey(s):
    image,audio,_,_=seed_media_and_music(s);page=save_content(s,'page','Backup story',{'body':'Original protected story','image_id':str(image)},slug='backup-story');pid,vid,_=seed_catalog(s)
    initial=snapshot(s)
    expect(Client(s.base).request('admin/backups/')[3].endswith('/admin/login.php'),'Anonymous visitor cannot access backup controls')
    expect(s.client.request('admin/backups/',{'csrf':'bad','action':'create','owner_password':s.password})[0]==403,'Backup creation requires CSRF')
    s.client.form('admin/backups/',{'action':'create','owner_password':'incorrect'})
    expect(snapshot(s)['backups']==[],'Owner reauthentication is required before snapshot creation')
    id=create(s);record=fixture(s,'archive',{'id':id});data=Path(record['path']).read_bytes()
    expect(data.startswith(b'SPIRIT-BACKUP-1\n') and b'Original protected story' not in data and s.password.encode() not in data and s.env['SPIRIT_DB_PASSWORD'].encode() not in data,'Encrypted archive contains no visible page, owner or database secrets')
    expect(record['key'] not in json.dumps(record['info']) and record['info']['sealed_key'].startswith('v1:'),'Recovery key is encrypted in account-private metadata')
    expect('verified' in action(s,'verify',id=id)[1] and any(r['id']==id and r['status']=='verified' for r in snapshot(s)['backups']),'Complete archive and media verification updates the stored verification state')
    status,receipt,headers=download(s,'key',id);key=re.search(rb'Recovery key: ([a-f0-9]{64})',receipt).group(1).decode()
    expect(status==200 and key==record['key'] and 'no-store' in headers.get('Cache-Control',''),'Separate recovery receipt requires owner access and prohibits caching')
    status,binary,headers=download(s,'download',id)
    expect(status==200 and binary==data and 'attachment' in headers.get('Content-Disposition','') and key.encode() not in binary,'Archive download preserves the exact encrypted bytes without including its key receipt')
    save_content(s,'page','Changed story',{'body':'Later content change','image_id':str(image)},item_id=page,slug='backup-story')
    later_revision=next(r['revision'] for r in snapshot(s)['content'] if int(r['id'])==page)
    extra,_,_=upload(s,'later-private.png','image/png',png_bytes());adjust(s,pid,vid,2)
    before=snapshot(s)
    action(s,'restore',id=id,scope='content',confirm_restore='wrong')
    expect('Changed story' in Client(s.base).request('pages/?slug=backup-story')[1],'Typed restore confirmation prevents accidental replacement')
    fixture(s,'media-break',{'media_id':image})
    response=action(s,'restore',id=id,scope='content',confirm_restore='RESTORE')
    after=snapshot(s)
    expect('Restore committed' in response[1] and 'Original protected story' in Client(s.base).request('pages/?slug=backup-story')[1],'Content restore replaces saved content and repairs a damaged immutable image')
    expect(after['protected']==before['protected'] and after['users']==before['users'] and after['config_hash']==initial['config_hash'],'Content restore preserves operational records, staff access and private configuration')
    expect(any(int(r['id'])==extra for r in after['media']) and len(after['backups'])==2,'Newer private uploads and an automatic pre-restore snapshot are retained')
    expect(int(next(r['revision'] for r in after['content'] if int(r['id'])==page))>int(later_revision),'Restored content receives a new revision so old edit forms cannot overwrite it')
    response=action(s,'restore',id=id,scope='site',confirm_restore='RESTORE')
    expect('would rewind operational records' in response[1] and snapshot(s)['protected']==before['protected'],'Site-data restore refuses to roll back later inventory or other business activity')
    before_import=len(snapshot(s)['backups']);wrong=import_archive(s,binary,'0'*64)
    expect('authentication failed' in wrong[1] and len(snapshot(s)['backups'])==before_import,'Wrong recovery key cannot import or replace a snapshot')
    response=import_archive(s,binary,key);imported=next(r['id'] for r in snapshot(s)['backups'] if r['label']=='Imported verified snapshot')
    expect('Backup imported and verified' in response[1] and len(snapshot(s)['backups'])==before_import+1,'Saved encrypted archive imports through a real multipart form without restoring anything')
    fixture(s,'tamper',{'id':imported});response=action(s,'verify',id=imported)
    expect('authentication failed' in response[1] and snapshot(s)['protected']==before['protected'],'Tampered archive is refused without modifying database records')
    fixture(s,'tamper',{'id':imported});response=action(s,'delete',id=imported,confirm_delete='DELETE')
    expect('removed' in response[1] and all(r['id']!=imported for r in snapshot(s)['backups']),'Explicit deletion removes only the chosen backup and its wrapped-key metadata')


def faults_and_locks(s):
    image,_,_,_=seed_media_and_music(s);page=save_content(s,'page','Backup story',{'body':'Original safe snapshot','image_id':str(image)},slug='backup-story');id=create(s)
    save_content(s,'page','Changed story',{'body':'Must survive failed restore','image_id':str(image)},item_id=page,slug='backup-story')
    before=snapshot(s);fixture(s,'fault-on')
    try:response=action(s,'restore',id=id,scope='content',confirm_restore='RESTORE')
    finally:fixture(s,'fault-off')
    expect('Restore committed' not in response[1] and 'Must survive failed restore' in Client(s.base).request('pages/?slug=backup-story')[1],'An enforced SQL insertion failure rolls back earlier restore deletes')
    expect(snapshot(s)['protected']==before['protected'],'Failed restore cannot alter protected payment, access or consent data')
    fixture(s,'media-break',{'media_id':image,'missing':True});response=action(s,'restore',id=id,scope='site',confirm_restore='RESTORE')
    expect('Restore committed' in response[1] and 'Original safe snapshot' in Client(s.base).request('pages/?slug=backup-story')[1],'Same-schema site-data restore repairs missing media when operational records are unchanged')
    for mode in ['shared','exclusive']:
        env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
        process=subprocess.Popen(['php',str(ROOT/'tests/backup_fixture.php'),'gate'],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True,env=env)
        process.stdin.write(json.dumps({'mode':mode}));process.stdin.close()
        try:
            expect(process.stdout.readline().strip()=='READY','Independent process acquires the '+mode+' maintenance lock')
            if mode=='exclusive':
                status,_,headers,_=Client(s.base).request('')
                expect(status==503 and headers.get('Retry-After')=='10','Exclusive restore lock blocks concurrent public requests with a retry response')
            else:
                count=len(snapshot(s)['backups']);response=action(s,'create')
                expect('Another website operation is active' in response[1] and len(snapshot(s)['backups'])==count,'Backup cannot overtake another active application request')
        finally:
            process.wait(timeout=10)
        expect(Client(s.base).request('')[0]==200,'Process completion releases the maintenance lock without a permanent outage flag')
    s.database('editor');expect(s.client.request('admin/backups/')[0]==403,'Non-owner role cannot use snapshot or restore controls');s.database('owner')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-backup-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder in ['', 'band']:
            with site(base/('root' if not folder else 'nested'),archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s)
        with site(base/'faults',archive,code,'band') as s:
            s.claim();s.client.form('install/',s.input());s.login();faults_and_locks(s)
    print('Backup SQL/HTTP encryption, restore isolation, rollback and lock checks passed.',flush=True)


if __name__=='__main__':main()
