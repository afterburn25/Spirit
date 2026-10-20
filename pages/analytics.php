<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

use Spirit\{Analytics, Paths};

header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); http_response_code(405); exit; }
try {
    $config = Paths::config();
    if (!$config || !Analytics::sameOrigin($_SERVER, $config['site_url'])) { http_response_code(403); exit; }
    $type = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
    if (!in_array($type, ['application/json', 'text/plain'], true)) { http_response_code(415); exit; }
    $length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($length > 1024 || $length < 0) { http_response_code(413); exit; }
    $raw = file_get_contents('php://input', false, null, 0, 1025);
    if (!is_string($raw) || strlen($raw) > 1024) { http_response_code(413); exit; }
    $payload = json_decode($raw, true, 4, JSON_THROW_ON_ERROR);
    if (!is_array($payload) || array_is_list($payload)) { http_response_code(400); exit; }
    Analytics::validateHeartbeat($payload);
    // A validly shaped sample may be expired, rate-limited or disabled. No private
    // state is exposed and measurement failure never interrupts the public site.
    Analytics::heartbeat($payload);
    http_response_code(204);
} catch (InvalidArgumentException|JsonException) { http_response_code(400); }
catch (Throwable) { http_response_code(204); }
