#!/usr/bin/env python3
"""Build a cumulative shared-host ZIP with a code-free single-page installer.
Run on a development machine; the host requires neither Python nor SSH.
"""
from __future__ import annotations
import argparse
import hashlib
import importlib.util
import json
import os
import re
import subprocess
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DIRECTORIES = {'app','admin','includes','config','database','storage','uploads','views','assets','images','install','docs','music','shows','band','media','booking','press','shop','account','pages','newsletter','tasks','payments'}
ROOT_FILES = {'.htaccess','index.php','VERSION','README.md','CHANGELOG.md'}
PRIVATE_SUFFIXES = {'.log','.zip','.pem','.key','.p12','.pfx','.sqlite','.sqlite3','.bak','.gz'}
HERO_SOURCE_FILES = (
    'spiritual-gorillaz-home-hero.png',
    'spiritual-gorillaz-music-hero.png',
    'spiritual-gorillaz-shows-hero.png',
    'spiritual-gorillaz-band-hero.png',
    'spiritual-gorillaz-booking-hero.png',
    'spiritual-gorillaz-merch-hero.png',
)


def generate_hero_assets() -> None:
    """Create deterministic wide WebP hero masters before collecting payload files.

    Release/candidate workflows install Pillow and therefore always regenerate the
    production WebP masters. Other CI jobs exercise packaging as a dependency of
    unrelated browser and maintenance tests; when those lean jobs intentionally do
    not install Pillow, they package the reviewed PNG fallbacks instead of failing.
    A normal developer-initiated package outside CI still fails with a clear
    dependency message if Pillow is unavailable.

    The isolated packaging unit tests replace ROOT with a minimal synthetic tree
    that contains neither production artwork nor development tools. In that test
    tree there is nothing to generate, so packaging proceeds normally.
    """
    generator = ROOT / 'tools' / 'generate_extended_heroes.py'
    has_production_hero_source = any((ROOT / 'images' / name).is_file() for name in HERO_SOURCE_FILES)
    if not generator.is_file():
        if not has_production_hero_source:
            return
        raise FileNotFoundError('Missing deterministic hero generator.')

    if importlib.util.find_spec('PIL') is None:
        if os.environ.get('CI', '').lower() in {'1', 'true', 'yes'}:
            return
        raise RuntimeError('Pillow 12.3.0 is required to generate the production hero masters.')

    try:
        subprocess.run([sys.executable, str(generator)], cwd=ROOT, check=True, capture_output=True, text=True)
    except subprocess.CalledProcessError as error:
        detail = (error.stderr or error.stdout or str(error)).strip()
        raise RuntimeError(f'Hero asset generation failed: {detail}') from error


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
    if source.name.startswith('.env') or any(marker in source.name.upper() for marker in ('PRIVATE-SETUP','PRIVATE-KEY','PRIVATE-RECOVERY')) or source.suffix.lower() in PRIVATE_SUFFIXES:
        return False
    if relative.as_posix() in {'install/access.php','install/package-key.php','admin/upgrades/location.php','docs/PACKAGE-MANIFEST.json'}:
        return False
    if relative.parts[0] in {'config','storage','uploads'}:
        return len(relative.parts)==2 and source.name in {'.htaccess','README.md'}
    if source.suffix.lower()=='.sql' and relative.parts[:2] != ('database','migrations'):
        return False
    return True


def package(destination: Path) -> Path:
    destination = destination.resolve()
    version = (ROOT/'VERSION').read_text().strip()
    if not re.fullmatch(r'\d+\.\d+\.\d+(?:-[A-Za-z0-9]+(?:[.-][A-Za-z0-9]+)*)?',version):
        raise ValueError('Invalid application version.')
    if destination == ROOT or (destination.is_relative_to(ROOT) and destination.relative_to(ROOT).parts[0] in DIRECTORIES):
        raise ValueError('Choose an output directory outside deployable source folders.')
    generate_hero_assets()
    destination.mkdir(parents=True, exist_ok=True)
    archive = destination/f'Spiritual-Gorillaz-{version}-Full.zip'
    if archive.exists():
        raise FileExistsError('Refusing to overwrite a versioned package.')
    manifest = {}
    created_archive = False
    try:
        with archive.open('xb') as handle:
            created_archive = True
            with zipfile.ZipFile(handle,'w',compression=zipfile.ZIP_DEFLATED) as output:
                def write_entry(name: str, body: bytes) -> None:
                    info=zipfile.ZipInfo(name,date_time=(1980,1,1,0,0,0))
                    info.compress_type=zipfile.ZIP_DEFLATED
                    info.create_system=3
                    info.external_attr=0o100644 << 16
                    output.writestr(info,body)

                for source in sorted(ROOT.rglob('*')):
                    if not eligible(source,destination):
                        continue
                    name = source.relative_to(ROOT).as_posix()
                    body = source.read_bytes()
                    write_entry(name,body)
                    manifest[name]=hashlib.sha256(body).hexdigest()
                manifest_body=json.dumps({'version':version,'files':manifest},indent=2).encode()
                write_entry('docs/PACKAGE-MANIFEST.json',manifest_body)
            handle.flush(); os.fsync(handle.fileno())
    except BaseException:
        if created_archive:
            archive.unlink(missing_ok=True)
        raise
    return archive


if __name__=='__main__':
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output',type=Path,default=ROOT/'dist')
    args=parser.parse_args()
    try:
        archive=package(args.output)
    except (OSError,ValueError,RuntimeError) as error:
        parser.exit(1,f'Packaging failed: {error}\n')
    print(f'ZIP: {archive}\nNo setup code or private setup receipt is required.')
