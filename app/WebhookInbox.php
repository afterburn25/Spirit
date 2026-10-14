<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;
use Throwable;

final class WebhookInbox
{
    public static function validSignature(string $raw,string $signature,string $key): bool
    {
        if(!preg_match('/^[A-Fa-f0-9]{128}$/D',$key)||!preg_match('/^sha512=([a-f0-9]{128})$/iD',$signature,$m)){return false;}
        // The Webhooks signature key is the copied text value, not the binary
        // hex-decoded transaction-response hash key used by other API features.
        return hash_equals(hash_hmac('sha512',$raw,$key),strtolower($m[1]));
    }
    public static function accept(string $mode,string $raw,string $signature): void
    {
        PaymentSettings::mode($mode);if(strlen($raw)>65536){throw new RuntimeException('webhook_body_too_large');}
        if(!self::validSignature($raw,$signature,PaymentSettings::signature($mode))){throw new RuntimeException('webhook_signature_invalid');}
        $body=json_decode($raw,true,32,JSON_THROW_ON_ERROR);$notification=$body['notificationId']??'';$event=$body['eventType']??'';$id=AuthorizeGateway::transactionId($body['payload']['id']??null);
        if(!is_string($notification)||!preg_match('/^[A-Za-z0-9-]{8,80}$/D',$notification)||!is_string($event)||!preg_match('/^net\.authorize\.payment\.[A-Za-z.]{1,65}$/D',$event)||!$id){throw new RuntimeException('webhook_payload_invalid');}
        $reference=$body['payload']['merchantReferenceId']??'';
        if(!is_string($reference)||($reference!==''&&!preg_match('/^[A-Za-z0-9_-]{1,20}$/D',$reference))){throw new RuntimeException('webhook_payload_invalid');}
        $db=Database::connect();$hash=hash('sha256',$raw);$db->beginTransaction();
        try{
            $q=$db->prepare('INSERT INTO '.Database::table('payment_webhooks').' (environment,notification_id,transaction_id,event_type,body_hash,merchant_reference,available_at,created_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');$q->execute([$mode,$notification,$id,$event,$hash,$reference]);
            $q=$db->prepare('SELECT body_hash FROM '.Database::table('payment_webhooks').' WHERE environment=? AND notification_id=?');$q->execute([$mode,$notification]);if(!hash_equals($hash,(string)$q->fetchColumn())){throw new RuntimeException('webhook_replay_conflict');}$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function receive(string $mode): never
    {
        header('Cache-Control: no-store');header('Content-Type: text/plain; charset=utf-8');
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);header('Allow: POST');exit('POST required.');}
        if(!Paths::installed()||!Migrations::ready()){http_response_code(503);exit('Not ready.');}
        if((int)($_SERVER['CONTENT_LENGTH']??0)>65536){http_response_code(413);exit('Payload too large.');}
        $h=fopen('php://input','rb');$raw=$h?stream_get_contents($h,65537):false;if($h){fclose($h);}
        if(!is_string($raw)||strlen($raw)>65536){http_response_code(413);exit('Payload too large.');}
        try{self::accept($mode,$raw,(string)($_SERVER['HTTP_X_ANET_SIGNATURE']??''));http_response_code(200);echo 'Notification stored.';}
        catch(Throwable $e){$known=$e instanceof RuntimeException?$e->getMessage():'';$status=$known==='webhook_signature_invalid'?401:($known==='webhook_replay_conflict'?409:($known==='webhook_payload_invalid'||$e instanceof \JsonException?400:503));http_response_code($status);echo 'Notification not accepted.';}
        exit;
    }
    public static function process(int $limit=3,?callable $transport=null): array
    {
        if($transport!==null&&PHP_SAPI!=='cli'){throw new RuntimeException('Test transport refused.');}
        $db=Database::connect();$lock=InstallState::lock($db,Paths::config()['db']['prefix'].':payment-webhooks');$out=['processed'=>0,'deferred'=>0];
        try{
            $rows=$db->query('SELECT * FROM '.Database::table('payment_webhooks')." WHERE state IN ('queued','retry') AND attempts<6 AND available_at<=UTC_TIMESTAMP() ORDER BY id LIMIT ".max(1,min(10,$limit)))->fetchAll();
            foreach($rows as $r){try{
                if($r['event_type']==='net.authorize.payment.refund.created'){OrderAdjustments::reconcileRefund($r['environment'],$r['transaction_id'],$transport,0,$r['merchant_reference']);}
                else{Orders::reconcile($r['environment'],$r['transaction_id'],$transport,0,$r['merchant_reference']);}
                $q=$db->prepare('UPDATE '.Database::table('payment_webhooks')." SET state='processed',attempts=attempts+1,last_error=NULL,completed_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([$r['id']]);$out['processed']++;
            }catch(Throwable $e){$error=preg_match('/^payment_[a-z_]{1,60}$/D',$e->getMessage())?$e->getMessage():'payment_processing_deferred';$q=$db->prepare('UPDATE '.Database::table('payment_webhooks').' SET state=?,attempts=attempts+1,last_error=?,available_at=? WHERE id=?');$q->execute([(int)$r['attempts']>=5?'failed':'retry',$error,gmdate('Y-m-d H:i:s',time()+min(3600,60*(2**(int)$r['attempts']))),$r['id']]);$out['deferred']++;}}
        }finally{InstallState::unlock($db,$lock);}return $out;
    }
}
