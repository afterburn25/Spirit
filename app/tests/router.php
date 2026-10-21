<?php
// Local test server only. Production uses Apache/LiteSpeed access rules.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$parts = array_values(array_filter(explode('/', $path)));
foreach ($parts as $part) {
    if (str_starts_with($part,'.') || in_array($part, ['app','includes','config','storage','uploads','database','docs','tests','tools','views','design'], true)) { http_response_code(403); exit('Forbidden'); }
}
if (str_ends_with($path,'/upgrades/RecoveryEngine.php') || str_ends_with($path,'/upgrades/location.php') || str_ends_with($path,'/access.php') || str_ends_with($path,'.md') || str_ends_with($path,'/VERSION')) { http_response_code(403); exit('Forbidden'); }
$file = $_SERVER['DOCUMENT_ROOT'] . $path;
if (is_dir($file)) { $file = rtrim($file,'/') . '/index.php'; }
if (!is_file($file)) {
    $root = getenv('SPIRIT_TEST_APP_ROOT');
    if (!is_string($root) || !is_file($root . '/index.php')) { http_response_code(404); exit('Not found'); }
    $doc = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/');
    $base = substr($root, strlen($doc));
    if ($base !== '' && !str_starts_with($path, $base . '/')) { http_response_code(404); exit('Not found'); }
    $_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
    $_SERVER['SCRIPT_NAME'] = $base . '/index.php';
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    require $root . '/index.php';
    return true;
}
return false;
