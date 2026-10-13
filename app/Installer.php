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
            ['name' => 'PHP 8.2 or newer', 'ok' => PHP_VERSION_ID >= 80200, 'required' => true, 'detail' => PHP_VERSION],
            ['name' => 'PDO MySQL', 'ok' => extension_loaded('pdo_mysql'), 'required' => true, 'detail' => 'Database driver'],
            ['name' => 'OpenSSL', 'ok' => extension_loaded('openssl'), 'required' => true, 'detail' => 'Secure random and future service connections'],
            ['name' => 'HTTPS', 'ok' => Security::secureTransport(), 'required' => true, 'detail' => Security::isHttps() ? 'Encrypted connection' : 'Local development connection only'],
        ];
        try {
            Filesystem::prepare(Paths::runtime(), Paths::documentRoot());
            $checks[] = ['name' => 'Account-private runtime', 'ok' => true, 'required' => true, 'detail' => 'Created and verified outside the public document root'];
            $checks[] = ['name' => 'Create / write / read / rename / delete', 'ok' => true, 'required' => true, 'detail' => 'Actual filesystem operations passed'];
        } catch (RuntimeException $e) {
            $checks[] = ['name' => 'Account-private runtime', 'ok' => false, 'required' => true, 'detail' => $e->getMessage()];
        }
        $deletable = is_writable(Paths::root()) && is_writable(Paths::root() . '/install');
        $checks[] = ['name' => 'Installer cleanup readiness', 'ok' => $deletable, 'required' => false, 'detail' => $deletable ? 'Permissions appear suitable; final deletion will be attempted and verified.' : 'The host may prevent physical removal. A permanent installation lock is still enforced.'];
        return $checks;
    }
    public static function setupHash(): ?string
    {
        $file = Paths::root() . '/install/access.php';
        if (!is_file($file) || is_link($file)) { return null; }
        $value = require $file;
        return is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value) ? $value : null;
    }
    public static function claim(string $code): bool
    {
        $hash = self::setupHash();
        if ($hash !== null && hash_equals($hash, hash('sha256', $code))) {
            session_regenerate_id(true); $_SESSION['setup_claim'] = $hash; return true;
        }
        usleep(250000); return false;
    }
    public static function claimed(): bool
    {
        $hash = self::setupHash();
        return $hash !== null && isset($_SESSION['setup_claim']) && hash_equals($hash, $_SESSION['setup_claim']);
    }
    public static function install(array $input): void
    {
        if (!self::claimed() || Paths::installed()) { throw new RuntimeException('Installation is not authorized or is already locked.'); }
        foreach (self::preflight() as $check) { if ($check['required'] && !$check['ok']) { throw new RuntimeException('Resolve the required hosting checks before installing.'); } }
        $dbConfig = Database::validate($input['db']);
        $email = Security::email($input['email']);
        $passwordHash = Security::password($input['password']);
        if ($input['password'] !== $input['password_confirm']) { throw new InvalidArgumentException('Owner passwords do not match.'); }
        $ownerName = trim($input['owner_name']);
        if ($ownerName === '' || strlen($ownerName) > 120) { throw new InvalidArgumentException('Owner name is required and must be at most 120 bytes.'); }
        $url = Security::siteUrl($input['site_url']);
        $lock = fopen(Paths::runtime() . '/install-operation.lock', 'c+b');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { throw new RuntimeException('Another installation is already running.'); }
        try {
            if (Paths::installed()) { throw new RuntimeException('This application is already installed.'); }
            $db = Database::connect($dbConfig);
            Database::probe($db);
            $pendingPath = Paths::runtime() . '/pending.php';
            $pending = is_file($pendingPath) && !is_link($pendingPath) ? require $pendingPath : null;
            if ($pending !== null && (!is_array($pending) || array_diff_assoc($pending['db'], $dbConfig))) {
                throw new RuntimeException('An interrupted installation exists with different database settings. Keep the original settings to resume; no existing tables were changed.');
            }
            $state = '`' . $dbConfig['prefix'] . 'installation`';
            if ($pending === null) {
                foreach (['installation', 'users', 'settings', 'audit', 'login_limits'] as $table) {
                    if (Database::exists($db, $dbConfig['prefix'] . $table)) { throw new RuntimeException('This table prefix is already in use. Existing data was left untouched.'); }
                }
                $pending = ['db' => $dbConfig, 'secret' => bin2hex(random_bytes(32)), 'site_url' => $url, 'installed' => false, 'created_at' => gmdate('c')];
                Filesystem::atomicPhp($pendingPath, $pending);
            }
            if (Database::exists($db, $dbConfig['prefix'] . 'installation')) {
                $row = $db->query('SELECT * FROM ' . $state . ' WHERE id=1')->fetch();
                if (!$row || !hash_equals($row['fingerprint'], hash('sha256', $pending['secret']))) { throw new RuntimeException('Existing installation ownership does not match. Refusing to overwrite data.'); }
            } else {
                $db->exec('CREATE TABLE ' . $state . ' (id INT PRIMARY KEY, fingerprint CHAR(64) NOT NULL, status VARCHAR(20) NOT NULL, version VARCHAR(40) NOT NULL) ENGINE=InnoDB');
                $q = $db->prepare('INSERT INTO ' . $state . " VALUES (1,?,'provisioning',?)");
                $q->execute([hash('sha256', $pending['secret']), Paths::version()]);
            }
            // MySQL DDL is not transactional. Persisted ownership allows same-install retries.
            $schema = str_replace('{{prefix}}', $dbConfig['prefix'], (string)file_get_contents(Paths::root() . '/database/migrations/001.sql'));
            foreach (explode(';', $schema) as $statement) { if (trim($statement) !== '') { $db->exec($statement); } }
            $db->beginTransaction();
            try {
                if ((int)$db->query('SELECT COUNT(*) FROM `' . $dbConfig['prefix'] . 'users`')->fetchColumn() === 0) {
                    $q = $db->prepare('INSERT INTO `' . $dbConfig['prefix'] . 'users` (name,email,password_hash,role,created_at) VALUES (?,?,?,\'owner\',UTC_TIMESTAMP())');
                    $q->execute([$ownerName, $email, $passwordHash]);
                }
                $q = $db->prepare('INSERT IGNORE INTO `' . $dbConfig['prefix'] . 'settings` (setting_key,setting_value) VALUES (?,?)');
                foreach (['site_name' => 'Spiritual Gorillaz', 'tagline' => 'Music. People. Purpose.', 'welcome' => 'The new Spiritual Gorillaz website is taking shape. Music, shows and more are on their way.'] as $key => $value) { $q->execute([$key, $value]); }
                $db->exec('UPDATE ' . $state . " SET status='installed' WHERE id=1");
                $db->commit();
            } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
            $pending['installed'] = true;
            Filesystem::atomicPhp(Paths::runtime() . '/config.php', $pending);
            Filesystem::atomicPhp(Paths::runtime() . '/installed.lock', ['version' => Paths::version(), 'installed_at' => gmdate('c')]);
            @unlink($pendingPath);
            Filesystem::atomicPhp(Paths::runtime() . '/cleanup.php', Filesystem::cleanup(Paths::root()));
            // Do not auto-login across the pre-install -> private session-store boundary.
            $_SESSION = []; session_destroy();
        } catch (PDOException $e) {
            throw new RuntimeException('Database verification or provisioning failed. Check the host, database credentials and CREATE/ALTER/SELECT/INSERT/UPDATE/DELETE/DROP privileges. An interrupted installation can be resumed with the same settings.');
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }
}
