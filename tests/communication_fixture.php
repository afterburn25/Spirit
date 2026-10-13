<?php
declare(strict_types=1);
// Test-only CLI driver, excluded from every deployment ZIP.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,Bookings,Communications,ConfigStore,MailQueue,PrivateValue,RequestLimit,SmtpMailer,DeliveryUncertain};
$c=Paths::config();
if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Refusing non-test configuration.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);$db=Database::connect();$id=max(0,(int)($input['id']??0));$result=[];
switch($argv[1]??''){
case 'snapshot':
    foreach(['bookings','mail_queue','subscribers'] as $table){$result[$table]=$db->query('SELECT * FROM '.Database::table($table).' ORDER BY id')->fetchAll();}
    $result['settings']=ConfigStore::read('communication');break;
case 'decrypt':
    $q=$db->prepare('SELECT encrypted_body FROM '.Database::table('mail_queue').' WHERE id=?');$q->execute([$id]);$result=['html'=>PrivateValue::open((string)$q->fetchColumn())];break;
case 'enqueue':
    $result=['id'=>MailQueue::enqueue('fixture@example.test','Synthetic delivery test','<p>Generated test message.</p>',$input['key'])];break;
case 'clear_mail':$db->exec('DELETE FROM '.Database::table('mail_queue'));break;
case 'due':$db->exec('UPDATE '.Database::table('mail_queue')." SET available_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE) WHERE status IN ('queued','retry')");break;
case 'interrupt':
    $q=$db->prepare('UPDATE '.Database::table('mail_queue')." SET status='sending',claimed_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 6 MINUTE) WHERE id=?");$q->execute([$id]);break;
case 'expire':
    $q=$db->prepare('UPDATE '.Database::table('subscribers')." SET confirm_expires_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE) WHERE email=?");$q->execute([$input['email']]);break;
case 'queue_break':$db->exec('RENAME TABLE '.Database::table('mail_queue').' TO '.Database::table('mail_queue_fixture_offline'));break;
case 'queue_restore':$db->exec('RENAME TABLE '.Database::table('mail_queue_fixture_offline').' TO '.Database::table('mail_queue'));break;
case 'process':
    $mode=$input['mode']??'accept';$nested=false;
    $sender=static function(array $message)use($mode,&$nested):void{
        if($mode==='fail'){throw new RuntimeException('Synthetic failure with a secret that must not enter diagnostics');}
        if($mode==='uncertain'){throw new DeliveryUncertain('smtp_delivery_uncertain');}
        if($mode==='nested'){
            try{MailQueue::process(1,1,static function(array $m):void{});}catch(RuntimeException $e){$nested=true;}
            if(!$nested){throw new RuntimeException('Second worker acquired the same queue lock.');}
        }
    };
    $result=MailQueue::process(50,5,$sender);$result['nested_lock_rejected']=$nested;break;
case 'retry':
    try{MailQueue::retry($id,1,(bool)($input['ack']??false));$result=['ok'=>true];}catch(RuntimeException $e){$result=['ok'=>false,'error'=>$e->getMessage()];}break;
case 'limit':
    try{RequestLimit::consume('fixture-limit',1,900,'synthetic-subject');$result=['allowed'=>true];}catch(RuntimeException $e){$result=['allowed'=>false];}break;
case 'smtp':
    $host=$input['host']??'localhost';if(!in_array($host,['localhost','127.0.0.1'],true)){throw new RuntimeException('SMTP tests only allow loopback.');}
    $options=['smtp_host'=>$host,'smtp_port'=>(int)$input['port'],'smtp_security'=>$input['security']??'starttls','smtp_user'=>'fixture-user','smtp_password'=>PrivateValue::seal('fixture-password'),'from_email'=>'band@example.test','from_name'=>'Spiritual Gorillaz test'];
    $message=['id'=>1,'dedupe_key'=>'smtp-fixture','recipient'=>'fan@example.test','subject'=>'Synthetic SMTP fixture','html'=>'<p>Generated test body. Not a real email.</p>'];$start=microtime(true);
    try{SmtpMailer::send($message,$options,(float)($input['seconds']??3));$result=['ok'=>true];}
    catch(Throwable $e){$result=['ok'=>false,'class'=>get_class($e),'error'=>$e->getMessage()];}
    $result['elapsed']=microtime(true)-$start;break;
default:throw new RuntimeException('Unknown communication fixture action.');
}
echo json_encode($result,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
