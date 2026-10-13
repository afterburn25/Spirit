<?php
declare(strict_types=1);
// Never deployed: exercise a real database failure after simulated SMTP acceptance.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,MailQueue};
$c=Paths::config();
if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Refusing non-test database.');}
function check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";}
$db=Database::connect();$table=Database::table('mail_queue');$trigger=Database::table('fixture_receipt_failure');
$id=MailQueue::enqueue('fixture@example.test','Synthetic receipt failure','<p>Test only.</p>','fixture:receipt-failure');
$sent=0;$sender=static function(array $message)use(&$sent):void{$sent++;};
try{
    $db->exec("CREATE TRIGGER ".$trigger." BEFORE UPDATE ON ".$table." FOR EACH ROW BEGIN IF NEW.status='sent' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic receipt write failure'; END IF; END");
    $r=MailQueue::process(1,5,$sender);
    check($r['uncertain']===1&&$r['sent']===0&&$sent===1,'Accepted message with failed database receipt is uncertain, not retryable');
    $q=$db->prepare('SELECT status,last_error FROM '.$table.' WHERE id=?');$q->execute([$id]);$row=$q->fetch();
    check($row['status']==='uncertain'&&$row['last_error']==='smtp_delivery_uncertain','Persistence failure leaves a redacted uncertain-delivery state');
    MailQueue::process(1,5,$sender);
    check($sent===1,'A second worker pass cannot resend an accepted message with an uncertain receipt');
    $rejected=false;try{MailQueue::retry($id,1);}catch(RuntimeException $e){$rejected=true;}
    check($rejected,'Database-receipt uncertainty still requires explicit duplicate-risk acknowledgement');
}finally{$db->exec('DROP TRIGGER IF EXISTS '.$trigger);}
MailQueue::retry($id,1,true);$r=MailQueue::process(1,5,$sender);
check($r['sent']===1&&$sent===2,'Explicitly acknowledged retry works after the database fault is repaired');
