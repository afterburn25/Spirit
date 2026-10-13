<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

final class Paths
{
    public static function root(): string { return realpath(SPIRIT_ROOT) ?: throw new RuntimeException('Application root unavailable.'); }
    public static function documentRoot(): string
    {
        $path = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
        if (!$path || $path === DIRECTORY_SEPARATOR) { throw new RuntimeException('A valid document root is required.'); }
        return rtrim($path, DIRECTORY_SEPARATOR);
    }
    public static function within(string $path, string $parent): bool
    {
        return $path === $parent || str_starts_with($path, rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
    }
    public static function runtime(): string
    {
        $parent = realpath(dirname(self::documentRoot()));
        if (!$parent || $parent === DIRECTORY_SEPARATOR) { throw new RuntimeException('No private account directory is available outside the document root.'); }
        return $parent . '/.spirit-' . substr(hash('sha256', self::root()), 0, 20);
    }
    public static function config(): ?array
    {
        $path = self::runtime() . '/config.php';
        if (!file_exists($path)) { return null; }
        if (is_link(dirname($path)) || is_link($path)) { throw new RuntimeException('Private configuration must not be a symlink.'); }
        $data = require $path;
        if (!is_array($data) || ($data['installed'] ?? false) !== true) { throw new RuntimeException('Configuration is incomplete; installation remains blocked.'); }
        return $data;
    }
    public static function installed(): bool { return self::config() !== null || is_file(self::runtime() . '/installed.lock'); }
    public static function basePath(): string
    {
        $script = (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $file = str_replace('\\', '/', (string)($_SERVER['SCRIPT_FILENAME'] ?? self::root() . '/index.php'));
        $relative = substr($file, strlen(str_replace('\\', '/', self::root())));
        if ($relative !== '' && str_ends_with($script, $relative)) { $base = substr($script, 0, -strlen($relative)); }
        else { $base = ''; }
        if (!preg_match('#^(?:/[A-Za-z0-9_.~-]+)*$#D', $base) || str_contains($base, '..')) { throw new RuntimeException('Unsupported installation URL path.'); }
        return rtrim($base, '/');
    }
    public static function version(): string { return trim((string)file_get_contents(self::root() . '/VERSION')); }
}
