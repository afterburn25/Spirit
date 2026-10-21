<?php
declare(strict_types=1);
// CLI only, disposable installation and database. Never deployed.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing test installation.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,Backups,BackupSchema,MaintenanceGate,Media};
$c=Paths::config();if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Refusing a non-test database.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);$db=Database::connect();$out=[];$id=$input['id']??'';
try{
 switch($argv[1]??''){
 case 'snapshot':
  $out=['backups'=>Backups::listing(),'config_hash'=>hash('sha256',json_encode(Paths::config(),JSON_THROW_ON_ERROR)),'content'=>$db->query('SELECT id,title,revision FROM '.Database::table('content').' ORDER BY id')->fetchAll(),'media'=>$db->query('SELECT id,storage_name,checksum FROM '.Database::table('media').' ORDER BY id')->fetchAll(),'users'=>$db->query('SELECT id,email,role,session_version FROM '.Database::table('users').' ORDER BY id')->fetchAll()];
  $out['protected']=[];foreach(BackupSchema::protectedNames() as $table){$out['protected'][$table]=BackupSchema::digest($db,$table);}
  foreach(['analytics_visits','analytics_pageviews','analytics_crawlers','analytics_search_queries'] as $table){$out['analytics'][$table]=BackupSchema::digest($db,$table);}break;
 case 'analytics-after-snapshot':
  $db->prepare('INSERT INTO '.Database::table('analytics_visits').' (visit_key,started_at,last_seen,active_clock_at,page_count) VALUES (?,UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP(),1)')->execute([hash('sha256','backup-synthetic-current-visit')]);$visit=(int)$db->lastInsertId();
  $db->prepare('INSERT INTO '.Database::table('analytics_pageviews').' (visit_id,page_path,viewed_at,last_seen) VALUES (?,\'/music/\',UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute([$visit]);
  $db->exec('INSERT INTO '.Database::table('analytics_crawlers')." (visit_date,family,verification,page_path,last_seen) VALUES (UTC_DATE(),'Synthetic crawler','reported_ua','/music/',UTC_TIMESTAMP())");
  $db->exec('INSERT INTO '.Database::table('analytics_search_queries')." (period_start,period_end,query_text,clicks,impressions,average_position,imported_at) VALUES (UTC_DATE(),UTC_DATE(),'Synthetic current query snapshot',1,10,2,UTC_TIMESTAMP())");
  foreach(['analytics_visits','analytics_pageviews','analytics_crawlers','analytics_search_queries'] as $table){$out[$table]=BackupSchema::digest($db,$table);}break;
 case 'archive':$out=['path'=>Backups::path($id),'key'=>bin2hex(Backups::key($id)),'info'=>Backups::info($id)];break;
 case 'tamper':$path=Backups::path($id);$h=fopen($path,'r+b');fseek($h,100);$b=fread($h,1);fseek($h,100);fwrite($h,chr(ord($b)^1));fclose($h);break;
 case 'media-break':
  $row=Media::get((int)$input['media_id'])??throw new RuntimeException('Missing fixture media.');$path=Media::path($row);
  if(($input['missing']??false)===true){rename($path,$path.'.fixture-hidden');}else{file_put_contents($path,'Synthetic corrupted file bytes');}break;
 case 'fault-on':$db->exec('ALTER TABLE '.Database::table('content').' ADD CONSTRAINT '.Database::table('backup_fixture')." CHECK(title <> 'Backup story')");break;
 case 'fault-off':$db->exec('ALTER TABLE '.Database::table('content').' DROP CONSTRAINT '.Database::table('backup_fixture'));break;
 case 'gate':if(($input['mode']??'shared')==='exclusive'){MaintenanceGate::exclusive();}echo "READY\n";flush();sleep(3);exit;
 case 'remove':Backups::remove($id,1);break;
 default:throw new RuntimeException('Unknown backup test action.');
 }
}catch(Throwable $e){$out=['ok'=>false,'error'=>$e->getMessage(),'class'=>get_class($e)];}
echo json_encode($out,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
