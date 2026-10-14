<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;
use Throwable;

/** Full refunds / unsettled voids. Never automatically retry an ambiguous request. */
final class OrderAdjustments
{
    public static function submit(int $id,string $kind,string $nonce,int $actor,?callable $transport=null): void
    {
        if($transport!==null&&PHP_SAPI!=='cli'){throw new RuntimeException('Test transport refused.');}
        if(!in_array($kind,['void','refund'],true)||!preg_match('/^[a-f0-9]{64}$/D',$nonce)){throw new RuntimeException('Invalid reversal request.');}
        $o=Orders::get($id)??throw new RuntimeException('Order unavailable.');
        if(!$o['transaction_id']){throw new RuntimeException('A verified transaction is required.');}
        $details=AuthorizeGateway::details($o['environment'],$o['transaction_id'],$o['merchant_login'],$transport);
        $expected=$kind==='void'?'capturedPendingSettlement':'settledSuccessfully';
        if(($details['transactionStatus']??'')!==$expected||($details['order']['invoiceNumber']??'')!==$o['reference']||AuthorizeGateway::reportedCents($details['authAmount']??null)!==(int)$o['total_cents']){throw new RuntimeException('Provider status or amount does not permit this reversal. Refresh the order before proceeding.');}
        $masked=(string)($details['payment']['creditCard']['cardNumber']??'');$last=preg_match('/^[Xx*]+([0-9]{4})$/D',$masked,$m)?$m[1]:$o['last_four'];
        if($kind==='refund'&&(!is_string($last)||!preg_match('/^[0-9]{4}$/D',$last))){throw new RuntimeException('Provider did not supply a safe last-four reference for refund.');}
        $db=Database::connect();$db->beginTransaction();
        try{
            $o=Orders::lock($db,$id);
            $q=$db->prepare('SELECT id FROM '.Database::table('payment_operations')." WHERE order_id=? AND kind IN ('void','refund') FOR UPDATE");$q->execute([$id]);
            if($q->fetchColumn()){$db->commit();return;}
            if($o['payment_state']!=='paid'){throw new RuntimeException('Only a verified paid order can be reversed here. Uncertain operations need reconciliation, not another submission.');}
            $ref='SR'.strtoupper(bin2hex(random_bytes(9)));
            $q=$db->prepare('INSERT INTO '.Database::table('payment_operations').' (order_id,kind,reference,nonce_hash,amount_cents,created_at,updated_at) VALUES (?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())');$q->execute([$id,$kind,$ref,hash('sha256',$nonce),$o['total_cents']]);$operation=(int)$db->lastInsertId();
            $q=$db->prepare('UPDATE '.Database::table('orders')." SET payment_state='reversal_pending',revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$id]);Orders::event($db,$id,'payment.'.$kind.'_requested',$actor);$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        $request=['transactionType'=>$kind==='void'?'voidTransaction':'refundTransaction'];
        if($kind==='refund'){$request['amount']=Money::decimal((int)$o['total_cents']);$request['payment']=['creditCard'=>['cardNumber'=>$last,'expirationDate'=>'XXXX']];}
        $request['refTransId']=$o['transaction_id'];
        try{$result=AuthorizeGateway::result(AuthorizeGateway::request($o['environment'],'createTransactionRequest',['refId'=>$ref,'transactionRequest'=>$request],$o['merchant_login'],$transport));}
        catch(Throwable $e){$result=['state'=>'uncertain','transaction_id'=>null,'error'=>'payment_acceptance_uncertain'];}
        $db=Database::connect();$db->beginTransaction();
        try{
            $o=Orders::lock($db,$id);$q=$db->prepare('SELECT state FROM '.Database::table('payment_operations').' WHERE id=? FOR UPDATE');$q->execute([$operation]);
            if($q->fetchColumn()!=='processing'||in_array($o['payment_state'],['voided','refunded'],true)){$db->commit();return;}
            $q=$db->prepare('UPDATE '.Database::table('payment_operations').' SET state=?,transaction_id=?,last_error=?,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$result['state'],$result['transaction_id'],$result['error'],$operation]);
            $state=$result['state']==='succeeded'?($kind==='void'?'voided':'refunded'):($result['state']==='declined'?'paid':'reversal_uncertain');
            $q=$db->prepare('UPDATE '.Database::table('orders').' SET payment_state=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$state,$id]);Orders::event($db,$id,'payment.'.$state,$actor,'Returned stock is not automatically added back.');
            if(in_array($state,['voided','refunded'],true)){self::notice($db,$o,$state);}$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function reconcileRefund(string $mode,string $transaction,?callable $transport=null,int $expectedOrder=0,string $signedReference=''): int
    {
        $d=AuthorizeGateway::details($mode,$transaction,'',$transport);$parent=AuthorizeGateway::transactionId($d['refTransId']??null);
        if(!$parent||AuthorizeGateway::transactionId($d['transId']??null)!==$transaction||($d['transactionType']??'')!=='refundTransaction'||!in_array($d['transactionStatus']??'',['refundPendingSettlement','refundSettledSuccessfully'],true)){throw new RuntimeException('payment_refund_unverified');}
        $db=Database::connect();$q=$db->prepare('SELECT id FROM '.Database::table('orders').' WHERE environment=? AND transaction_id=?');$q->execute([$mode,$parent]);$id=$q->fetchColumn();if(!$id||($expectedOrder>0&&(int)$id!==$expectedOrder)){throw new RuntimeException('payment_refund_order_unknown');}
        $db->beginTransaction();
        try{
            $o=Orders::lock($db,(int)$id);
            if($o['merchant_login']!==PaymentSettings::environment($mode)['login_id']){throw new RuntimeException('payment_merchant_mismatch');}
            if(AuthorizeGateway::reportedCents($d['authAmount']??$d['settleAmount']??null)!==(int)$o['total_cents']){throw new RuntimeException('payment_partial_refund_needs_review');}
            $q=$db->prepare('SELECT * FROM '.Database::table('payment_operations')." WHERE order_id=? AND kind='refund' FOR UPDATE");$q->execute([$id]);$op=$q->fetch();
            if(!$op||($signedReference!==$op['reference']&&$op['transaction_id']!==$transaction&&$expectedOrder!==(int)$id)){throw new RuntimeException('payment_external_refund_needs_review');}
            if($o['payment_state']!=='refunded'){$q=$db->prepare('UPDATE '.Database::table('orders')." SET payment_state='refunded',revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$id]);Orders::event($db,(int)$id,'payment.refunded',null,'Verified by provider reporting; stock unchanged.');self::notice($db,$o,'refunded');}
            $q=$db->prepare('UPDATE '.Database::table('payment_operations')." SET state='succeeded',transaction_id=?,last_error=NULL,updated_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$transaction,$op['id']]);$db->commit();return (int)$id;
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    private static function notice(\PDO $db,array $o,string $state): void
    {
        MailQueue::enqueue($o['buyer_email'],($o['environment']==='sandbox'?'[SANDBOX] ':'').'Order '.$o['reference'].' '.$state,Communications::message('Payment update','Reference: '.$o['reference']."\nStatus: ".$state."\nAmount: ".Money::format((int)$o['total_cents']).' USD. Your provider determines posting times.'),'order:'.$o['id'].':'.$state,$db);
    }
}
