<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class Orders
{
    public const PAYMENT=['awaiting_payment'=>'Awaiting payment','processing'=>'Processing — do not resubmit','uncertain'=>'Payment needs verification','held'=>'Payment under review','paid'=>'Payment accepted','declined'=>'Payment declined','cancelled'=>'Checkout cancelled','expired'=>'Checkout expired','voided'=>'Payment voided','refunded'=>'Payment refunded','review_required'=>'Owner review required','reversal_pending'=>'Reversal processing','reversal_uncertain'=>'Reversal needs verification'];
    public static function event(PDO $db,int $id,string $event,?int $actor=null,string $detail=''):void
    {
        $q=$db->prepare('INSERT INTO '.Database::table('order_events').' (order_id,event,actor_id,detail,created_at) VALUES (?,?,?,?,UTC_TIMESTAMP())');$q->execute([$id,$event,$actor,substr($detail,0,300)]);
    }
    /** Internal full record; never serialize it directly in a public response. */
    public static function get(int $id,?PDO $db=null): ?array
    {
        $db??=Database::connect();$q=$db->prepare('SELECT * FROM '.Database::table('orders').' WHERE id=?');$q->execute([$id]);$o=$q->fetch();
        if(!$o){return null;}$o['data']=json_decode(PrivateValue::open($o['private_data']),true,64,JSON_THROW_ON_ERROR);
        $q=$db->prepare('SELECT * FROM '.Database::table('order_items').' WHERE order_id=? ORDER BY variant_id');$q->execute([$id]);$o['items']=$q->fetchAll();return $o;
    }
    public static function lock(PDO $db,int $id): array
    {
        $q=$db->prepare('SELECT cart_id FROM '.Database::table('orders').' WHERE id=?');$q->execute([$id]);$cart=$q->fetchColumn();if(!$cart){throw new RuntimeException('Order unavailable.');}
        $q=$db->prepare('SELECT id FROM '.Database::table('carts').' WHERE id=? FOR UPDATE');$q->execute([$cart]);$q->fetch();
        $q=$db->prepare('SELECT id FROM '.Database::table('orders').' WHERE id=? FOR UPDATE');$q->execute([$id]);$q->fetch();return self::get($id,$db)??throw new RuntimeException('Order unavailable.');
    }
    private static function grant(string $reference): void
    {
        $_SESSION['order_access']??=[];$_SESSION['order_access'][$reference]=time()+30*86400;
        if(count($_SESSION['order_access'])>25){array_shift($_SESSION['order_access']);}
    }
    public static function accessible(string $reference,string $token=''): ?array
    {
        if(!preg_match('/^SG-[A-F0-9]{16}$/D',$reference)){return null;}
        $db=Database::connect();$q=$db->prepare('SELECT id,customer_id,access_hash,access_expires_at FROM '.Database::table('orders').' WHERE reference=?');$q->execute([$reference]);$row=$q->fetch();if(!$row){return null;}
        $customer=Customers::current();$owned=$customer&&(int)$row['customer_id']===(int)$customer['id'];
        $granted=($_SESSION['order_access'][$reference]??0)>time();
        if(!$owned&&!$granted&&preg_match('/^[a-f0-9]{64}$/D',$token)&&strtotime($row['access_expires_at'].' UTC')>time()&&hash_equals($row['access_hash'],hash('sha256',$token))){self::grant($reference);$granted=true;}
        return $owned||$granted?self::get((int)$row['id'],$db):null;
    }
    public static function prepare(array $input): array
    {
        if(!PaymentSettings::available()){throw new RuntimeException('Checkout is not configured. No payment was requested.');}
        $nonce=Input::text($input,'nonce',64,true);if(!preg_match('/^[a-f0-9]{64}$/D',$nonce)){throw new InvalidArgumentException('Reload the checkout form.');}
        if(($input['order_consent']??'')!=='1'){throw new InvalidArgumentException('Confirm the displayed shipping and store terms before reviewing your order.');}
        $address=DeliveryPolicy::validate($input);$customer=Customers::current();
        $email=$customer?$customer['email']:Security::email(Input::text($input,'email',190,true));
        RequestLimit::consume('checkout-preparation',20,3600);
        $options=PaymentSettings::all();$env=$options['environment'];$merchant=PaymentSettings::environment($env)['login_id'];$db=Database::connect();$db->beginTransaction();
        try{
            $cart=Cart::checkoutCart($db);$hash=hash_hmac('sha256',$cart.':'.$nonce,Paths::config()['secret']);$payload=hash('sha256',json_encode([$address,$email,$customer['id']??null],JSON_THROW_ON_ERROR));
            $q=$db->prepare('SELECT id,payload_hash,reference FROM '.Database::table('orders').' WHERE submission_hash=?');$q->execute([$hash]);$prior=$q->fetch();
            if($prior){if(!hash_equals($prior['payload_hash'],$payload)){throw new RuntimeException('This checkout form was already used with different details. Reload your bag.');}$db->commit();self::grant($prior['reference']);return self::get((int)$prior['id']);}
            $q=$db->prepare('SELECT id FROM '.Database::table('orders')." WHERE cart_id=? AND payment_state IN ('awaiting_payment','processing','uncertain','held') ORDER BY id FOR UPDATE");$q->execute([$cart]);
            foreach($q->fetchAll(PDO::FETCH_COLUMN) as $existing){$o=self::get((int)$existing,$db);if($o['payment_state']==='awaiting_payment'&&strtotime($o['expires_at'].' UTC')<=time()){self::release($db,$o,'expired');}else{throw new RuntimeException('This bag already has a checkout awaiting payment or verification. Open its receipt before starting another.');}}
            $q=$db->prepare('SELECT i.variant_id,i.quantity,v.product_id FROM '.Database::table('cart_items').' i JOIN '.Database::table('product_variants').' v ON v.id=i.variant_id WHERE i.cart_id=? ORDER BY i.variant_id');$q->execute([$cart]);$raw=$q->fetchAll();
            if(!$raw||count($raw)>30){throw new RuntimeException('Your bag is empty or exceeds the supported size.');}
            // Lock product before variant, matching owner catalog operations.
            $productIds=array_unique(array_map(fn($r)=>(int)$r['product_id'],$raw));sort($productIds);$products=[];
            foreach($productIds as $pid){$q=$db->prepare('SELECT id FROM '.Database::table('products').' WHERE id=? FOR UPDATE');$q->execute([$pid]);$q->fetch();$products[$pid]=Catalog::product($pid,true,$db);}
            $items=[];$subtotal=0;
            foreach($raw as $r){$q=$db->prepare('SELECT * FROM '.Database::table('product_variants').' WHERE id=? FOR UPDATE');$q->execute([$r['variant_id']]);$v=$q->fetch();$p=$products[(int)$r['product_id']];$qty=(int)$r['quantity'];
                if(!$p||!$v||!(int)$v['active']||$qty<1||$qty>20||$qty>(int)$v['stock']){throw new RuntimeException('An item changed or lacks stock. Review your bag; no order was created.');}
                $line=(int)$v['price_cents']*$qty;$subtotal+=$line;$items[]=['variant_id'=>(int)$v['id'],'name'=>$p['data']['name'],'sku'=>$v['sku'],'variant_label'=>Catalog::label($v),'quantity'=>$qty,'unit_cents'=>(int)$v['price_cents'],'line_cents'=>$line];
            }
            $totals=DeliveryPolicy::quote($subtotal,$address);$reference='SG-'.strtoupper(bin2hex(random_bytes(8)));$access=bin2hex(random_bytes(32));
            $private=['address'=>$address,'access_token'=>$access,'policy'=>$totals['policy'],'tax_rate_ppm'=>$totals['rate_ppm'],'consent_version'=>'checkout-2026-10-v1'];
            $q=$db->prepare('INSERT INTO '.Database::table('orders').' (reference,submission_hash,payload_hash,cart_id,customer_id,buyer_email,private_data,environment,merchant_login,subtotal_cents,shipping_cents,tax_cents,total_cents,expires_at,access_hash,access_expires_at,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE),?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 30 DAY),UTC_TIMESTAMP(),UTC_TIMESTAMP())');
            $q->execute([$reference,$hash,$payload,$cart,$customer['id']??null,$email,PrivateValue::seal(json_encode($private,JSON_THROW_ON_ERROR)),$env,$merchant,$totals['subtotal_cents'],$totals['shipping_cents'],$totals['tax_cents'],$totals['total_cents'],hash('sha256',$access)]);$id=(int)$db->lastInsertId();
            foreach($items as $item){$q=$db->prepare('INSERT INTO '.Database::table('order_items').' (order_id,variant_id,name,sku,variant_label,quantity,unit_cents,line_cents) VALUES (?,?,?,?,?,?,?,?)');$q->execute([$id,...array_values($item)]);self::stock($db,$id,$item['variant_id'],-$item['quantity'],'reserve');}
            self::event($db,$id,'checkout.prepared',null,'Inventory held for 15 minutes; no payment submitted.');$db->commit();self::grant($reference);return self::get($id);
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    private static function stock(PDO $db,int $order,int $variant,int $delta,string $kind): void
    {
        $q=$db->prepare('SELECT stock FROM '.Database::table('product_variants').' WHERE id=? FOR UPDATE');$q->execute([$variant]);$stock=$q->fetchColumn();if($stock===false){throw new RuntimeException('Reserved variant no longer exists.');}$after=(int)$stock+$delta;if($after<0){throw new RuntimeException('Inventory reservation failed.');}
        $q=$db->prepare('UPDATE '.Database::table('product_variants').' SET stock=?,revision=revision+1 WHERE id=?');$q->execute([$after,$variant]);
        $q=$db->prepare('INSERT INTO '.Database::table('inventory_adjustments').' (variant_id,operation_hash,quantity_delta,stock_after,reason,actor_id,created_at) VALUES (?,?,?,?,?,0,UTC_TIMESTAMP())');$q->execute([$variant,hash('sha256','order:'.$order.':'.$variant.':'.$kind),$delta,$after,'Order #'.$order.' '.$kind]);
    }
    private static function release(PDO $db,array $o,string $state): void
    {
        if($o['reservation_state']==='held'){foreach($o['items'] as $i){self::stock($db,(int)$o['id'],(int)$i['variant_id'],(int)$i['quantity'],'release');}}
        $q=$db->prepare('UPDATE '.Database::table('orders')." SET payment_state=?,reservation_state=IF(reservation_state='held','released',reservation_state),revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$state,$o['id']]);self::event($db,(int)$o['id'],'payment.'.$state);
    }
    public static function cancel(int $id): void
    {
        $db=Database::connect();$db->beginTransaction();try{$o=self::lock($db,$id);if(in_array($o['payment_state'],['cancelled','expired'],true)){$db->commit();return;}if($o['payment_state']!=='awaiting_payment'){throw new RuntimeException('Payment has been submitted. Contact the band; do not start another charge.');}self::release($db,$o,'cancelled');$db->commit();}catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function expire(int $limit=20): int
    {
        $db=Database::connect();$ids=$db->query('SELECT id FROM '.Database::table('orders')." WHERE payment_state='awaiting_payment' AND expires_at<=UTC_TIMESTAMP() ORDER BY id LIMIT ".max(1,min(100,$limit)))->fetchAll(PDO::FETCH_COLUMN);$count=0;
        foreach($ids as $id){$db->beginTransaction();try{$o=self::lock($db,(int)$id);if($o['payment_state']==='awaiting_payment'&&strtotime($o['expires_at'].' UTC')<=time()){self::release($db,$o,'expired');$count++;}$db->commit();}catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}}return $count;
    }
    public static function pay(int $id,array $input,?callable $transport=null): void
    {
        if($transport!==null&&PHP_SAPI!=='cli'){throw new RuntimeException('Test transport refused.');}
        if(array_diff(array_keys($input),['csrf','nonce','data_descriptor','data_value'])){throw new InvalidArgumentException('Only a tokenized payment is accepted. Raw card data must never be submitted here.');}
        $nonce=Input::text($input,'nonce',64,true);$descriptor=Input::text($input,'data_descriptor',80,true);$value=Input::text($input,'data_value',2048,true);
        if(!preg_match('/^[a-f0-9]{64}$/D',$nonce)||$descriptor!=='COMMON.ACCEPT.INAPP.PAYMENT'||!preg_match('/^[A-Za-z0-9+\/_=-]{20,2048}$/D',$value)){throw new InvalidArgumentException('A valid Authorize.net payment token is required.');}
        if(!PaymentSettings::available()){throw new RuntimeException('Checkout is not enabled. No charge was submitted.');}
        $db=Database::connect();$db->beginTransaction();
        try{
            $o=self::lock($db,$id);
            if($o['environment']!==PaymentSettings::all()['environment']){throw new RuntimeException('This checkout belongs to a different payment environment. No new charge was submitted.');}
            if($o['payment_state']!=='awaiting_payment'){$db->commit();return;}
            if(strtotime($o['expires_at'].' UTC')<=time()){self::release($db,$o,'expired');$db->commit();return;}
            PaymentSettings::credentials($o['environment'],$o['merchant_login']);
            $ref='SP'.strtoupper(bin2hex(random_bytes(9)));$a=$o['data']['address'];
            $request=['refId'=>$ref,'transactionRequest'=>['transactionType'=>'authCaptureTransaction','amount'=>Money::decimal((int)$o['total_cents']),'payment'=>['opaqueData'=>['dataDescriptor'=>$descriptor,'dataValue'=>$value]],'order'=>['invoiceNumber'=>$o['reference'],'description'=>'Spiritual Gorillaz merchandise'],'customer'=>['email'=>$o['buyer_email']],'billTo'=>['address'=>$a['billing_line'],'city'=>$a['billing_city'],'state'=>$a['billing_region'],'zip'=>$a['billing_zip'],'country'=>'USA'],'transactionSettings'=>['setting'=>[['settingName'=>'duplicateWindow','settingValue'=>'180']]]]];
            $q=$db->prepare('INSERT INTO '.Database::table('payment_operations').' (order_id,kind,reference,nonce_hash,amount_cents,created_at,updated_at) VALUES (?,\'charge\',?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())');$q->execute([$id,$ref,hash('sha256',$nonce),$o['total_cents']]);$operation=(int)$db->lastInsertId();
            $q=$db->prepare('UPDATE '.Database::table('orders')." SET payment_state='processing',revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$id]);self::event($db,$id,'payment.submitted');$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        // Durable processing state precedes the irreversible network operation.
        if(PHP_SAPI!=='cli'&&session_status()===PHP_SESSION_ACTIVE){session_write_close();}
        try{$result=AuthorizeGateway::result(AuthorizeGateway::request($o['environment'],'createTransactionRequest',$request,$o['merchant_login'],$transport));}
        catch(Throwable $e){$result=['state'=>'uncertain','transaction_id'=>null,'last_four'=>null,'error'=>'payment_acceptance_uncertain'];}
        unset($request,$value);
        self::applyCharge($id,$operation,$result);
    }
    private static function applyCharge(int $id,int $operation,array $result): void
    {
        $db=Database::connect();$db->beginTransaction();
        try{
            $o=self::lock($db,$id);
            // A concurrently processed authenticated notification may already have
            // resolved the order. A delayed charge reply must not regress it.
            if(!in_array($o['payment_state'],['processing','uncertain','held'],true)){$db->commit();return;}
            $q=$db->prepare('SELECT state FROM '.Database::table('payment_operations').' WHERE id=? FOR UPDATE');$q->execute([$operation]);
            if($q->fetchColumn()!=='processing'){$db->commit();return;}
            $state=match($result['state']){'succeeded'=>'paid','declined'=>'declined','held'=>'held',default=>'uncertain'};
            $q=$db->prepare('UPDATE '.Database::table('payment_operations').' SET state=?,transaction_id=COALESCE(?,transaction_id),last_error=?,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$result['state'],$result['transaction_id'],$result['error'],$operation]);
            $q=$db->prepare('UPDATE '.Database::table('orders').' SET transaction_id=COALESCE(?,transaction_id),last_four=COALESCE(?,last_four) WHERE id=?');$q->execute([$result['transaction_id'],$result['last_four'],$id]);
            if($state==='declined'){self::release($db,$o,$state);}
            elseif($state==='paid'){self::paid($db,$o);}
            else{$q=$db->prepare('UPDATE '.Database::table('orders').' SET payment_state=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$state,$id]);self::event($db,$id,'payment.'.$state);}
            $db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        self::queueReceiptSafely($id);
    }
    private static function paid(PDO $db,array $o): void
    {
        if($o['reservation_state']==='consumed'){
            $q=$db->prepare('UPDATE '.Database::table('orders')." SET payment_state='paid',revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$o['id']]);self::event($db,(int)$o['id'],'payment.paid');return;
        }
        if($o['reservation_state']!=='held'){
            $q=$db->prepare('UPDATE '.Database::table('orders')." SET payment_state='review_required',revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$o['id']]);self::event($db,(int)$o['id'],'payment.inventory_review');return;
        }
        $q=$db->prepare('UPDATE '.Database::table('orders')." SET payment_state='paid',reservation_state='consumed',revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$o['id']]);
        foreach($o['items'] as $i){$q=$db->prepare('UPDATE '.Database::table('cart_items').' SET quantity=GREATEST(0,CAST(quantity AS SIGNED)-?) WHERE cart_id=? AND variant_id=?');$q->execute([$i['quantity'],$o['cart_id'],$i['variant_id']]);}
        $q=$db->prepare('DELETE FROM '.Database::table('cart_items').' WHERE cart_id=? AND quantity=0');$q->execute([$o['cart_id']]);self::event($db,(int)$o['id'],'payment.paid');
    }
    public static function reconcile(string $mode,string $transaction,?callable $transport=null,int $expectedOrder=0,string $signedReference=''): int
    {
        $reportDb=Database::connect();$reportLock=InstallState::lock($reportDb,Paths::config()['db']['prefix'].':report:'.$mode.':'.$transaction);
        try {
        $details=AuthorizeGateway::details($mode,$transaction,'',$transport);$invoice=(string)($details['order']['invoiceNumber']??'');
        $db=Database::connect();$q=$db->prepare('SELECT id FROM '.Database::table('orders').' WHERE reference=? AND environment=?');$q->execute([$invoice,$mode]);$id=$q->fetchColumn();
        if(!$id||($expectedOrder>0&&(int)$id!==$expectedOrder)){throw new RuntimeException('payment_order_not_matched');}
        $db->beginTransaction();
        try{
            $o=self::lock($db,(int)$id);$q=$db->prepare('SELECT * FROM '.Database::table('payment_operations')." WHERE order_id=? AND kind='charge' FOR UPDATE");$q->execute([$id]);$op=$q->fetch();
            $reported=AuthorizeGateway::transactionId($details['transId']??null);
            if(!$op||$reported!==$transaction||$o['merchant_login']!==PaymentSettings::environment($mode)['login_id']||AuthorizeGateway::reportedCents($details['authAmount']??null)!==(int)$o['total_cents']||($o['transaction_id']!==null&&$o['transaction_id']!==$transaction)){throw new RuntimeException('payment_details_mismatch');}
            // Transaction reporting does not persist the request refId. A signed
            // webhook supplies merchantReferenceId. Otherwise require a known
            // transaction, or explicit owner-selected order reconciliation.
            if($signedReference!==$op['reference']&&$o['transaction_id']!==$transaction&&$expectedOrder!==(int)$id){throw new RuntimeException('payment_reference_mismatch');}
            if(($details['transactionType']??'')!=='authCaptureTransaction'){throw new RuntimeException('payment_transaction_type_mismatch');}
            $status=(string)($details['transactionStatus']??'');$state=match($status){'capturedPendingSettlement','settledSuccessfully'=>'paid','FDSAuthorizedPendingReview','FDSPendingReview','authorizedPendingCapture'=>'held','declined','failedReview'=>'declined','voided'=>'voided',default=>'uncertain'};
            if(in_array($o['payment_state'],['refunded','voided','reversal_pending','reversal_uncertain'],true)&&$state!=='voided'){$db->commit();return (int)$id;}
            $q=$db->prepare('UPDATE '.Database::table('orders').' SET transaction_id=? WHERE id=?');$q->execute([$transaction,$id]);
            $q=$db->prepare('UPDATE '.Database::table('payment_operations').' SET state=?,transaction_id=?,last_error=NULL,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$state==='paid'?'succeeded':$state,$transaction,$op['id']]);
            if($state==='paid'&&$o['payment_state']!=='paid'){self::paid($db,$o);}
            elseif(in_array($state,['declined','voided'],true)&&$o['payment_state']!==$state){
                if($o['reservation_state']==='held'){self::release($db,$o,$state);}
                else{$q=$db->prepare('UPDATE '.Database::table('orders').' SET payment_state=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$state,$id]);self::event($db,(int)$id,'payment.'.$state,null,'Returned merchandise is not automatically restocked.');}
            }elseif($state!==$o['payment_state']&&in_array($o['payment_state'],['processing','uncertain','held','paid'],true)){$q=$db->prepare('UPDATE '.Database::table('orders').' SET payment_state=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$state,$id]);self::event($db,(int)$id,'payment.'.$state);}
            $db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        self::queueReceiptSafely((int)$id);return (int)$id;
        } finally {InstallState::unlock($reportDb,$reportLock);}
    }
    public static function queueReceiptSafely(int $id): void
    {
        try{self::queueReceipt($id);}catch(Throwable $e){error_log('Spirit order receipt deferred ['.get_class($e).']');}
    }
    public static function queueReceipt(int $id): void
    {
        $db=Database::connect();$db->beginTransaction();
        try{
            $o=self::lock($db,$id);if($o['payment_state']!=='paid'||(int)$o['receipt_queued']===1){$db->commit();return;}
            $link=Communications::absolute('shop/order.php?reference='.$o['reference'].'&access='.$o['data']['access_token']);
            $subject=($o['environment']==='sandbox'?'[SANDBOX] ':'').'Your order '.$o['reference'];$text='Reference: '.$o['reference']."\nTotal: ".Money::format((int)$o['total_cents'])." USD\nPayment accepted; fulfillment has not yet occurred.";
            if($o['environment']==='sandbox'){$text="TEST ORDER — no real payment was processed by this sandbox workflow.\n\n".$text;}
            MailQueue::enqueue($o['buyer_email'],$subject,Communications::message('Order received',$text,'View private order',$link),'order:'.$id.':receipt',$db);
            $owner=Communications::bookingAddress();if($owner!==''){MailQueue::enqueue($owner,$subject,Communications::message('Merchandise order received',$text,'Review order',Communications::absolute('admin/orders/view.php?id='.$id)),'order:'.$id.':owner',$db);}
            $q=$db->prepare('UPDATE '.Database::table('orders').' SET receipt_queued=1 WHERE id=?');$q->execute([$id]);$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function fulfill(int $id,array $input,int $actor): void
    {
        $state=Input::choice($input,'fulfillment',['processing','shipped','completed'],'');$carrier=Input::text($input,'carrier',80);$tracking=Input::text($input,'tracking',180);$revision=Input::integer($input,'revision',1,PHP_INT_MAX,1);
        if(preg_match('/[\x00-\x1f\x7f]/',$carrier.$tracking)){throw new InvalidArgumentException('Shipment references cannot contain control characters.');}
        if($state==='shipped'&&($carrier===''||$tracking==='')){throw new InvalidArgumentException('Shipment requires a carrier and tracking number.');}
        $db=Database::connect();$db->beginTransaction();
        try{$o=self::lock($db,$id);if($o['payment_state']!=='paid'||$o['reservation_state']!=='consumed'){throw new RuntimeException('Only a verified paid order can be fulfilled.');}if((int)$o['revision']!==$revision){throw new RuntimeException('Order changed. Reload before updating fulfillment.');}
            $q=$db->prepare('UPDATE '.Database::table('orders').' SET fulfillment_state=?,tracking_carrier=?,tracking_number=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$state,$carrier,$tracking,$id]);self::event($db,$id,'fulfillment.'.$state,$actor);
            $link=Communications::absolute('shop/order.php?reference='.$o['reference'].'&access='.$o['data']['access_token']);$text='Reference: '.$o['reference']."\nFulfillment: ".$state."\nCarrier: ".$carrier."\nTracking: ".$tracking;
            MailQueue::enqueue($o['buyer_email'],($o['environment']==='sandbox'?'[SANDBOX] ':'').'Order update '.$o['reference'],Communications::message('Your order was updated',$text,'View private order',$link),'order:'.$id.':fulfillment:'.($revision+1),$db);$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
}
