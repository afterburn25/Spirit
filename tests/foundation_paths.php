<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{Paths,HostingException};
$base=sys_get_temp_dir().'/spirit-path-review-'.bin2hex(random_bytes(8));mkdir($base,0700);mkdir($base.'/public_html',0700);mkdir($base.'/public_html/addon',0700);$_SERVER['DOCUMENT_ROOT']=$base.'/public_html/addon';putenv('SPIRIT_ACCOUNT_HOME='.$base);$checks=0;
function pathCheck(bool $pass,string $label):void{global $checks;if(!$pass){throw new RuntimeException($label);}$checks++;echo 'PASS '.$label."\n";}
try{
 pathCheck(Paths::config()===null,'A genuinely new account layout has no installed configuration');
 $legacy=$base.'/public_html/.spirit-'.substr(hash('sha256',Paths::root()),0,20);mkdir($legacy,0700);file_put_contents($legacy.'/config.php',"<?php throw new RuntimeException('Legacy config must not execute');");
 $blocked=false;try{Paths::config();}catch(HostingException $e){$blocked=str_contains($e->getMessage(),'earlier development');}pathCheck($blocked,'Legacy dev.1 config detected without executing or adopting its source');pathCheck(is_file($legacy.'/config.php'),'Legacy source is left untouched');
 unlink($legacy.'/config.php');file_put_contents($legacy.'/installed.lock','protected');$blocked=false;try{Paths::installed();}catch(HostingException $e){$blocked=true;}pathCheck($blocked,'Legacy lock cannot silently become a fresh installation');
 unlink($legacy.'/installed.lock');rmdir($legacy);putenv('SPIRIT_ACCOUNT_HOME='.$base.'/public_html');$blocked=false;try{Paths::runtime();}catch(HostingException $e){$blocked=true;}pathCheck($blocked,'Public account base produces an actionable hosting exception');
 echo $checks." foundation path checks passed.\n";
}finally{putenv('SPIRIT_ACCOUNT_HOME');foreach(glob($base.'/public_html/.spirit-*')?:[] as $folder){foreach(glob($folder.'/*')?:[] as $file){unlink($file);}rmdir($folder);}rmdir($base.'/public_html/addon');rmdir($base.'/public_html');rmdir($base);}
