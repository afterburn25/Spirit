<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){exit(1);}
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{Database,InstallState};
$name=(string)getenv('SPIRIT_DB_NAME');if(!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$name)){throw new RuntimeException('Disposable database required.');}
$prefix='sgci_'.bin2hex(random_bytes(4)).'_';$c=['host'=>getenv('SPIRIT_DB_HOST')?:'127.0.0.1','port'=>getenv('SPIRIT_DB_PORT')?:3306,'name'=>$name,'user'=>getenv('SPIRIT_DB_USER'),'password'=>getenv('SPIRIT_DB_PASSWORD'),'prefix'=>$prefix];
$db=Database::connect($c);$table='`'.$prefix.'installation`';$fingerprint=hash('sha256','disposable-fixture-owner');$checks=0;
function stateCheck(bool $pass,string $label):void{global $checks;if(!$pass){throw new RuntimeException($label);}$checks++;echo 'PASS '.$label."\n";}
try{
 $lock=InstallState::lock($db,$prefix);$other=Database::connect($c);$blocked=false;try{InstallState::lock($other,$prefix);}catch(RuntimeException $e){$blocked=true;}stateCheck($blocked,'A second application process cannot claim the same database prefix');
 InstallState::ensure($db,$prefix,$fingerprint,'test');stateCheck((int)$db->query('SELECT COUNT(*) FROM '.$table)->fetchColumn()===1,'Initial sentinel ownership row created');
 $db->exec('DELETE FROM '.$table);InstallState::ensure($db,$prefix,$fingerprint,'test');stateCheck((int)$db->query('SELECT COUNT(*) FROM '.$table)->fetchColumn()===1,'Interruption after CREATE resumes from the durable table ownership comment');
 $blocked=false;try{InstallState::ensure($db,$prefix,hash('sha256','different-owner'),'test');}catch(RuntimeException $e){$blocked=true;}stateCheck($blocked,'Mismatched ownership cannot overwrite a populated sentinel');
 $db->exec('DELETE FROM '.$table);$db->exec('ALTER TABLE '.$table." COMMENT='unrelated-table'");$blocked=false;try{InstallState::ensure($db,$prefix,$fingerprint,'test');}catch(RuntimeException $e){$blocked=true;}stateCheck($blocked,'An empty unrelated sentinel is never adopted');stateCheck((int)$db->query('SELECT COUNT(*) FROM '.$table)->fetchColumn()===0,'Unknown empty sentinel remains unmodified');
 InstallState::unlock($db,$lock);$newLock=InstallState::lock($other,$prefix);stateCheck($newLock===$lock,'Prefix lock becomes available after release');InstallState::unlock($other,$newLock);
 echo $checks." foundation database checks passed.\n";
}finally{$db->exec('DROP TABLE IF EXISTS '.$table);}
