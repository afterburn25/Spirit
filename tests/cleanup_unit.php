<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\TransientCleanup;
$n=0;
function check(bool $ok,string $label):void{global $n;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$n++;}
function rejected(callable $fn,string $label):void{try{$fn();$no=false;}catch(Throwable $e){$no=true;}check($no,$label);}
$base=sys_get_temp_dir().'/spirit-cleanup-unit-'.bin2hex(random_bytes(8));mkdir($base,0700);mkdir($base.'/backups',0700);mkdir($base.'/media',0700);
$old=time()-9*86400;$files=[];$dirs=[];
function putTest(string $path,string $value,int $when):void{global $files;$files[]=$path;file_put_contents($path,$value);touch($path,$when);}
try{
 $stale=$base.'/media/.restore-'.str_repeat('a',32);putTest($stale,'abandoned',$old);
 $recent=$base.'/media/.restore-'.str_repeat('b',32);putTest($recent,'current',time());
 $media=$base.'/media/'.str_repeat('c',32).'.jpg';putTest($media,'real upload',$old);
 $backup=$base.'/backups/backup-'.str_repeat('d',32).'.sgbackup';putTest($backup,'valuable encrypted backup',$old);
 $partial=$base.'/backups/backup-'.str_repeat('e',32).'.partial';putTest($partial,'partial preserved',$old);
 $metadata=$base.'/backups/backup-'.str_repeat('d',32).'.json';putTest($metadata,'wrapped key preserved',$old);
 $tmp=$metadata.'.'.str_repeat('f',12).'.tmp';putTest($tmp,'abandoned metadata temporary',$old);
 $stage=$base.'/backups/stage-'.str_repeat('1',32);mkdir($stage,0700);$dirs[]=$stage;putTest($stage.'/rows.jsonl','stale rows',$old);putTest($stage.'/'.str_repeat('2',32).'.png','stale staged photo',$old);touch($stage,$old);
 $unknown=$base.'/backups/stage-'.str_repeat('3',32);mkdir($unknown,0700);$dirs[]=$unknown;putTest($unknown.'/custom.txt','unrecognized owner file',$old);touch($unknown,$old);
 $fresh=$base.'/backups/stage-'.str_repeat('4',32);mkdir($fresh,0700);$dirs[]=$fresh;putTest($fresh.'/rows.jsonl','still written',time());touch($fresh,$old);
 $outside=$base.'/outside';putTest($outside,'symlink target must survive',$old);$link=$base.'/media/.restore-'.str_repeat('5',32);symlink($outside,$link);$files[]=$link;
 $plan=TransientCleanup::temporaryPlan($base,time()-7*86400);
 check(count($plan)===5,'Only old recognized files plus a safe staging directory are candidates');
 check(is_file($stale)&&is_file($tmp)&&is_dir($stage),'Preview does not remove temporary files');
 $r=TransientCleanup::pruneTemporary($base,time()-7*86400);
 check($r['files']===4&&$r['directories']===1&&$r['skipped']===0,'Cleanup removes the selected four temporary files and empty stage');
 check(!file_exists($stale)&&!is_dir($stage),'Pruned temporary paths are physically absent');
 check(file_get_contents($outside)==='symlink target must survive'&&is_link($link),'Symlink and its external target remain unchanged');
 foreach([$recent,$media,$backup,$partial,$metadata,$unknown.'/custom.txt',$fresh.'/rows.jsonl'] as $path){check(is_file($path),'Current or protected file survives: '.basename($path));}
 check(TransientCleanup::pruneTemporary($base,time()-7*86400)['files']===0,'Repeated cleanup is idempotent');
 rejected(fn()=>TransientCleanup::run(null,0),'Zero-size cleanup is rejected before database work');
 rejected(fn()=>TransientCleanup::run(null,501),'Oversized cleanup is rejected before database work');
 check(!array_intersect(array_keys(TransientCleanup::RULES),['orders','users','customers','subscribers','audit','bookings','content']),'Business records are never direct retention-delete targets');
 $alias=$base.'-alias';symlink($base,$alias);try{rejected(fn()=>TransientCleanup::temporaryPlan($alias,time()),'Symlink runtime cannot become a cleanup root');}finally{unlink($alias);}
}finally{foreach(array_reverse($files) as $p){if(is_file($p)||is_link($p)){unlink($p);}}foreach(array_reverse($dirs) as $d){if(is_dir($d)){rmdir($d);}}rmdir($base.'/media');rmdir($base.'/backups');rmdir($base);}
echo $n." transient cleanup unit checks passed.\n";
