<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

final class CustomerPortal
{
    public static function render(string $view): void
    {
        Storefront::boot();$error=null;$customer=null;
        if(in_array($view,['account','addresses','password'],true)){$customer=Customers::requireCustomer();}
        $purpose=is_string($_GET['purpose']??null)?$_GET['purpose']:'';$token=is_string($_GET['token']??null)?$_GET['token']:'';
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                if($view==='login'){
                    if(!Customers::login(post('email'),post('password'))){throw new RuntimeException('Sign-in was not accepted. Check your details and verify your email, or try again later.');}go('account/');
                }elseif($view==='register'||$view==='forgot'){
                    if(post('company_website')!==''){flash('Check your email for the next step, if this address is eligible.');go('account/'.$view.'.php');}
                    Customers::requestToken($view==='register'?'activate':'reset',$_POST);flash('Check your email for the next step, if this address is eligible. Delivery can take a few minutes.');go('account/'.$view.'.php');
                }elseif($view==='complete'){
                    RequestLimit::consume('customer-token-complete',20);Customers::finish($purpose,$token,$_POST);flash('Your password is saved. Sign in using your verified email.');go('account/login.php');
                }elseif($view==='password'){
                    Customers::changePassword((int)$customer['id'],$_POST);flash('Password changed. Other customer sessions and unused account links have been revoked.');go('account/');
                }elseif($view==='addresses'){
                    $id=Input::integer($_POST,'id',0,PHP_INT_MAX,0);
                    if(post('action')==='delete'){Addresses::remove((int)$customer['id'],$id,Input::integer($_POST,'revision',1,100000000,1));}
                    else{Addresses::save((int)$customer['id'],$id,$_POST);}
                    flash('Address book updated.');go('account/addresses.php');
                }
            }catch(InvalidArgumentException|RuntimeException $e){$error=Storefront::error($e);}
        }
        Storefront::render($view,compact('error','customer','purpose','token'));
    }
}
