<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,PrivateValue,TransientCleanup,BackupSchema};
$c=Paths::config();if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Non-test database refused.');}
$db=Database::connect();$out=[];
switch($argv[1]??''){
case 'seed':
 foreach(['request_limits','login_limits'] as $name){$q=$db->prepare('INSERT INTO '.Database::table($name).' (bucket,attempts,expires_at) VALUES (?,?,?)');$q->execute([hash('sha256',$name.':active'),5,time()+3600]);for($i=0;$i<($name==='request_limits'?205:1);$i++){$q->execute([hash('sha256',$name.':old:'.$i),5,time()-2*86400]);}}
 foreach([45,-1] as $days){
  $q=$db->prepare('INSERT INTO '.Database::table('customers')." (name,email,password_hash,status,consent_version,created_at,verified_at) VALUES (?,?,?,'active','fixture',UTC_TIMESTAMP(),UTC_TIMESTAMP())");$q->execute(['Cleanup fixture','cleanup'.$days.'@example.test',password_hash('Synthetic customer secret',PASSWORD_DEFAULT)]);$id=(int)$db->lastInsertId();
  $q=$db->prepare('INSERT INTO '.Database::table('customer_tokens')." (customer_id,purpose,token_hash,expires_at,requested_at) VALUES (?,'reset',?,?,?)");$q->execute([$id,hash('sha256','customer:'.$days),gmdate('Y-m-d H:i:s',time()-$days*86400),gmdate('Y-m-d H:i:s',time()-($days+1)*86400)]);
  $q=$db->prepare('INSERT INTO '.Database::table('staff_tokens')." (token_hash,email,name,kind,target_role,expires_at,created_at) VALUES (?,?,?,'invite','content_editor',?,?)");$q->execute([hash('sha256','staff:'.$days),'staff'.$days.'@example.test','Fixture invite',gmdate('Y-m-d H:i:s',time()-$days*86400),gmdate('Y-m-d H:i:s',time()-($days+1)*86400)]);
 }
 foreach(['sent','cancelled','queued','uncertain','failed'] as $status){$q=$db->prepare('INSERT INTO '.Database::table('mail_queue')." (dedupe_key,recipient,subject,encrypted_body,status,available_at,created_at,sent_at) VALUES (?,'fixture@example.test','Synthetic retention test',?,?,UTC_TIMESTAMP(),DATE_SUB(UTC_TIMESTAMP(),INTERVAL 100 DAY),DATE_SUB(UTC_TIMESTAMP(),INTERVAL 99 DAY))");$q->execute(['cleanup:'.$status,PrivateValue::seal('Synthetic private body'),$status]);}
 foreach(['old','ordered','current'] as $kind){
  $expires=gmdate('Y-m-d H:i:s',time()+($kind==='current'?86400:-45*86400));$q=$db->prepare('INSERT INTO '.Database::table('carts').' (token_hash,expires_at,updated_at) VALUES (?,?,UTC_TIMESTAMP())');$q->execute([hash('sha256','cart:'.$kind),$expires]);$id=(int)$db->lastInsertId();
  $q=$db->prepare('INSERT INTO '.Database::table('cart_items').' (cart_id,variant_id,quantity) VALUES (?,1,1)');$q->execute([$id]);$q=$db->prepare('INSERT INTO '.Database::table('cart_operations').' (cart_id,nonce_hash,payload_hash) VALUES (?,?,?)');$q->execute([$id,hash('sha256',$kind),hash('sha256','payload')]);
  if($kind==='ordered'){
   $q=$db->prepare('INSERT INTO '.Database::table('orders')." (reference,submission_hash,payload_hash,cart_id,buyer_email,private_data,environment,merchant_login,subtotal_cents,shipping_cents,tax_cents,total_cents,expires_at,access_hash,access_expires_at,created_at,updated_at) VALUES ('RETENTION-FIXTURE',?,?,?,'fixture@example.test',?,'sandbox','fixture',100,0,0,100,UTC_TIMESTAMP(),?,UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP())");$q->execute([hash('sha256','order-submit'),hash('sha256','order-payload'),$id,PrivateValue::seal('{}'),hash('sha256','access')]);
  }
 }
 $path=Paths::runtime().'/media/.restore-'.str_repeat('a',32);file_put_contents($path,'old restore temporary');touch($path,time()-9*86400);break;
case 'snapshot':
 foreach(['request_limits','login_limits'] as $table){$out[$table]=(int)$db->query('SELECT COUNT(*) FROM '.Database::table($table).' WHERE expires_at<UNIX_TIMESTAMP()-86400')->fetchColumn();}
 foreach(['customer_tokens','staff_tokens','carts','cart_items','cart_operations'] as $table){$out[$table]=(int)$db->query('SELECT COUNT(*) FROM '.Database::table($table))->fetchColumn();}
 $out['mail']=$db->query('SELECT status,LENGTH(encrypted_body) AS length FROM '.Database::table('mail_queue')." WHERE dedupe_key LIKE 'cleanup:%' ORDER BY id")->fetchAll();
 $out['protected']=[];foreach(BackupSchema::names() as $name){if(in_array($name,['request_limits','login_limits','customer_tokens','staff_tokens','carts','cart_items','cart_operations','mail_queue','audit'],true)){continue;}$out['protected'][$name]=hash('sha256',json_encode($db->query('SELECT * FROM '.Database::table($name))->fetchAll(),JSON_THROW_ON_ERROR));}
 $out['temporary_exists']=is_file(Paths::runtime().'/media/.restore-'.str_repeat('a',32));break;
case 'fault':
 $db->exec('RENAME TABLE '.Database::table('login_limits').' TO '.Database::table('login_limits_offline_fixture'));
 try{TransientCleanup::run(null);$out['failed']=false;}catch(Throwable $e){$out['failed']=true;}
 finally{$db->exec('RENAME TABLE '.Database::table('login_limits_offline_fixture').' TO '.Database::table('login_limits'));}break;
default:throw new RuntimeException('Unknown cleanup fixture.');
}
echo json_encode((object)$out,JSON_THROW_ON_ERROR);
