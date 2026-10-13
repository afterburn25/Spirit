<?php
declare(strict_types=1);
// Disposable integration fixtures only; this file is excluded from deployments.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') {http_response_code(404);exit(1);}
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\Database;
$name=(string)getenv('SPIRIT_DB_NAME');$prefix=(string)getenv('SPIRIT_DB_PREFIX');
if(!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$name)||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$prefix)){throw new RuntimeException('Refusing non-test database or prefix.');}
try{
 $db=Database::connect(['host'=>getenv('SPIRIT_DB_HOST')?:'127.0.0.1','port'=>getenv('SPIRIT_DB_PORT')?:3306,'name'=>$name,'user'=>getenv('SPIRIT_DB_USER'),'password'=>getenv('SPIRIT_DB_PASSWORD'),'prefix'=>$prefix]);$users='`'.$prefix.'users`';
 switch($argv[1]??''){
 case 'ping':echo json_encode(['ready'=>true,'database'=>$db->query('SELECT VERSION()')->fetchColumn()]);break;
 case 'count':echo json_encode(['users'=>(int)$db->query('SELECT COUNT(*) FROM '.$users)->fetchColumn(),'state'=>$db->query('SELECT status FROM `'.$prefix.'installation` WHERE id=1')->fetchColumn()]);break;
 case 'collision':$db->exec('CREATE TABLE '.$users.' (id INT PRIMARY KEY) ENGINE=InnoDB');$db->exec('INSERT INTO '.$users.' VALUES (42)');echo '{}';break;
 case 'sentinel':echo json_encode(['id'=>(int)$db->query('SELECT id FROM '.$users)->fetchColumn()]);break;
 case 'editor':$db->exec("UPDATE ".$users." SET role='editor'");echo '{}';break;
 case 'owner':$db->exec("UPDATE ".$users." SET role='owner'");echo '{}';break;
 case 'limits':$db->exec('DELETE FROM `'.$prefix.'login_limits`');echo '{}';break;
 case 'content-count':echo json_encode(['content'=>(int)$db->query('SELECT COUNT(*) FROM `'.$prefix.'content`')->fetchColumn(),'media'=>(int)$db->query('SELECT COUNT(*) FROM `'.$prefix.'media`')->fetchColumn()]);break;
 case 'legacy-schema':
   foreach(['content_media','content_revisions','content','media','migrations'] as $table){$db->exec('DROP TABLE IF EXISTS `'.$prefix.$table.'`');}echo '{}';break;
 case 'cleanup':
   $q=$db->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() AND LEFT(table_name,?)=?');$q->execute([strlen($prefix),$prefix]);
   foreach($q->fetchAll(PDO::FETCH_COLUMN) as $table){if(!preg_match('/^sgci_[a-f0-9]{8}_[a-z_]+$/D',$table)){throw new RuntimeException('Unexpected fixture table name.');}$db->exec('DROP TABLE IF EXISTS `'.$table.'`');}echo '{}';break;
 default:throw new RuntimeException('Unknown fixture command.');
 }
}catch(Throwable $e){fwrite(STDERR,'Fixture failed: '.get_class($e)."\n");exit(1);}
