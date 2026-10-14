<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use Throwable;

final class OrderMaintenance
{
    public static function run(int $limit=20): array
    {
        $limit=max(1,min(50,$limit));$db=Database::connect();$out=['expired'=>Orders::expire($limit),'interrupted'=>0,'receipts'=>0];
        $ids=$db->query('SELECT id FROM '.Database::table('orders')." WHERE payment_state IN ('processing','reversal_pending') AND updated_at<DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) ORDER BY id LIMIT ".$limit)->fetchAll(PDO::FETCH_COLUMN);
        foreach($ids as $id){$db->beginTransaction();try{$o=Orders::lock($db,(int)$id);
            if(in_array($o['payment_state'],['processing','reversal_pending'],true)&&strtotime($o['updated_at'].' UTC')<time()-300){
                $state=$o['payment_state']==='processing'?'uncertain':'reversal_uncertain';$q=$db->prepare('UPDATE '.Database::table('orders').' SET payment_state=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$state,$id]);
                $q=$db->prepare('UPDATE '.Database::table('payment_operations')." SET state='uncertain',last_error='payment_worker_interrupted',updated_at=UTC_TIMESTAMP() WHERE order_id=? AND state='processing'");$q->execute([$id]);Orders::event($db,(int)$id,'payment.interrupted');$out['interrupted']++;
            }$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}}
        $ids=$db->query('SELECT id FROM '.Database::table('orders')." WHERE payment_state='paid' AND receipt_queued=0 ORDER BY id LIMIT ".$limit)->fetchAll(PDO::FETCH_COLUMN);
        foreach($ids as $id){Orders::queueReceipt((int)$id);$out['receipts']++;}return $out;
    }
}
