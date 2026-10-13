#!/usr/bin/env python3
"""Build a cumulative shared-host ZIP with a unique installer claim code.

Run on a development machine, never on the shared host. The private receipt is
outside the ZIP and must not be committed or uploaded to the public website.
"""
from __future__ import annotations
import argparse
import hashlib
import json
import secrets
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EXCLUDE = {'.git', '.github', '__pycache__', 'dist', 'build', 'test-results', 'vendor'}

def package(destination: Path) -> tuple[Path, Path]:
    version = (ROOT / 'VERSION').read_text().strip()
    destination.mkdir(parents=True, exist_ok=True)
    archive = destination / f'Spiritual-Gorillaz-{version}-Full.zip'
    receipt = destination / f'Spiritual-Gorillaz-{version}-PRIVATE-SETUP.txt'
    if archive.exists() or receipt.exists():
        raise FileExistsError('Refusing to overwrite an existing versioned package or setup receipt.')
    code = secrets.token_urlsafe(32)
    digest = hashlib.sha256(code.encode()).hexdigest()
    manifest = {}
    with zipfile.ZipFile(archive, 'x', compression=zipfile.ZIP_DEFLATED) as output:
        for source in sorted(ROOT.rglob('*')):
            relative = source.relative_to(ROOT)
            if not source.is_file() or source.is_symlink() or any(p in EXCLUDE for p in relative.parts):
                continue
            if relative.as_posix() in ('install/access.php', 'config/local.php', '.env') or source.suffix in ('.log', '.zip'):
                continue
            if relative.parts[0] in ('storage', 'uploads', 'config') and source.name not in ('.htaccess', 'README.md'):
                continue
            body = source.read_bytes()
            output.writestr(relative.as_posix(), body)
            manifest[relative.as_posix()] = hashlib.sha256(body).hexdigest()
        access = f"<?php declare(strict_types=1); return '{digest}';\n".encode()
        output.writestr('install/access.php', access)
        manifest['install/access.php'] = hashlib.sha256(access).hexdigest()
        output.writestr('docs/PACKAGE-MANIFEST.json', json.dumps({'version':version, 'files':manifest}, indent=2))
    receipt.write_text(
        f'SPIRITUAL GORILLAZ — PRIVATE SETUP RECEIPT\nVersion: {version}\n\n'
        f'Setup code: {code}\n\n'
        'KEEP THIS FILE PRIVATE. Do not upload it to your website or commit it to Git.\n'
        'Extract the Full ZIP into the intended website folder, including hidden .htaccess files.\n'
        'Open https://YOUR-DOMAIN/YOUR-FOLDER/install/ and enter the code above.\n'
        'Create the database/user in your hosting panel first. The installer creates its own private runtime.\n'
        'See docs/INSTALLATION.md inside the ZIP. This development build is not the complete v1.0 website.\n', encoding='utf-8')
    receipt.chmod(0o600)
    return archive, receipt

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=Path, default=ROOT/'dist')
    arguments = parser.parse_args()
    try:
        archive, receipt = package(arguments.output.resolve())
    except (OSError, ValueError) as error:
        parser.exit(1, f'Packaging failed: {error}\n')
    print(f'ZIP: {archive}\nPrivate setup receipt: {receipt}')
