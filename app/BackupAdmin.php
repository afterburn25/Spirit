<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

final class BackupAdmin
{
    public static function render(): void
    {
        $user=ContentAdmin::boot();$actor=(int)$user['id'];$error=null;
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                Auth::reauthenticateOwner($actor,password_input('owner_password'));
                $action=Input::choice($_POST,'action',['create','verify','download','key','restore','import','delete'],'');$id=post('id');
                if($action==='create'){$info=Backups::create($actor);flash('Encrypted snapshot created. Verify it, and save its separate recovery key before relying on it.');}
                elseif($action==='verify'){$r=Backups::verify($id);flash('Archive authentication, '.$r['rows'].' database records and '.$r['files'].' media files verified. External provider state is not part of a backup.');}
                elseif($action==='download'||$action==='key'){self::download($id,$action==='key');}
                elseif($action==='import'){$r=Backups::import(is_array($_FILES['backup']??null)?$_FILES['backup']:[],password_input('recovery_key'),$actor);flash('Backup imported and verified for this installation. Nothing has been restored.');}
                elseif($action==='restore'){
                    if(post('confirm_restore')!=='RESTORE'){throw new RuntimeException('Type RESTORE to confirm replacing the selected site data.');}
                    $r=Backups::restore($id,post('scope'),$actor);flash('Restore committed. Automatic pre-restore snapshot '.$r['pre_restore'].' is retained. Current credentials, payments and consent records were not rewound.');
                }else{
                    if(post('confirm_delete')!=='DELETE'){throw new RuntimeException('Type DELETE to permanently remove this backup copy.');}Backups::remove($id,$actor);flash('Selected backup archive and its private key metadata removed.');
                }
                MaintenanceGate::release();go('admin/backups/');
            }catch(InvalidArgumentException|RuntimeException $e){MaintenanceGate::release();$error=Storefront::error($e);}
        }
        $all=Backups::listing();$pages=max(1,(int)ceil(count($all)/25));$page=max(1,min($pages,Input::integer($_GET,'page',1,PHP_INT_MAX,1)));$rows=array_slice($all,($page-1)*25,25);View::start('Backups & safe restore','admin','backups',$user);require Paths::root().'/views/admin/backups.php';View::end();
    }
    private static function download(string $id,bool $key): never
    {
        $info=Backups::info($id);header('Cache-Control: private, no-store');header('Referrer-Policy: no-referrer');header('X-Robots-Tag: noindex, nofollow');
        if($key){
            $body="SPIRITUAL GORILLAZ — PRIVATE BACKUP RECOVERY KEY\nBackup: ".$id."\nVersion: ".$info['version']."\nRecovery key: ".bin2hex(Backups::key($id))."\n\nStore separately from the encrypted .sgbackup archive. Never upload this receipt to the public site or commit it to Git. The archive contains confidential configuration, identity and order data. Browser import/restore requires this same installation and schema; this is not a cross-host disaster-recovery installer.\n";
            header('Content-Type: text/plain; charset=utf-8');header('Content-Disposition: attachment; filename="Spirit-backup-'.$id.'-PRIVATE-KEY.txt"');header('Content-Length: '.strlen($body));echo $body;exit;
        }
        $file=Backups::path($id);header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="Spirit-backup-'.$id.'.sgbackup"');header('Content-Length: '.filesize($file));session_write_close();$h=fopen($file,'rb');fpassthru($h);fclose($h);exit;
    }
}
