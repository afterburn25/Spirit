<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\Auth::requireOwner();
header('Cache-Control: private, no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
$method=$_SERVER['REQUEST_METHOD']??'GET';
if(!in_array($method,['GET','HEAD','POST'],true)){header('Allow: GET, HEAD, POST');http_response_code(405);exit('Method not allowed.');}
if($method==='POST'){
    Spirit\Security::checkPost();
    if(post('action')!=='export'){http_response_code(400);exit('Unsupported diagnostic action. No settings were changed.');}
}
$snapshot=Spirit\LaunchChecks::snapshot();
if($method==='POST'){
    $body=json_encode(Spirit\LaunchChecks::supportReport($snapshot),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
    header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename="Spirit-support-report.json"');header('Content-Length: '.strlen($body));echo $body;exit;
}
Spirit\View::start('Launch checks','admin','launch',$user);
require Spirit\Paths::root().'/app/views/admin/launch.php';
Spirit\View::end();
