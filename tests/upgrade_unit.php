<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\UpdatePackage;
$n=0;
function check(bool $condition,string $message):void{global $n;if(!$condition){throw new RuntimeException($message);}echo 'PASS '.$message."\n";$n++;}
foreach(['app/Example.php','assets/css/site.css','images/hero.webp','admin/upgrades/index.php','VERSION'] as $path){check(UpdatePackage::validName($path)&&SpiritRecovery::mutable($path),'Controlled source path accepted: '.$path);}
foreach(['../config.php','app/../config.php','/etc/passwd','app//bad.php','app\\bad.php','php://filter','app/%2e%2e/file','app/é.php',"app/file\0.php",str_repeat('x',221)] as $path){check(!UpdatePackage::validName($path),'Unsafe archive pathname refused');}
foreach(['config/local.php','storage/session.php','uploads/shell.php','install/index.php','admin/upgrades/location.php','tests/tool.php','tools/package.py','app/debug.log','app/private.pem'] as $path){check(!SpiritRecovery::mutable($path),'Private or non-code-updatable destination refused: '.$path);}
foreach(UpdatePackage::PINNED as $path){check(!SpiritRecovery::mutable($path),'Recovery kernel is pinned: '.$path);}
foreach(['../x',str_repeat('a',31),str_repeat('g',32)] as $id){try{SpiritRecovery::id($id);$refused=false;}catch(RuntimeException $e){$refused=true;}check($refused,'Invalid checkpoint identity refused');}
check(SpiritRecovery::id(str_repeat('a',32))===str_repeat('a',32),'Canonical checkpoint identity accepted');
check(str_starts_with(file_get_contents(dirname(__DIR__).'/includes/bootstrap.php'),"<?php\ndeclare(strict_types=1);\nrequire_once __DIR__ . '/maintenance-entry.php';\n"),'Recovery guard loads before replaceable application classes');
echo $n." upgrade policy unit checks passed.\n";
