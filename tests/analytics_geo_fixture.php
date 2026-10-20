<?php
declare(strict_types=1);
// Synthetic CLI fixtures only. Never shipped in a deployment package.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { http_response_code(404); exit(1); }
$root = (string)getenv('SPIRIT_FIXTURE_ROOT');
$_SERVER['DOCUMENT_ROOT'] = (string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if (!is_file($root . '/includes/bootstrap.php')) { throw new RuntimeException('Missing disposable application.'); }
require $root . '/includes/bootstrap.php';
use Spirit\{AnalyticsGeo, AnalyticsGeoAdmin, Database, Paths};
$config = Paths::config();
if (!$config || !preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $config['db']['name'])
    || !preg_match('/^sgci_[a-f0-9]{8}_$/D', $config['db']['prefix']) || $config['db']['prefix'] !== getenv('SPIRIT_DB_PREFIX')) {
    throw new RuntimeException('Refusing non-test geography configuration.');
}
$input = json_decode(stream_get_contents(STDIN) ?: '{}', true, 8, JSON_THROW_ON_ERROR);
$db = Database::connect();
switch ($argv[1] ?? '') {
case 'state':
    $result = ['status' => AnalyticsGeo::status(), 'lookup' => AnalyticsGeo::lookup('81.2.69.142')];
    break;
case 'role':
    if (!in_array($input['role'] ?? null, ['owner', 'content_editor', 'store_manager'], true)) { throw new RuntimeException('Unknown test role.'); }
    $db->prepare('UPDATE ' . Database::table('users') . ' SET role=?')->execute([$input['role']]);
    $result = ['ok' => true];
    break;
case 'guard-checks':
    $owner = $db->query('SELECT id,role,active,session_version FROM ' . Database::table('users') . ' ORDER BY id LIMIT 1')->fetch();
    $path = AnalyticsGeo::cityPath();
    if (!$owner || $owner['role'] !== 'owner' || !is_file($path) || is_link($path)) { throw new RuntimeException('Expected installed test City data and owner.'); }
    $digest = hash_file('sha256', $path); $checks = [];
    $cases = [
        ['name' => 'A stale owner session cannot remove City data', 'session_version' => (int)$owner['session_version'] + 1],
        ['name' => 'A different actor session cannot remove City data', 'user_id' => (int)$owner['id'] + 1],
        ['name' => 'Demotion is rechecked while holding the current owner row', 'role' => 'content_editor'],
        ['name' => 'An inactive owner is refused at the final filesystem mutation', 'active' => 0],
    ];
    foreach ($cases as $case) {
        $_SESSION = ['user_id' => $case['user_id'] ?? (int)$owner['id'], 'session_version' => $case['session_version'] ?? (int)$owner['session_version']];
        $db->prepare('UPDATE ' . Database::table('users') . ' SET role=?,active=? WHERE id=?')->execute([$case['role'] ?? 'owner', $case['active'] ?? 1, $owner['id']]);
        try {
            $rejected = false;
            try { AnalyticsGeoAdmin::remove((int)$owner['id']); } catch (RuntimeException) { $rejected = true; }
            clearstatcache(true, $path);
            if (!$rejected || !is_file($path) || hash_file('sha256', $path) !== $digest || $db->inTransaction()) {
                throw new RuntimeException($case['name']);
            }
            $checks[] = $case['name'];
        } finally {
            $db->prepare('UPDATE ' . Database::table('users') . ' SET role=?,active=? WHERE id=?')->execute([$owner['role'], $owner['active'], $owner['id']]);
        }
    }
    $result = ['checks' => $checks];
    break;
default: throw new RuntimeException('Unknown geography fixture command.');
}
echo json_encode($result, JSON_THROW_ON_ERROR);
