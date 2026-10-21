<?php
declare(strict_types=1);
// All files stay private under app/http; published URLs remain unchanged.
$script = (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php');
$base = rtrim(str_replace('\\', '/', dirname($script)), '/');
if ($base === '.' || $base === '/') { $base = ''; }
if (!preg_match('#^(?:/[A-Za-z0-9_.~-]+)*$#D', $base) || str_contains($base, '..')) {
    http_response_code(400); exit('Unsupported installation URL.');
}
$request = (string)($_SERVER['REQUEST_URI'] ?? '/');
$path = parse_url($request, PHP_URL_PATH);
if (!is_string($path) || ($base !== '' && !str_starts_with($path, $base . '/'))) {
    http_response_code(404); exit('Not found');
}
$path = substr($path, strlen($base));
if ($path === '/' || $path === '/index.php') {
    require_once __DIR__ . '/bootstrap.php';
    if (isset($_GET['sitemap'])) { Spirit\Discovery::sitemap(); }
    Spirit\PublicSite::render('home');
    return;
}
$routes = require __DIR__ . '/public-routes.php';
if (isset($routes[$path])) {
    require dirname(__DIR__) . '/' . $routes[$path];
    return;
}
if (isset($routes[$path . '/'])) {
    $query = parse_url($request, PHP_URL_QUERY);
    header('Location: ' . $base . $path . '/' . (is_string($query) ? '?' . $query : ''), true, 308);
    exit;
}
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');
exit('Not found');
