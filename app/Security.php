<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

final class Security
{
    public static function isLocal(): bool { return PHP_SAPI === 'cli-server' && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true); }
    public static function isHttps(): bool { return ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['SERVER_PORT'] ?? '') === '443'; }
    public static function secureTransport(): bool { return self::isHttps() || self::isLocal(); }
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) { return; }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', '1800');
        session_name('sg_' . substr(hash('sha256', Paths::root()), 0, 12));
        session_set_cookie_params(['lifetime' => 0, 'path' => Paths::basePath() . '/', 'secure' => self::isHttps(), 'httponly' => true, 'samesite' => 'Lax']);
        // Before installation the hosting provider's session storage is used.
        // After installation, sessions move to the account-private runtime.
        if (Paths::installed() && is_dir(Paths::runtime() . '/sessions')) { session_save_path(Paths::runtime() . '/sessions'); }
        if (!session_start()) { throw new RuntimeException('Session storage unavailable.'); }
        if (isset($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > 1800) {
            $_SESSION = []; session_regenerate_id(true);
        }
        $_SESSION['last_activity'] = time();
    }
    public static function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
    public static function validCsrf(string $token): bool { return isset($_SESSION['csrf']) && $token !== '' && hash_equals($_SESSION['csrf'], $token); }
    public static function checkPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !self::validCsrf(post('csrf'))) {
            http_response_code(403); exit('This form expired. Reload the page and try again.');
        }
    }
    public static function password(string $value): string
    {
        if (strlen($value) < 12 || strlen($value) > 72 || str_contains($value, "\0")) {
            throw new InvalidArgumentException('Use a password between 12 and 72 bytes.');
        }
        return password_hash($value, PASSWORD_DEFAULT);
    }
    public static function email(string $value): string
    {
        $value = strtolower(trim($value));
        if (strlen($value) > 190 || !filter_var($value, FILTER_VALIDATE_EMAIL)) { throw new InvalidArgumentException('Enter a valid email address.'); }
        return $value;
    }
    public static function siteUrl(string $value): string
    {
        $parts = parse_url($value);
        if (!$parts || !filter_var($value, FILTER_VALIDATE_URL) || isset($parts['user'], $parts['pass']) || isset($parts['user']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('Use a complete site URL without credentials, a query, or a fragment.');
        }
        if (($parts['scheme'] ?? '') !== 'https' && !(self::isLocal() && ($parts['scheme'] ?? '') === 'http')) {
            throw new InvalidArgumentException('The public site URL must use HTTPS.');
        }
        $path = rtrim($parts['path'] ?? '', '/');
        if (!preg_match('#^(?:/[A-Za-z0-9_.~-]+)*$#D', $path) || str_contains($path, '..')) { throw new InvalidArgumentException('Use a simple root or subfolder URL.'); }
        if ($path !== Paths::basePath()) { throw new InvalidArgumentException('The site URL path must match this installation folder: ' . (Paths::basePath() ?: '/')); }
        return rtrim($value, '/');
    }
}
