<?php
// Local test server only. Production uses Apache/LiteSpeed access rules.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$parts = array_values(array_filter(explode('/', $path)));
foreach ($parts as $part) {
    if (str_starts_with($part,'.') || in_array($part, ['app','includes','config','storage','uploads','database','docs','tests','tools','views','design'], true)) { http_response_code(403); exit('Forbidden'); }
}
if (str_ends_with($path,'/access.php') || str_ends_with($path,'.md') || str_ends_with($path,'/VERSION')) { http_response_code(403); exit('Forbidden'); }
$file = $_SERVER['DOCUMENT_ROOT'] . $path;
if (is_dir($file)) { $file = rtrim($file,'/') . '/index.php'; }
if (!is_file($file)) { http_response_code(404); exit('Not found'); }
return false;
