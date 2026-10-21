<?php
declare(strict_types=1);
require dirname(__DIR__, 3).'/includes/bootstrap.php';
use Spirit\{Paths,Migrations,Security,RequestLimit,Newsletter,Communications,PublicSite};
header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
if(!Paths::installed()||!Migrations::ready()){http_response_code(503);exit('Subscription confirmation is temporarily unavailable.');}
$token=is_string($_GET['token']??null)?$_GET['token']:'';$result=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 Security::checkPost();$token=post('token');
 try{RequestLimit::consume('newsletter-token',30,900);$result=Newsletter::confirm($token)?'Your subscription is confirmed. Welcome to the movement.':'This confirmation link is invalid, expired or already used. You can request a new signup link.';Communications::scheduleDelivery();}catch(RuntimeException $e){$result='Confirmation is temporarily unavailable. Please try again later.';}
}
PublicSite::render('newsletter',null,['mode'=>'confirm','token'=>Newsletter::validToken($token)?$token:'','result'=>$result]);
