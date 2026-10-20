<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user = Spirit\ContentAdmin::boot();
header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
if (!in_array($_SERVER['REQUEST_METHOD'],['GET','HEAD'],true)) { header('Allow: GET, HEAD'); http_response_code(405); exit; }
if (($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site') { http_response_code(403); exit; }
$error = null;
try {
    [$start,$end] = Spirit\AnalyticsAdmin::range($_GET);
    $explorer = Spirit\AnalyticsVisitors::report($start,$end,$_GET);
} catch (InvalidArgumentException $exception) {
    http_response_code(400); $error = $exception->getMessage();
    [$start,$end] = Spirit\AnalyticsAdmin::range([]);
    $explorer = Spirit\AnalyticsVisitors::report($start,$end,[]);
}
if (!$explorer['available']) { http_response_code(503); }
elseif ($explorer['selected_missing']) { http_response_code(404); }
Spirit\View::start('Visitor explorer','admin','analytics-visitors',$user);
require Spirit\Paths::root().'/views/admin/analytics-visitors.php';
Spirit\View::end();
