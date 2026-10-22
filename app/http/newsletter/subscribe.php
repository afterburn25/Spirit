<?php
declare(strict_types=1);
require dirname(__DIR__, 3).'/includes/bootstrap.php';
use Spirit\{Paths,Migrations,Security,RequestLimit,Newsletter,Communications};
if(!Paths::installed()||!Migrations::ready()){http_response_code(503);exit('Mailing-list signup is unavailable.');}
Security::checkPost();
try{
 if(post('company_website')!==''){throw new InvalidArgumentException('Signup could not be verified. Please try again.');}
 RequestLimit::consume('newsletter',8,3600);
 Newsletter::request(post('email'),post('marketing_consent')==='1');
 $_SESSION['newsletter_notice']='Check your email for the next step. If this address is already subscribed or recently requested a link, another message may not be sent.';
 Communications::scheduleDelivery();
}catch(InvalidArgumentException|RuntimeException $e){$_SESSION['newsletter_error']=$e instanceof PDOException?'Signup is temporarily unavailable. Please try again later.':$e->getMessage();}
go('newsletter/');
