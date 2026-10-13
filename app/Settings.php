<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use Throwable;

final class Settings
{
    public static function all(): array
    {
        return Database::connect()->query('SELECT setting_key,setting_value FROM ' . Database::table('settings'))->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
    public static function save(array $values, int $owner): void
    {
        $limits = ['site_name' => 120, 'tagline' => 200, 'welcome' => 1200];
        foreach ($limits as $key => $limit) {
            if (!isset($values[$key]) || !is_string($values[$key]) || trim($values[$key]) === '' || strlen($values[$key]) > $limit) { throw new InvalidArgumentException('All fields are required and must fit their displayed limits.'); }
        }
        $db = Database::connect(); $db->beginTransaction();
        try {
            $q = $db->prepare('UPDATE ' . Database::table('settings') . ' SET setting_value=? WHERE setting_key=?');
            foreach ($limits as $key => $limit) { $q->execute([trim($values[$key]), $key]); }
            Database::audit($db, $owner, 'settings.updated'); $db->commit();
        } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
    }
}
