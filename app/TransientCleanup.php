<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;
use Throwable;

/** Fixed, conservative technical retention. Never prunes business history. */
final class TransientCleanup
{
    public const RULES=[
        'request_limits'=>['Expired request-limit buckets','expires_at < UNIX_TIMESTAMP() - 86400'],
        'login_limits'=>['Expired sign-in-limit buckets','expires_at < UNIX_TIMESTAMP() - 86400'],
        'customer_tokens'=>['Customer activation/reset tokens expired for 30 days','expires_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 30 DAY)'],
        'staff_tokens'=>['Staff invitation/reset tokens expired for 30 days','expires_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 30 DAY)'],
    ];
    private const MAIL="encrypted_body <> '' AND ((status='sent' AND sent_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 90 DAY)) OR (status='cancelled' AND created_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 90 DAY)))";
    private static function cartCondition(): string
    {
        return 'c.expires_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 30 DAY) AND NOT EXISTS (SELECT 1 FROM '.Database::table('orders').' o WHERE o.cart_id=c.id)';
    }
    public static function preview(): array
    {
        $db=Database::connect();$rows=[];
        foreach(self::RULES as $table=>[$label,$where]){$rows[]=['kind'=>$table,'label'=>$label,'count'=>(int)$db->query('SELECT COUNT(*) FROM '.Database::table($table).' WHERE '.$where)->fetchColumn()];}
        $rows[]=['kind'=>'carts','label'=>'Bags expired for 30 days with no order reference','count'=>(int)$db->query('SELECT COUNT(*) FROM '.Database::table('carts').' c WHERE '.self::cartCondition())->fetchColumn()];
        $rows[]=['kind'=>'mail_bodies','label'=>'Accepted/cancelled message bodies older than 90 days; keep delivery records','count'=>(int)$db->query('SELECT COUNT(*) FROM '.Database::table('mail_queue').' WHERE '.self::MAIL)->fetchColumn()];
        $rows=array_merge($rows,Analytics::retentionPreview());
        $files=self::temporaryPlan(Paths::runtime(),time()-7*86400);
        return ['rows'=>$rows,'files'=>count($files),'bytes'=>array_sum(array_column($files,'bytes'))];
    }
    /** Exact private temporary names only. No recursively following directories. */
    public static function temporaryPlan(string $runtime,int $cutoff): array
    {
        if(is_link($runtime)||realpath($runtime)!==$runtime){throw new RuntimeException('Private cleanup location is not canonical.');}
        $items=[];$inspected=0;$deadline=microtime(true)+2;
        foreach(['backups','media','cache'] as $area){
            $parent=$runtime.'/'.$area;
            if($area==='cache'&&!file_exists($parent)&&!is_link($parent)){continue;}
            if(is_link($parent)||!is_dir($parent)||realpath($parent)!==$parent){throw new RuntimeException('Private cleanup directory is unavailable.');}
            foreach(new \FilesystemIterator($parent,\FilesystemIterator::SKIP_DOTS) as $entry){
                if(++$inspected>1500||microtime(true)>$deadline){return $items;}
                $name=$entry->getFilename();$path=$entry->getPathname();
                if($entry->isLink()||$entry->getMTime()>=$cutoff){continue;}
                if($area==='cache'&&ImageVariants::disposableName($name)&&$entry->isFile()){$items[]=self::fileRecord($path);}
                elseif($area==='media'&&preg_match('/^\.restore-[a-f0-9]{32}$/D',$name)&&$entry->isFile()){$items[]=self::fileRecord($path);}
                elseif($area==='backups'&&preg_match('/^backup-[a-f0-9]{32}\.json\.[a-f0-9]{12}\.tmp$/D',$name)&&$entry->isFile()){$items[]=self::fileRecord($path);}
                elseif($area==='backups'&&preg_match('/^stage-[a-f0-9]{32}$/D',$name)&&$entry->isDir()){
                    $children=[];$safe=true;
                    foreach(new \FilesystemIterator($path,\FilesystemIterator::SKIP_DOTS) as $child){
                        if(++$inspected>1500||microtime(true)>$deadline){$safe=false;break;}
                        $n=$child->getFilename();
                        if($child->isLink()||!$child->isFile()||$child->getMTime()>=$cutoff||($n!=='rows.jsonl'&&!preg_match('/^[a-f0-9]{32}\.(?:jpg|png|webp|mp3|ogg|wav|pdf)$/D',$n))){$safe=false;break;}
                        $children[]=self::fileRecord($child->getPathname());
                    }
                    if($safe){foreach($children as $file){$items[]=$file;}$items[]=['path'=>$path,'bytes'=>0,'directory'=>true,'identity'=>self::identity($path)];}
                }
            }
        }
        return $items;
    }
    private static function fileRecord(string $path): array {return ['path'=>$path,'bytes'=>filesize($path),'directory'=>false,'identity'=>self::identity($path)];}
    private static function identity(string $path): array
    {
        $s=lstat($path);if($s===false){throw new RuntimeException('Temporary file disappeared. Preview cleanup again.');}
        return array_intersect_key($s,array_flip(['dev','ino','mode','size','mtime']));
    }
    public static function pruneTemporary(string $runtime,int $cutoff): array
    {
        $plan=self::temporaryPlan($runtime,$cutoff);$result=['files'=>0,'directories'=>0,'bytes'=>0,'skipped'=>0];
        foreach($plan as $item){
            $path=$item['path'];clearstatcache(true,$path);
            // Recheck all ancestors and identity immediately before unlink.
            $parent=dirname($path);
            if(is_link($path)||realpath($parent)!==$parent||!str_starts_with($parent,$runtime.'/')||is_link($parent)||!file_exists($path)){$result['skipped']++;continue;}
            if(!$item['directory']&&self::identity($path)!==$item['identity']){$result['skipped']++;continue;}
            if($item['directory']){if(@rmdir($path)){$result['directories']++;}else{$result['skipped']++;}}
            elseif(@unlink($path)){$result['files']++;$result['bytes']+=$item['bytes'];}else{$result['skipped']++;}
        }
        return $result;
    }
    public static function run(?int $actor,int $limit=200): array
    {
        if($limit<1||$limit>500){throw new RuntimeException('Cleanup batch size must be between 1 and 500.');}
        MaintenanceGate::exclusive();
        if(!Migrations::ready()){throw new RuntimeException('Apply pending schema updates before cleanup.');}
        $db=Database::connect();$out=['at'=>gmdate('c'),'rows'=>[],'temporary'=>[]];$db->beginTransaction();
        try{
            foreach(self::RULES as $table=>[$label,$where]){$out['rows'][$table]=$db->exec('DELETE FROM '.Database::table($table).' WHERE '.$where.' LIMIT '.$limit);}
            $ids=$db->query('SELECT c.id FROM '.Database::table('carts').' c WHERE '.self::cartCondition().' ORDER BY c.id LIMIT '.$limit.' FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN);
            $out['rows']['carts']=0;
            foreach($ids as $id){
                foreach(['cart_items','cart_operations'] as $table){$q=$db->prepare('DELETE FROM '.Database::table($table).' WHERE cart_id=?');$q->execute([$id]);}
                $q=$db->prepare('DELETE FROM '.Database::table('carts').' WHERE id=?');$q->execute([$id]);$out['rows']['carts']+=$q->rowCount();
            }
            $out['rows']['mail_bodies']=$db->exec('UPDATE '.Database::table('mail_queue')." SET encrypted_body='' WHERE ".self::MAIL.' LIMIT '.$limit);
            Database::audit($db,$actor,'maintenance.transient_cleanup');$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        // Files are a separate best-effort phase; never claim SQL can undo an unlink.
        $out['temporary']=self::pruneTemporary(Paths::runtime(),time()-7*86400);
        // The owner-selected analytics policy applies only to analytics records.
        $out['analytics']=Analytics::prune($limit);
        return $out;
    }
    public static function render(): void
    {
        $user=Auth::requireOwner();$actor=(int)$user['id'];$error=null;$result=null;
        if(!Migrations::ready()){go('admin/updates.php');}
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                Auth::reauthenticateOwner($actor,password_input('owner_password'));
                if(post('action')==='policy'){
                    $settings=ConfigStore::read('maintenance');$settings['scheduled_cleanup']=post('scheduled_cleanup')==='1';ConfigStore::write('maintenance',$settings,$actor);flash('Scheduled cleanup preference saved. Configure the separate hosting-panel task before relying on it.');go('admin/maintenance/');
                }
                if(post('action')!=='clean'||post('confirm_cleanup')!=='CLEAN TEMPORARY DATA'){throw new RuntimeException('Type CLEAN TEMPORARY DATA to confirm this limited cleanup.');}
                $result=self::run($actor);MaintenanceGate::release();
            }catch(\InvalidArgumentException|RuntimeException $e){MaintenanceGate::release();$error=Storefront::error($e);}
        }
        $preview=self::preview();$scheduled=(ConfigStore::read('maintenance')['scheduled_cleanup']??false)===true;
        View::start('Temporary data & retention','admin','maintenance',$user);require Paths::root().'/app/views/admin/maintenance.php';View::end();
    }
}
