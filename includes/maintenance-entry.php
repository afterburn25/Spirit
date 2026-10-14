<?php
declare(strict_types=1);
// Frozen early guard: runs before any replaceable application class is loaded.
require_once dirname(__DIR__) . '/admin/upgrades/RecoveryEngine.php';
if (PHP_SAPI !== 'cli') {
    try { $runtime = SpiritRecovery::locate(); if ($runtime !== null) { SpiritRecovery::enter($runtime); } }
    catch (Throwable $e) {
        http_response_code(503); header('Retry-After: 10'); header('Cache-Control: no-store');
        header('Content-Type: text/html; charset=utf-8'); header('X-Content-Type-Options: nosniff');
        echo '<!doctype html><html lang="en"><meta name="viewport" content="width=device-width"><title>Website maintenance</title><main><h1>Website maintenance</h1><p>A protected update or recovery is in progress. Please try again shortly.</p></main></html>'; exit;
    }
}
