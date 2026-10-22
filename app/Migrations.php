<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;

/** Versioned, additive schema changes; never invoked from a public page. */
final class Migrations
{
    public static function files(): array
    {
        $files = glob(Paths::root() . '/config/database/migrations/[0-9][0-9][0-9].sql') ?: [];
        if (!$files) { throw new RuntimeException('Migration source files are missing. Restore config/database/migrations before continuing.'); }
        sort($files, SORT_STRING); return $files;
    }
    public static function ready(): bool
    {
        $db = Database::connect(); $prefix = Paths::config()['db']['prefix'];
        if (!Database::exists($db, $prefix . 'migrations')) { return false; }
        $rows = $db->query('SELECT migration,checksum FROM `' . $prefix . 'migrations`')->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach (self::files() as $file) {
            if (!isset($rows[basename($file)]) || !hash_equals($rows[basename($file)], hash_file('sha256', $file))) { return false; }
        }
        return true;
    }
    /** Caller holds the database-prefix advisory lock. */
    public static function apply(PDO $db, string $prefix): array
    {
        if (!preg_match('/^[a-z][a-z0-9_]{0,15}$/D', $prefix)) { throw new RuntimeException('Invalid migration prefix.'); }
        $files = self::files(); // Validate source before creating or changing schema.
        $ledger = '`' . $prefix . 'migrations`';
        $db->exec('CREATE TABLE IF NOT EXISTS ' . $ledger . ' (migration VARCHAR(40) PRIMARY KEY, checksum CHAR(64) NOT NULL, applied_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $rows = $db->query('SELECT migration,checksum FROM ' . $ledger)->fetchAll(PDO::FETCH_KEY_PAIR); $applied = [];
        foreach ($files as $file) {
            $name = basename($file); $checksum = hash_file('sha256', $file);
            if (isset($rows[$name])) {
                if (!hash_equals($rows[$name], $checksum)) { throw new RuntimeException('An applied migration changed: ' . $name . '. Restore its original source; no later changes were applied.'); }
                continue;
            }
            $sql = str_replace('{{prefix}}', $prefix, (string)file_get_contents($file));
            // Committed migration files are deliberately simple, idempotent DDL.
            // MySQL DDL auto-commits; do not pretend a transaction rolls it back.
            foreach (explode(';', $sql) as $statement) { if (trim($statement) !== '') { $db->exec($statement); } }
            $q = $db->prepare('INSERT INTO ' . $ledger . ' (migration,checksum,applied_at) VALUES (?,?,UTC_TIMESTAMP())'); $q->execute([$name,$checksum]);
            $applied[] = $name;
        }
        return $applied;
    }
}
