<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;
use Throwable;

/** Application-data snapshots and guarded same-installation restoration.
 * Never restores provider transactions, consent, identity state or PHP from uploads.
 */
final class Backups
{
    private const CONTENT=['content','content_media','content_revisions','settings'];
    private const SITE=['content','content_media','content_revisions','settings','products','shop_categories'];
    private const FILE_PATTERN='/^[a-f0-9]{32}\.(?:jpg|png|webp|mp3|ogg|wav|pdf)$/D';
    private static function time(float $deadline): void
    {
        if(microtime(true)>$deadline){throw new RuntimeException('The snapshot exceeded the safe browser time budget. No restore was committed. Use a hosting-panel backup for larger installations.');}
    }
    public static function folder(): string
    {
        $folder=Paths::runtime().'/backups';
        if(is_link($folder)||!is_dir($folder)||realpath($folder)!==$folder||(fileperms($folder)&0077)!==0){throw new RuntimeException('Protected backup storage is unavailable.');}return $folder;
    }
    public static function id(string $id): string
    {
        if(!preg_match('/^[a-f0-9]{32}$/D',$id)){throw new RuntimeException('Invalid backup selection.');}return $id;
    }
    private static function binding(): string
    {
        $c=Paths::config();return hash_hmac('sha256',$c['site_url'].'|'.$c['db']['name'].'|'.$c['db']['prefix'],$c['secret']);
    }
    private static function migrations(): array
    {
        $out=[];foreach(Migrations::files() as $file){$out[basename($file)]=hash_file('sha256',$file);}return $out;
    }
    private static function writeInfo(string $id,array $info): void
    {
        $target=self::folder().'/backup-'.self::id($id).'.json';$temp=$target.'.'.bin2hex(random_bytes(6)).'.tmp';
        if(is_link($target)){throw new RuntimeException('Unsafe backup metadata.');}
        $h=@fopen($temp,'x+b');if(!$h){throw new RuntimeException('Backup metadata is not writable.');}
        try{
            if(!chmod($temp,0600)){throw new RuntimeException('Cannot protect backup metadata.');}
            $body=json_encode($info,JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT);
            if(fwrite($h,$body)!==strlen($body)||!fflush($h)||(function_exists('fsync')&&!fsync($h))){throw new RuntimeException('Incomplete backup metadata.');}
            fclose($h);$h=null;if(!rename($temp,$target)){throw new RuntimeException('Cannot publish backup metadata.');}
        }finally{if(is_resource($h)){fclose($h);}if(is_file($temp)){unlink($temp);}}
    }
    public static function info(string $id): array
    {
        $path=self::folder().'/backup-'.self::id($id).'.json';
        if(is_link($path)||!is_file($path)||filesize($path)>20000){throw new RuntimeException('Backup metadata unavailable.');}
        $info=json_decode((string)file_get_contents($path),true,32,JSON_THROW_ON_ERROR);
        if(!is_array($info)||($info['id']??'')!==$id){throw new RuntimeException('Invalid backup metadata.');}return $info;
    }
    public static function listing(): array
    {
        $rows=[];foreach(glob(self::folder().'/backup-*.json')?:[] as $path){
            $id=substr(basename($path),7,32);if(!preg_match('/^[a-f0-9]{32}$/D',$id)){continue;}
            try{$info=self::info($id);if(($info['status']??'')==='building'){continue;}self::path($id);unset($info['sealed_key']);$rows[]=$info;}catch(Throwable $e){}
        }
        usort($rows,static fn($a,$b)=>strcmp($b['created_at'],$a['created_at']));return $rows;
    }
    public static function path(string $id): string
    {
        $path=self::folder().'/backup-'.self::id($id).'.sgbackup';
        if(is_link($path)||!is_file($path)){throw new RuntimeException('Backup archive unavailable.');}return $path;
    }
    public static function key(string $id): string
    {
        $key=PrivateValue::open(self::info($id)['sealed_key']);if(strlen($key)!==32){throw new RuntimeException('Backup recovery key unavailable.');}return $key;
    }
    public static function create(int $actor,string $label='Owner snapshot',?float $deadline=null,bool $diagnostic=false): array
    {
        $deadline??=microtime(true)+15;MaintenanceGate::exclusive();$db=Database::connect();
        if(!Migrations::ready()){throw new RuntimeException('Apply the pending database schema before creating a snapshot.');}
        if(!Filesystem::probe(self::folder())){throw new RuntimeException('Backup storage failed real write/rename/delete checks.');}
        $id=bin2hex(random_bytes(16));$key=random_bytes(32);$partial=self::folder().'/backup-'.$id.'.partial';$archive=self::folder().'/backup-'.$id.'.sgbackup';$writer=null;
        try{
            $building=['id'=>$id,'created_at'=>gmdate('c'),'version'=>Paths::version(),'label'=>substr($label,0,120),'sealed_key'=>PrivateValue::seal($key),'status'=>'building'];
            // Persist a protected recovery key before writing archive bytes. A
            // hard interruption cannot leave an encrypted file with a lost key.
            self::writeInfo($id,$building);$shape=BackupSchema::shape($db);$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);$db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$db->beginTransaction();
            $writer=new BackupStream($partial,$key);$counts=[];$fingerprints=[];
            $writer->record(['kind'=>'meta','version'=>Paths::version(),'binding'=>self::binding(),'migrations'=>self::migrations(),'shape'=>$shape,'created_at'=>gmdate('c'),'config'=>Paths::config()]);
            foreach($shape as $table=>$columns){
                self::time($deadline);$counts[$table]=0;
                $primary=array_column(array_filter($columns,static fn($c)=>$c['key']==='PRI'),'name');
                if(!$primary){throw new RuntimeException('Snapshot requires primary keys on application tables.');}
                $order=implode(',',array_map(static fn($name)=>'`'.$name.'`',$primary));
                $q=$db->query('SELECT * FROM '.Database::table($table).' ORDER BY '.$order);$hash=hash_init('sha256');
                while($row=$q->fetch()){
                    self::time($deadline);$encoded=BackupSchema::encode($row);$json=json_encode($encoded,JSON_THROW_ON_ERROR);hash_update($hash,strlen($json).':'.$json);
                    $writer->record(['kind'=>'row','table'=>$table,'value'=>$encoded]);$counts[$table]++;
                }
                $fingerprints[$table]=hash_final($hash);
            }
            $warnings=0;$mediaCount=0;$q=$db->query('SELECT storage_name,byte_size,checksum FROM '.Database::table('media').' ORDER BY id');
            while($media=$q->fetch()){
                self::time($deadline);$name=$media['storage_name'];if(!preg_match(self::FILE_PATTERN,$name)){throw new RuntimeException('Unsafe media record in the database.');}
                $source=Paths::runtime().'/media/'.$name;if(is_link($source)){throw new RuntimeException('A referenced media file is an unsafe symlink.');}if(!is_file($source)){if(!$diagnostic){throw new RuntimeException('A referenced media file is missing. This cannot be a complete backup.');}$writer->record(['kind'=>'missing','name'=>$name]);$warnings++;continue;}
                $size=filesize($source);$hash=hash_file('sha256',$source);$warnings+=(int)($hash!==$media['checksum']||$size!==(int)$media['byte_size']);
                $writer->record(['kind'=>'file','name'=>$name,'size'=>$size,'sha256'=>$hash]);$h=fopen($source,'rb');$offset=0;$streamHash=hash_init('sha256');
                try{while(!feof($h)){self::time($deadline);$bytes=fread($h,262144);if($bytes===false){throw new RuntimeException('Media snapshot read failed.');}if($bytes===''){break;}$writer->record(['kind'=>'chunk','name'=>$name,'offset'=>$offset,'data'=>base64_encode($bytes)]);$offset+=strlen($bytes);hash_update($streamHash,$bytes);}}finally{fclose($h);}
                if($offset!==$size||hash_final($streamHash)!==$hash){throw new RuntimeException('Media file changed during backup.');}$writer->record(['kind'=>'file_end','name'=>$name]);$mediaCount++;
            }
            if($warnings&&!$diagnostic){throw new RuntimeException('Media integrity does not match the database. Repair it before making a verified backup.');}
            $writer->record(['kind'=>'checks','counts'=>$counts,'fingerprints'=>$fingerprints,'media_count'=>$mediaCount]);$writer->finish();$writer=null;$db->commit();
            if(!rename($partial,$archive)){throw new RuntimeException('Cannot publish the completed backup.');}
            $info=['id'=>$id,'created_at'=>gmdate('c'),'version'=>Paths::version(),'label'=>substr($label,0,120),'bytes'=>filesize($archive),'sha256'=>hash_file('sha256',$archive),'sealed_key'=>PrivateValue::seal($key),'status'=>$warnings?'integrity_warning':'created','media_warnings'=>$warnings];
            self::writeInfo($id,$info);Database::audit($db,$actor,'backup.created');unset($info['sealed_key']);return $info;
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}$writer=null;if(is_file($partial)){unlink($partial);}throw $e;}
    }
    /** Decode everything before any live database change. Staged files are private. */
    private static function stage(string $path,string $key,float $deadline): array
    {
        $dir=self::folder().'/stage-'.bin2hex(random_bytes(16));if(!mkdir($dir,0700)){throw new RuntimeException('Cannot create protected restore staging.');}
        $rows=@fopen($dir.'/rows.jsonl','x+b');if(!$rows||!@chmod($dir.'/rows.jsonl',0600)){if(is_resource($rows)){fclose($rows);}self::cleanStage($dir);throw new RuntimeException('Cannot protect staged database records.');}$meta=null;$checks=null;$counts=[];$hashes=[];$mediaRows=[];$files=[];$current=null;$handle=null;$fileHash=null;$offset=0;
        try{
            foreach(BackupStream::read($path,$key) as $record){
                self::time($deadline);$kind=$record['kind'];
                if($meta===null){
                    if($kind!=='meta'||($record['binding']??'')!==self::binding()||($record['migrations']??[])!==self::migrations()||($record['shape']??[])!==BackupSchema::shape(Database::connect())){throw new RuntimeException('Backup must belong to this installation and its current database schema.');}
                    if(($record['config']['secret']??'')!==Paths::config()['secret']){throw new RuntimeException('Backup uses another private installation key.');}
                    $meta=$record;foreach($meta['shape'] as $name=>$columns){$counts[$name]=0;$hashes[$name]=hash_init('sha256');}continue;
                }
                if($checks!==null){throw new RuntimeException('Unexpected records after backup checks.');}
                if($kind==='row'){
                    if($current!==null||$files){throw new RuntimeException('Database rows are out of order in the backup.');}
                    $table=$record['table']??'';if(!isset($meta['shape'][$table])||!is_array($record['value']??null)){throw new RuntimeException('Unknown database record.');}
                    $decoded=BackupSchema::decode($record['value'],$meta['shape'][$table]);$json=json_encode($record['value'],JSON_THROW_ON_ERROR);hash_update($hashes[$table],strlen($json).':'.$json);$counts[$table]++;
                    $line=json_encode($record,JSON_THROW_ON_ERROR)."\n";if(fwrite($rows,$line)!==strlen($line)){throw new RuntimeException('Cannot stage database records.');}
                    if($table==='media'){$mediaRows[$decoded['storage_name']]=$decoded;}continue;
                }
                if($kind==='file'){
                    $name=$record['name']??'';
                    if($current!==null||!is_string($name)||!preg_match(self::FILE_PATTERN,$name)||isset($files[$name])||!isset($mediaRows[$name])||!is_int($record['size']??null)||$record['size']<0||$record['size']>52428800||!preg_match('/^[a-f0-9]{64}$/D',$record['sha256']??'')){throw new RuntimeException('Invalid or duplicate backup media entry.');}
                    $current=$record;$offset=0;$fileHash=hash_init('sha256');$handle=fopen($dir.'/'.$name,'x+b');if(!$handle){throw new RuntimeException('Cannot stage media.');}continue;
                }
                if($kind==='chunk'){
                    if($current===null||($record['name']??'')!==$current['name']||($record['offset']??null)!==$offset||!is_string($record['data']??null)||($bytes=base64_decode($record['data'],true))===false||strlen($bytes)>262144||$offset+strlen($bytes)>$current['size']){throw new RuntimeException('Invalid backup media chunk.');}
                    if(fwrite($handle,$bytes)!==strlen($bytes)){throw new RuntimeException('Incomplete staged media write.');}hash_update($fileHash,$bytes);$offset+=strlen($bytes);continue;
                }
                if($kind==='file_end'){
                    if($current===null||($record['name']??'')!==$current['name']||$offset!==$current['size']||hash_final($fileHash)!==$current['sha256']){throw new RuntimeException('Media checksum or length does not match the backup.');}
                    $row=$mediaRows[$current['name']];if($row['checksum']!==$current['sha256']||(int)$row['byte_size']!==$offset){throw new RuntimeException('Backup media bytes do not match their database records. This snapshot is diagnostic, not safe to restore.');}
                    fclose($handle);$handle=null;$files[$current['name']]=$current;$current=null;continue;
                }
                if($kind==='checks'){
                    if($current!==null||($record['counts']??[])!==$counts||($record['media_count']??null)!==count($files)||count($files)!==count($mediaRows)){throw new RuntimeException('Backup record counts are incomplete.');}
                    foreach($hashes as $table=>$hash){if(hash_final($hash)!==($record['fingerprints'][$table]??'')){throw new RuntimeException('Database snapshot fingerprint mismatch.');}}
                    $checks=$record;continue;
                }
                throw new RuntimeException('Unexpected backup record kind.');
            }
            if(!$meta||!$checks||$current!==null){throw new RuntimeException('The backup has no complete data manifest.');}fclose($rows);$rows=null;
            return ['dir'=>$dir,'meta'=>$meta,'checks'=>$checks,'media'=>$mediaRows,'files'=>$files];
        }catch(Throwable $e){if(is_resource($rows)){fclose($rows);}if(is_resource($handle)){fclose($handle);}self::cleanStage($dir);throw $e;}
    }
    private static function cleanStage(string $directory): void
    {
        if(dirname($directory)!==self::folder()||!preg_match('/^stage-[a-f0-9]{32}$/D',basename($directory))||is_link($directory)){return;}
        foreach(scandir($directory)?:[] as $entry){if($entry==='rows.jsonl'||preg_match(self::FILE_PATTERN,$entry)){if(is_file($directory.'/'.$entry)&&!is_link($directory.'/'.$entry)){unlink($directory.'/'.$entry);}}}@rmdir($directory);
    }
    public static function verify(string $id): array
    {
        MaintenanceGate::exclusive();$stage=self::stage(self::path($id),self::key($id),microtime(true)+15);
        try{$info=self::info($id);$info['status']='verified';$info['verified_at']=gmdate('c');self::writeInfo($id,$info);return ['rows'=>array_sum($stage['checks']['counts']),'files'=>count($stage['files'])];}finally{self::cleanStage($stage['dir']);}
    }
    public static function restore(string $id,string $mode,int $actor): array
    {
        if(!in_array($mode,['content','site'],true)){throw new RuntimeException('Select a supported restore scope.');}
        MaintenanceGate::exclusive();$deadline=microtime(true)+20;$stage=self::stage(self::path($id),self::key($id),$deadline);$db=Database::connect();
        try{
            if($mode==='site'){
                foreach(BackupSchema::protectedNames() as $name){self::time($deadline);if(!hash_equals($stage['checks']['fingerprints'][$name],BackupSchema::digest($db,$name))){throw new RuntimeException('Site-data restore would rewind operational records ('.$name.'). Use content-only restore; payment, identity, consent and inventory history must remain current.');}}
            }
            // Never replace a different immutable media identity under an old ID.
            foreach($stage['media'] as $name=>$row){$q=$db->prepare('SELECT id,storage_name,checksum FROM '.Database::table('media').' WHERE id=? OR storage_name=?');$q->execute([$row['id'],$name]);foreach($q as $existing){if((string)$existing['id']!==(string)$row['id']||$existing['storage_name']!==$name||$existing['checksum']!==$row['checksum']){throw new RuntimeException('A media identifier was reused. Refusing to overwrite a different file.');}}}
            $pre=self::create($actor,'Automatic pre-restore snapshot',$deadline,true);$targets=$mode==='content'?self::CONTENT:self::SITE;
            $oldRevisions=[];foreach(array_intersect($targets,['content','products']) as $table){$oldRevisions[$table]=$db->query('SELECT id,revision FROM '.Database::table($table))->fetchAll(PDO::FETCH_KEY_PAIR);}
            // Files are immutable by identity. New bytes are safe before the DB
            // transaction; they remain private/unreferenced if it rolls back.
            foreach($stage['files'] as $name=>$file){self::time($deadline);$target=Paths::runtime().'/media/'.$name;if(is_link($target)){throw new RuntimeException('Unsafe media restore destination.');}if(is_file($target)&&hash_file('sha256',$target)===$file['sha256']){continue;}
                $temp=Paths::runtime().'/media/.restore-'.bin2hex(random_bytes(16));if(!copy($stage['dir'].'/'.$name,$temp)||!chmod($temp,0600)||!rename($temp,$target)){if(is_file($temp)){unlink($temp);}throw new RuntimeException('Media could not be restored atomically.');}}
            $db->beginTransaction();foreach($targets as $table){$db->exec('DELETE FROM '.Database::table($table));}
            $statements=[];$h=fopen($stage['dir'].'/rows.jsonl','rb');
            try{while(($line=fgets($h,BackupStream::MAX_RECORD+200))!==false){self::time($deadline);$record=json_decode($line,true,64,JSON_THROW_ON_ERROR);$table=$record['table'];if(!in_array($table,[...$targets,'media'],true)){continue;}$row=BackupSchema::decode($record['value'],$stage['meta']['shape'][$table]);
                if(isset($row['revision'],$row['id'],$oldRevisions[$table])){$row['revision']=(string)(max((int)$row['revision'],(int)($oldRevisions[$table][$row['id']]??0))+1);}
                if(!isset($statements[$table])){$names=array_keys($row);$columns=implode(',',array_map(static fn($name)=>'`'.$name.'`',$names));$suffix=$table==='media'?' ON DUPLICATE KEY UPDATE original_name=VALUES(original_name),alt_text=VALUES(alt_text)':'';$statements[$table]=$db->prepare('INSERT INTO '.Database::table($table).' ('.$columns.') VALUES ('.implode(',',array_fill(0,count($names),'?')).')'.$suffix);}
                $statements[$table]->execute(array_values($row));
            }}finally{fclose($h);}
            Database::audit($db,$actor,'backup.restored.'.$mode);$db->commit();return ['pre_restore'=>$pre['id'],'scope'=>$mode];
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}finally{self::cleanStage($stage['dir']);}
    }
    public static function import(array $file,string $hex,int $actor): array
    {
        if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_string($file['tmp_name']??null)||!is_uploaded_file($file['tmp_name'])||!preg_match('/^[a-f0-9]{64}$/Di',$hex)){throw new RuntimeException('Select a complete backup file and its separate 64-character recovery key.');}
        MaintenanceGate::exclusive();$key=hex2bin($hex);$stage=self::stage($file['tmp_name'],$key,microtime(true)+15);
        try{$id=bin2hex(random_bytes(16));$path=self::folder().'/backup-'.$id.'.sgbackup';if(!move_uploaded_file($file['tmp_name'],$path)||!chmod($path,0600)){throw new RuntimeException('Cannot store the verified imported backup.');}
            $info=['id'=>$id,'created_at'=>gmdate('c'),'version'=>$stage['meta']['version'],'label'=>'Imported verified snapshot','bytes'=>filesize($path),'sha256'=>hash_file('sha256',$path),'sealed_key'=>PrivateValue::seal($key),'status'=>'verified','verified_at'=>gmdate('c'),'media_warnings'=>0];self::writeInfo($id,$info);Database::audit(Database::connect(),$actor,'backup.imported');unset($info['sealed_key']);return $info;
        }finally{self::cleanStage($stage['dir']);}
    }
    public static function remove(string $id,int $actor): void
    {
        MaintenanceGate::exclusive();$info=self::info($id);$path=self::path($id);$meta=self::folder().'/backup-'.self::id($id).'.json';
        if(!unlink($path)){throw new RuntimeException('The host refused backup-file removal.');}if(!unlink($meta)){throw new RuntimeException('Archive removed but the host refused metadata removal.');}Database::audit(Database::connect(),$actor,'backup.deleted');
    }
}
