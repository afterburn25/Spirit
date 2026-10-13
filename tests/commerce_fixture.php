<?php
declare(strict_types=1);
// Synthetic-only fixture. Never included in a deployment archive.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,PrivateValue,MailQueue};
$c=Paths::config();if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Non-test database refused.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);$db=Database::connect();$result=[];
switch($argv[1]??''){
case 'snapshot':
 foreach(['products','product_variants','inventory_adjustments','customers','customer_addresses','customer_tokens','mail_queue'] as $table){$result[$table]=$db->query('SELECT * FROM '.Database::table($table).' ORDER BY '.($table==='customer_tokens'?'customer_id':'id'))->fetchAll();}break;
case 'mail':
 $q=$db->prepare('SELECT encrypted_body FROM '.Database::table('mail_queue').' WHERE dedupe_key LIKE ? ORDER BY id DESC LIMIT 1');$q->execute(['customer:'.$input['purpose'].':%']);$result=['html'=>PrivateValue::open((string)$q->fetchColumn())];break;
case 'expire':
 $db->exec('UPDATE '.Database::table('customer_tokens').' SET expires_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE)');break;
case 'cooldown':
 $db->exec('UPDATE '.Database::table('customer_tokens').' SET requested_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 10 MINUTE)');break;
case 'expire-carts':
 $db->exec('UPDATE '.Database::table('carts').' SET expires_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE)');break;
case 'process':$result=MailQueue::process(50,5,static function(array $message):void{});break;
default:throw new RuntimeException('Unknown fixture action.');
}
echo json_encode($result,JSON_THROW_ON_ERROR);
