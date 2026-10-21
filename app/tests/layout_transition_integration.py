#!/usr/bin/env python3
"""Rehearse documented same-root rc.8 -> grouped layout on disposable SQL only.
This runs the cPanel-equivalent file transition in a test directory, never the
user's host. It does not bypass the old updater or change schema/credentials.
"""
from __future__ import annotations
import hashlib,json,os,shutil,subprocess,tempfile,zipfile
from pathlib import Path
from http_integration import ROOT,Client,build_package,expect,site,dashboard
from content_integration import save_content,upload,png_bytes
from upgrade_integration import fixture as upgrade_fixture

BASE_SHA256='26fb560e2da2f14af1fbd61d19630898f6146b43de7704e2278af347f0bacdfc'

def fixture(s,action,new_package=None):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot),'SPIRIT_NEW_PACKAGE':str(new_package or '')}
    result=subprocess.run(['php',str(ROOT/'app/tests/layout_transition_fixture.php'),action],env=env,text=True,capture_output=True,timeout=30)
    if result.returncode:raise AssertionError('Layout fixture failed: '+action+' '+result.stderr)
    return json.loads(result.stdout)

def apply_same_root(s,archive):
    """Test implementation of the documented panel operations, not shipped updater."""
    old=json.loads((s.root/'docs/PACKAGE-MANIFEST.json').read_text())['files']
    with zipfile.ZipFile(archive) as bundle:
        new=json.loads(bundle.read('docs/PACKAGE-MANIFEST.json'))['files']
        expect(all(hashlib.sha256(bundle.read(name)).hexdigest()==digest for name,digest in new.items()),'Transition input manifest hashes match every file')
        for name in bundle.namelist():
            if name.startswith('install/'):continue # A removed installer must stay removed.
            target=s.root/name;target.parent.mkdir(parents=True,exist_ok=True)
            if target.is_symlink():raise AssertionError('Unexpected symlink in isolated source')
            target.write_bytes(bundle.read(name))
        for name,digest in old.items():
            target=s.root/name
            if name not in new and target.is_file():
                if hashlib.sha256(target.read_bytes()).hexdigest()!=digest:raise AssertionError('Refusing to remove changed legacy file '+name)
                target.unlink()
    for directory in sorted([p for p in s.root.rglob('*') if p.is_dir()],key=lambda p:len(p.parts),reverse=True):
        if directory.name in ['admin','app','assets','config','docs','images','includes','storage','uploads']:continue
        try:directory.rmdir()
        except OSError:pass

def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Unprivileged disposable environment required')
    legacy=Path(os.environ['SPIRIT_LEGACY_PACKAGE'])
    expect(hashlib.sha256(legacy.read_bytes()).hexdigest()==BASE_SHA256,'Exact delivered rc.8 package hash verified')
    with tempfile.TemporaryDirectory(prefix='spirit-layout-transition-') as directory:
        area=Path(directory);current,_=build_package(area/'packages')
        for sub in ['', 'band']:
            with site(area/('root' if not sub else 'subfolder'),legacy,None,sub) as s:
                s.claim();status,body,_,_=s.client.form('install/',s.input())
                expect(status==200 and 'id="install-complete"' in body,'Original rc.8 installs through its real single-page installer')
                expect(dashboard(s.login()[3]),'Original owner signs in before transition')
                page_id=save_content(s,'page','Layout preservation example',{'body':'Content must survive the folder transition.'},slug='layout-preserved')
                media_id,code,_=upload(s,'layout-fixture.png','image/png',png_bytes())
                expect(code==200 and media_id>0,'Real private media upload created before transition')
                expect(s.client.form('admin/settings.php',{'site_name':'Spiritual Gorillaz','tagline':'Preserved tagline','footer_copyright':'© {year} Original owner. All rights reserved.'})[0]==200,'Original settings saved before transition')
                # Record one anonymous visit before the snapshot; all historic rows survive.
                expect(Client(s.base).request('pages/?slug=layout-preserved')[0]==200,'Original content page creates a pre-transition recorded visit')
                s.root.joinpath('uploads/user-retained.txt').write_text('User-owned sentinel; not shipped code.')
                private_media={str(p.relative_to(s.runtime)):hashlib.sha256(p.read_bytes()).hexdigest() for p in (s.runtime/'uploads').rglob('*') if p.is_file()} if (s.runtime/'uploads').exists() else {}
                # Capture actual media path as well: discover from all non-session private files.
                private_static={str(p.relative_to(s.runtime)):hashlib.sha256(p.read_bytes()).hexdigest() for p in s.runtime.rglob('*') if p.is_file() and p.suffix.lower() in ['.png','.webp','.jpg','.wav']}
                expect(bool(private_static),'Private uploaded image bytes exist before transition')
                before=fixture(s,'snapshot');code_hash=hashlib.sha256((s.root/'index.php').read_bytes()).hexdigest()
                rejected=fixture(s,'legacy-reject',current)
                expect(rejected['rejected'] and any(word in rejected['reason'].lower() for word in ['pinned','unsafe','allow','schema','path']),'Old updater rejects changed routing/recovery foundation')
                expect(code_hash==hashlib.sha256((s.root/'index.php').read_bytes()).hexdigest() and fixture(s,'snapshot')==before,'Rejected old upgrade changes neither application nor stored data')
                fixture(s,'old-marker');old_marker=(s.runtime/'updates/latest.json').read_bytes()
                apply_same_root(s,current)
                after=fixture(s,'snapshot')
                expect(after==before,'Same-root transition preserves every business/analytics table, owner, configuration, lock and runtime identity')
                expect(not (s.root/'install').exists(),'Removed installer is not recreated')
                expect(s.root.joinpath('uploads/user-retained.txt').read_text()=='User-owned sentinel; not shipped code.','User-owned file remains unchanged')
                expect(all((s.runtime/name).is_file() and hashlib.sha256((s.runtime/name).read_bytes()).hexdigest()==digest for name,digest in private_static.items()),'All original uploaded image bytes remain unchanged')
                generation=fixture(s,'generation')
                expect(generation=={'layout':'grouped-v2','latest':None} and (s.runtime/'updates/latest.json').read_bytes()==old_marker,'Old recovery checkpoint is preserved but cannot become a grouped-layout rollback')
                expect(dashboard(s.login()[3]),'Original owner signs into reorganized site')
                for route in ['', 'music/','shows/','band/','media/','press/','booking/','pages/?slug=layout-preserved','admin/','admin/media/','admin/settings.php','admin/analytics/','admin/analytics/visitors.php','admin/upgrades/']:
                    response=s.client.request(route)
                    expect(response[0]==200,'Existing application route opens after same-root transition: '+route)
                expect('Content must survive the folder transition.' in s.client.request('pages/?slug=layout-preserved')[1],'Original published content renders after transition')
                expect(s.database('count')=={'users':1,'state':'installed'},'Original owner and installation state retained')
                expected={'admin','app','assets','config','docs','images','includes','storage','uploads'}
                expect({p.name for p in s.root.iterdir() if p.is_dir()}==expected,'Installed root contains only approved folders after removed-installer transition')
    print('Exact rc.8 same-root transition passed at root and /band. No live host was touched.',flush=True)
if __name__=='__main__':main()
