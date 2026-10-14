<?php
declare(strict_types=1);
// Only local isolated test installations can invoke this source fixture.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){exit(1);}
define('SPIRIT_UPGRADE_CONTROL',true);
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
// The recovery engine deliberately does not load possibly broken application code.
require $root.'/admin/upgrades/RecoveryEngine.php';
$runtime=SpiritRecovery::locate();
if(!$runtime){echo '{}';exit;}
$config=require $runtime.'/config.php';$c=$config['db'];
if(!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['prefix'])||$c['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Refusing non-test database.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,32,JSON_THROW_ON_ERROR);SpiritRecovery::enter($runtime);$out=[];
switch($argv[1]??''){
case 'state':$latest=SpiritRecovery::latest();$out=$latest?SpiritRecovery::job($latest['id']):[];break;
case 'damage-stage':$job=SpiritRecovery::job(SpiritRecovery::latest()['id']);$path=array_key_first($job['changes']);file_put_contents(SpiritRecovery::folder().'/job-'.$job['id'].'/new/'.hash('sha256',$path),'Corrupt staged data');break;
case 'interrupt':
    SpiritRecovery::exclusive();$j=SpiritRecovery::job(SpiritRecovery::latest()['id']);$base=SpiritRecovery::folder().'/job-'.$j['id'];
    foreach($j['changes'] as $path=>$change){if($change['before']!==null){SpiritRecovery::atomic($base.'/old/'.hash('sha256',$path),(string)file_get_contents(SpiritRecovery::target($path)));}}
    $j['state']='applying';SpiritRecovery::save($j);SpiritRecovery::writeJson(SpiritRecovery::folder().'/active.json',['id'=>$j['id'],'state'=>'applying']);
    foreach($j['changes'] as $path=>$change){if($path!=='includes/bootstrap.php'){SpiritRecovery::atomic(SpiritRecovery::target($path,true),(string)file_get_contents($base.'/new/'.hash('sha256',$path)),0644);break;}}
    // A hard process exit after a partial copy leaves the guard and originals.
    exit(0);
case 'schema-change':case 'schema-revert':
    $db=new PDO('mysql:host='.$c['host'].';port='.$c['port'].';dbname='.$c['name'],$c['user'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $db->exec('ALTER TABLE `'.$c['prefix'].'settings` '.(($argv[1]==='schema-change')?'ADD fixture_changed INT NULL':'DROP COLUMN fixture_changed'));break;
case 'schema':$out=['hash'=>SpiritRecovery::schema()];break;
default:throw new RuntimeException('Unknown isolated update action.');
}
echo json_encode($out,JSON_THROW_ON_ERROR);
