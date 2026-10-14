<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

final class OrderAdmin
{
    private static function reauthenticate(int $actor): void
    {
        RequestLimit::consume('owner-payment-confirm',8,900,(string)$actor);$password=password_input('owner_password');
        $q=Database::connect()->prepare('SELECT password_hash FROM '.Database::table('users')." WHERE id=? AND role='owner' AND active=1");$q->execute([$actor]);$hash=$q->fetchColumn();
        if(strlen($password)>72||str_contains($password,"\0")||!$hash||!password_verify($password,$hash)){throw new RuntimeException('Confirm your current owner password for this sensitive operation.');}
    }
    public static function render(string $view): void
    {
        $user=ContentAdmin::boot();$actor=(int)$user['id'];$db=Database::connect();$error=null;$id=Input::integer($_GET,'id',0,PHP_INT_MAX,0);
        $titles=['orders'=>'Merchandise orders','view'=>'Order details','payments'=>'Payment configuration','policy'=>'Shipping, tax & store terms','webhooks'=>'Payment notifications'];
        if(!isset($titles[$view])){throw new RuntimeException('Unknown order screen.');}
        $order=$view==='view'?Orders::get($id):null;if($view==='view'&&!$order){http_response_code(404);exit('Order not found.');}
        $mode=PaymentSettings::mode(is_string($_GET['environment']??null)?$_GET['environment']:PaymentSettings::all()['environment']);
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                $action=post('action');
                if($view==='payments'){
                    self::reauthenticate($actor);
                    if($action==='test'){$r=AuthorizeGateway::request($mode,'authenticateTestRequest',[]);if(($r['messages']['resultCode']??'')!=='Ok'){throw new RuntimeException('Provider authentication was not accepted. No payment was attempted.');}flash('The provider accepted the API authentication test. This is not a verified charge, webhook, or merchant compliance approval.');}
                    else{PaymentSettings::save($_POST,$actor);flash('Payment settings saved. Test transactions and webhook delivery still need actual provider acceptance.');}
                    go('admin/orders/payments.php?environment='.rawurlencode(post('environment')?:$mode));
                }
                if($view==='policy'){DeliveryPolicy::save($_POST,$actor);flash('Delivery and store terms saved. Rates are merchant configuration, not automatically verified tax advice.');go('admin/orders/policy.php');}
                if($view==='orders'){$r=OrderMaintenance::run();flash('Maintenance completed: '.$r['expired'].' expired holds released; '.$r['interrupted'].' interrupted payments flagged; '.$r['receipts'].' receipts queued.');go('admin/orders/');}
                if($view==='view'){
                    if($action==='fulfill'){Orders::fulfill($id,$_POST,$actor);flash('Fulfillment updated and customer notification queued.');}
                    elseif($action==='cancel'){Orders::cancel($id);flash('Unpaid checkout cancelled.');}
                    elseif($action==='reconcile'){
                        $transaction=AuthorizeGateway::transactionId(post('transaction_id'));if(!$transaction){throw new InvalidArgumentException('Enter the provider transaction ID, not an authorization code.');}
                        if(post('kind')==='refund'){OrderAdjustments::reconcileRefund($order['environment'],$transaction,null,$id);}else{Orders::reconcile($order['environment'],$transaction,null,$id);}flash('Order status was reconciled against authenticated provider reporting.');
                    }elseif(in_array($action,['void','refund'],true)){
                        self::reauthenticate($actor);if(post('confirm_reversal')!=='1'){throw new RuntimeException('Explicit reversal confirmation is required.');}
                        OrderAdjustments::submit($id,$action,post('nonce'),$actor);flash('Reversal request processed once. Check the resulting state; uncertain results must not be resubmitted.');
                    }else{throw new InvalidArgumentException('Select an available order action.');}
                    go('admin/orders/view.php?id='.$id);
                }
                if($view==='webhooks'){
                    if($action==='retry'){$q=$db->prepare('UPDATE '.Database::table('payment_webhooks')." SET state='queued',attempts=0,available_at=UTC_TIMESTAMP(),last_error=NULL WHERE id=? AND state IN ('failed','retry')");$q->execute([Input::integer($_POST,'notification_id',1,PHP_INT_MAX)]);flash('Reporting lookup requeued. This does not submit another charge.');}
                    else{$r=WebhookInbox::process(3);flash('Verified notifications processed: '.$r['processed'].'. Deferred: '.$r['deferred'].'. No charge was created by this reporting worker.');}go('admin/orders/webhooks.php');
                }
            }catch(InvalidArgumentException|RuntimeException $e){$error=Storefront::error($e);}
        }
        View::start($titles[$view],'admin','orders',$user);require Paths::root().'/views/admin/orders.php';View::end();
    }
}
