<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database};
$c=Paths::config();
if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Non-test database refused.');}
$db=Database::connect();$q=$db->prepare('INSERT INTO '.Database::table('users').' (name,email,password_hash,role,active,created_at) VALUES (?,?,?,?,1,UTC_TIMESTAMP())');
foreach(['owner','content_editor','store_manager'] as $role){
 $q->execute(['Synthetic menu '.$role,'menu-'.$role.'@example.test',password_hash('Menu-fixture-only-Password-492',PASSWORD_DEFAULT),$role]);
}
echo json_encode(['created'=>3],JSON_THROW_ON_ERROR);
