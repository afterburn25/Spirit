<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot('content.manage');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);header('Allow: POST');exit('Method not allowed.');}
Spirit\Security::checkPost();
try {
    $id=Spirit\Input::integer($_POST,'id',1,PHP_INT_MAX);
    $revision=Spirit\Input::integer($_POST,'revision',0,4294967294);
    $kind=Spirit\Input::choice($_POST,'kind',['member']);
    if(($_POST['confirm']??'')!=='yes'){throw new InvalidArgumentException('Confirm the member removal.');}
    $db=Spirit\Database::connect();$db->beginTransaction();
    try {
        $staff=$db->prepare('SELECT id,role,active,session_version FROM '.Spirit\Database::table('users').' WHERE id=? FOR UPDATE');
        $staff->execute([(int)$user['id']]);$current=$staff->fetch();
        if(!Spirit\Permissions::allows($current?:null,'content.manage')||(int)($_SESSION['session_version']??0)!==(int)($current['session_version']??-1)){throw new RuntimeException('Your content permissions changed. Sign in again.');}
        $q=$db->prepare('SELECT id,kind,revision FROM '.Spirit\Database::table('content').' WHERE id=? AND deleted_at IS NULL FOR UPDATE');
        $q->execute([$id]);$row=$q->fetch();
        if(!$row||$row['kind']!==$kind){throw new InvalidArgumentException('The member was not found.');}
        if((int)$row['revision']!==$revision){throw new RuntimeException('This member changed since the page opened. Reload and review before deleting.');}
        $q=$db->prepare('UPDATE '.Spirit\Database::table('content').' SET deleted_at=UTC_TIMESTAMP(),published_slug=NULL,published_json=NULL,published_at=NULL,revision=revision+1,updated_by=?,updated_at=UTC_TIMESTAMP() WHERE id=?');
        $q->execute([(int)$user['id'],$id]);
        $q=$db->prepare('DELETE FROM '.Spirit\Database::table('content_media').' WHERE content_id=?');
        $q->execute([$id]);
        Spirit\Database::audit($db,(int)$user['id'],'content.member.delete');
        $db->commit();
    } catch(Throwable $error) {if($db->inTransaction()){$db->rollBack();}throw $error;}
    flash('Band member removed from the public site and content list. Uploaded photographs are retained.');
    go('admin/content/?kind=member');
} catch(InvalidArgumentException|RuntimeException $error) {
    http_response_code(409);
    Spirit\View::start('Could not remove member','admin','member',$user);
    echo '<section class="panel"><h1>Removal not completed</h1><p>'.e($error->getMessage()).'</p><a class="button" href="'.e(app_url('admin/content/?kind=member')).'">Return to band members</a></section>';
    Spirit\View::end();
}
