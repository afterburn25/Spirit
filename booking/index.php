<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{Paths,Migrations,Security,Input,RequestLimit,Bookings,Communications,PublicSite};
if(!Paths::installed()){go('install/');}
if(!Migrations::ready()){PublicSite::render('booking');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 Security::checkPost();
 try{
   if(post('company_website')!==''){throw new InvalidArgumentException('The inquiry could not be verified. Please reload and try again.');}
   $nonce=Input::text($_POST,'submission_nonce',64,true);
   if(isset($_SESSION['booking_last_nonce'])&&hash_equals($_SESSION['booking_last_nonce'],$nonce)&&isset($_SESSION['booking_receipt'])){go('booking/thanks.php');}
   if(!isset($_SESSION['booking_nonce'])||!hash_equals($_SESSION['booking_nonce'],$nonce)){throw new RuntimeException('This inquiry form expired. Reload the page and try again.');}
   RequestLimit::consume('booking',8,3600);
   $result=Bookings::submit($_POST,$nonce);
   $_SESSION['booking_receipt']=$result['reference'];$_SESSION['booking_last_nonce']=$nonce;unset($_SESSION['booking_nonce'],$_SESSION['booking_error'],$_SESSION['booking_old']);
   Communications::scheduleDelivery();go('booking/thanks.php');
 }catch(InvalidArgumentException|RuntimeException $e){
   $_SESSION['booking_error']=$e instanceof PDOException?'The inquiry could not be saved. Please try again later.':$e->getMessage();
   $allowed=['name','email','phone','organization','location','event_type','preferred_date','audience','performance_length','budget','sound','message'];$old=[];
   foreach($allowed as $key){if(is_string($_POST[$key]??null)){$old[$key]=substr($_POST[$key],0,8000);}}$_SESSION['booking_old']=$old;go('booking/');
 }
}
PublicSite::render('booking');
