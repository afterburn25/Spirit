<?php
declare(strict_types=1);
// Never deployed or reachable over HTTP. Synthetic isolated-database helper.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,ConfigStore,PrivateValue,Migrations};
$c=Paths::config();
if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Refusing non-test configuration.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);$db=Database::connect();$out=[];
switch($argv[1]??''){
case 'fingerprint':
    $q=$db->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() AND LEFT(table_name,?)=? ORDER BY table_name');$q->execute([strlen($c['db']['prefix']),$c['db']['prefix']]);
    foreach($q->fetchAll(PDO::FETCH_COLUMN) as $table){
        if(!preg_match('/^sgci_[a-f0-9]{8}_[a-z_]+$/D',$table)){throw new RuntimeException('Unexpected fixture table.');}
        $records=array_map(static fn($row)=>json_encode($row,JSON_THROW_ON_ERROR),$db->query('SELECT * FROM `'.$table.'`')->fetchAll());sort($records,SORT_STRING);
        $out[substr($table,strlen($c['db']['prefix']))]=hash('sha256',implode("\n",$records));
    }break;
case 'configure':
    $s='PRIVATE-CONFIG-SENTINEL';
    ConfigStore::write('communication',['enabled'=>true,'newsletter_enabled'=>true,'smtp_host'=>'private-host.example.test','smtp_user'=>$s,'smtp_password'=>PrivateValue::seal($s),'from_email'=>'private-sender@example.test','from_name'=>$s],1);
    ConfigStore::write('store',['enabled'=>true,'accounts_enabled'=>true,'intro'=>$s,'delivery'=>['configured'=>true,'policy'=>$s]],1);
    ConfigStore::write('payments',['enabled'=>true,'environment'=>'sandbox','sandbox'=>['login_id'=>$s,'client_key'=>$s,'transaction_key'=>PrivateValue::seal($s),'signature_key'=>PrivateValue::seal($s)]],1);break;
case 'role':
    $role=$input['role']??'';if(!in_array($role,['owner','content_editor','store_manager'],true)){throw new RuntimeException('Invalid fixture role.');}
    $q=$db->prepare('UPDATE '.Database::table('users').' SET role=? WHERE id=1');$q->execute([$role]);break;
case 'schema_bad':$db->exec('UPDATE '.Database::table('migrations')." SET checksum=REPEAT('0',64) WHERE migration='006.sql'");break;
case 'schema_restore':
    $q=$db->prepare('UPDATE '.Database::table('migrations').' SET checksum=? WHERE migration=?');foreach(Migrations::files() as $file){$q->execute([hash_file('sha256',$file),basename($file)]);}break;
case 'service_offline':$db->exec('RENAME TABLE '.Database::table('service_settings').' TO '.Database::table('service_settings_offline'));break;
case 'service_restore':$db->exec('RENAME TABLE '.Database::table('service_settings_offline').' TO '.Database::table('service_settings'));break;
default:throw new RuntimeException('Unknown launch fixture.');
}
echo json_encode($out,JSON_THROW_ON_ERROR);
