<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;
use Throwable;

final class UpdateManager
{
    public static function initialize(): void
    {
        $runtime=Paths::runtime();$folder=$runtime.'/updates';
        if(is_link($folder)||(!is_dir($folder)&&!mkdir($folder,0700))){throw new RuntimeException('Cannot create account-private update storage.');}
        if(!chmod($folder,0700)||!Filesystem::probe($folder)){throw new RuntimeException('Update storage is not privately writable.');}
        $anchor=Paths::root().'/admin/upgrades/location.php';
        if(!file_exists($anchor)&&!is_link($anchor)){
            // Contains a validated directory only, never a key or DB credential.
            // This pointer is locally generated, not accepted from an uploaded package.
            \SpiritRecovery::atomic($anchor,"<?php declare(strict_types=1); return ".var_export($runtime,true).";\n");
        }
        if(\SpiritRecovery::locate()!==$runtime){throw new RuntimeException('Recovery location does not match this installation.');}
    }
    public static function current(): ?array
    {
        $anchor=Paths::root().'/admin/upgrades/location.php';if(!is_file($anchor)){return null;}
        $latest=\SpiritRecovery::latest();return $latest?\SpiritRecovery::job($latest['id']):null;
    }
    public static function stage(array $upload,int $actor): array
    {
        MaintenanceGate::exclusive();self::initialize();
        if(\SpiritRecovery::active()!==null){throw new RuntimeException('Complete or recover the active update before staging another.');}
        if(!Migrations::ready()){throw new RuntimeException('Finish the pending database update before uploading a code package.');}
        $source=$upload['tmp_name']??'';
        if(($upload['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_string($source)||!is_uploaded_file($source)){throw new RuntimeException('Select a successfully uploaded full installation ZIP. Check the host upload limit.');}
        $id=bin2hex(random_bytes(16));$folder=\SpiritRecovery::folder().'/job-'.$id;
        if(!mkdir($folder,0700)||!mkdir($folder.'/new',0700)||!mkdir($folder.'/old',0700)){throw new RuntimeException('Cannot create update staging directories.');}
        try{
            $package=UpdatePackage::stage($source,$folder);$changes=[];
            foreach($package['files'] as $path=>$hash){
                $target=\SpiritRecovery::target($path);$before=is_file($target)?hash_file('sha256',$target):null;
                if($before===false||(is_file($target)&&filesize($target)>8388608)){throw new RuntimeException('Existing application file cannot be safely read and backed up.');}
                if($before===$hash){continue;}
                $parent=dirname($target);while(!is_dir($parent)&&$parent!==Paths::root()){$parent=dirname($parent);}
                if(!Filesystem::probe($parent)){throw new RuntimeException('PHP cannot safely replace one of the destination files. Check hosting-panel ownership; no application files were changed.');}
                $changes[$path]=['before'=>$before,'after'=>$hash,'mode'=>is_file($target)?(fileperms($target)&0777):0644];
            }
            $key=bin2hex(random_bytes(32));$job=['id'=>$id,'root'=>Paths::root(),'state'=>'staged','created_at'=>gmdate('c'),'from'=>Paths::version(),'to'=>$package['version'],'changes'=>$changes,'schema'=>\SpiritRecovery::schema(),'recovery_hash'=>hash('sha256',$key),'sealed_recovery'=>PrivateValue::seal($key),'actor'=>$actor,'pre_backup'=>null];
            \SpiritRecovery::save($job);\SpiritRecovery::writeJson(\SpiritRecovery::folder().'/latest.json',['id'=>$id]);
            Database::audit(Database::connect(),$actor,'upgrade.staged');return $job;
        }catch(Throwable $e){self::cleanJobFolder($folder);throw $e;}
    }
    private static function cleanJobFolder(string $folder): void
    {
        if(dirname($folder)!==\SpiritRecovery::folder()||!preg_match('/^job-[a-f0-9]{32}$/D',basename($folder))||is_link($folder)){return;}
        foreach(['new','old'] as $sub){$dir=$folder.'/'.$sub;if(is_dir($dir)&&!is_link($dir)){foreach(scandir($dir)?:[] as $file){if(preg_match('/^[a-f0-9]{64}$/D',$file)&&is_file($dir.'/'.$file)&&!is_link($dir.'/'.$file)){unlink($dir.'/'.$file);}}@rmdir($dir);}}@rmdir($folder);
    }
    public static function apply(string $id,int $actor): void
    {
        MaintenanceGate::exclusive();$job=\SpiritRecovery::job($id);
        if((\SpiritRecovery::latest()['id']??'')!==$id||$job['state']!=='staged'||\SpiritRecovery::active()!==null){throw new RuntimeException('Only the latest staged update can be installed once.');}
        if(!hash_equals($job['schema'],\SpiritRecovery::schema())||$job['from']!==Paths::version()){throw new RuntimeException('The installed version or schema changed. Upload a fresh package for review.');}
        $base=\SpiritRecovery::folder().'/job-'.$id;
        foreach($job['changes'] as $path=>$change){
            $target=\SpiritRecovery::target($path);$current=is_file($target)?hash_file('sha256',$target):null;
            if($current!==$change['before']){throw new RuntimeException('An application file changed after staging. Nothing was overwritten; review and stage again.');}
            $source=$base.'/new/'.hash('sha256',$path);
            if(is_link($source)||!is_file($source)||!hash_equals($change['after'],hash_file('sha256',$source))){throw new RuntimeException('Staged update checksum changed. Application files were left untouched.');}
            if($current!==null){\SpiritRecovery::atomic($base.'/old/'.hash('sha256',$path),(string)file_get_contents($target));}
        }
        $backup=Backups::create($actor,'Automatic pre-code-update snapshot');$job['pre_backup']=$backup['id'];
        $job['state']='applying';\SpiritRecovery::save($job);\SpiritRecovery::writeJson(\SpiritRecovery::folder().'/active.json',['id'=>$id,'state'=>'applying']);
        // From here a crash must leave the persistent guard active. New code is
        // confirmed only by a separate authenticated request after it starts.
        try{
            foreach($job['changes'] as $path=>$change){\SpiritRecovery::atomic(\SpiritRecovery::target($path,true),(string)file_get_contents($base.'/new/'.hash('sha256',$path)),0644);}
            $job['state']='awaiting_confirmation';$job['applied_at']=gmdate('c');\SpiritRecovery::save($job);
            \SpiritRecovery::writeJson(\SpiritRecovery::folder().'/active.json',['id'=>$id,'state'=>'awaiting_confirmation']);
        }catch(Throwable $e){
            // Best-effort rollback uses only verified originals. If it also
            // fails, do not unlock or erase the journal; use independent rescue.
            try{\SpiritRecovery::rollback($id);}catch(Throwable $recovery){error_log('Spirit update requires owner recovery ['.get_class($recovery).']');}
            throw new RuntimeException('Code update could not finish. Review the saved state and use the independent recovery receipt when needed.');
        }
    }
    public static function confirm(string $id,int $actor): void
    {
        MaintenanceGate::exclusive();$job=\SpiritRecovery::job($id);
        if((\SpiritRecovery::active()['id']??'')!==$id||$job['state']!=='awaiting_confirmation'){throw new RuntimeException('This update is not awaiting confirmation.');}
        if(!hash_equals($job['schema'],\SpiritRecovery::schema())||!Migrations::ready()||Paths::version()!==$job['to']){throw new RuntimeException('The new application did not pass its version and unchanged-schema checks. Keep maintenance active and recover.');}
        foreach($job['changes'] as $path=>$change){$file=\SpiritRecovery::target($path);if(!is_file($file)||!hash_equals($change['after'],hash_file('sha256',$file))){throw new RuntimeException('Installed update integrity failed. Recover the previous code.');}}
        Database::audit(Database::connect(),$actor,'upgrade.confirmed');$job['state']='committed';$job['confirmed_at']=gmdate('c');\SpiritRecovery::save($job);
        if(!unlink(\SpiritRecovery::folder().'/active.json')){throw new RuntimeException('Code is verified but the maintenance marker could not be cleared. Use owner recovery.');}
    }
    public static function receipt(string $id): string
    {
        $job=\SpiritRecovery::job($id);
        return "SPIRITUAL GORILLAZ — PRIVATE UPDATE RECOVERY\nCheckpoint: ".$id."\nRecovery key: ".PrivateValue::open($job['sealed_recovery'])."\nRecovery page: ".Communications::absolute('admin/upgrades/recover.php')."\nFrom: ".$job['from']."\nTo: ".$job['to']."\n\nSave this BEFORE installation. Do not upload, email publicly or commit it. It can restore only this installation's latest applied code checkpoint while its database schema remains unchanged. It cannot restore a database, bypass hosting permissions, or recover after the private rollback files are deleted.\n";
    }
    public static function render(): void
    {
        $user=Auth::requireOwner();$actor=(int)$user['id'];$error=null;
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                Auth::reauthenticateOwner($actor,password_input('owner_password'));
                $action=Input::choice($_POST,'action',['stage','key','apply','confirm','rollback'],'');$id=post('id');
                if($action==='stage'){
                    if(post('trust_package')!=='1'){throw new RuntimeException('Confirm that this ZIP comes from your trusted project release. Checksums do not authenticate a publisher.');}
                    self::stage(is_array($_FILES['package']??null)?$_FILES['package']:[],$actor);flash('Package validated and staged. No application code has been replaced. Save the separate recovery receipt before installing.');
                }elseif($action==='key'){
                    $body=self::receipt($id);header('Content-Type: text/plain; charset=utf-8');header('Content-Disposition: attachment; filename="Spirit-update-'.$id.'-PRIVATE-RECOVERY.txt"');header('Cache-Control: private, no-store');header('Referrer-Policy: no-referrer');echo $body;exit;
                }elseif($action==='apply'){
                    if(post('saved_receipt')!=='1'){throw new RuntimeException('Save your independent recovery receipt before installing.');}self::apply($id,$actor);flash('Files installed under maintenance. This is a new request running the new application: confirm checks to reopen the site, or roll back.');
                }elseif($action==='confirm'){self::confirm($id,$actor);flash('Code update verified and committed. Public access is restored.');}
                else{MaintenanceGate::enter();\SpiritRecovery::rollback($id);flash('Previous code restored without rewinding your database or uploaded media.');}
                MaintenanceGate::release();go('admin/upgrades/');
            }catch(\InvalidArgumentException|RuntimeException $e){MaintenanceGate::release();$error=Storefront::error($e);}
        }
        $job=self::current();View::start('Browser upgrades & recovery','admin','upgrades',$user);require Paths::root().'/views/admin/upgrades.php';View::end();
    }
}
