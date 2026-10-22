<?php
declare(strict_types=1);
// Disposable CLI-only upgrade fixture; never packaged or reachable over HTTP.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { http_response_code(404); exit(1); }
$root = (string)getenv('SPIRIT_FIXTURE_ROOT');
$_SERVER['DOCUMENT_ROOT'] = (string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if (!is_file($root.'/includes/bootstrap.php')) { throw new RuntimeException('Missing disposable application.'); }
require $root.'/includes/bootstrap.php';
use Spirit\{Branding,Database,Paths};
$config = Paths::config();
if (!$config || !preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $config['db']['name']) ||
    !preg_match('/^sgci_[a-f0-9]{8}_$/D', $config['db']['prefix']) || $config['db']['prefix'] !== getenv('SPIRIT_DB_PREFIX')) {
    throw new RuntimeException('Refusing non-test configuration.');
}
$input = json_decode(stream_get_contents(STDIN) ?: '{}', true, 8, JSON_THROW_ON_ERROR);
$db = Database::connect();
switch ($argv[1] ?? '') {
    case 'legacy':
        $state = Branding::decode(json_encode($input, JSON_THROW_ON_ERROR));
        $db->beginTransaction();
        try {
            $q = $db->prepare('INSERT INTO '.Database::table('settings').' (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
            $q->execute([Branding::KEY, json_encode($state, JSON_THROW_ON_ERROR)]);
            $q = $db->prepare('DELETE FROM '.Database::table('settings').' WHERE setting_key=?');
            $q->execute([Branding::SOURCE_KEY]);
            $db->commit();
        } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
        break;
    case 'snapshot': break;
    default: throw new RuntimeException('Unknown branding fixture.');
}
$q = $db->prepare('SELECT setting_key,setting_value FROM '.Database::table('settings').' WHERE setting_key IN (?,?) ORDER BY setting_key');
$q->execute([Branding::KEY, Branding::SOURCE_KEY]);
$stored = $q->fetchAll(PDO::FETCH_KEY_PAIR);
echo json_encode(['record'=>$stored[Branding::KEY] ?? null, 'source'=>$stored[Branding::SOURCE_KEY] ?? null], JSON_THROW_ON_ERROR);
