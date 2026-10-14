<?php
declare(strict_types=1);
namespace Spirit;
use PDO;

final class Auth
{
    public static function current(): ?array
    {
        if (!isset($_SESSION['user_id'], $_SESSION['session_version'])) { return null; }
        $db = Database::connect();
        $q = $db->prepare('SELECT id,name,email,role,session_version,active FROM ' . Database::table('users') . ' WHERE id=?');
        $q->execute([(int)$_SESSION['user_id']]); $user = $q->fetch();
        if (!$user || !(int)$user['active'] || (int)$user['session_version'] !== (int)$_SESSION['session_version']) { unset($_SESSION['user_id'], $_SESSION['session_version']); return null; }
        return $user;
    }
    public static function requireOwner(): array { return self::requirePermission('owner'); }
    public static function requirePermission(string $permission): array
    {
        header('Cache-Control: no-store');
        if (!Paths::installed()) { go('install/'); }
        $user = self::current();
        if (!$user) { go('admin/login.php'); }
        if (!Permissions::allows($user,$permission)) { http_response_code(403); exit('Your staff account does not have permission for this action.'); }
        return $user;
    }
    public static function reauthenticateOwner(int $actor,string $password): void
    {
        RequestLimit::consume('owner-sensitive-confirm',12,900,(string)$actor);
        $q=Database::connect()->prepare('SELECT password_hash,session_version FROM '.Database::table('users')." WHERE id=? AND role='owner' AND active=1");
        $q->execute([$actor]);$row=$q->fetch();
        if(!$row||(int)$row['session_version']!==(int)($_SESSION['session_version']??0)||strlen($password)>72||str_contains($password,"\0")||!password_verify($password,$row['password_hash'])){
            throw new \RuntimeException('Confirm your current owner password for this sensitive operation.');
        }
    }
    private static function consumeLimit(PDO $db, string $subject, int $limit): bool
    {
        $key = hash_hmac('sha256', $subject, Paths::config()['secret']);
        $now = time();
        $q = $db->prepare('INSERT INTO ' . Database::table('login_limits') . ' (bucket,attempts,expires_at) VALUES (?,1,?) ON DUPLICATE KEY UPDATE attempts=IF(expires_at < ?,1,attempts+1), expires_at=IF(expires_at < ?,?,expires_at)');
        $q->execute([$key, $now + 900, $now, $now, $now + 900]);
        $q = $db->prepare('SELECT attempts FROM ' . Database::table('login_limits') . ' WHERE bucket=?'); $q->execute([$key]);
        return (int)$q->fetchColumn() <= $limit;
    }
    public static function login(string $email, string $password): bool
    {
        $db = Database::connect();
        $email = strtolower(trim($email));
        if (strlen($email) > 190 || strlen($password) > 72 || str_contains($password, "\0")) { return false; }
        $ipOk = self::consumeLimit($db, 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 40);
        $accountOk = self::consumeLimit($db, 'email:' . $email, 8);
        if (!$ipOk || !$accountOk) { return false; }
        $q = $db->prepare('SELECT * FROM ' . Database::table('users') . ' WHERE email=?'); $q->execute([$email]); $user = $q->fetch();
        // Valid fixed dummy hash keeps unknown-account password checks expensive too.
        $hash = $user['password_hash'] ?? '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify($password, $hash);
        if (!$valid || !$user || !(int)$user['active'] || !isset(Permissions::ROLES[$user['role']])) { return false; }
        // Recheck after the expensive password verification under a row lock.
        // A simultaneous reset, suspension or role change cannot be overwritten
        // by a late password rehash or silently inherit a new session version.
        $db->beginTransaction();
        try {
            $q=$db->prepare('SELECT * FROM '.Database::table('users').' WHERE id=? FOR UPDATE');
            $q->execute([$user['id']]);$fresh=$q->fetch();
            if(!$fresh||!(int)$fresh['active']||!isset(Permissions::ROLES[$fresh['role']])||!hash_equals($hash,$fresh['password_hash'])||(int)$fresh['session_version']!==(int)$user['session_version']){
                $db->rollBack();return false;
            }
            if(password_needs_rehash($hash,PASSWORD_DEFAULT)){
                $q=$db->prepare('UPDATE '.Database::table('users').' SET password_hash=? WHERE id=?');
                $q->execute([password_hash($password,PASSWORD_DEFAULT),$fresh['id']]);
            }
            Database::audit($db,(int)$fresh['id'],$fresh['role']==='owner'?'owner.login':'staff.login');$db->commit();
        }catch(\Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        session_regenerate_id(true);
        $_SESSION=['user_id'=>(int)$fresh['id'],'session_version'=>(int)$fresh['session_version'],'last_activity'=>time(),'csrf'=>bin2hex(random_bytes(32))];
        // Expired counters reset in consumeLimit. Global pruning is handled
        // by the separately authorized bounded retention workflow.
        return true;
    }
    public static function changePassword(array $user,string $current,string $password,string $confirmation): void
    {
        RequestLimit::consume('staff-password-change',8,900,(string)$user['id']);
        if($password!==$confirmation){throw new \InvalidArgumentException('New passwords do not match.');}
        $hash=Security::password($password);$db=Database::connect();
        $tokens=Database::exists($db,Paths::config()['db']['prefix'].'staff_tokens');
        $db->beginTransaction();
        try{
            $q=$db->prepare('SELECT * FROM '.Database::table('users').' WHERE id=? FOR UPDATE');$q->execute([$user['id']]);$fresh=$q->fetch();
            if(!Permissions::allows($fresh?:null,'account')||(int)$fresh['session_version']!==(int)$user['session_version']){throw new \RuntimeException('Staff access changed. Sign in again.');}
            if(strlen($current)>72||str_contains($current,"\0")||!password_verify($current,$fresh['password_hash'])){throw new \InvalidArgumentException('The current password is incorrect.');}
            $q=$db->prepare('UPDATE '.Database::table('users').' SET password_hash=?,session_version=session_version+1 WHERE id=?');$q->execute([$hash,$fresh['id']]);
            if($tokens){$q=$db->prepare('UPDATE '.Database::table('staff_tokens')." SET used_at=UTC_TIMESTAMP() WHERE email=? AND kind='reset' AND used_at IS NULL");$q->execute([$fresh['email']]);}
            Database::audit($db,(int)$fresh['id'],$fresh['role']==='owner'?'owner.password_changed':'staff.password_changed');$db->commit();
        }catch(\Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        $_SESSION['session_version']=(int)$user['session_version']+1;session_regenerate_id(true);
    }
    public static function logout(): void
    {
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Lax']);
        session_destroy();
    }
}
