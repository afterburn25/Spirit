<?php
declare(strict_types=1);
namespace Spirit;
use Throwable;

final class StaffAccessPage
{
    public static function render(bool $recovery=false): void
    {
        if(!Paths::installed()){go('install/');}
        header('Cache-Control: private, no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
        if(!Migrations::ready()){http_response_code(503);exit('An owner must apply the pending database update before staff recovery is available.');}
        $error=null;$done=false;$token=is_string($_POST['token']??$_GET['token']??null)?($_POST['token']??$_GET['token']):'';
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                if($recovery){
                    if(post('company_website')===''){try{Staff::recover(post('email'));}catch(Throwable $e){error_log('Spirit staff recovery deferred ['.get_class($e).']');}}
                    $done=true;Communications::scheduleDelivery();
                }else{
                    RequestLimit::consume('staff-token-completion',12,900);Staff::complete($token,['password'=>password_input('password'),'password_confirm'=>password_input('password_confirm')]);$done=true;
                }
            }catch(\InvalidArgumentException|\RuntimeException $e){$error=Storefront::error($e);}
        }
        $valid=$recovery?null:Staff::token(preg_match('/^[a-f0-9]{64}$/D',$token)?hash('sha256',$token):'');
        View::start($recovery?'Staff password recovery':'Complete staff access','login');require Paths::root().'/views/admin/staff-access.php';View::end();
    }
}
