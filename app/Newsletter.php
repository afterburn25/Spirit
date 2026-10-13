<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class Newsletter
{
    public const CONSENT_VERSION='band-news-2026-10-13-v1';
    public static function request(string $email,bool $consent): void
    {
        if(!$consent){throw new InvalidArgumentException('Confirm that you would like band news, releases and show updates.');}
        if(!Communications::newsletterReady()){throw new RuntimeException('Mailing-list signup is not available yet. Please check back later.');}
        $email=Security::email($email);$db=Database::connect();$db->beginTransaction();
        try{
            $q=$db->prepare('INSERT IGNORE INTO '.Database::table('subscribers').' (email,requested_at,consent_version,source) VALUES (?,UTC_TIMESTAMP(),?,\'website\')');$q->execute([$email,self::CONSENT_VERSION]);$created=$q->rowCount()===1;
            $q=$db->prepare('SELECT * FROM '.Database::table('subscribers').' WHERE email=? FOR UPDATE');$q->execute([$email]);$row=$q->fetch();
            // Generic response in every case: never expose whether an address exists.
            if($row['status']==='confirmed'||(!$created&&$row['status']==='pending'&&strtotime($row['requested_at'].' UTC')>time()-600)){$db->commit();return;}
            $token=bin2hex(random_bytes(32));$hash=hash('sha256',$token);$expires=gmdate('Y-m-d H:i:s',time()+48*3600);
            $q=$db->prepare('UPDATE '.Database::table('subscribers')." SET status='pending',confirm_hash=?,confirm_expires_at=?,unsubscribe_hash=NULL,requested_at=UTC_TIMESTAMP(),confirmed_at=NULL,unsubscribed_at=NULL,consent_version=?,source='website' WHERE id=?");$q->execute([$hash,$expires,self::CONSENT_VERSION,$row['id']]);
            $url=Communications::absolute('newsletter/confirm.php?token='.$token);
            MailQueue::enqueue($email,'Confirm your Spiritual Gorillaz updates',Communications::message('One more step.','Confirm that you want band news, new releases and show updates. This link expires in 48 hours. You are not subscribed until you confirm. If you did not request this, ignore the message.','Confirm my subscription',$url),'newsletter:confirm:'.$hash,$db);
            $db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function validToken(string $token): bool {return (bool)preg_match('/^[a-f0-9]{64}$/D',$token);}
    public static function confirm(string $token): bool
    {
        if(!self::validToken($token)){return false;}$db=Database::connect();$db->beginTransaction();
        try{
            $q=$db->prepare('SELECT id,email FROM '.Database::table('subscribers')." WHERE confirm_hash=? AND status='pending' AND confirm_expires_at>UTC_TIMESTAMP() FOR UPDATE");$q->execute([hash('sha256',$token)]);$row=$q->fetch();
            if(!$row){$db->commit();return false;}
            $unsubscribe=bin2hex(random_bytes(32));
            $q=$db->prepare('UPDATE '.Database::table('subscribers')." SET status='confirmed',confirm_hash=NULL,confirm_expires_at=NULL,unsubscribe_hash=?,confirmed_at=UTC_TIMESTAMP() WHERE id=?");$q->execute([hash('sha256',$unsubscribe),$row['id']]);
            MailQueue::enqueue($row['email'],'You are on the Spiritual Gorillaz mailing list',Communications::message('Welcome to the movement.','Your subscription is confirmed. You can leave the list at any time using the unsubscribe link below.','Unsubscribe from updates',Communications::absolute('newsletter/unsubscribe.php?token='.$unsubscribe)),'newsletter:welcome:'.hash('sha256',$unsubscribe),$db);
            $db->commit();return true;
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function unsubscribe(string $token): bool
    {
        if(!self::validToken($token)){return false;}
        $q=Database::connect()->prepare('UPDATE '.Database::table('subscribers')." SET status='unsubscribed',confirm_hash=NULL,confirm_expires_at=NULL,unsubscribed_at=UTC_TIMESTAMP() WHERE unsubscribe_hash=? AND status IN ('confirmed','unsubscribed')");$q->execute([hash('sha256',$token)]);
        // Repeated valid links remain a safe, idempotent unsubscribe action.
        $q=Database::connect()->prepare('SELECT COUNT(*) FROM '.Database::table('subscribers')." WHERE unsubscribe_hash=? AND status='unsubscribed'");$q->execute([hash('sha256',$token)]);return (int)$q->fetchColumn()>0;
    }
    public static function csvValue(string $value): string
    {
        return preg_match('/^[\s]*[=+@\-\t\r]/u',$value)?"'".$value:$value;
    }
}
