<?php
declare(strict_types=1);
// Test-only: no production data, no web entry point, no secrets in output.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { exit(1); }
define('SPIRIT_UPGRADE_CONTROL', true);
$root = (string)getenv('SPIRIT_FIXTURE_ROOT');
$_SERVER['DOCUMENT_ROOT'] = (string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
require $root.'/includes/bootstrap.php';
$config = Spirit\Paths::config();
if (!$config || !preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $config['db']['name']) ||
    !preg_match('/^sgci_[a-f0-9]{8}_$/D', $config['db']['prefix']) || $config['db']['prefix'] !== getenv('SPIRIT_DB_PREFIX')) {
    throw new RuntimeException('Refusing non-test configuration.');
}
$db = Spirit\Database::connect();
if (($argv[1] ?? '') === 'legacy-reject') {
    $target = (string)getenv('SPIRIT_NEW_PACKAGE');
    $folder = Spirit\Paths::runtime().'/layout-probe';
    mkdir($folder,0700,true);
    try { Spirit\UpdatePackage::stage($target,$folder); echo json_encode(['rejected'=>false]); }
    catch (RuntimeException $e) { echo json_encode(['rejected'=>true,'reason'=>$e->getMessage()]); }
    exit;
}
if (($argv[1] ?? '') === 'old-marker') {
    // Idle old-generation record: retain it, never adopt it as a new checkpoint.
    $folder=Spirit\Paths::runtime().'/updates';
    if (!is_dir($folder) && !mkdir($folder,0700,true)) { throw new RuntimeException('Cannot create isolated recovery fixture directory.'); }
    $written=file_put_contents($folder.'/latest.json', json_encode(['id'=>str_repeat('a',32)]));
    if ($written === false) { throw new RuntimeException('Cannot write isolated recovery fixture marker.'); }
    echo '{}'; exit;
}
if (($argv[1] ?? '') === 'generation') {
    echo json_encode(['layout'=>SpiritRecovery::LAYOUT,'latest'=>SpiritRecovery::latest()]); exit;
}
if (($argv[1] ?? '') !== 'snapshot') { throw new RuntimeException('Unknown fixture action.'); }
$tables=[];
$q=$db->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() AND LEFT(table_name,?)=? ORDER BY table_name');
$q->execute([strlen($config['db']['prefix']),$config['db']['prefix']]);
foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $table) {
    if (!preg_match('/^sgci_[a-f0-9]{8}_[a-z_]+$/D',$table)) { throw new RuntimeException('Invalid fixture table.'); }
    $name=substr($table,strlen($config['db']['prefix']));
    if (in_array($name,['audit','login_limits','request_limits'],true)) { continue; }
    $rows=$db->query('SELECT * FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
    $encoded=array_map(static fn(array $row): string => json_encode($row,JSON_THROW_ON_ERROR),$rows);sort($encoded);
    $tables[$name]=['rows'=>count($rows),'sha256'=>hash('sha256',implode("\n",$encoded))];
}
echo json_encode(['tables'=>$tables,'runtime'=>Spirit\Paths::runtime(),
    'config'=>hash_file('sha256',Spirit\Paths::runtime().'/config.php'),
    'lock'=>hash_file('sha256',Spirit\Paths::runtime().'/installed.lock')],JSON_THROW_ON_ERROR);
