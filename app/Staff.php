<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class Staff
{
    public static function role(array $input): string
    {
        return Input::choice($input,'role',array_keys(Permissions::ROLES),'');
    }
    /** Serialize access changes, including the last-owner invariant. */
    private static function mutate(int $actor,callable $operation): mixed
    {
        $db=Database::connect();$lock=InstallState::lock($db,Paths::config()['db']['prefix'].':staff');
        try{
            $db->beginTransaction();$q=$db->prepare('SELECT * FROM '.Database::table('users').' WHERE id=? FOR UPDATE');$q->execute([$actor]);$user=$q->fetch();
            if(!Permissions::allows($user?:null,'owner')||(int)$user['session_version']!==(int)($_SESSION['session_version']??0)){throw new RuntimeException('Owner access changed. Sign in again.');}
            $result=$operation($db);$db->commit();return $result;
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        finally{InstallState::unlock($db,$lock);}
    }
    public static function invite(array $input,int $actor): void
    {
        if(!Communications::ready()){throw new RuntimeException('Configure and verify email before inviting staff.');}
        $email=Security::email(Input::text($input,'email',190,true));$name=Input::text($input,'name',120,true);$role=self::role($input);
        self::mutate($actor,static function(PDO $db)use($actor,$email,$name,$role):void{
            $q=$db->prepare('SELECT id FROM '.Database::table('users').' WHERE email=?');$q->execute([$email]);if($q->fetch()){throw new RuntimeException('This staff address already exists. Edit its access or use password recovery instead.');}
            $q=$db->prepare('SELECT id FROM '.Database::table('staff_tokens')." WHERE email=? AND kind='invite' AND used_at IS NULL AND created_at>DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE)");$q->execute([$email]);if($q->fetch()){throw new RuntimeException('An invitation was recently sent. Wait five minutes before replacing it.');}
            self::invalidate($db,$email);
            self::issue($db,$email,$name,'invite',$role,null,$actor,null);
            Database::audit($db,$actor,'staff.invited');
        });
    }
    private static function invalidate(PDO $db,string $email): void
    {
        $q=$db->prepare('UPDATE '.Database::table('staff_tokens').' SET used_at=UTC_TIMESTAMP() WHERE email=? AND used_at IS NULL');$q->execute([$email]);
    }
    private static function issue(PDO $db,string $email,string $name,string $kind,string $role,?int $user,?int $issuer,?int $version): void
    {
        $token=bin2hex(random_bytes(32));$hash=hash('sha256',$token);$minutes=$kind==='invite'?2880:30;
        $q=$db->prepare('INSERT INTO '.Database::table('staff_tokens').' (token_hash,email,name,kind,target_role,user_id,issuer_id,expected_version,expires_at,created_at) VALUES (?,?,?,?,?,?,?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL '.$minutes.' MINUTE),UTC_TIMESTAMP())');
        $q->execute([$hash,$email,$name,$kind,$role,$user,$issuer,$version]);
        $heading=$kind==='invite'?'Your backstage invitation':'Reset your backstage password';
        $text=$kind==='invite'?'You have been invited as '.Permissions::ROLES[$role].'. The link expires in 48 hours. Choose your own password after verifying this email.':'This staff password reset link expires in 30 minutes. Opening it does not change your password. Ignore it if you did not request recovery.';
        MailQueue::enqueue($email,$heading,Communications::message($heading,$text,'Continue securely',Communications::absolute('admin/staff/complete.php?token='.$token)),'staff:'.$kind.':'.$hash,$db);
    }
    /** Public requests always get the same response. No passwords are preselected. */
    public static function recover(string $email): void
    {
        if(!Communications::ready()){return;}
        $email=Security::email($email);RequestLimit::consume('staff-recovery-ip',12,3600);RequestLimit::consume('staff-recovery-email',3,900,$email);
        $db=Database::connect();$lock=InstallState::lock($db,Paths::config()['db']['prefix'].':staff');
        try{
            $db->beginTransaction();$q=$db->prepare('SELECT * FROM '.Database::table('users').' WHERE email=? FOR UPDATE');$q->execute([$email]);$user=$q->fetch();
            if(!$user||!(int)$user['active']||!isset(Permissions::ROLES[$user['role']])){$db->commit();return;}
            $q=$db->prepare('SELECT id FROM '.Database::table('staff_tokens')." WHERE email=? AND kind='reset' AND used_at IS NULL AND created_at>DATE_SUB(UTC_TIMESTAMP(),INTERVAL 5 MINUTE)");$q->execute([$email]);if($q->fetch()){$db->commit();return;}
            self::invalidate($db,$email);self::issue($db,$email,$user['name'],'reset',$user['role'],(int)$user['id'],null,(int)$user['session_version']);Database::audit($db,(int)$user['id'],'staff.recovery_requested');$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        finally{InstallState::unlock($db,$lock);}
    }
    public static function token(string $hash,?PDO $db=null): ?array
    {
        if(!preg_match('/^[a-f0-9]{64}$/D',$hash)){return null;}$db??=Database::connect();
        $q=$db->prepare('SELECT * FROM '.Database::table('staff_tokens').' WHERE token_hash=? AND used_at IS NULL AND expires_at>UTC_TIMESTAMP()');$q->execute([$hash]);$t=$q->fetch();
        if(!$t||!isset(Permissions::ROLES[$t['target_role']])){return null;}
        if($t['kind']==='invite'){
            $q=$db->prepare('SELECT role,active FROM '.Database::table('users').' WHERE id=?');$q->execute([$t['issuer_id']]);if(!Permissions::allows($q->fetch()?:null,'owner')){return null;}
        }elseif($t['kind']==='reset'){
            $q=$db->prepare('SELECT email,session_version,active FROM '.Database::table('users').' WHERE id=?');$q->execute([$t['user_id']]);$u=$q->fetch();
            if(!$u||!(int)$u['active']||$u['email']!==$t['email']||(int)$u['session_version']!==(int)$t['expected_version']){return null;}
        }else{return null;}return $t;
    }
    public static function complete(string $token,array $input): void
    {
        if(!preg_match('/^[a-f0-9]{64}$/D',$token)){throw new InvalidArgumentException('This link is unavailable or expired.');}
        $password=$input['password']??null;$confirmation=$input['password_confirm']??null;
        if(!is_string($password)||!is_string($confirmation)||$password!==$confirmation){throw new InvalidArgumentException('Passwords must match.');}
        $hash=Security::password($password);$db=Database::connect();$lock=InstallState::lock($db,Paths::config()['db']['prefix'].':staff');
        try{
            $db->beginTransaction();$t=self::token(hash('sha256',$token),$db);if(!$t){throw new RuntimeException('This link is unavailable or expired.');}
            if($t['kind']==='invite'){
                $q=$db->prepare('SELECT id FROM '.Database::table('users').' WHERE email=? FOR UPDATE');$q->execute([$t['email']]);if($q->fetch()){throw new RuntimeException('This invitation no longer applies. Use staff password recovery.');}
                $q=$db->prepare('INSERT INTO '.Database::table('users').' (name,email,password_hash,role,created_at) VALUES (?,?,?,?,UTC_TIMESTAMP())');$q->execute([$t['name'],$t['email'],$hash,$t['target_role']]);$id=(int)$db->lastInsertId();
            }else{
                $id=(int)$t['user_id'];$q=$db->prepare('UPDATE '.Database::table('users').' SET password_hash=?,session_version=session_version+1 WHERE id=? AND session_version=? AND active=1');$q->execute([$hash,$id,$t['expected_version']]);if($q->rowCount()!==1){throw new RuntimeException('Staff access changed. Request a new link.');}
            }
            self::invalidate($db,$t['email']);Database::audit($db,$id,'staff.'.$t['kind'].'.completed');$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        finally{InstallState::unlock($db,$lock);}
    }
    public static function update(int $id,array $input,int $actor): void
    {
        $role=self::role($input);$active=Input::choice($input,'active',['0','1'],'');$version=Input::integer($input,'version',1,PHP_INT_MAX);
        self::mutate($actor,static function(PDO $db)use($id,$role,$active,$version,$actor):void{
            $q=$db->prepare('SELECT * FROM '.Database::table('users').' WHERE id=? FOR UPDATE');$q->execute([$id]);$target=$q->fetch();
            if(!$target||(int)$target['session_version']!==$version){throw new RuntimeException('Staff access changed. Reload before saving.');}
            if($id===$actor&&($role!=='owner'||$active!=='1')){throw new RuntimeException('You cannot remove or reduce your own owner access.');}
            if($target['role']==='owner'&&(int)$target['active']&&($role!=='owner'||$active==='0')){
                $count=(int)$db->query('SELECT COUNT(*) FROM '.Database::table('users')." WHERE role='owner' AND active=1")->fetchColumn();if($count<=1){throw new RuntimeException('At least one active owner must remain.');}
            }
            $q=$db->prepare('UPDATE '.Database::table('users').' SET role=?,active=?,session_version=session_version+1 WHERE id=?');$q->execute([$role,(int)$active,$id]);self::invalidate($db,$target['email']);
            if($role!=='owner'||$active==='0'){$q=$db->prepare('UPDATE '.Database::table('staff_tokens').' SET used_at=UTC_TIMESTAMP() WHERE issuer_id=? AND used_at IS NULL');$q->execute([$id]);}
            Database::audit($db,$actor,'staff.access.updated');
        });
        if($id===$actor){$_SESSION['session_version']=$version+1;}
    }
    public static function cancelInvitation(int $id,int $actor): void
    {
        self::mutate($actor,static function(PDO $db)use($id,$actor):void{$q=$db->prepare('UPDATE '.Database::table('staff_tokens')." SET used_at=UTC_TIMESTAMP() WHERE id=? AND kind='invite' AND used_at IS NULL");$q->execute([$id]);Database::audit($db,$actor,'staff.invitation.cancelled');});
    }
}
