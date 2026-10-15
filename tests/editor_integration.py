#!/usr/bin/env python3
"""Rejected product saves preserve correction fields, not database changes."""
from __future__ import annotations
import os
import tempfile
from html.parser import HTMLParser
from pathlib import Path
from http_integration import Client,build_package,expect,site
from commerce_integration import seed_catalog,product_values,snapshot

class FormValues(HTMLParser):
    def __init__(self):super().__init__();self.fields={};self.active=None
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='input' and a.get('name'):self.fields[a['name']]=a.get('value','')
        if tag=='textarea':self.active=a.get('name');self.fields[self.active]=''
    def handle_data(self,data):
        if self.active:self.fields[self.active]+=data
    def handle_endtag(self,tag):
        if tag=='textarea':self.active=None

def fields(body):
    parser=FormValues();parser.feed(body);return parser.fields

def journey(s):
    pid,_,image=seed_catalog(s);path=f'admin/shop/edit.php?id={pid}'
    old=product_values(s,pid,image)
    authored={**old,'name':'UNSAVED <strong>name</strong>','slug':'invalid URL name','description':'Unsent description <script>not executable</script>','size_guide':'Keep these care instructions','private_token':'DO-NOT-REFLECT-THIS'}
    before=snapshot(s)
    result=s.client.form(path,authored)
    expect(result[0]==200 and 'role="alert"' in result[1],'Invalid authored input returns a correctable error')
    parsed=fields(result[1])
    for name in ['name','slug','description','size_guide','revision']:
        expect(parsed[name]==authored[name],'Rejected save retains submitted '+name)
    expect('data-edit-guard-dirty="true"' in result[1],'Rejected response is marked unsaved even before another edit')
    expect('<script>not executable</script>' not in result[1] and 'DO-NOT-REFLECT-THIS' not in result[1],'Correction fields are escaped and unexpected secrets excluded')
    expect(snapshot(s)==before,'Rejected save changes no catalog, stock, identity or audit data')
    expect('UNSAVED' not in Client(s.base).request('shop/product.php?slug=fixture-shirt')[1],'Public snapshot is not altered by rejected input')
    expect(s.client.request(path,{'csrf':'wrong','action':'draft','name':'Unauthorized'})[0]==403 and snapshot(s)==before,'Server CSRF remains mandatory without relying on warnings')
    # A stale open editor must not silently adopt the latest revision on error.
    s.client.form(path,{**old,'name':'Newer private draft'})
    before=snapshot(s)
    response=s.client.form(path,{**authored,'slug':'fixture-shirt'})
    expect('changed' in response[1].lower() and fields(response[1])['revision']==old['revision'],'Stale correction retains its old revision instead of bypassing conflict detection')
    expect(fields(response[1])['name']==authored['name'] and snapshot(s)==before,'Stale product save retains unsent text without overwriting newer draft')
    response=s.client.request(path)
    expect(fields(response[1])['name']=='Newer private draft' and 'data-edit-guard-dirty="false"' in response[1],'Explicit reload shows the actual saved draft as the clean baseline')
    current=product_values(s,pid,image,name='Corrected draft')
    expect(s.client.form(path,current)[0]==200 and fields(s.client.request(path)[1])['name']=='Corrected draft','A freshly loaded revision can complete a legitimate draft save')
    expect('Corrected draft' not in Client(s.base).request('shop/product.php?slug=fixture-shirt')[1],'Successful private draft still leaves live product unchanged')
    s.database('editor')
    expect(s.client.request(path)[0]==403,'Content-only staff cannot use product correction workflow')
    s.database('owner')
    for route in ['admin/account.php','admin/orders/payments.php','admin/backups/','admin/upgrades/']:
        expect('data-edit-guard ' not in s.client.request(route)[1],'Sensitive or operational form is not enrolled: '+route)

def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Use a non-root disposable database.')
    with tempfile.TemporaryDirectory(prefix='spirit-editor-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder,label in [('', 'root'),('band','subfolder')]:
            with site(base/label,archive,code,folder) as s:
                s.claim();s.client.form('install/',s.input());s.login();journey(s)
    print('Product error/correction/state-preservation SQL/HTTP checks passed.',flush=True)
if __name__=='__main__':main()
