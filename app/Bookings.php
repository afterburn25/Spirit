<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class Bookings
{
    public const STATUSES=['new'=>'New','reviewing'=>'Reviewing','discussing'=>'Discussing details','quote_sent'=>'Quote sent','confirmed'=>'Confirmed','declined'=>'Declined'];
    public const TYPES=['festival'=>'Festival','private'=>'Private event','venue'=>'Club or venue','corporate'=>'Corporate event','community'=>'Community event','other'=>'Other'];

    public static function validate(array $input): array
    {
        $data=['name'=>Input::text($input,'name',120,true),'email'=>Security::email(Input::text($input,'email',190,true))];
        foreach(['phone'=>50,'organization'=>180,'location'=>300,'performance_length'=>180,'budget'=>180,'sound'=>300,'message'=>8000] as $key=>$max){$data[$key]=Input::text($input,$key,$max,$key==='location');}
        $data['event_type']=Input::choice($input,'event_type',array_keys(self::TYPES),'other');
        $data['preferred_date']=Input::date(Input::text($input,'preferred_date',10));
        $data['audience']=Input::integer($input,'audience',0,500000,0);
        if(($input['contact_consent']??'')!=='1'){throw new InvalidArgumentException('Please confirm that the band may contact you about this inquiry.');}
        $data['contact_consent_version']='booking-contact-2026-10-13-v1';
        return $data;
    }

    public static function submit(array $input,string $nonce): array
    {
        if(!preg_match('/^[a-f0-9]{64}$/D',$nonce)){throw new RuntimeException('This inquiry form expired. Reload the page.');}
        $data=self::validate($input);$hash=hash_hmac('sha256',$nonce,Paths::config()['secret']);$db=Database::connect();
        $q=$db->prepare('INSERT INTO '.Database::table('bookings')." (reference,submission_hash,name,email,payload_json,private_notes,created_at,updated_at) VALUES (?,?,?,?,?,'',UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
        $q->execute(['SG-'.strtoupper(bin2hex(random_bytes(7))),$hash,$data['name'],$data['email'],json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
        // Resolve by the submitted nonce, never by a colliding public reference.
        $q=$db->prepare('SELECT id,reference FROM '.Database::table('bookings').' WHERE submission_hash=?');$q->execute([$hash]);$booking=$q->fetch();
        if(!$booking){throw new RuntimeException('Unable to allocate an inquiry reference. Please submit again.');}
        // The inquiry is committed before notification work. An interrupted queue
        // write remains discoverable through notifications_queued=0.
        try{self::queueNotifications((int)$booking['id']);}
        catch(Throwable $e){error_log('Spirit inquiry saved; notification reconciliation pending ['.get_class($e).']');}
        return ['id'=>(int)$booking['id'],'reference'=>$booking['reference']];
    }

    public static function queueNotifications(int $id): void
    {
        $db=Database::connect();$db->beginTransaction();
        try{
            $q=$db->prepare('SELECT id,reference,email,notifications_queued FROM '.Database::table('bookings').' WHERE id=? FOR UPDATE');$q->execute([$id]);$booking=$q->fetch();
            if(!$booking||(int)$booking['notifications_queued']===1){$db->commit();return;}
            $owner=Communications::bookingAddress();
            if($owner===''){throw new RuntimeException('No active booking notification recipient.');}
            MailQueue::enqueue($owner,'New booking inquiry '.$booking['reference'],Communications::message('A new event inquiry is waiting.',"Reference: ".$booking['reference']."\nSign in to review the organizer, event details and private notes.",'Review inquiry',Communications::absolute('admin/bookings/view.php?id='.$id)),'booking:'.$id.':owner',$db);
            MailQueue::enqueue($booking['email'],'Your Spiritual Gorillaz inquiry '.$booking['reference'],Communications::message('Your inquiry has been received.',"Reference: ".$booking['reference']."\n\nThe band will review your event details and contact you. This is an inquiry acknowledgement, not a confirmed reservation."),'booking:'.$id.':customer',$db);
            $q=$db->prepare('UPDATE '.Database::table('bookings').' SET notifications_queued=1 WHERE id=?');$q->execute([$id]);$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }

    public static function reconcileNotifications(int $limit=10): void
    {
        $limit=max(1,min(50,$limit));
        $ids=Database::connect()->query('SELECT id FROM '.Database::table('bookings').' WHERE notifications_queued=0 ORDER BY id LIMIT '.$limit)->fetchAll(\PDO::FETCH_COLUMN);
        foreach($ids as $id){self::queueNotifications((int)$id);}
    }

    public static function get(int $id): ?array
    {
        $q=Database::connect()->prepare('SELECT * FROM '.Database::table('bookings').' WHERE id=?');$q->execute([$id]);$row=$q->fetch();
        if(!$row){return null;}$row['data']=json_decode($row['payload_json'],true,64,JSON_THROW_ON_ERROR);return $row;
    }

    public static function update(int $id,array $input,int $actor): void
    {
        $status=Input::choice($input,'status',array_keys(self::STATUSES),'new');$notes=Input::text($input,'private_notes',16000);
        $revision=Input::integer($input,'revision',1,100000000,1);$assigned=Input::integer($input,'assigned_to',0,PHP_INT_MAX,0);$db=Database::connect();
        if($assigned){$q=$db->prepare('SELECT id FROM '.Database::table('users')." WHERE id=? AND role='owner' AND active=1");$q->execute([$assigned]);if(!$q->fetchColumn()){throw new InvalidArgumentException('Select an active owner for this inquiry.');}}
        $db->beginTransaction();
        try{
            $q=$db->prepare('UPDATE '.Database::table('bookings').' SET status=?,private_notes=?,assigned_to=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=? AND revision=?');$q->execute([$status,$notes,$assigned?:null,$id,$revision]);
            if($q->rowCount()!==1){throw new RuntimeException('This inquiry changed in another session. Reload before updating.');}
            Database::audit($db,$actor,'booking.updated');$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
}
