#!/usr/bin/env python3
"""Build a cumulative shared-host ZIP and a separate PRIVATE setup receipt.
Run on a development machine; the host requires neither Python nor SSH.
"""
from __future__ import annotations
import argparse
import hashlib
import json
import os
import re
import secrets
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DIRECTORIES = {'app','admin','includes','config','database','storage','uploads','views','assets','images','install','docs','music','shows','band','media','booking','press','shop','account','pages','newsletter','tasks'}
ROOT_FILES = {'.htaccess','index.php','VERSION','README.md','CHANGELOG.md'}
PRIVATE_SUFFIXES = {'.log','.zip','.pem','.key','.p12','.pfx','.sqlite','.sqlite3','.bak','.gz'}

def eligible(source: Path, destination: Path) -> bool:
    relative = source.relative_to(ROOT)
    if not source.is_file() or any((ROOT / Path(*relative.parts[:i])).is_symlink() for i in range(1,len(relative.parts)+1)):
        return False
    if source.is_relative_to(destination):
        return False
    if len(relative.parts) == 1:
        return relative.name in ROOT_FILES
    if relative.parts[0] not in DIRECTORIES:
        return False
    if any(part in {'.git','__pycache__','vendor','node_modules'} for part in relative.parts):
        return False
    if source.name.startswith('.env') or 'PRIVATE-SETUP' in source.name.upper() or source.suffix.lower() in PRIVATE_SUFFIXES:
        return False
    if relative.as_posix() == 'install/access.php':
        return False
    if relative.parts[0] in {'config','storage','uploads'}:
        return len(relative.parts)==2 and source.name in {'.htaccess','README.md'}
    if source.suffix.lower()=='.sql' and relative.parts[:2] != ('database','migrations'):
        return False
    return True

def package(destination: Path) -> tuple[Path, Path]:
    destination = destination.resolve()
    version = (ROOT/'VERSION').read_text().strip()
    if not re.fullmatch(r'\d+\.\d+\.\d+(?:-[A-Za-z0-9]+(?:[.-][A-Za-z0-9]+)*)?',version):
        raise ValueError('Invalid application version.')
    if destination == ROOT or (destination.is_relative_to(ROOT) and destination.relative_to(ROOT).parts[0] in DIRECTORIES):
        raise ValueError('Choose an output directory outside deployable source folders.')
    destination.mkdir(parents=True, exist_ok=True)
    archive = destination/f'Spiritual-Gorillaz-{version}-Full.zip'
    receipt = destination/f'Spiritual-Gorillaz-{version}-PRIVATE-SETUP.txt'
    if archive.exists() or receipt.exists():
        raise FileExistsError('Refusing to overwrite a versioned package or setup receipt.')
    code = secrets.token_urlsafe(32)
    digest = hashlib.sha256(code.encode()).hexdigest()
    manifest = {}
    created_archive = created_receipt = False
    try:
        fd = os.open(receipt,os.O_WRONLY|os.O_CREAT|os.O_EXCL,0o600)
        created_receipt = True
        with os.fdopen(fd,'w',encoding='utf-8') as handle:
            handle.write(f'SPIRITUAL GORILLAZ — PRIVATE SETUP RECEIPT\nVersion: {version}\n\nSetup code: {code}\n\n'
                         'KEEP PRIVATE. Do not upload this receipt to the website or commit it to Git.\n'
                         'Extract only the Full ZIP, including hidden .htaccess files.\n'
                         'Open https://YOUR-DOMAIN/YOUR-FOLDER/install/ and enter the code above.\n'
                         'Create an isolated database/user in the hosting panel first. See docs/INSTALLATION.md.\n'
                         'This development build is not the complete accepted v1.0 website.\n')
            handle.flush(); os.fsync(handle.fileno())
        with archive.open('xb') as handle:
            created_archive = True
            with zipfile.ZipFile(handle,'w',compression=zipfile.ZIP_DEFLATED) as output:
                for source in sorted(ROOT.rglob('*')):
                    if not eligible(source,destination):
                        continue
                    name = source.relative_to(ROOT).as_posix()
                    body = source.read_bytes()
                    output.writestr(name,body)
                    manifest[name]=hashlib.sha256(body).hexdigest()
                access=f"<?php declare(strict_types=1); return '{digest}';\n".encode()
                output.writestr('install/access.php',access)
                manifest['install/access.php']=hashlib.sha256(access).hexdigest()
                output.writestr('docs/PACKAGE-MANIFEST.json',json.dumps({'version':version,'files':manifest},indent=2))
            handle.flush(); os.fsync(handle.fileno())
    except BaseException:
        if created_archive:
            archive.unlink(missing_ok=True)
        if created_receipt:
            receipt.unlink(missing_ok=True)
        raise
    return archive,receipt

if __name__=='__main__':
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output',type=Path,default=ROOT/'dist')
    args=parser.parse_args()
    try:
        archive,receipt=package(args.output)
    except (OSError,ValueError) as error:
        parser.exit(1,f'Packaging failed: {error}\n')
    print(f'ZIP: {archive}\nPrivate setup receipt: {receipt}')
