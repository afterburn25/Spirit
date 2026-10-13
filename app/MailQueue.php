<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;
use Throwable;

final class MailQueue
{
    public static function enqueue(string $recipient,string $subject,string $html,string $dedupeKey,?PDO $db=null): int
    {
        $recipient=Security::email($recipient);$subject=Input::text(['subject'=>$subject],'subject',200,true);
        if(strlen($html)>200000||strlen($dedupeKey)>190||$dedupeKey===''){throw new RuntimeException('Invalid queued message.');}
        $db??=Database::connect();$q=$db->prepare('INSERT INTO '.Database::table('mail_queue').' (dedupe_key,recipient,subject,encrypted_body,available_at,created_at) VALUES (?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
        $q->execute([$dedupeKey,$recipient,$subject,PrivateValue::seal($html)]);return (int)$db->lastInsertId();
    }
    /** Injectable sender is for CLI tests; HTTP callers never accept one. */
    public static function process(int $limit=3,int $seconds=15,?callable $sender=null): array
    {
        if($limit<1||$limit>50){throw new RuntimeException('Invalid mail batch size.');}
        if($sender!==null&&PHP_SAPI!=='cli'){throw new RuntimeException('Custom senders are not accepted in web requests.');}
        if($sender===null&&!Communications::ready()){return ['sent'=>0,'failed'=>0,'uncertain'=>0,'waiting'=>true];}
        $db=Database::connect();$prefix=Paths::config()['db']['prefix'];$lock=InstallState::lock($db,$prefix.'mail');$started=microtime(true);$result=['sent'=>0,'failed'=>0,'uncertain'=>0,'waiting'=>false];
        try{
            $db->exec('UPDATE '.Database::table('mail_queue')." SET status='uncertain',last_error='worker_interrupted' WHERE status='sending' AND claimed_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE)");
            for($i=0;$i<$limit&&microtime(true)-$started<$seconds;$i++){
                $row=$db->query('SELECT * FROM '.Database::table('mail_queue')." WHERE status IN ('queued','retry') AND attempts<5 AND available_at<=UTC_TIMESTAMP() ORDER BY id LIMIT 1")->fetch();if(!$row){break;}
                $token=bin2hex(random_bytes(16));$q=$db->prepare('UPDATE '.Database::table('mail_queue')." SET status='sending',attempts=attempts+1,claimed_at=UTC_TIMESTAMP(),claim_token=? WHERE id=? AND status IN ('queued','retry')");$q->execute([$token,$row['id']]);if($q->rowCount()!==1){continue;}
                try{
                    $message=['id'=>$row['id'],'dedupe_key'=>$row['dedupe_key'],'recipient'=>$row['recipient'],'subject'=>$row['subject'],'html'=>PrivateValue::open($row['encrypted_body'])];
                    if($sender){$sender($message);}else{SmtpMailer::send($message,Communications::options());}
                    $q=$db->prepare('UPDATE '.Database::table('mail_queue')." SET status='sent',sent_at=UTC_TIMESTAMP(),last_error=NULL WHERE id=? AND claim_token=?");$q->execute([$row['id'],$token]);$result['sent']++;
                }catch(Throwable $e){
                    $uncertain=$e instanceof DeliveryUncertain;$attempts=(int)$row['attempts']+1;$state=$uncertain?'uncertain':($attempts>=5?'failed':'retry');
                    $error=$uncertain?'smtp_delivery_uncertain':(preg_match('/^smtp_[a-z0-9_]{1,60}$/D',$e->getMessage())?$e->getMessage():'delivery_failed');$next=gmdate('Y-m-d H:i:s',time()+min(3600,60*(2**$attempts)));
                    $q=$db->prepare('UPDATE '.Database::table('mail_queue').' SET status=?,last_error=?,available_at=? WHERE id=? AND claim_token=?');$q->execute([$state,$error,$next,$row['id'],$token]);$result[$uncertain?'uncertain':'failed']++;
                }
            }
        }finally{InstallState::unlock($db,$lock);}
        return $result;
    }
    public static function retry(int $id,int $actor,bool $acknowledgeDuplicate=false): void
    {
        $db=Database::connect();$q=$db->prepare('SELECT status FROM '.Database::table('mail_queue').' WHERE id=?');$q->execute([$id]);$state=$q->fetchColumn();
        if(!in_array($state,['failed','retry','uncertain'],true)){throw new RuntimeException('Only failed, retrying or uncertain messages can be retried.');}
        if($state==='uncertain'&&!$acknowledgeDuplicate){throw new RuntimeException('Delivery is uncertain. Confirm that retrying may send a duplicate after checking the mailbox/provider.');}
        $q=$db->prepare('UPDATE '.Database::table('mail_queue')." SET status='queued',attempts=0,available_at=UTC_TIMESTAMP(),last_error=NULL WHERE id=? AND status=?");$q->execute([$id,$state]);Database::audit($db,$actor,'mail.retry_requested');
    }
}
