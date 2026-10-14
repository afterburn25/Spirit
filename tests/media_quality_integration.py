#!/usr/bin/env python3
"""Real responsive image bytes, bounded cache, permissions and media isolation."""
from __future__ import annotations
import fcntl
import hashlib
import json
import os
import struct
import subprocess
import tempfile
import urllib.error
import urllib.request
import zlib
from pathlib import Path
from http_integration import ROOT, Client, build_package, expect, site
from content_integration import upload, save_content, raw_request


def photograph(width=1800, height=1200):
    # Reproducible textured RGBA image. Its transparent margin catches accidental
    # black backgrounds; it is test data, not an approved band photograph.
    rows=[]
    for y in range(height):
        row=bytearray(b'\0')
        for x in range(width):
            # Keep the upload below PHP's common 2MB default without changing
            # that limit. Pixel dimensions and alpha still exercise real resizing.
            xx,yy=x//4,y//4
            grain=((xx*73)^(yy*151)^((xx*yy)>>3))&63
            row.extend(((xx//7+grain)&255,(yy//5+grain)&255,(xx//11+yy//13+grain)&255,0 if x<48 or y<48 else 255))
        rows.append(row)
    def chunk(kind, payload):return struct.pack('>I',len(payload))+kind+payload+struct.pack('>I',zlib.crc32(kind+payload)&0xffffffff)
    return b'\x89PNG\r\n\x1a\n'+chunk(b'IHDR',struct.pack('>IIBBBBB',width,height,8,6,0,0,0))+chunk(b'IDAT',zlib.compress(b''.join(rows),6))+chunk(b'IEND',b'')


def fixture(s,action,id=0,low_memory=False):
    env={**s.env,'SPIRIT_FIXTURE_ROOT':str(s.root),'SPIRIT_FIXTURE_DOCUMENT_ROOT':str(s.docroot)}
    command=['php']+(['-d','memory_limit=32M'] if low_memory else [])+[str(ROOT/'tests/media_quality_fixture.php'),action]
    r=subprocess.run(command,input=json.dumps({'id':id}),env=env,text=True,capture_output=True,timeout=25)
    if r.returncode:raise AssertionError('Media fixture failed: '+r.stderr)
    return json.loads(r.stdout)


def owner_raw(s,path,headers=None):
    try:r=s.client.opener.open(urllib.request.Request(s.base+path,headers=headers or {}),timeout=30)
    except urllib.error.HTTPError as error:r=error
    with r:return r.status,r.read(),r.headers


def seed(s):
    id,status,body=upload(s,'generated-quality.png','image/png',photograph())
    expect(status==200 and id>0,'High-resolution transparent fixture uploads through the real owner handler')
    page=save_content(s,'page','Media quality fixture',{'body':'Generated fixture, not band content.','image_id':str(id)},slug='media-quality')
    return id,page


def journey(s):
    s.claim();s.client.form('install/',s.input());s.login()
    id,status,_=upload(s,'generated-quality.png','image/png',photograph())
    expect(status==200 and id>0,'Media quality uses the real multipart image pipeline')
    path=f'media/file.php?id={id}'
    before=fixture(s,'inspect',id)
    expect(raw_request(s.base+path+'&width=640')[0]==404 and not fixture(s,'inspect',id)['variants'],'Private image cannot produce an anonymous derivative')
    page=save_content(s,'page','Media quality fixture',{'body':'Generated fixture, not band content.','image_id':str(id)},slug='media-quality')
    html=Client(s.base).request('pages/?slug=media-quality')[1]
    expect('srcset=' in html and '&amp;width=320 320w' in html and 'width="1800" height="1200"' in html,'Published markup provides fixed size candidates and intrinsic ratio')
    status,original,headers=raw_request(s.base+path)
    expect(status==200 and hashlib.sha256(original).hexdigest()==before['source']['checksum'],'Original authorized image bytes are preserved')
    # While another request generates, fallback is immediate, valid original data.
    with (s.runtime/'cache/.image-variants.lock').open('a+b') as lock:
        fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
        expect(raw_request(s.base+path+'&width=640')[1]==original,'Busy image generation lock returns original without waiting or duplicating work')
    for width in (320,640,960):
        status,body,h=raw_request(s.base+path+f'&width={width}')
        expect(status==200 and len(body)<len(original) and h.get('ETag')!='"'+before['source']['checksum']+'"','Resized image delivers fewer real bytes with its own validator: '+str(width))
    snapshot=fixture(s,'inspect',id)
    expect(len(snapshot['variants'])==3 and all(v['mode']==0o600 for v in snapshot['variants']),'Generated copies are finite and private on disk')
    expect(all(v['alpha']>=120 for v in snapshot['variants']),'Transparent image margin remains transparent after downsampling')
    expect(snapshot['source']==before['source'],'Responsive generation never changes the original database-backed file')
    status,small,headers=raw_request(s.base+path+'&width=640');etag=headers['ETag']
    expect(raw_request(s.base+path+'&width=640',{'If-None-Match':etag})[0]==304,'Conditional derivative request revalidates the actual representation')
    status,head,h=raw_request(s.base+path+'&width=640',method='HEAD')
    expect(status==200 and not head and int(h['Content-Length'])==len(small),'Image HEAD returns actual representation metadata only')
    status,part,h=raw_request(s.base+path+'&width=640',{'Range':'bytes=0-11','If-Range':etag})
    expect(status==206 and part==small[:12],'Matching If-Range returns exact derivative range')
    expect(raw_request(s.base+path+'&width=640',{'Range':'bytes=0-11','If-Range':'"obsolete"'})[1]==small,'Mismatched If-Range returns full representation rather than mixing old and new bytes')
    expect(raw_request(s.base+path+'&width=640&download=1')[1]==original,'Explicit image download retains full original quality')
    for invalid in ['321','-640','0640','640.0','../../config','640&width[]=320']:
        expect(raw_request(s.base+path+'&width='+invalid)[0]==400,'Malformed/unbounded width is refused: '+invalid)
    cached=[v for v in snapshot['variants'] if v['width']==640][0]
    (s.runtime/'cache'/cached['name']).write_bytes(b'broken')
    expect(raw_request(s.base+path+'&width=640')[1]==small,'Damaged generated cache is validated and regenerated')
    (s.runtime/'cache').chmod(0o500)
    try:expect(raw_request(s.base+path+'&width=1440')[1]==original,'Host-denied cache writes keep the approved original usable')
    finally:(s.runtime/'cache').chmod(0o700)
    outside=s.home/'outside-cache';outside.mkdir();(outside/'sentinel').write_text('untouched')
    cache=s.runtime/'cache';saved=s.runtime/'saved-cache';cache.rename(saved);cache.symlink_to(outside,target_is_directory=True)
    try:expect(raw_request(s.base+path+'&width=320')[1]==original and sorted(p.name for p in outside.iterdir())==['sentinel'],'Unsafe cache symlink is neither read nor written')
    finally:cache.unlink();saved.rename(cache)
    # The HTTP route still authorizes before cache/304, including after unpublish.
    save_content(s,'page','Media quality fixture',{'body':'Private again','image_id':str(id)},action='unpublish',item_id=page,slug='media-quality')
    status,body,h=raw_request(s.base+path+'&width=640',{'If-None-Match':etag})
    expect(status==404 and 'no-store' in h.get('Cache-Control',''),'Unpublishing withdraws cached derivatives even with a prior ETag')
    expect(owner_raw(s,path+'&width=640')[0]==200 and 'no-store' in owner_raw(s,path+'&width=640')[2].get('Cache-Control',''),'Authorized owner preview retains private no-store derivative access')
    original_path=s.runtime/'media'/before['source']['name'];saved_source=original_path.with_suffix('.saved');original_path.rename(saved_source)
    try:expect(owner_raw(s,path+'&width=640')[0]==503,'Existing cache cannot conceal a missing authoritative original')
    finally:saved_source.rename(original_path)
    for item in (s.runtime/'cache').glob('image-v1-*'):os.utime(item,(1,1))
    os.utime(original_path,(1,1))
    result=fixture(s,'prune',id)
    expect(result['files']==3 and original_path.exists() and (s.runtime/'cache/.image-variants.lock').exists(),'Retention removes only old derivatives while preserving originals and generation lock')
    expect(not fixture(s,'low_memory',id,True)['resized'],'Small PHP memory budget falls back rather than attempting an unsafe GD allocation')
    print('MEASURED generated fixture original='+str(len(original))+' bytes;640px='+str(len(small))+' bytes; reduction='+str(round(100*(1-len(small)/len(original)),1))+'%',flush=True)


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':raise RuntimeError('Non-root disposable database required.')
    with tempfile.TemporaryDirectory(prefix='spirit-media-quality-') as directory:
        base=Path(directory);archive,code=build_package(base/'packages')
        for folder in ['', 'band']:
            with site(base/('nested' if folder else 'root'),archive,code,folder) as s:journey(s)
    print('Responsive media SQL/HTTP, alpha, privacy and fallback checks passed.',flush=True)
if __name__=='__main__':main()
