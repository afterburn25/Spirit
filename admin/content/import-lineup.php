<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot('content.manage');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);header('Allow: POST');exit('Method not allowed.');}
Spirit\Security::checkPost();
try{
    if(($_POST['confirm']??'')!=='yes'){throw new InvalidArgumentException('Confirm that you want to import the previous published lineup.');}
    $count=Spirit\MemberOnboarding::import((int)$user['id']);
    flash($count.' band members imported as editable published profiles. Add approved portraits and biographies in the editor.');
    go('admin/content/?kind=member');
}catch(InvalidArgumentException|RuntimeException $error){
    http_response_code(409);
    Spirit\View::start('Lineup import unavailable','admin','member',$user);
    echo '<section class="panel"><h1>Lineup import not completed</h1><p>'.e($error->getMessage()).'</p><a class="button" href="'.e(app_url('admin/content/?kind=member')).'">Back to Band Members</a></section>';
    Spirit\View::end();
}
