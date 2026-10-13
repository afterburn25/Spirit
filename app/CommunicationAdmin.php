<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;

/** Owner-only communication screens. No public method may select arbitrary files. */
final class CommunicationAdmin
{
    public static function render(string $section): void
    {
        $user=ContentAdmin::boot();
        $titles=['bookings'=>'Booking inquiries','booking'=>'Review inquiry','communication'=>'Email settings','mail'=>'Delivery queue','subscribers'=>'Subscribers'];
        if(!isset($titles[$section])){throw new RuntimeException('Unknown administration section.');}
        $id=max(0,(int)($_GET['id']??0));$record=$section==='booking'?Bookings::get($id):null;
        if($section==='booking'&&!$record){http_response_code(404);exit('Inquiry not found.');}
        $error=null;$db=Database::connect();
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                if($section==='booking'){Bookings::update($id,$_POST,(int)$user['id']);flash('Inquiry updated. Private notes remain private.');go('admin/bookings/view.php?id='.$id);}
                elseif($section==='communication'){
                    if(post('action')==='test'){
                        if(!Communications::ready()){throw new RuntimeException('Save and enable SMTP before testing delivery.');}
                        MailQueue::enqueue($user['email'],'Spiritual Gorillaz delivery test',Communications::message('Your delivery test.','Verify that this message arrived before relying on automated email.'),'owner-test:'.bin2hex(random_bytes(16)));
                        $r=MailQueue::process(3,20);flash(self::batchMessage($r));
                    }else{Communications::save($_POST,(int)$user['id']);flash('Communication settings saved. Delivery still needs a verified test.');}
                    go('admin/communication/');
                }elseif($section==='mail'){
                    if(post('action')==='retry'){MailQueue::retry(max(0,(int)post('id')),(int)$user['id'],post('acknowledge_duplicate')==='1');flash('Retry queued.');}
                    else{flash(self::batchMessage(MailQueue::process(5,30)));}go('admin/mail/');
                }elseif($section==='subscribers'&&post('action')==='export'){self::export((int)$user['id']);}
            }catch(\InvalidArgumentException|RuntimeException $e){$error=$e instanceof \PDOException?'The operation could not complete. Please try again.':$e->getMessage();}
        }
        $options=Communications::options();$values=$error?array_merge($options,$_POST):$options;
        $items=[];$total=0;$pages=1;$page=1;$status=is_string($_GET['status']??null)?$_GET['status']:'';$search=is_string($_GET['q']??null)?substr(trim($_GET['q']),0,100):'';
        if(in_array($section,['bookings','mail','subscribers'],true)){
            $table=Database::table(match($section){'bookings'=>'bookings','mail'=>'mail_queue',default=>'subscribers'});
            $conditions=[];$parameters=[];$allowed=$section==='bookings'?array_keys(Bookings::STATUSES):($section==='subscribers'?['pending','confirmed','unsubscribed']:['queued','sending','sent','retry','failed','uncertain']);
            if(in_array($status,$allowed,true)){$conditions[]='status=?';$parameters[]=$status;}
            if($section==='bookings'&&$search!==''){$conditions[]='(name LIKE ? OR email LIKE ? OR reference LIKE ?)';$parameters=array_merge($parameters,array_fill(0,3,'%'.$search.'%'));}
            $where=$conditions?' WHERE '.implode(' AND ',$conditions):'';
            $q=$db->prepare('SELECT COUNT(*) FROM '.$table.$where);$q->execute($parameters);$total=(int)$q->fetchColumn();$pages=max(1,(int)ceil($total/25));$page=max(1,min($pages,(int)($_GET['page']??1)));
            $fields=match($section){'bookings'=>'id,reference,name,email,status,created_at','mail'=>'id,recipient,subject,status,attempts,last_error,created_at',default=>'email,status,requested_at,confirmed_at,consent_version'};
            $q=$db->prepare('SELECT '.$fields.' FROM '.$table.$where.' ORDER BY id DESC LIMIT 25 OFFSET '.(($page-1)*25));$q->execute($parameters);$items=$q->fetchAll();
        }
        View::start($titles[$section],'admin',$section==='booking'?'bookings':$section,$user);
        require Paths::root().'/views/admin/communication.php';
        View::end();
    }
    private static function batchMessage(array $r): string
    {
        return $r['waiting']?'Delivery is not configured. Messages remain safely queued.':'SMTP accepted '.$r['sent'].' message(s); '.$r['failed'].' failed/deferred; '.$r['uncertain'].' uncertain. Check the provider and mailbox.';
    }
    private static function export(int $actor): never
    {
        $db=Database::connect();Database::audit($db,$actor,'subscribers.export_confirmed');
        header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="confirmed-band-subscribers.csv"');header('Cache-Control: private, no-store');
        $out=fopen('php://output','wb');fputcsv($out,['Email','Confirmed UTC','Consent version','Source'],',','"','');
        $q=$db->query('SELECT email,confirmed_at,consent_version,source FROM '.Database::table('subscribers')." WHERE status='confirmed' ORDER BY id");
        while($row=$q->fetch(PDO::FETCH_NUM)){fputcsv($out,array_map(fn($v)=>Newsletter::csvValue((string)$v),$row),',','"','');}fclose($out);exit;
    }
}
