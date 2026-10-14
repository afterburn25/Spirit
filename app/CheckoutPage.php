<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

final class CheckoutPage
{
    public static function render(string $view): void
    {
        Storefront::boot();header('X-Robots-Tag: noindex, nofollow');header('Referrer-Policy: no-referrer');
        $error=null;$customer=Customers::current();$order=null;$reference=is_string($_GET['reference']??null)?$_GET['reference']:'';
        if($view!=='checkout'){
            $access=is_string($_GET['access']??null)?$_GET['access']:'';$order=Orders::accessible($reference,$access);
            if(!$order){http_response_code(404);$view='unavailable';}
            elseif($access!==''&&$_SERVER['REQUEST_METHOD']==='GET'){go('shop/order.php?reference='.$reference);}
        }
        if($_SERVER['REQUEST_METHOD']==='POST'&&$view!=='unavailable'){
            Security::checkPost();
            try{
                if($view==='checkout'){$order=Orders::prepare($_POST);go('shop/order.php?reference='.$order['reference']);}
                elseif($view==='pay'){Orders::pay((int)$order['id'],$_POST);go('shop/order.php?reference='.$reference);}
                elseif(post('action')==='cancel'){Orders::cancel((int)$order['id']);go('shop/order.php?reference='.$reference);}
            }catch(InvalidArgumentException|RuntimeException $e){$error=Storefront::error($e);}
            if($order){$order=Orders::get((int)$order['id']);}
        }
        if($view==='pay'){$view='order';}
        $payable=$order&&$order['payment_state']==='awaiting_payment'&&strtotime($order['expires_at'].' UTC')>time()&&PaymentSettings::available()&&$order['environment']===PaymentSettings::all()['environment'];
        if($payable){$host=$order['environment']==='sandbox'?'jstest.authorize.net':'js.authorize.net';$api=$order['environment']==='sandbox'?'apitest.authorize.net':'api.authorize.net';header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self' https://".$host."; img-src 'self' data:; font-src 'self'; media-src 'self'; connect-src 'self' https://".$api."; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");}
        $settings=Settings::all();$active='shop';$record=null;$canonical=null;$isPreview=false;$title=$view==='checkout'?'Checkout':($view==='unavailable'?'Order unavailable':'Your order');$description='Private merchandise checkout and order receipt.';
        require Paths::root().'/views/public/header.php';require Paths::root().'/views/public/checkout.php';require Paths::root().'/views/public/footer.php';
    }
    public static function totals(array $order): void
    {
        echo '<dl class="checkout-totals">';foreach(['subtotal_cents'=>'Items','shipping_cents'=>'Shipping','tax_cents'=>'Tax','total_cents'=>'Total (USD)'] as $field=>$label){echo '<div><dt>'.e($label).'</dt><dd>'.e(Money::format((int)$order[$field])).'</dd></div>';}echo '</dl>';
    }
}
