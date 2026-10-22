#!/usr/bin/env python3
"""Conservative retention through actual private owner and optional CLI workflows."""
from __future__ import annotations
import json
import os
import subprocess
import tempfile
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site


def fixture(s, action):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
    result=subprocess.run(['php',str(ROOT/'app/tests/cleanup_fixture.php'),action],env=env,text=True,capture_output=True,timeout=25)
    if result.returncode:raise AssertionError(result.stderr)
    return json.loads(result.stdout)


def run_cli(s):
    return subprocess.run(['php',str(s.root/'app/tasks/cleanup.php'),'--document-root='+str(s.docroot)],env=s.env,text=True,capture_output=True,timeout=25)


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-cleanup-http-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder in ['', 'band']:
            with site(base/('root' if not folder else 'nested'),archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login();fixture(s,'seed');before=fixture(s,'snapshot')
                expect(Client(s.base).request('admin/maintenance/')[3].endswith('/admin/login.php'),'Anonymous cleanup access is denied')
                expect(s.client.request('admin/maintenance/',{'csrf':'bad','action':'clean'})[0]==403,'Cleanup requires CSRF')
                preview=s.client.request('admin/maintenance/');expect(fixture(s,'snapshot')==before,'Opening cleanup preview cannot change records or temporary files')
                expect('Anonymous pageviews older than 90 days' in preview[1] and 'Crawler daily records older than 90 days' in preview[1],'Maintenance preview includes the current analytics retention policy')
                expect('disabled' in run_cli(s).stdout and fixture(s,'snapshot')==before,'Scheduled cleanup is disabled by default without changing data')
                expect(s.client.request('app/tasks/cleanup.php')[0]==403,'CLI cleanup has no public HTTP execution path')
                s.client.form('admin/maintenance/',{'action':'clean','owner_password':'wrong','confirm_cleanup':'CLEAN TEMPORARY DATA'})
                expect(fixture(s,'snapshot')==before,'Cleanup rejects an incorrect owner password')
                s.client.form('admin/maintenance/',{'action':'clean','owner_password':s.password,'confirm_cleanup':'wrong'})
                expect(fixture(s,'snapshot')==before,'Typed cleanup confirmation prevents unintended deletion')
                expect(fixture(s,'fault')['failed'] and fixture(s,'snapshot')==before,'Mid-cleanup database failure rolls back previously deleted technical rows')
                result=s.client.form('admin/maintenance/',{'action':'clean','owner_password':s.password,'confirm_cleanup':'CLEAN TEMPORARY DATA'});after=fixture(s,'snapshot')
                expect('Cleanup completed' in result[1] and after['request_limits']==5,'Owner cleanup respects the 200-record batch cap')
                expect(after['login_limits']==0 and after['customer_tokens']==1 and after['staff_tokens']==1,'Expired technical tokens are pruned while active tokens remain')
                expect(after['carts']==2 and after['cart_items']==2 and after['cart_operations']==2,'Only expired order-free cart and its children are deleted')
                expect(all(rows==before['analytics'][table][-1:] for table,rows in after['analytics'].items()),'Owner cleanup removes expired analytics while retaining exact recent traffic rows')
                expect('3 expired analytics records' in result[1],'Cleanup confirmation reports the actual analytics rows removed')
                bodies={r['status']:int(r['length']) for r in after['mail']}
                expect(bodies['sent']==0 and bodies['cancelled']==0 and all(bodies[x]>0 for x in ['queued','uncertain','failed']),'Old accepted/cancelled bodies are cleared without discarding unresolved mail')
                expect(after['protected']==before['protected'] and not after['temporary_exists'],'Business history remains byte-equivalent while old private temporary file is removed')
                s.client.form('admin/maintenance/',{'action':'policy','owner_password':s.password,'scheduled_cleanup':'1'})
                r=run_cli(s);after=fixture(s,'snapshot');expect(r.returncode==0 and after['request_limits']==0,'Explicitly enabled private scheduled task processes remaining expired records')
                r=run_cli(s);expect(sum(json.loads(r.stdout)['rows'].values())==0,'Repeated scheduled cleanup is idempotent')
                s.database('editor');expect(s.client.request('admin/maintenance/')[0]==403,'Content editor cannot change retention or clean owner data');s.database('owner')
    print('Temporary-data retention and optional scheduling SQL/HTTP checks passed.',flush=True)


if __name__=='__main__':main()
