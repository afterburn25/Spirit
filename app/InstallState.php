<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;

/** Durable installation ownership, including interruption immediately after CREATE. */
final class InstallState
{
    public static function lock(PDO $db, string $prefix): string
    {
        $database = (string)$db->query('SELECT DATABASE()')->fetchColumn();
        $name = 'spirit:' . substr(hash('sha256', $database . ':' . $prefix), 0, 48);
        $q = $db->prepare('SELECT GET_LOCK(?,0)'); $q->execute([$name]);
        if ((int)$q->fetchColumn() !== 1) { throw new RuntimeException('Another installation or database update is already using this table prefix.'); }
        return $name;
    }
    public static function unlock(PDO $db, string $name): void
    {
        $q = $db->prepare('SELECT RELEASE_LOCK(?)'); $q->execute([$name]);
    }
    public static function ensure(PDO $db, string $prefix, string $fingerprint, string $version): void
    {
        if (!preg_match('/^[a-z][a-z0-9_]{0,15}$/D', $prefix) || !preg_match('/^[a-f0-9]{64}$/D', $fingerprint)) { throw new RuntimeException('Invalid installation ownership metadata.'); }
        $name = $prefix . 'installation'; $table = '`' . $name . '`';
        $comment = 'spirit-owner:' . $fingerprint;
        if (!Database::exists($db, $name)) {
            // The comment is committed with the table itself. An empty table can be
            // resumed only when this durable ownership fingerprint matches.
            $db->exec('CREATE TABLE ' . $table . ' (id INT PRIMARY KEY, fingerprint CHAR(64) NOT NULL, status VARCHAR(20) NOT NULL, version VARCHAR(40) NOT NULL) ENGINE=InnoDB COMMENT=' . $db->quote($comment));
        }
        $row = $db->query('SELECT * FROM ' . $table . ' WHERE id=1')->fetch();
        if ($row) {
            if (!hash_equals($fingerprint, (string)$row['fingerprint'])) { throw new RuntimeException('Existing installation ownership does not match. Refusing to overwrite data.'); }
            return;
        }
        $q = $db->prepare('SELECT table_comment FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?'); $q->execute([$name]);
        if (!hash_equals($comment, (string)$q->fetchColumn()) || (int)$db->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn() !== 0) {
            throw new RuntimeException('The existing installation table has no verifiable ownership row. Existing data was left untouched; do not delete the table to retry.');
        }
        $q = $db->prepare('INSERT INTO ' . $table . " (id,fingerprint,status,version) VALUES (1,?,'provisioning',?)");
        $q->execute([$fingerprint, $version]);
    }
}
