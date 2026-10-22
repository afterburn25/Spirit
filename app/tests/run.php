<?php
declare(strict_types=1);
// Isolate the original checks from any real account-home runtime/configuration.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(1); }
$unitHome = sys_get_temp_dir() . '/spirit-unit-home-' . bin2hex(random_bytes(8));
mkdir($unitHome, 0700);
putenv('SPIRIT_ACCOUNT_HOME=' . $unitHome);
putenv('SPIRIT_PUBLIC_ROOTS');
unset($_SERVER['SPIRIT_PUBLIC_ROOTS']);
register_shutdown_function(static function () use ($unitHome): void {
    $remove = static function (string $path) use (&$remove): void {
        if (is_link($path) || is_file($path)) { unlink($path); return; }
        foreach (array_diff(scandir($path), ['.', '..']) as $entry) { $remove($path . '/' . $entry); }
        rmdir($path);
    };
    if (is_dir($unitHome)) { $remove($unitHome); }
});
require __DIR__ . '/unit_checks.inc';
