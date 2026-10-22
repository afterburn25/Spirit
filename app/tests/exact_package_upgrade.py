#!/usr/bin/env python3
"""Upgrade unchanged supplied ZIPs through real owner forms, then recover code.

Requires the same non-root SPIRIT_TESTING / disposable SPIRIT_DB_* environment
as http_integration.py. No ZIP is rebuilt, patched or given a synthetic version.
This checks local SQL/HTTP behavior, not acceptance on the owner's actual host.
"""
from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
import stat
import subprocess
import tempfile
import zipfile
from datetime import datetime, timezone
from pathlib import Path, PurePosixPath

import http_integration as http
from content_integration import png_bytes, raw_request, save_content, upload as upload_media
from upgrade_integration import action, fixture, recovery_receipt, rescue, upload


# Read-only probe: return opaque hashes, never configuration or database rows.
# It deliberately avoids application bootstrap so state can also be inspected
# while the real durable maintenance gate is active. All mutations use HTTP or
# the existing explicitly guarded interrupted-copy fixture.
DATABASE_PROBE = r'''
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { exit(1); }
$root = realpath((string)getenv('SPIRIT_TEST_APP_ROOT'));
$home = realpath((string)getenv('SPIRIT_ACCOUNT_HOME'));
if (!$root || !$home) { throw new RuntimeException('Missing isolated site.'); }
$runtime = $home.'/.spirit-'.substr(hash('sha256', $root), 0, 20);
$config = require $runtime.'/config.php'; $c = $config['db'];
if (!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $c['name']) ||
    !preg_match('/^sgci_[a-f0-9]{8}_$/D', $c['prefix']) ||
    $c['name'] !== getenv('SPIRIT_DB_NAME') ||
    $c['prefix'] !== getenv('SPIRIT_DB_PREFIX')) {
    throw new RuntimeException('Refusing non-test database or prefix.');
}
$db = new PDO('mysql:host='.$c['host'].';port='.$c['port'].';dbname='.$c['name'].';charset=utf8mb4',
    $c['user'], $c['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
$db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
$db->exec('SET TRANSACTION READ ONLY'); $db->beginTransaction();
$q = $db->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND LEFT(TABLE_NAME,?)=? ORDER BY TABLE_NAME');
$q->execute([strlen($c['prefix']), $c['prefix']]);
$tables = $q->fetchAll(PDO::FETCH_COLUMN); $out = ['tables'=>[], 'rows'=>[]];
foreach ($tables as $full) {
    if (!preg_match('/^sgci_[a-f0-9]{8}_[a-z_]+$/D', $full)) { throw new RuntimeException('Unsafe test table.'); }
    $name = substr($full, strlen($c['prefix']));
    if (in_array($name, ['audit','login_limits','request_limits'], true)) { continue; }
    $keys = $db->query('SHOW KEYS FROM `'.$full.'` WHERE Key_name=\'PRIMARY\'')->fetchAll();
    usort($keys, static fn($a,$b)=>$a['Seq_in_index']<=>$b['Seq_in_index']);
    if (!$keys) { throw new RuntimeException('Test table lacks a primary key.'); }
    $order = [];
    foreach ($keys as $key) {
        if (!preg_match('/^[a-z_]+$/D', $key['Column_name'])) { throw new RuntimeException('Unsafe test column.'); }
        $order[] = '`'.$key['Column_name'].'`';
    }
    $q = $db->query('SELECT * FROM `'.$full.'` ORDER BY '.implode(',', $order));
    $digest = hash_init('sha256'); $count = 0;
    while ($row = $q->fetch()) {
        $encoded = json_encode(array_map(static fn($v)=>$v===null?null:base64_encode((string)$v), $row), JSON_THROW_ON_ERROR);
        hash_update($digest, strlen($encoded).':'.$encoded); $count++;
    }
    $out['tables'][$name] = hash_final($digest); $out['rows'][$name] = $count;
}
$shape = [];
foreach (['COLUMNS'=>['TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA','TABLE_NAME,ORDINAL_POSITION'],
          'STATISTICS'=>['TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME,SUB_PART,INDEX_TYPE','TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX']] as $view=>[$fields,$order]) {
    $q = $db->prepare('SELECT '.$fields.' FROM information_schema.'.$view.' WHERE TABLE_SCHEMA=? AND LEFT(TABLE_NAME,?)=? ORDER BY '.$order);
    $q->execute([$c['name'], strlen($c['prefix']), $c['prefix']]); $shape[$view] = $q->fetchAll();
}
$out['schema_sha256'] = hash('sha256', json_encode($shape, JSON_THROW_ON_ERROR));
$out['php'] = PHP_VERSION; $out['database'] = $db->query('SELECT VERSION()')->fetchColumn();
$db->commit(); echo json_encode($out, JSON_THROW_ON_ERROR);
'''


def digest(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def observer(s) -> http.Client:
    # Verification GETs must not change the analytics being compared. This is
    # the application's ordinary visitor privacy signal, not a test bypass.
    client = http.Client(s.base)
    client.opener.addheaders.append(('DNT', '1'))
    return client


def archive_info(path: Path) -> dict:
    path = path.resolve(strict=True)
    with zipfile.ZipFile(path) as bundle:
        names = bundle.namelist()
        safe = all(not name.endswith('/') and not PurePosixPath(name).is_absolute()
                   and '..' not in PurePosixPath(name).parts and '\\' not in name
                   and stat.S_IFMT(info.external_attr >> 16) in (0, stat.S_IFREG)
                   for name, info in zip(names, bundle.infolist()))
        http.expect(safe and len(names) == len(set(n.lower() for n in names)),
                    path.name + ': archive paths are unique regular files')
        manifest = json.loads(bundle.read('docs/PACKAGE-MANIFEST.json'))
        files = manifest['files']
        http.expect(set(names) == set(files) | {'docs/PACKAGE-MANIFEST.json'},
                    path.name + ': original manifest covers the exact payload')
        http.expect(all(hashlib.sha256(bundle.read(name)).hexdigest() == value
                        for name, value in files.items()),
                    path.name + ': every original payload hash matches')
        version = bundle.read('VERSION').decode().strip()
        http.expect(version == manifest['version'], path.name + ': original version matches manifest')
        files = {**files, 'docs/PACKAGE-MANIFEST.json':
                 hashlib.sha256(bundle.read('docs/PACKAGE-MANIFEST.json')).hexdigest()}
    return {'path': str(path), 'version': version, 'sha256': digest(path),
            'bytes': path.stat().st_size, 'files': files}


def payload_matches(s, package: dict, label: str, absent: set[str] | None = None):
    bad = [name for name, value in package['files'].items()
           if not name.startswith('install/') and
           (not (s.root / name).is_file() or digest(s.root / name) != value)]
    http.expect(not bad, label + ': installed payload matches exact ZIP' + (': ' + ', '.join(bad[:5]) if bad else ''))
    http.expect(not (s.root / 'install').exists(), label + ': installer remains physically removed')
    if absent:
        http.expect(all(not (s.root / name).exists() for name in absent),
                    label + ': recovery removes files added only by the target ZIP')


def state_snapshot(s) -> dict:
    result = subprocess.run(['php', '-r', DATABASE_PROBE], env=s.env,
                            capture_output=True, text=True, timeout=30)
    if result.returncode:
        raise AssertionError('Read-only isolated database probe failed: ' + result.stderr)
    database = json.loads(result.stdout)
    media = {}
    for path in sorted((s.runtime / 'media').rglob('*')):
        if path.is_symlink():
            raise AssertionError('Unexpected symlink in isolated media')
        if path.is_file():
            media[str(path.relative_to(s.runtime / 'media'))] = {
                'sha256': digest(path), 'bytes': path.stat().st_size}
    private = {name: {'sha256': digest(s.runtime / name),
                      'mode': stat.S_IMODE((s.runtime / name).stat().st_mode)}
               for name in ('config.php', 'installed.lock')}
    http.expect(all(value['mode'] & 0o077 == 0 for value in private.values()),
                'Original configuration and completion lock remain private')
    http.expect(database['rows']['users'] == 1, 'Original installation retains exactly one owner')
    return {'database': database, 'private': private, 'media': media,
            'runtime': str(s.runtime.resolve())}


def unchanged(s, expected: dict, label: str) -> dict:
    current = state_snapshot(s)
    for key in ('database', 'private', 'media', 'runtime'):
        detail = ''
        if key == 'database' and current[key] != expected[key]:
            names = set(current[key]['tables']) | set(expected[key]['tables'])
            changed = [name for name in sorted(names)
                       if current[key]['tables'].get(name) != expected[key]['tables'].get(name)]
            detail = '; changed tables: ' + ', '.join(changed)
        http.expect(current[key] == expected[key], label + ': unchanged ' + key + detail)
    http.expect(not (s.root / 'install').exists(), label + ': setup was not recreated')
    return current


def seed(s) -> dict:
    public, status, _ = upload_media(s, 'exact-upgrade-public.png', 'image/png', png_bytes())
    http.expect(status == 200 and public > 0, 'Real owner form stores a synthetic QA image')
    private, status, _ = upload_media(s, 'exact-upgrade-private.png', 'image/png', png_bytes())
    http.expect(status == 200 and private > 0, 'Real owner form stores a second private QA image')
    page = save_content(s, 'page', 'Exact upgrade published page',
                        {'body': 'Retained published upgrade content', 'image_id': str(public)},
                        slug='exact-upgrade-content')
    save_content(s, 'page', 'Exact upgrade private draft',
                 {'body': 'Retained unpublished upgrade draft', 'image_id': str(public)},
                 action='save', item_id=page, slug='exact-upgrade-content')
    status, data, _ = raw_request(s.base + f'media/file.php?id={public}')
    http.expect(status == 200, 'Published QA media is retrievable before upgrading')
    return {'public': public, 'private': private, 'page': page,
            'served_sha256': hashlib.sha256(data).hexdigest()}


def content_retained(s, seeded: dict, label: str):
    anon = observer(s)
    status, body, _, _ = anon.request('pages/?slug=exact-upgrade-content')
    http.expect(status == 200 and 'Retained published upgrade content' in body
                and 'Retained unpublished upgrade draft' not in body,
                label + ': original public copy and private draft remain separate')
    preview = s.client.request(f"admin/content/preview.php?id={seeded['page']}")
    http.expect(preview[0] == 200 and 'Retained unpublished upgrade draft' in preview[1],
                label + ': original private draft remains available to the owner')
    status, data, _ = raw_request(s.base + f"media/file.php?id={seeded['public']}")
    http.expect(status == 200 and hashlib.sha256(data).hexdigest() == seeded['served_sha256'],
                label + ': served original media bytes are identical')
    http.expect(anon.request(f"media/file.php?id={seeded['private']}")[0] == 404,
                label + ': unreferenced original media remains private')


def original_owner_works(s, label: str):
    fresh = http.Client(s.base)
    http.expect(http.dashboard(s.login(client=fresh)[3])
                and fresh.request('admin/settings.php')[0] == 200,
                label + ': original credentials still open owner-only administration')


def stage(s, baseline: dict, target: dict):
    response = upload(s, Path(target['path']).read_bytes())
    if response[0] != 200 or 'Package validated and staged' not in response[1]:
        alerts = re.findall(r'role="alert"[^>]*>(.*?)</', response[1], re.S)
        raise AssertionError('Exact target ZIP was not staged: ' + re.sub('<[^>]+>', '', ' '.join(alerts))[:600])
    job, key = recovery_receipt(s)
    http.expect(job['state'] == 'staged' and job['from'] == baseline['version']
                and job['to'] == target['version'] and bool(job['changes']),
                'Actual updater stages the original baseline-to-target version pair')
    http.expect(all(change['after'] == target['files'][name] for name, change in job['changes'].items()),
                'Every staged change comes from the unchanged target ZIP')
    payload_matches(s, baseline, 'After staging')
    return job, key


def public_archive_info(package: dict, provenance: dict[str, str]) -> dict:
    return {**{key: package[key] for key in ('path', 'version', 'sha256', 'bytes')},
            'manifest_files': len(package['files']) - 1,
            'provenance': provenance.get(package['sha256'],
                                         'Supplied archive; original-delivery provenance is not asserted by this test')}


def journey(area: Path, baseline: dict, target: dict, subfolder: str, interrupted: bool) -> dict:
    start = http.CHECKS
    with http.site(area, Path(baseline['path']), None, subfolder) as s:
        s.open_installer()
        status, body, _, _ = s.client.form('install/', s.input())
        http.expect(status == 200 and 'id="install-complete"' in body,
                    'Original baseline ZIP completes its real one-page installer')
        http.expect(http.dashboard(s.login()[3]), 'Original owner signs in before upgrading')
        payload_matches(s, baseline, 'Installed baseline')
        seeded = seed(s)
        content_retained(s, seeded, 'Baseline')
        http.expect(http.Client(s.base).request('pages/?slug=exact-upgrade-content')[0] == 200,
                    'Baseline includes one ordinary anonymous visit before preservation checks')
        before = state_snapshot(s)
        job, key = stage(s, baseline, target)
        unchanged(s, before, 'After staging')
        anchor = digest(s.root / 'admin/upgrades/location.php')
        if interrupted:
            fixture(s, 'interrupt')
            http.expect(fixture(s, 'state')['state'] == 'applying'
                        and observer(s).request('')[0] == 503,
                        'Interrupted copy of real target bytes retains the durable maintenance gate')
            unchanged(s, before, 'After interrupted application')
            preserved = before
        else:
            response = action(s, 'apply', id=job['id'], saved_receipt='1')
            applied = fixture(s, 'state')
            http.expect(response[0] == 200 and applied['state'] == 'awaiting_confirmation',
                        'Real owner apply installs target code pending a separate confirmation')
            payload_matches(s, target, 'After applying')
            backup = s.runtime / 'backups' / ('backup-' + str(applied['pre_backup']) + '.sgbackup')
            http.expect(backup.is_file() and backup.read_bytes().startswith(b'SPIRIT-BACKUP-1\n'),
                        'Apply retains a real encrypted pre-update data snapshot')
            http.expect(observer(s).request('')[0] == 503,
                        'Public requests remain blocked before owner confirmation')
            unchanged(s, before, 'After applying before confirmation')
            response = action(s, 'confirm', id=job['id'])
            http.expect(response[0] == 200 and fixture(s, 'state')['state'] == 'committed'
                        and not (s.runtime / 'updates/active.json').exists()
                        and observer(s).request('')[0] == 200,
                        'Separate real owner confirmation commits exact target and reopens public access')
            unchanged(s, before, 'After confirmation')
            content_retained(s, seeded, 'Confirmed target')
            original_owner_works(s, 'Confirmed target')
            save_content(s, 'page', 'Later content after exact upgrade',
                         {'body': 'Later business content must survive code recovery'},
                         slug='exact-upgrade-later')
            preserved = state_snapshot(s)
        # rescue() always uses a fresh Client and the pinned recovery entrance,
        # without the signed-in application session or replaceable bootstrap.
        response = rescue(s, job, key)
        http.expect(response[0] == 200 and 'Previous code restored' in response[1]
                    and fixture(s, 'state')['state'] == 'rolled_back',
                    'Private receipt recovers original code through the independent entrance')
        http.expect(not (s.runtime / 'updates/active.json').exists()
                    and observer(s).request('')[0] == 200,
                    'Independent recovery clears maintenance and restores public access')
        added = set(target['files']) - set(baseline['files'])
        payload_matches(s, baseline, 'After recovery', added)
        unchanged(s, preserved, 'After independent recovery')
        http.expect(digest(s.root / 'admin/upgrades/location.php') == anchor,
                    'Recovery retains the original private-runtime location')
        content_retained(s, seeded, 'Recovered baseline')
        original_owner_works(s, 'Recovered baseline')
        if not interrupted:
            http.expect('Later business content must survive code recovery' in
                        observer(s).request('pages/?slug=exact-upgrade-later')[1],
                        'Independent code recovery preserves content created after the upgrade')
        http.expect('already been restored' in rescue(s, job, key)[1],
                    'A consumed recovery receipt cannot replay the completed rollback')
        return {'layout': subfolder or 'root',
                'scenario': 'interrupted-copy-recovery' if interrupted else 'apply-confirm-independent-recovery',
                'baseline': baseline['version'], 'target': target['version'], 'result': 'passed',
                'checks': http.CHECKS - start, 'changed_payload_files': len(job['changes']),
                'retained_tables': sorted(before['database']['tables']),
                'excluded_mutable_tables': ['audit', 'login_limits', 'request_limits'],
                'original_media_files': len(before['media']),
                'php': before['database']['php'], 'database': before['database']['database']}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--baseline', type=Path, action='append', required=True,
                        help='Unchanged baseline Full ZIP; repeat for multiple versions')
    parser.add_argument('--target', type=Path, required=True, help='Unchanged target Full ZIP')
    parser.add_argument('--provenance', action='append', default=[], metavar='SHA256=DESCRIPTION',
                        help='Optional explicit archive origin; distinguish source reproductions from recovered deliveries')
    parser.add_argument('--output', type=Path, required=True, help='New directory for a redacted JSON report')
    args = parser.parse_args()
    if os.geteuid() == 0 or os.environ.get('SPIRIT_TESTING') != '1':
        raise RuntimeError('Run as an unprivileged user with SPIRIT_TESTING=1 and disposable SPIRIT_DB_* credentials.')
    if not re.fullmatch(r'spirit_ci(?:_[a-z0-9]+)?', os.environ.get('SPIRIT_DB_NAME', '')):
        raise RuntimeError('Refusing a non-test database name.')
    provenance = {}
    for value in args.provenance:
        sha, separator, description = value.partition('=')
        if not separator or not re.fullmatch('[a-f0-9]{64}', sha) or not description.strip():
            parser.error('--provenance must be SHA256=DESCRIPTION')
        provenance[sha] = description.strip()
    args.output.mkdir(parents=True, exist_ok=False)
    report = {'started_at': datetime.now(timezone.utc).isoformat(),
              'scope': 'Exact supplied ZIP bytes; local disposable SQL/HTTP, not live-host acceptance',
              'scenarios': [], 'result': 'failed'}
    try:
        baselines = [archive_info(path) for path in args.baseline]
        target = archive_info(args.target)
        if set(provenance) - {value['sha256'] for value in [*baselines, target]}:
            raise ValueError('Provenance SHA does not identify one of the supplied archives')
        report['baselines'] = [public_archive_info(value, provenance) for value in baselines]
        report['target'] = public_archive_info(target, provenance)
        with tempfile.TemporaryDirectory(prefix='spirit-exact-upgrade-') as directory:
            for index, baseline in enumerate(baselines):
                for folder, interrupted, label in [('', False, 'root'), ('band', False, 'subfolder'),
                                                   ('band', True, 'interrupted')]:
                    print(f"SCENARIO {baseline['version']} -> {target['version']}: {label}", flush=True)
                    area = Path(directory) / f'{index}-{label}'
                    report['scenarios'].append(journey(area, baseline, target, folder, interrupted))
        http.expect(all(digest(Path(value['path'])) == value['sha256'] for value in [*baselines, target]),
                    'Every supplied baseline and target archive remains byte-identical')
        report['result'] = 'passed'
    except Exception as error:
        report['error'] = type(error).__name__ + ': ' + str(error)
        raise
    finally:
        report['checks'] = http.CHECKS
        report['finished_at'] = datetime.now(timezone.utc).isoformat()
        (args.output / 'report.json').write_text(json.dumps(report, indent=2) + '\n')
    print(f"{http.CHECKS} exact-package upgrade/recovery assertions passed.", flush=True)
    print('REPORT ' + str((args.output / 'report.json').resolve()), flush=True)


if __name__ == '__main__':
    main()
