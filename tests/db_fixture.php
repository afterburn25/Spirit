<?php
declare(strict_types=1);
// Development fixture only: never connect this script to a production database.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { http_response_code(404); exit(1); }
require dirname(__DIR__) . '/includes/bootstrap.php';
use Spirit\Database;
$name = (string)getenv('SPIRIT_DB_NAME');
$prefix = (string)getenv('SPIRIT_DB_PREFIX');
if (!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $name) || !preg_match('/^sgci_[a-f0-9]{8}_$/D', $prefix)) { throw new RuntimeException('Refusing non-test database or table prefix.'); }
try {
    $db = Database::connect(['host'=>getenv('SPIRIT_DB_HOST') ?: '127.0.0.1','port'=>getenv('SPIRIT_DB_PORT') ?: 3306,'name'=>$name,'user'=>getenv('SPIRIT_DB_USER'),'password'=>getenv('SPIRIT_DB_PASSWORD'),'prefix'=>$prefix]);
    $command = $argv[1] ?? '';
    $users = '`' . $prefix . 'users`';
    switch ($command) {
        case 'ping': echo json_encode(['ready'=>true,'database'=>$db->query('SELECT VERSION()')->fetchColumn()]); break;
        case 'count': echo json_encode(['users'=>(int)$db->query('SELECT COUNT(*) FROM '.$users)->fetchColumn(),'state'=>$db->query('SELECT status FROM `'.$prefix.'installation` WHERE id=1')->fetchColumn()]); break;
        case 'collision': $db->exec('CREATE TABLE '.$users.' (id INT PRIMARY KEY) ENGINE=InnoDB'); $db->exec('INSERT INTO '.$users.' VALUES (42)'); echo '{}'; break;
        case 'sentinel': echo json_encode(['id'=>(int)$db->query('SELECT id FROM '.$users)->fetchColumn()]); break;
        case 'editor': $db->exec("UPDATE ".$users." SET role='editor'"); echo '{}'; break;
        case 'owner': $db->exec("UPDATE ".$users." SET role='owner'"); echo '{}'; break;
        case 'limits': $db->exec('DELETE FROM `'.$prefix.'login_limits`'); echo '{}'; break;
        case 'cleanup':
            foreach (['login_limits','audit','settings','users','installation'] as $table) { $db->exec('DROP TABLE IF EXISTS `'.$prefix.$table.'`'); }
            echo '{}'; break;
        default: throw new RuntimeException('Unknown fixture command.');
    }
} catch (Throwable $e) {
    // Credentials and driver messages must never enter public CI logs.
    fwrite(STDERR, 'Fixture failed: '.get_class($e)."\n"); exit(1);
}
