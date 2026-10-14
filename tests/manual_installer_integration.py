#!/usr/bin/env python3
"""Manual-only installation, legacy-state preservation and real missing extensions.
No provider account is contacted. Only the existing guarded disposable SQL user
is used; this suite never creates databases or users.
"""
from __future__ import annotations
import contextlib
import hashlib
import json
import os
import re
import subprocess
import tempfile
from pathlib import Path
from http_integration import build_package, expect, site, dashboard


def php_info():
    result = subprocess.run(['php','-r', 'echo json_encode(["main"=>php_ini_loaded_file(),"scan"=>php_ini_scanned_files(),"curl"=>extension_loaded("curl"),"zip"=>extension_loaded("zip"),"pdo"=>extension_loaded("pdo_mysql"),"gd"=>extension_loaded("gd")]);'], capture_output=True, text=True, check=True, timeout=10)
    return json.loads(result.stdout)


@contextlib.contextmanager
def without_optional_extensions(base: Path):
    """Copy the current test PHP configuration, excluding only curl/zip loads."""
    info = php_info(); base.mkdir(); scan = base/'scan'; scan.mkdir()
    def copy_config(source, target):
        content = Path(source).read_text() if source else ''
        content = re.sub(r'(?im)^\s*extension\s*=.*\b(?:curl|zip)\b[^\r\n]*', '; Optional extension omitted in disposable test only', content)
        target.write_text(content); target.chmod(0o600)
    copy_config(info['main'], base/'php.ini')
    for index, source in enumerate(filter(None, (p.strip() for p in (info['scan'] or '').split(',')))):
        copy_config(source,scan/f'{index:03}.ini')
    previous = {key:os.environ.get(key) for key in ['PHPRC','PHP_INI_SCAN_DIR']}
    os.environ['PHPRC'] = str(base/'php.ini'); os.environ['PHP_INI_SCAN_DIR'] = str(scan)
    try:
        measured = php_info()
        expect(not measured['curl'] and not measured['zip'] and measured['pdo'] and measured['gd'], 'Actual child PHP has no cURL/ZIP but retains required database and image drivers')
        yield
    finally:
        for key, value in previous.items():
            if value is None: os.environ.pop(key,None)
            else: os.environ[key] = value


def manual_journey(current, missing=False):
    current.open_installer()
    body = current.client.request('install/')[1]
    expect('name="cpanel_' not in body and 'name="db_mode"' not in body, 'No automatic controls or hosting token input are delivered')
    expect('PHP cURL' in body and 'PHP ZIP' in body and 'cURL and ZIP ·' not in body, 'Hosting checks identify optional PHP extensions separately')
    if missing:
        expect('PHP cURL · Notice' in body and 'PHP ZIP · Notice' in body and 'Manual installation can continue' in body, 'Real missing extensions show notices, not mandatory install failures')
        expect('id="install-form" data-ready="true"' in body, 'Missing optional extensions leave the manual form installable')
    for values in [{'db_mode':'cpanel'}, {'db_mode':'unknown'}, {'cpanel_token':'DO-NOT-REFLECT-SYNTHETIC-TOKEN'}]:
        status, response, _, _ = current.client.form('install/', {**current.input(), **values})
        expect(status == 200 and 'id="install-form"' in response and ('removed' in response or 'no longer accepted' in response), 'Retired automatic submissions return correctable manual-only feedback')
        expect('DO-NOT-REFLECT-SYNTHETIC-TOKEN' not in response and not (current.runtime/'config.php').exists() and not (current.runtime/'cpanel-provision.php').exists(), 'Rejected automatic submissions cannot reflect tokens or create installation state')
    record = current.runtime/'cpanel-provision.php'
    record.write_text('<?php throw new RuntimeException("LEGACY-SECRET-MUST-NOT-EXECUTE");')
    record.chmod(0o600); digest = hashlib.sha256(record.read_bytes()).hexdigest()
    response = current.client.form('install/',current.input())[1]
    expect('needs manual review' in response and 'LEGACY-SECRET-MUST-NOT-EXECUTE' not in response, 'An older partial provisioning record is not executed or adopted')
    expect(hashlib.sha256(record.read_bytes()).hexdigest() == digest and not (current.runtime/'config.php').exists(), 'Legacy private evidence remains unchanged before manual owner review')
    record.unlink()  # Test-owned synthetic marker, never an application cleanup.
    response = current.client.form('install/',current.input())
    expect(response[0] == 200 and 'id="install-complete"' in response[1] and response[3].endswith('/install/'), 'Existing database installation completes on the same page')
    expect(current.database('count') == {'users':1,'state':'installed'} and not (current.root/'install').exists(), 'Manual installation creates one owner and physically removes the installer')
    expect(dashboard(current.login()[3]), 'Owner can sign in after manual installation')
    status, body, _, _ = current.client.request('admin/installation.php')
    expect(status==200 and 'PHP cURL' in body and 'PHP ZIP' in body and 'planned for v0.9.0' not in body, 'Owner installation health exposes individual extension results and current maintenance features')
    if missing:
        expect('PHP cURL · Notice' in body and 'PHP ZIP · Notice' in body, 'Installed owner health retains accurate missing-extension notices')
        expect(current.client.request('admin/orders/payments.php')[0]==200 and current.client.request('admin/upgrades/')[0]==200, 'Missing optional extensions do not crash payment settings or upgrade administration')


def main():
    if os.geteuid()==0 or os.environ.get('SPIRIT_TESTING')!='1':
        raise RuntimeError('Use a non-root disposable SQL environment.')
    with tempfile.TemporaryDirectory(prefix='spirit-manual-installer-') as directory:
        base=Path(directory); archive, code=build_package(base/'packages')
        with site(base/'regular',archive,code,'manual') as current:
            manual_journey(current)
        with without_optional_extensions(base/'php-no-optional'):
            for folder in ['', 'band']:
                with site(base/('missing-sub' if folder else 'missing-root'),archive,code,folder) as current:
                    manual_journey(current,missing=True)
    print('Manual-only, rejected auto fields, legacy-record preservation and real no-cURL/no-ZIP SQL/HTTP checks passed.',flush=True)


if __name__=='__main__': main()
