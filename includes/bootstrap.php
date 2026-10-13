<?php
declare(strict_types=1);

const SPIRIT_ROOT = __DIR__ . '/..';
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Spirit\\')) {
        $name = substr($class, 7);
        if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/D', $name)) {
            $file = SPIRIT_ROOT . '/app/' . $name . '.php';
            if (is_file($file)) { require $file; }
        }
    }
});
require_once __DIR__ . '/helpers.php';
date_default_timezone_set('UTC');
umask(0077);
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; font-src 'self'; media-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    header('X-Frame-Options: DENY');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    set_exception_handler(static function (Throwable $error): void {
        $id = bin2hex(random_bytes(6));
        // Never log exception messages: database drivers may include credentials/PII.
        error_log('Spirit failure ' . $id . ' [' . get_class($error) . ']');
        http_response_code(503);
        header('Cache-Control: no-store');
        echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Service unavailable</title><h1>Temporarily unavailable</h1><p>Please try again later. Reference: ' . $id . '</p></html>';
    });
    if (!Spirit\Security::secureTransport()) {
        http_response_code(403);
        exit('HTTPS is required. Enable HTTPS in your hosting control panel before continuing. Forwarded headers are not automatically trusted.');
    }
    Spirit\Security::startSession();
}
