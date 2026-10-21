<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

final class StaffAdmin
{
    public static function render(): void
    {
        $user=ContentAdmin::boot();$error=null;
        if($_SERVER['REQUEST_METHOD']==='POST'){
            Security::checkPost();
            try{
                Auth::reauthenticateOwner((int)$user['id'],password_input('owner_password'));
                $action=Input::choice($_POST,'action',['invite','update','cancel'],'');
                if($action==='invite'){Staff::invite($_POST,(int)$user['id']);flash('Invitation queued. The recipient must verify the email and choose a password.');}
                elseif($action==='update'){Staff::update(Input::integer($_POST,'id',1,PHP_INT_MAX),$_POST,(int)$user['id']);flash('Staff access updated. Previous sessions and outstanding recovery links were revoked.');}
                else{Staff::cancelInvitation(Input::integer($_POST,'id',1,PHP_INT_MAX),(int)$user['id']);flash('Invitation cancelled. Its link can no longer create a staff account.');}
                Communications::scheduleDelivery();go('admin/staff/');
            }catch(InvalidArgumentException|RuntimeException $e){$error=Storefront::error($e);}
        }
        $db=Database::connect();$total=(int)$db->query('SELECT COUNT(*) FROM '.Database::table('users'))->fetchColumn();$pages=max(1,(int)ceil($total/25));$page=max(1,min($pages,Input::integer($_GET,'page',1,PHP_INT_MAX,1)));
        $users=$db->query('SELECT id,name,email,role,active,session_version FROM '.Database::table('users').' ORDER BY id LIMIT 25 OFFSET '.(($page-1)*25))->fetchAll();
        $invites=$db->query('SELECT id,name,email,target_role,expires_at FROM '.Database::table('staff_tokens')." WHERE kind='invite' AND used_at IS NULL AND expires_at>UTC_TIMESTAMP() ORDER BY id DESC LIMIT 25")->fetchAll();
        View::start('Staff access','admin','staff',$user);require Paths::root().'/app/views/admin/staff.php';View::end();
    }
}
