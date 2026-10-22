<?php
declare(strict_types=1);
require dirname(__DIR__, 3).'/includes/bootstrap.php';
use Spirit\{Paths,Migrations,Security,RequestLimit,Newsletter,PublicSite};
header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
if(!Paths::installed()||!Migrations::ready()){http_response_code(503);exit('Subscription management is temporarily unavailable.');}
$token=is_string($_GET['token']??null)?$_GET['token']:'';$result=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 Security::checkPost();$token=post('token');
 try{RequestLimit::consume('newsletter-token',30,900);$result=Newsletter::unsubscribe($token)?'You have been unsubscribed from band updates.':'This unsubscribe link is invalid. Use the link from your most recent subscription email.';}catch(RuntimeException $e){$result='Subscription management is temporarily unavailable. Please try again later.';}
}
PublicSite::render('newsletter',null,['mode'=>'unsubscribe','token'=>Newsletter::validToken($token)?$token:'','result'=>$result]);
