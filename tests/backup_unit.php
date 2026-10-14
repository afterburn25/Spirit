<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{BackupStream,BackupSchema,Backups};
$checks=0;$dir=sys_get_temp_dir().'/spirit-backup-unit-'.bin2hex(random_bytes(8));mkdir($dir,0700);
function check(bool $ok,string $label):void{global $checks;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$checks++;}
function rejects(callable $fn,string $label):void{try{$fn();}catch(Throwable $e){check(true,$label);return;}check(false,$label);}
try{
 $key=random_bytes(32);$path=$dir.'/good';$writer=new BackupStream($path,$key);
 $values=[['kind'=>'test','text'=>'Private secrets ü and SQL-like text DROP TABLE users;','binary'=>base64_encode(random_bytes(120))],['kind'=>'record','value'=>null]];
 foreach($values as $v){$writer->record($v);}$writer->finish();$body=file_get_contents($path);
 check(iterator_to_array(BackupStream::read($path,$key),false)===$values,'Authenticated stream round-trips ordered structured records');
 check(!str_contains($body,'Private secrets')&&!str_contains($body,'DROP TABLE'),'Archive does not expose plaintext record data');
 check((fileperms($path)&0077)===0,'Backup archive is private on disk');
 rejects(fn()=>iterator_to_array(BackupStream::read($path,random_bytes(32))),'Wrong recovery key cannot decrypt the archive');
 rejects(fn()=>new BackupStream($path,$key),'Existing backup output cannot be overwritten');
 rejects(fn()=>new BackupStream($dir.'/bad-key','short'),'Invalid encryption key size is refused');
 foreach([0,12,46,strlen($body)-1,strlen($body)-33] as $size){$p=$dir.'/short-'.$size;file_put_contents($p,substr($body,0,$size));rejects(fn()=>iterator_to_array(BackupStream::read($p,$key)),'Truncated archive cannot be accepted');}
 $mutated=$body;$mutated[65]=chr(ord($mutated[65])^1);file_put_contents($dir.'/tamper',$mutated);
 rejects(fn()=>iterator_to_array(BackupStream::read($dir.'/tamper',$key)),'Tampered ciphertext or authentication tag is refused');
 file_put_contents($dir.'/trailing',$body.'extra');rejects(fn()=>iterator_to_array(BackupStream::read($dir.'/trailing',$key)),'Trailing data after authenticated footer is refused');
 $headerSize=strlen("SPIRIT-BACKUP-1\n")+33;$firstLength=unpack('N',substr($body,$headerSize,4))[1]+32;
 $secondStart=$headerSize+$firstLength;$secondLength=unpack('N',substr($body,$secondStart,4))[1]+32;
 file_put_contents($dir.'/reorder',substr($body,0,$headerSize).substr($body,$secondStart,$secondLength).substr($body,$headerSize,$firstLength).substr($body,$secondStart+$secondLength));
 rejects(fn()=>iterator_to_array(BackupStream::read($dir.'/reorder',$key)),'Reordering authenticated records is detected');
 file_put_contents($dir.'/length',substr($body,0,$headerSize).pack('N',BackupStream::MAX_RECORD+1).substr($body,$headerSize+4));
 rejects(fn()=>iterator_to_array(BackupStream::read($dir.'/length',$key)),'Excessive record lengths are rejected before allocation');
 $w=new BackupStream($dir.'/unfinished',$key);$w->record(['kind'=>'test']);unset($w);
 rejects(fn()=>iterator_to_array(BackupStream::read($dir.'/unfinished',$key)),'Unfinished output has no valid end marker');
 $w=new BackupStream($dir.'/oversized',$key);rejects(fn()=>$w->record(['kind'=>'huge','value'=>str_repeat('a',BackupStream::MAX_RECORD)]),'Oversized plaintext record is refused');unset($w);
 symlink($path,$dir.'/link');rejects(fn()=>iterator_to_array(BackupStream::read($dir.'/link',$key)),'Backup source symlinks are refused');
 foreach(['../x','',str_repeat('A',32),str_repeat('a',31)] as $id){rejects(fn()=>Backups::id($id),'Unsafe backup selectors are refused');}
 $row=['id'=>'1','name'=>'binary '."\0".' ü','optional'=>null];$encoded=BackupSchema::encode($row);$fields=array_map(fn($name)=>['name'=>$name],array_keys($row));
 check(BackupSchema::decode($encoded,$fields)===$row,'SQL values round-trip as data, including null and binary text');
 rejects(fn()=>BackupSchema::decode(['injected'=>'x'],$fields),'A row cannot inject a column name');
 rejects(fn()=>BackupSchema::decode(['id'=>'%%','name'=>'','optional'=>null],$fields),'Malformed base64 database values are refused');
 echo $checks." backup stream/schema unit checks passed.\n";
}finally{foreach(scandir($dir) as $file){if(!in_array($file,['.','..'])){unlink($dir.'/'.$file);}}rmdir($dir);}
