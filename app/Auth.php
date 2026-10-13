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
    public static function requireOwner(): array
    {
        header('Cache-Control: no-store');
        if (!Paths::installed()) { go('install/'); }
        $user = self::current();
        if (!$user) { go('admin/login.php'); }
        if ($user['role'] !== 'owner') { http_response_code(403); exit('Owner access is required for this development release.'); }
        return $user;
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
        if (!$valid || !$user || !(int)$user['active']) { return false; }
        session_regenerate_id(true);
        $_SESSION = ['user_id' => (int)$user['id'], 'session_version' => (int)$user['session_version'], 'last_activity' => time(), 'csrf' => bin2hex(random_bytes(32))];
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $q = $db->prepare('UPDATE ' . Database::table('users') . ' SET password_hash=? WHERE id=?');
            $q->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
        $q = $db->prepare('DELETE FROM ' . Database::table('login_limits') . ' WHERE expires_at < ?'); $q->execute([time()]);
        Database::audit($db, (int)$user['id'], 'owner.login');
        return true;
    }
    public static function logout(): void
    {
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Lax']);
        session_destroy();
    }
}
