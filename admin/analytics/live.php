<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
Spirit\ContentAdmin::boot();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) { header('Allow: GET, HEAD'); http_response_code(405); exit; }
if (($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site') { http_response_code(403); exit; }
try {
    $live = Spirit\Analytics::live();
    if (!$live['available']) { http_response_code(503); }
    if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') { echo json_encode($live, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR); }
} catch (Throwable $error) { http_response_code(503); echo '{"available":false}'; }
