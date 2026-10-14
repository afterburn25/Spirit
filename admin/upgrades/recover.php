<?php
declare(strict_types=1);
// Independent, pinned recovery entrance: intentionally never includes bootstrap.php.
ini_set('display_errors','0'); umask(0077);
header('Cache-Control: private, no-store'); header('Referrer-Policy: no-referrer'); header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff'); header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
$secure=isset($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off'&&$_SERVER['HTTPS']!=='';
$local=PHP_SAPI==='cli-server'&&in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true);
if(!$secure&&!$local){http_response_code(403);exit('HTTPS is required.');}
require_once __DIR__.'/RecoveryEngine.php';
$error='';$success=false;$ready=false;
try {
    $runtime=SpiritRecovery::locate(); if(!$runtime){throw new RuntimeException('No update recovery checkpoint is configured.');}
    SpiritRecovery::enter($runtime);
    $sessions=$runtime.'/sessions';if(is_link($sessions)||!is_dir($sessions)||(fileperms($sessions)&0077)!==0){throw new RuntimeException('Private recovery sessions unavailable.');}
    ini_set('session.use_strict_mode','1');ini_set('session.use_only_cookies','1');session_name('SPIRIT_RECOVERY');session_save_path($sessions);
    session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Strict','path'=>'/']);session_start();
    $_SESSION['csrf']??=bin2hex(random_bytes(32));$ready=true;
    if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
        $csrf=$_POST['csrf']??'';
        if(!is_string($csrf)||!hash_equals($_SESSION['csrf'],$csrf)){http_response_code(403);throw new RuntimeException('Reload this recovery form and try again.');}
        $id=$_POST['checkpoint']??'';$key=$_POST['recovery_key']??'';
        if(!is_string($id)||!is_string($key)||($_POST['confirm']??'')!=='ROLLBACK'){throw new RuntimeException('Enter the checkpoint, recovery key and ROLLBACK confirmation from your private receipt.');}
        SpiritRecovery::rollback(trim($id),trim($key));$success=true;$_SESSION['csrf']=bin2hex(random_bytes(32));
    }
}catch(Throwable $e){$error=$e instanceof PDOException?'Database identity could not be verified. Recovery did not proceed.':$e->getMessage();}
finally{SpiritRecovery::release();}
function safe(string $s):string{return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Spiritual Gorillaz · Update recovery</title><link rel="stylesheet" href="../../assets/css/app.css"></head><body><main class="public-wrap"><section class="panel"><p>SPIRITUAL GORILLAZ / PROTECTED RECOVERY</p><h1>Recover the previous code.</h1><p>This entrance remains available when an application update cannot start. It only restores the latest saved code checkpoint; it never rewinds orders, customers, passwords, stock or subscription consent.</p>
<?php if($success):?><div role="status">Previous code restored. Return to <a href="../login.php">owner sign-in</a> and verify the site.</div><?php endif;?>
<?php if($error!==''):?><p role="alert"><?=safe($error)?></p><?php endif;?>
<?php if($ready&&!$success):?><form method="post" autocomplete="off"><input type="hidden" name="csrf" value="<?=safe($_SESSION['csrf'])?>"><label>Checkpoint<input name="checkpoint" required maxlength="32" pattern="[a-f0-9]{32}"></label><label>Private recovery key<input name="recovery_key" type="password" required maxlength="64" autocomplete="off"></label><label>Type ROLLBACK<input name="confirm" required pattern="ROLLBACK"></label><button type="submit" class="button">Restore previous code</button></form><?php endif;?></section></main></body></html>
