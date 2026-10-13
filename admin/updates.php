<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
$user=Spirit\Auth::requireOwner();$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 Spirit\Security::checkPost();$db=null;$lock=null;
 try{$db=Spirit\Database::connect();$config=Spirit\Paths::config();$lock=Spirit\InstallState::lock($db,$config['db']['prefix']);Spirit\InstallState::ensure($db,$config['db']['prefix'],hash('sha256',$config['secret']),Spirit\Paths::version());$applied=Spirit\Migrations::apply($db,$config['db']['prefix']);Spirit\Database::audit($db,(int)$user['id'],'schema.updated');flash($applied?'Applied '.count($applied).' additive migration(s). Existing owner, settings and data were preserved.':'The database is already up to date.');}catch(Throwable $e){$error=$e instanceof PDOException?'The database update could not complete. Existing data was not intentionally deleted. Check hosting privileges and retry this additive update.':$e->getMessage();}finally{if($db&&$lock){Spirit\InstallState::unlock($db,$lock);}}
}
$ready=Spirit\Migrations::ready();Spirit\View::start('Database updates','admin','updates',$user);
?>
<div class="page-heading"><div><p class="eyebrow">SYSTEM / DATABASE UPDATES</p><h1>Move forward.<br><em>Keep your data.</em></h1></div></div><?php Spirit\View::notice($error);?>
<section class="panel form-width"><h2><?=$ready?'Database up to date':'An additive database update is available'?></h2><p>This applies the versioned schema included with the code you uploaded. It does not reinstall the site, reset passwords, replace settings or delete your uploads.</p><p>Before any update, export a backup using the hosting panel. A full browser ZIP-upgrade and backup/restore workflow belongs to the later maintenance milestone and is not claimed here.</p><form method="post"><?=csrf_field()?><button class="button" type="submit"><?=$ready?'Verify database schema':'Apply database update'?></button></form></section>
<?php Spirit\View::end();?>
