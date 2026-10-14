#!/usr/bin/env python3
"""Consolidation: both installed-preview URLs, passive follow-up and access controls."""
from __future__ import annotations
import json
import os
import tempfile
import urllib.error
import urllib.request
from pathlib import Path
from http_integration import Client, build_package, expect, site
from content_integration import token
from launch_integration import fixture, report
from manual_installer_integration import without_optional_extensions


def request_method(s,route,method):
    try:response=s.client.opener.open(urllib.request.Request(s.base+route,method=method),timeout=20)
    except urllib.error.HTTPError as error:response=error
    with response:return response.status,response.read(),response.headers


def journey(s):
    s.open_installer();s.client.form('install/',s.input());s.login()
    fixture(s,'configure');fixture(s,'extras')
    before=fixture(s,'fingerprint');results=[]
    for route in ['admin/launch/','admin/readiness/']:
        expect(Client(s.base).request(route)[3].endswith('/admin/login.php'),'Anonymous preview/canonical URL cannot expose diagnostics')
        status,body,headers,_=s.client.request(route)
        expect(status==200 and body.count('class="launch-check"')==43,'Canonical and preview URL render one consolidated 43-check dashboard')
        expect('live-charge risk' in body and '1 published product(s)' in body,'Production selection warns and product count excludes hidden categories/private drafts')
        expect('EXTRA-PRIVATE' not in body and 'private-contact@example.test' not in body,'Raw message, error and contact values are absent from rendered checks')
        status,text,headers,_=s.client.form(route,{'action':'export'});data=json.loads(text);results.append(data['check_states'])
        expect(status==200 and data['format']=='spirit-support-v1' and len(data['check_states'])==43,'Both URLs use the same bounded support-report model')
        expect(data['check_states']['payment_mode']=='attention' and data['check_states']['mail_pending']=='attention' and data['check_states']['payment_pending']=='attention','Follow-up detects uncertain operations without processing them')
        expect(all(data['check_states']['external_'+k]=='review' for k in ['host','mailbox','merchant','recovery','artwork','quality']),'External acceptance cannot turn green from configured or production settings')
        expect('EXTRA-' not in text and '@example.test' not in text and str(s.runtime) not in text,'Support export omits fixture secrets, personal details, paths and content')
        expect(s.client.request(route,{'action':'export','csrf':'wrong'})[0]==403,'Preview alias does not bypass CSRF')
        expect(s.client.form(route,{'action':'enable_live_payments'})[0]==400,'Preview alias cannot enable a service')
        code,raw,h=request_method(s,route,'HEAD');expect(code==200 and raw==b'' and 'no-store' in h.get('Cache-Control',''),'HEAD returns private headers without a body')
        expect(request_method(s,route,'PUT')[0]==405,'Unsupported method is refused on both URLs')
    expect(results[0]==results[1] and fixture(s,'fingerprint')==before,'Both routes report identical states without changing any database table')
    fixture(s,'mail_offline')
    try:
        data=report(s)
        expect(data['check_states']['mail_pending']=='unavailable' and data['check_states']['payment_pending']=='attention','A missing outbox is unavailable, not an invented empty queue')
    finally:fixture(s,'mail_restore')
    expect(fixture(s,'fingerprint')==before,'Failure observation preserves the restored original records')
    fixture(s,'schema_bad')
    try:
        data=report(s)
        expect(data['check_states']['mail_pending']=='unavailable' and data['check_states']['sandbox_keys']=='unavailable','Unconfirmed schema prevents dependent extra queries')
        expect(data['check_states']['external_merchant']=='review','Schema failure does not remove manual acceptance boundaries')
    finally:fixture(s,'schema_restore')
    for role in ['content_editor','store_manager']:
        fixture(s,'role',{'role':role})
        for route in ['admin/launch/','admin/readiness/']:
            expect(s.client.request(route)[0]==403 and s.client.request(route,{'action':'export'})[0]==403,'Changed staff role denies both observations and export: '+role)
    fixture(s,'role',{'role':'owner'});fixture(s,'suspend')
    expect(s.client.request('admin/readiness/')[3].endswith('/admin/login.php'),'Suspended owner cannot retain preview URL access')
    fixture(s,'activate');s.login();fixture(s,'revoke')
    expect(s.client.request('admin/launch/')[3].endswith('/admin/login.php'),'Revoked owner session cannot retain canonical access')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable SQL environment required.')
    with tempfile.TemporaryDirectory(prefix='spirit-launch-extras-') as d:
        base=Path(d);archive,unused=build_package(base/'packages')
        for folder in ['', 'band']:
            with site(base/('root' if not folder else 'sub'),archive,unused,folder) as s:journey(s)
        with without_optional_extensions(base/'no-extensions'):
            with site(base/'without',archive,unused,'band') as s:journey(s)
    print('Consolidated preview compatibility, live-mode warning, passive follow-up and owner boundaries passed.',flush=True)


if __name__=='__main__':main()
