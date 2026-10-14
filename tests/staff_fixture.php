<?php
declare(strict_types=1);
// Synthetic-only data fixture. Never packaged, never accessible over HTTP.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,PrivateValue,MailQueue};
$c=Paths::config();if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Non-test database refused.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);$db=Database::connect();$out=[];
switch($argv[1]??''){
case 'snapshot':
 $out['users']=$db->query('SELECT id,name,email,role,active,session_version FROM '.Database::table('users').' ORDER BY id')->fetchAll();
 $out['tokens']=$db->query('SELECT * FROM '.Database::table('staff_tokens').' ORDER BY id')->fetchAll();
 $out['mail']=$db->query('SELECT id,dedupe_key,recipient,encrypted_body,status FROM '.Database::table('mail_queue')." WHERE dedupe_key LIKE 'staff:%' ORDER BY id")->fetchAll();
 $out['audit']=$db->query('SELECT action FROM '.Database::table('audit')." WHERE action LIKE 'staff.%' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);break;
case 'mail':
 $q=$db->prepare('SELECT encrypted_body FROM '.Database::table('mail_queue').' WHERE recipient=? AND dedupe_key LIKE ? ORDER BY id DESC LIMIT 1');$q->execute([$input['email'],'staff:'.$input['kind'].':%']);$body=$q->fetchColumn();
 $out=['html'=>$body?PrivateValue::open($body):''];break;
case 'expire':
 $q=$db->prepare('UPDATE '.Database::table('staff_tokens').' SET expires_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE) WHERE email=? AND used_at IS NULL');$q->execute([$input['email']]);break;
case 'due':$db->exec('UPDATE '.Database::table('mail_queue')." SET available_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE) WHERE dedupe_key LIKE 'staff:%' AND status IN ('queued','retry')");break;
case 'process':$out=MailQueue::process(50,5,static function(array $m):void{});break;
default:throw new RuntimeException('Unknown staff fixture.');
}
echo json_encode($out,JSON_THROW_ON_ERROR);
