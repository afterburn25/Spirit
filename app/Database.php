<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class Database
{
    public static function validate(array $input): array
    {
        $host = trim((string)($input['host'] ?? ''));
        $name = trim((string)($input['name'] ?? ''));
        $user = trim((string)($input['user'] ?? ''));
        $prefix = trim((string)($input['prefix'] ?? 'sg_'));
        $port = filter_var($input['port'] ?? 3306, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if (!preg_match('/^[A-Za-z0-9._-]{1,253}$/D', $host) || !$port) { throw new InvalidArgumentException('Enter a valid database hostname and port.'); }
        if (!preg_match('/^[A-Za-z0-9_$-]{1,64}$/D', $name) || $user === '' || strlen($user) > 128 || str_contains($user, "\0")) { throw new InvalidArgumentException('Enter the database name and username from your hosting panel.'); }
        if (!preg_match('/^[a-z][a-z0-9_]{0,15}$/D', $prefix)) { throw new InvalidArgumentException('Table prefix must be 1-16 lowercase letters, digits, or underscores, starting with a letter.'); }
        return ['host' => $host, 'port' => $port, 'name' => $name, 'user' => $user, 'password' => (string)($input['password'] ?? ''), 'prefix' => $prefix];
    }
    public static function connect(?array $configuration = null): PDO
    {
        if($configuration===null){MaintenanceGate::enter();}
        $c = self::validate($configuration ?? (Paths::config()['db'] ?? []));
        return new PDO('mysql:host=' . $c['host'] . ';port=' . $c['port'] . ';dbname=' . $c['name'] . ';charset=utf8mb4', $c['user'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => 10]);
    }
    public static function table(string $name): string
    {
        if (!preg_match('/^[a-z_]+$/D', $name)) { throw new InvalidArgumentException('Invalid table.'); }
        return '`' . (Paths::config()['db']['prefix'] ?? throw new RuntimeException('No database configuration.')) . $name . '`';
    }
    public static function exists(PDO $db, string $name): bool
    {
        $q = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?');
        $q->execute([$name]); return (int)$q->fetchColumn() > 0;
    }
    public static function probe(PDO $db): void
    {
        $name = '`sg_probe_' . bin2hex(random_bytes(10)) . '`';
        $created = false;
        try {
            $db->exec('CREATE TABLE ' . $name . ' (id INT PRIMARY KEY, note VARCHAR(20)) ENGINE=InnoDB'); $created = true;
            $db->exec('INSERT INTO ' . $name . " VALUES (1,'write')");
            $db->exec('UPDATE ' . $name . " SET note='verified' WHERE id=1");
            if ($db->query('SELECT note FROM ' . $name . ' WHERE id=1')->fetchColumn() !== 'verified') { throw new RuntimeException('Database read/write test failed.'); }
            $db->exec('ALTER TABLE ' . $name . ' ADD checked_at INT NULL');
            $db->exec('DELETE FROM ' . $name . ' WHERE id=1');
        } finally {
            if ($created) { $db->exec('DROP TABLE ' . $name); }
        }
    }
    public static function audit(PDO $db, ?int $user, string $action): void
    {
        $q = $db->prepare('INSERT INTO ' . self::table('audit') . ' (user_id, action, created_at) VALUES (?,?,UTC_TIMESTAMP())');
        $q->execute([$user, $action]);
    }
}
