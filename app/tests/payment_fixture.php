<?php
declare(strict_types=1);
// Disposable CI driver. Callable gateway transport exists only in CLI, never HTTP.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,Orders,OrderAdjustments,OrderMaintenance,AuthorizeGateway,PaymentSettings,PaymentUncertain,PrivateValue,WebhookInbox};
$c=Paths::config();if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Non-test database refused.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);$db=Database::connect();$id=max(0,(int)($input['id']??0));$action=$argv[1]??'';$out=[];$calls=0;$reads=0;
function getFixtureOrder(int $id): array {return Orders::get($id)??throw new RuntimeException('Fixture order missing.');}
function details(array $o,array $input): array
{
    $id=(string)($input['transaction_id']??$o['transaction_id']??('7000000000'.$o['id']));
    return ['messages'=>['resultCode'=>'Ok'],'transaction'=>['transId'=>$id,'transactionType'=>$input['transaction_type']??'authCaptureTransaction','transactionStatus'=>$input['provider_status']??'capturedPendingSettlement','authAmount'=>$input['amount']??Spirit\Money::decimal((int)$o['total_cents']),'order'=>['invoiceNumber'=>$input['invoice']??$o['reference']],'payment'=>['creditCard'=>['cardNumber'=>'XXXX1111']]]];
}
$transport=static function(array $payload)use(&$calls,&$reads,$input,$id):array{
    $action=array_key_first($payload);$p=$payload[$action];$o=getFixtureOrder($id);
    if($action==='getTransactionDetailsRequest'){$reads++;$r=details($o,$input);$r['transaction']['transId']=(string)$p['transId'];
        if(($input['refund_reporting']??false)===true){$r['transaction']['transactionType']='refundTransaction';$r['transaction']['transactionStatus']='refundSettledSuccessfully';$r['transaction']['refTransId']=$o['transaction_id'];}
        return $r;
    }
    if($action!=='createTransactionRequest'){throw new RuntimeException('Unexpected fixture API action.');}
    $calls++;$t=$p['transactionRequest'];$kind=$t['transactionType'];
    if(($p['merchantAuthentication']['name']??'')!==$o['merchant_login']){throw new RuntimeException('Wrong merchant context.');}
    if($kind==='authCaptureTransaction'){
        if($t['amount']!==Spirit\Money::decimal((int)$o['total_cents'])||isset($t['payment']['creditCard'])||isset($t['payment']['opaqueData']['cardNumber'])){throw new RuntimeException('Unsafe charge payload.');}
        if(($input['delay']??0)>0){usleep(min(500000,(int)$input['delay']));}
    }elseif($kind==='refundTransaction'){
        if($t['amount']!==Spirit\Money::decimal((int)$o['total_cents'])||$t['payment']['creditCard']['cardNumber']!=='1111'||$t['payment']['creditCard']['expirationDate']!=='XXXX'){throw new RuntimeException('Unsafe refund payload.');}
    }elseif($kind!=='voidTransaction'){throw new RuntimeException('Unexpected transaction kind.');}
    $mode=$input['response']??'approved';
    if($mode==='lost'){throw new PaymentUncertain('Synthetic network loss after possible acceptance.');}
    if($mode==='malformed'){return ['messages'=>['resultCode'=>'Error']];}
    if($mode==='duplicate'){return ['transactionResponse'=>['responseCode'=>'3','transId'=>'0','errors'=>[['errorCode'=>'11']]]];}
    $code=$mode==='declined'?'2':($mode==='held'?'4':'1');$transaction=$code==='2'?'0':(string)($input['transaction_id']??(($kind==='refundTransaction'?'8000000000':'7000000000').$id));
    return ['messages'=>['resultCode'=>'Ok'],'transactionResponse'=>['responseCode'=>$code,'transId'=>$transaction,'accountNumber'=>'XXXX1111']];
};
try{
    switch($action){
    case 'snapshot':foreach(['orders','order_items','payment_operations','payment_webhooks','order_events','inventory_adjustments'] as $table){$out[$table]=$db->query('SELECT * FROM '.Database::table($table).' ORDER BY id')->fetchAll();}$out['payments']=PaymentSettings::all();break;
    case 'private-link':$o=getFixtureOrder($id);$out=['reference'=>$o['reference'],'access'=>$o['data']['access_token']];break;
    case 'pay':
        Orders::pay($id,['nonce'=>bin2hex(random_bytes(32)),'data_descriptor'=>'COMMON.ACCEPT.INAPP.PAYMENT','data_value'=>'SYNTHETIC_OPAQUE_TOKEN_NOT_A_CARD_0001'],$transport);$out=['ok'=>true];break;
    case 'reconcile':$o=getFixtureOrder($id);$out=['id'=>Orders::reconcile($o['environment'],(string)($input['transaction_id']??$o['transaction_id']),$transport,(int)($input['expected_order']??$id),(string)($input['signed_reference']??''))];break;
    case 'reverse':OrderAdjustments::submit($id,$input['kind'],str_repeat('b',64),1,$transport);$out=['ok'=>true];break;
    case 'refund-report':$o=getFixtureOrder($id);$out=['id'=>OrderAdjustments::reconcileRefund($o['environment'],$input['transaction_id'],$transport,$id)];break;
    case 'process-webhooks':$out=WebhookInbox::process(10,$transport);break;
    case 'expire':$q=$db->prepare('UPDATE '.Database::table('orders').' SET expires_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE) WHERE id=?');$q->execute([$id]);break;
    case 'interrupt':$q=$db->prepare('UPDATE '.Database::table('orders')." SET updated_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 6 MINUTE) WHERE id=?");$q->execute([$id]);break;
    case 'maintenance':$out=OrderMaintenance::run(50);break;
    case 'break-receipt':
        // CHECK expressions cannot reference AUTO_INCREMENT columns. Scope the
        // disposable fault to the immutable order reference, not its numeric id.
        $reference=$db->quote(getFixtureOrder($id)['reference']);
        $constraint=Database::table('fixture_paid_receipt');$db->exec('ALTER TABLE '.Database::table('orders').' ADD CONSTRAINT '.$constraint." CHECK(reference<>".$reference." OR payment_state<>'paid')");
        try{Orders::pay($id,['nonce'=>str_repeat('a',64),'data_descriptor'=>'COMMON.ACCEPT.INAPP.PAYMENT','data_value'=>'SYNTHETIC_OPAQUE_TOKEN_NOT_A_CARD_0001'],$transport);}
        catch(Throwable $e){
            if(!$e instanceof \PDOException||!in_array((int)($e->errorInfo[1]??0),[3819,4025],true)){throw $e;}
            $out=['error'=>get_class($e),'fault_installed'=>true,'driver_code'=>(int)$e->errorInfo[1]];
        }
        finally{$db->exec('ALTER TABLE '.Database::table('orders').' DROP CONSTRAINT '.$constraint);}
        if(!($out['fault_installed']??false)){throw new RuntimeException('Expected enforced CHECK did not reject the receipt.');}break;
    case 'queue-break':$db->exec('RENAME TABLE '.Database::table('mail_queue').' TO '.Database::table('mail_queue_fixture_offline'));break;
    case 'queue-restore':$db->exec('RENAME TABLE '.Database::table('mail_queue_fixture_offline').' TO '.Database::table('mail_queue'));break;
    case 'prepare':
        $_COOKIE[Spirit\Cart::cookieName()]=$input['cart_cookie'];$_SESSION=[];
        $o=Orders::prepare($input['checkout']);$out=['ok'=>true,'id'=>$o['id'],'reference'=>$o['reference']];break;
    case 'http':
        $host=$input['host']??'localhost';if(!in_array($host,['localhost','127.0.0.1'],true)){throw new RuntimeException('Only loopback HTTP transport fixtures are permitted.');}
        $start=microtime(true);try{$r=Spirit\AuthorizeHttp::post('https://'.$host.':'.(int)$input['port'].'/request.api','{"synthetic":true}',(float)($input['seconds']??3),$input['ca']??null);$out=['ok'=>true,'response'=>$r];}catch(Throwable $e){$out=['ok'=>false,'error'=>$e->getMessage()];}$out['elapsed']=microtime(true)-$start;break;
    case 'state':$o=getFixtureOrder($id);$out=['payment_state'=>$o['payment_state'],'fulfillment_state'=>$o['fulfillment_state'],'reservation_state'=>$o['reservation_state'],'receipt_queued'=>$o['receipt_queued'],'transaction_id'=>$o['transaction_id']];break;
    default:throw new RuntimeException('Unknown payment fixture command.');
    }
}catch(Throwable $e){$out=['ok'=>false,'error'=>$e->getMessage(),'class'=>get_class($e)];}
$out['mutating_calls']=$calls;$out['reporting_calls']=$reads;echo json_encode($out,JSON_THROW_ON_ERROR);
