<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Throwable;

final class Installer
{
    public static function preflight(): array
    {
        $checks = [
            ['name'=>'PHP 8.2 or newer','ok'=>PHP_VERSION_ID >= 80200,'required'=>true,'detail'=>PHP_VERSION],
            ['name'=>'PDO MySQL','ok'=>extension_loaded('pdo_mysql'),'required'=>true,'detail'=>'Database driver'],
            ['name'=>'OpenSSL','ok'=>extension_loaded('openssl'),'required'=>true,'detail'=>'Secure service connections'],
            ['name'=>'File information and GD','ok'=>extension_loaded('fileinfo') && extension_loaded('gd'),'required'=>true,'detail'=>'Validated uploads and image processing'],
            ['name'=>'cURL and ZIP','ok'=>extension_loaded('curl') && extension_loaded('zip'),'required'=>false,'detail'=>'Needed for payment connections and browser ZIP upgrades'],
            ['name'=>'HTTPS','ok'=>Security::secureTransport(),'required'=>true,'detail'=>Security::isHttps() ? 'Encrypted connection' : 'Local development connection only'],
        ];
        try {
            Filesystem::prepare(Paths::runtime(), Paths::documentRoot());
            $checks[] = ['name'=>'Account-private runtime','ok'=>true,'required'=>true,'detail'=>'Verified against known and host-configured public roots'];
            $checks[] = ['name'=>'Create / write / read / rename / delete','ok'=>true,'required'=>true,'detail'=>'Actual private-directory operations passed'];
        } catch (RuntimeException $e) { $checks[] = ['name'=>'Account-private runtime','ok'=>false,'required'=>true,'detail'=>$e->getMessage()]; }
        $deletable = is_writable(Paths::root()) && is_writable(Paths::root() . '/install');
        $checks[] = ['name'=>'Installer cleanup readiness','ok'=>$deletable,'required'=>false,'detail'=>$deletable ? 'Final deletion will be attempted and verified.' : 'The host may prevent physical removal. The permanent installation lock remains enforced.'];
        return $checks;
    }
    public static function install(array $input): array
    {
        if (Paths::installed()) { throw new RuntimeException('Installation is already locked.'); }
        foreach (self::preflight() as $check) { if ($check['required'] && !$check['ok']) { throw new RuntimeException('Resolve the required hosting checks before installing.'); } }
        $dbConfig = Database::validate($input['db']); $email = Security::email($input['email']); $passwordHash = Security::password($input['password']);
        if ($input['password'] !== $input['password_confirm']) { throw new InvalidArgumentException('Owner passwords do not match.'); }
        $ownerName = trim($input['owner_name']); if ($ownerName === '' || strlen($ownerName) > 120) { throw new InvalidArgumentException('Owner name is required and must be at most 120 bytes.'); }
        $url = Security::siteUrl($input['site_url']); $lock = fopen(Paths::runtime() . '/install-operation.lock', 'c+b');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { if (is_resource($lock)) { fclose($lock); } throw new RuntimeException('Another installation is already running.'); }
        $db = null; $dbLock = null;
        try {
            if (Paths::installed()) { throw new RuntimeException('This application is already installed.'); }
            $db = Database::connect($dbConfig); $dbLock = InstallState::lock($db, $dbConfig['prefix']); Database::probe($db);
            $pendingPath = Paths::runtime() . '/pending.php';
            $pending = is_file($pendingPath) && !is_link($pendingPath) ? require $pendingPath : null;
            if ($pending !== null && (!is_array($pending) || !isset($pending['db'],$pending['secret']) || array_diff_assoc($pending['db'], $dbConfig))) { throw new RuntimeException('An interrupted installation exists with different database settings. Keep the original settings to resume; no existing tables were changed.'); }
            if ($pending === null) {
                $q = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND LEFT(table_name,?)=?'); $q->execute([strlen($dbConfig['prefix']),$dbConfig['prefix']]);
                if ((int)$q->fetchColumn() !== 0) { throw new RuntimeException('This table prefix is already in use. Existing data was left untouched.'); }
                $pending = ['db'=>$dbConfig,'secret'=>bin2hex(random_bytes(32)),'site_url'=>$url,'installed'=>false,'created_at'=>gmdate('c')];
                Filesystem::atomicPhp($pendingPath, $pending);
            }
            InstallState::ensure($db, $dbConfig['prefix'], hash('sha256',$pending['secret']), Paths::version());
            Migrations::apply($db, $dbConfig['prefix']);
            $state = '`' . $dbConfig['prefix'] . 'installation`';
            $db->beginTransaction();
            try {
                if ((int)$db->query('SELECT COUNT(*) FROM `' . $dbConfig['prefix'] . 'users`')->fetchColumn() === 0) {
                    $q = $db->prepare('INSERT INTO `' . $dbConfig['prefix'] . 'users` (name,email,password_hash,role,created_at) VALUES (?,?,?,\'owner\',UTC_TIMESTAMP())'); $q->execute([$ownerName,$email,$passwordHash]);
                }
                $q = $db->prepare('INSERT IGNORE INTO `' . $dbConfig['prefix'] . 'settings` (setting_key,setting_value) VALUES (?,?)');
                foreach (['site_name'=>'Spiritual Gorillaz','tagline'=>'Music. People. Purpose.','welcome'=>'Music with a higher purpose. Explore the sound, the story and the next chapter.'] as $key=>$value) { $q->execute([$key,$value]); }
                $db->exec('UPDATE ' . $state . " SET status='installed' WHERE id=1"); $db->commit();
            } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
            $pending['installed'] = true; Filesystem::atomicPhp(Paths::runtime() . '/config.php', $pending);
            // Config is independently a permanent lock. Secondary cleanup failures
            // must never turn a completed install into a destructive retry.
            try { Filesystem::atomicPhp(Paths::runtime() . '/installed.lock', ['version'=>Paths::version(),'installed_at'=>gmdate('c')]); } catch (RuntimeException $e) { error_log('Spirit: secondary completion marker unavailable; installed configuration remains locked.'); }
            @unlink($pendingPath);
            $cleanup = Filesystem::cleanup(Paths::root());
            try { Filesystem::atomicPhp(Paths::runtime() . '/cleanup.php', $cleanup); } catch (RuntimeException $e) { error_log('Spirit: cleanup receipt unavailable; inspect installer directory from owner administration.'); }
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) { session_destroy(); }
            return $cleanup;
        } catch (PDOException $e) {
            throw new RuntimeException('Database verification or provisioning failed. Check connection details and schema privileges. An interrupted installation can be resumed with the same settings; existing tables are never dropped.');
        } finally {
            if ($db !== null && $dbLock !== null) { try { InstallState::unlock($db,$dbLock); } catch (Throwable $e) {} }
            flock($lock,LOCK_UN); fclose($lock);
        }
    }
}
