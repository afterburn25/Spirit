<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use Throwable;

final class Settings
{
    public const FOOTER_COPYRIGHT_LIMIT = 240;

    public static function all(): array
    {
        return Database::connect()->query('SELECT setting_key,setting_value FROM ' . Database::table('settings'))->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public static function footerCopyright(array $settings): string
    {
        $copyright = $settings['footer_copyright'] ?? '';
        return is_string($copyright) && trim($copyright) !== ''
            ? trim($copyright)
            : '© ' . gmdate('Y') . ' ' . ($settings['site_name'] ?? 'Spiritual Gorillaz') . '.';
    }

    public static function save(array $values, int $owner): void
    {
        $limits = ['site_name' => 120, 'tagline' => 200, 'welcome' => 1200];
        foreach ($limits as $key => $limit) {
            if (!isset($values[$key]) || !is_string($values[$key]) || trim($values[$key]) === '' || strlen($values[$key]) > $limit) { throw new InvalidArgumentException('All fields are required and must fit their displayed limits.'); }
        }
        $copyright = null;
        if (array_key_exists('footer_copyright', $values)) {
            $copyright = $values['footer_copyright'];
            if (!is_string($copyright) || strlen($copyright) > self::FOOTER_COPYRIGHT_LIMIT || preg_match('//u', $copyright) !== 1 || preg_match('/[\x00-\x1F\x7F]/', $copyright)) {
                throw new InvalidArgumentException('Footer copyright must be one line of plain text, up to ' . self::FOOTER_COPYRIGHT_LIMIT . ' bytes.');
            }
            $copyright = trim($copyright);
        }
        $db = Database::connect(); $db->beginTransaction();
        try {
            $q = $db->prepare('UPDATE ' . Database::table('settings') . ' SET setting_value=? WHERE setting_key=?');
            foreach ($limits as $key => $limit) { $q->execute([trim($values[$key]), $key]); }
            if ($copyright !== null) {
                $q = $db->prepare('INSERT INTO ' . Database::table('settings') . ' (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
                $q->execute(['footer_copyright', $copyright]);
            }
            Database::audit($db, $owner, 'settings.updated'); $db->commit();
        } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
    }
}
