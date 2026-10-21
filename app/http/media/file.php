<?php
declare(strict_types=1);
require dirname(__DIR__, 3).'/includes/bootstrap.php';
if (!Spirit\Paths::installed() || !Spirit\Migrations::ready()) {http_response_code(404);exit('Media unavailable.');}
if(!in_array($_SERVER['REQUEST_METHOD'],['GET','HEAD'],true)){header('Allow: GET, HEAD');http_response_code(405);exit;}
Spirit\Media::serve(max(0,(int)($_GET['id']??0)));
