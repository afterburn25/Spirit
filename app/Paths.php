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
    /** Host configuration only. Never accept a path from a request or HTTP header. */
    private static function hostSetting(string $key): string
    {
        $value = getenv($key);
        if ($value === false || $value === '') { $value = $_SERVER[$key] ?? ''; }
        return is_string($value) ? trim($value) : '';
    }
    public static function publicRoots(?string $accountHome = null): array
    {
        $roots = [self::documentRoot()];
        $publicNames = ['public_html','www','htdocs','httpdocs','public','web'];
        for ($ancestor = self::documentRoot(); $ancestor !== DIRECTORY_SEPARATOR; $ancestor = dirname($ancestor)) {
            if (in_array(basename($ancestor), $publicNames, true)) { $roots[] = $ancestor; }
        }
        $context = $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?? '';
        if (is_string($context) && $context !== '' && ($real = realpath($context))) { $roots[] = $real; }
        $configured = self::hostSetting('SPIRIT_PUBLIC_ROOTS');
        if ($configured !== '') {
            foreach (explode(PATH_SEPARATOR, $configured) as $path) {
                $real = realpath($path);
                if (!$real || !is_dir($real)) { throw new RuntimeException('A host-configured public root is invalid.'); }
                $roots[] = $real;
            }
        }
        if ($accountHome !== null) {
            foreach ($publicNames as $name) {
                $path = realpath($accountHome . '/' . $name);
                if ($path && is_dir($path)) { $roots[] = $path; }
            }
        }
        return array_values(array_unique($roots));
    }
    public static function accountHome(): string
    {
        $configured = self::hostSetting('SPIRIT_ACCOUNT_HOME');
        $candidates = [];
        if ($configured !== '') { $candidates[] = [$configured, true]; }
        else {
            if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
                $account = posix_getpwuid(posix_geteuid());
                if (is_array($account) && !empty($account['dir'])) { $candidates[] = [$account['dir'], false]; }
            }
            $home = getenv('HOME');
            if (is_string($home) && $home !== '') { $candidates[] = [$home, false]; }
        }
        foreach ($candidates as [$candidate, $explicit]) {
            $home = realpath($candidate);
            if (!$home || !is_dir($home) || $home === DIRECTORY_SEPARATOR || is_link($candidate) || rtrim($candidate, DIRECTORY_SEPARATOR) !== $home) {
                if ($explicit) { throw new RuntimeException('The configured account-private base must be an existing canonical directory, not a symlink.'); }
                continue;
            }
            // Inferred process homes must actually contain this application.
            // An explicit host-managed private mount may be elsewhere.
            if (!$explicit && !self::within(self::root(), $home)) { continue; }
            foreach (self::publicRoots($home) as $public) {
                if (self::within($home, $public)) { throw new RuntimeException('The account-private base is inside a configured public root.'); }
            }
            return $home;
        }
        throw new RuntimeException('Cannot verify an account-private base. The hosting configuration must provide SPIRIT_ACCOUNT_HOME outside all public roots. No public-storage fallback is allowed.');
    }
    public static function runtime(): string
    {
        $home = self::accountHome();
        $runtime = $home . '/.spirit-' . substr(hash('sha256', self::root()), 0, 20);
        foreach (self::publicRoots($home) as $public) {
            if (self::within($runtime, $public)) { throw new RuntimeException('Runtime would be inside a public root.'); }
        }
        return $runtime;
    }
    public static function config(): ?array
    {
        $path = self::runtime() . '/config.php';
        if (!file_exists($path) && !is_link($path)) { return null; }
        if (is_link(dirname($path)) || is_link($path) || !is_file($path)) { throw new RuntimeException('Private configuration must be a regular file, not a symlink.'); }
        if ((fileperms($path) & 0077) !== 0) { throw new RuntimeException('Private configuration permissions are too broad.'); }
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
