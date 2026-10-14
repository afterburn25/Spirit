<?php
declare(strict_types=1);
/** Frozen recovery kernel. Does not depend on the replaceable application bootstrap.
 * Browser packages must preserve this file and its two entry points byte-for-byte.
 */
final class SpiritRecovery
{
    private static mixed $lock = null;
    private static bool $exclusive = false;
    private static ?string $runtime = null;
    public static function root(): string { return realpath(dirname(__DIR__, 2)) ?: throw new RuntimeException('Application directory unavailable.'); }
    public static function locate(): ?string
    {
        $anchor = __DIR__ . '/location.php';
        if (!file_exists($anchor) && !is_link($anchor)) { return null; }
        if (is_link($anchor) || !is_file($anchor) || (fileperms($anchor) & 0022) !== 0) { throw new RuntimeException('Recovery location is not protected.'); }
        $path = require $anchor;
        $expected = '.spirit-' . substr(hash('sha256', self::root()), 0, 20);
        if (!is_string($path) || is_link($path) || realpath($path) !== $path || basename($path) !== $expected || (fileperms($path) & 0077) !== 0) { throw new RuntimeException('Recovery storage is unavailable.'); }
        return $path;
    }
    public static function controlRequest(): bool
    {
        $file = realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
        return in_array($file, [__DIR__ . '/index.php', __DIR__ . '/recover.php', self::root() . '/admin/login.php', self::root() . '/admin/logout.php'], true);
    }
    public static function enter(string $runtime): void
    {
        if (is_resource(self::$lock)) { return; }
        if (is_link($runtime) || realpath($runtime) !== $runtime) { throw new RuntimeException('Unsafe maintenance storage.'); }
        $path = $runtime . '/maintenance.lock';
        if (is_link($path)) { throw new RuntimeException('Unsafe maintenance lock.'); }
        $h = @fopen($path, 'c+b');
        if (!$h || !@chmod($path, 0600)) { if (is_resource($h)) { fclose($h); } throw new RuntimeException('Private maintenance lock unavailable.'); }
        if (!flock($h, LOCK_SH | LOCK_NB)) { fclose($h); self::busy(); }
        self::$runtime = $runtime; self::$lock = $h;
        // A durable update journal continues to protect requests after a process
        // dies. The normal OS lock alone would disappear with the process.
        $active = self::active();
        if ($active !== null) {
            $recovery = realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __DIR__ . '/recover.php';
            $review = ($active['state'] ?? '') === 'awaiting_confirmation' && self::controlRequest();
            $cliControl = PHP_SAPI === 'cli' && defined('SPIRIT_UPGRADE_CONTROL') && SPIRIT_UPGRADE_CONTROL === true;
            if (!$recovery && !$review && !$cliControl) { self::busy(); }
        }
    }
    public static function exclusive(): void
    {
        if (!is_resource(self::$lock)) { throw new RuntimeException('Maintenance lock not initialized.'); }
        if (!flock(self::$lock, LOCK_EX | LOCK_NB)) {
            // Lock conversion need not be atomic. Never pretend a failed upgrade
            // still holds a shared lock; close and make the caller retry.
            self::release(); self::busy();
        }
        self::$exclusive = true;
    }
    public static function isExclusive(): bool { return self::$exclusive; }
    public static function release(): void
    {
        if (is_resource(self::$lock)) { flock(self::$lock, LOCK_UN); fclose(self::$lock); }
        self::$lock = null; self::$exclusive = false; self::$runtime = null;
    }
    public static function busy(): never
    {
        if (PHP_SAPI !== 'cli') { header('Retry-After: 10'); }
        throw new RuntimeException('Another website operation is active. Retry maintenance after current requests finish.');
    }
    public static function folder(): string
    {
        $runtime = self::$runtime ?? self::locate();
        if (!$runtime) { throw new RuntimeException('No recovery checkpoint has been initialized.'); }
        $path = $runtime . '/updates';
        if (is_link($path) || !is_dir($path) || realpath($path) !== $path || (fileperms($path) & 0077) !== 0) { throw new RuntimeException('Protected update storage is unavailable.'); }
        return $path;
    }
    public static function id(string $id): string
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $id)) { throw new RuntimeException('Invalid update selection.'); } return $id;
    }
    public static function readJson(string $file): array
    {
        if (is_link($file) || !is_file($file) || filesize($file) > 2097152 || (fileperms($file) & 0077) !== 0) { throw new RuntimeException('Update record unavailable.'); }
        $data = json_decode((string)file_get_contents($file), true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($data)) { throw new RuntimeException('Invalid update record.'); } return $data;
    }
    public static function atomic(string $file, string $body, int $mode = 0600): void
    {
        if (is_link($file) || !is_dir(dirname($file)) || is_link(dirname($file))) { throw new RuntimeException('Unsafe update destination.'); }
        $tmp = dirname($file) . '/.spirit-update-' . bin2hex(random_bytes(12));
        $h = @fopen($tmp, 'x+b');
        if (!$h) { throw new RuntimeException('Cannot write an update file.'); }
        try {
            if (!chmod($tmp, $mode) || fwrite($h, $body) !== strlen($body) || !fflush($h) || (function_exists('fsync') && !fsync($h))) { throw new RuntimeException('Incomplete update write.'); }
            fclose($h); $h = null;
            if (!@rename($tmp, $file)) { throw new RuntimeException('Cannot replace an update file.'); }
            if (function_exists('opcache_invalidate') && str_ends_with($file, '.php')) { @opcache_invalidate($file, true); }
        } finally { if (is_resource($h)) { fclose($h); } if (is_file($tmp)) { @unlink($tmp); } }
    }
    public static function writeJson(string $file, array $data): void { self::atomic($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)); }
    public static function active(): ?array
    {
        $runtime = self::$runtime ?? self::locate();
        if (!$runtime) { return null; }
        $file = $runtime . '/updates/active.json';
        return (!file_exists($file) && !is_link($file)) ? null : self::readJson($file);
    }
    public static function latest(): ?array
    {
        $file = self::folder() . '/latest.json';
        return !file_exists($file) ? null : self::readJson($file);
    }
    public static function job(string $id): array
    {
        $j = self::readJson(self::folder() . '/job-' . self::id($id) . '.json');
        if (($j['id'] ?? null) !== $id || ($j['root'] ?? null) !== self::root()) { throw new RuntimeException('Update belongs to another installation.'); }
        return $j;
    }
    public static function save(array $job): void { self::writeJson(self::folder() . '/job-' . self::id($job['id']) . '.json', $job); }
    public static function mutable(string $path): bool
    {
        if (!preg_match('#^(?:[A-Za-z0-9_-]+/)*(?:[A-Za-z0-9_.-]+|\.htaccess)$#D', $path) || str_contains($path, '..') || str_contains($path, '//')) { return false; }
        if (in_array($path, ['includes/maintenance-entry.php', 'admin/upgrades/RecoveryEngine.php', 'admin/upgrades/recover.php', 'admin/upgrades/.htaccess', '.htaccess'], true)) { return false; }
        if (preg_match('/private-(?:setup|key|recovery)/i', $path) || preg_match('/\.(?:zip|key|pem|log|gz|sqlite|bak)$/i', $path)) { return false; }
        $dir = explode('/', $path)[0];
        return in_array($path, ['index.php', 'VERSION', 'README.md', 'CHANGELOG.md'], true) || in_array($dir, ['app','admin','includes','views','assets','images','docs','music','shows','band','media','booking','press','shop','account','pages','newsletter','tasks','payments'], true) && !str_ends_with($path, '/location.php');
    }
    public static function target(string $path, bool $create = false): string
    {
        if (!self::mutable($path)) { throw new RuntimeException('Protected or invalid update path.'); }
        $parts = explode('/', $path); array_pop($parts); $parent = self::root();
        foreach ($parts as $part) {
            $parent .= '/' . $part;
            if (is_link($parent) || (file_exists($parent) && !is_dir($parent))) { throw new RuntimeException('An update parent is not a regular directory.'); }
            if (!is_dir($parent) && $create && !mkdir($parent, 0755)) { throw new RuntimeException('Cannot create an update directory.'); }
        }
        $file = self::root() . '/' . $path;
        if (is_link($file) || (file_exists($file) && !is_file($file))) { throw new RuntimeException('An update file is not a regular file.'); }
        return $file;
    }
    /** Hash schema, not business rows: code rollback must retain newer orders. */
    public static function schema(): string
    {
        $runtime = self::$runtime ?? self::locate(); $file = $runtime . '/config.php';
        if (is_link($file) || !is_file($file) || (fileperms($file) & 0077) !== 0) { throw new RuntimeException('Protected configuration unavailable.'); }
        $config = require $file; $c = $config['db'] ?? [];
        if (!preg_match('/^[a-z][a-z0-9_]{0,15}$/D', $c['prefix'] ?? '') || !preg_match('/^[A-Za-z0-9._-]{1,253}$/D', $c['host'] ?? '') || !preg_match('/^[A-Za-z0-9_$-]{1,64}$/D', $c['name'] ?? '')) { throw new RuntimeException('Invalid installed database identity.'); }
        $db = new PDO('mysql:host='.$c['host'].';port='.(int)$c['port'].';dbname='.$c['name'].';charset=utf8mb4', $c['user'], $c['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false, PDO::ATTR_TIMEOUT=>5]);
        $out = [];
        foreach ([
            'COLUMNS'=>['TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA', 'TABLE_NAME,ORDINAL_POSITION'],
            'STATISTICS'=>['TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME,SUB_PART,INDEX_TYPE', 'TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX']
        ] as $view => [$fields, $order]) {
            $q=$db->prepare('SELECT '.$fields.' FROM information_schema.'.$view.' WHERE TABLE_SCHEMA=? ORDER BY '.$order); $q->execute([$c['name']]);
            $out[$view]=array_values(array_filter($q->fetchAll(), static fn($r)=>str_starts_with($r['TABLE_NAME'],$c['prefix'])));
        }
        $out['ledger']=$db->query('SELECT migration,checksum FROM `'.$c['prefix'].'migrations` ORDER BY migration')->fetchAll();
        return hash('sha256', json_encode($out, JSON_THROW_ON_ERROR));
    }
    public static function rollback(string $id, ?string $key = null): void
    {
        $job=self::job($id); $latest=self::latest();
        if (($latest['id']??'')!==$id || !in_array($job['state'],['applying','awaiting_confirmation','committed','rollback_pending','rolled_back'],true)) { throw new RuntimeException('Only the latest applied checkpoint can be rolled back.'); }
        if ($key !== null && (!preg_match('/^[a-f0-9]{64}$/D',$key) || !hash_equals($job['recovery_hash'],hash('sha256',$key)))) { throw new RuntimeException('Recovery credentials were not accepted.'); }
        self::exclusive();
        // A completed rollback consumes the capability. Only a still-present
        // marker permits resuming cleanup after interrupted recovery.
        if ($job['state']==='rolled_back' && (self::active()['id']??'')!==$id) { throw new RuntimeException('This checkpoint has already been restored.'); }
        if (!hash_equals($job['schema'],self::schema())) { throw new RuntimeException('Database schema changed after this checkpoint. Automatic code rollback is refused; keep the recovery files and use reviewed hosting-panel recovery.'); }
        // Validate every old byte before replacing anything. An interrupted
        // rollback can safely repeat; it always uses these immutable originals.
        foreach ($job['changes'] as $path=>$change) {
            self::target($path);
            if ($change['before']!==null) {
                $old=self::folder().'/job-'.$id.'/old/'.hash('sha256',$path);
                if (is_link($old)||!is_file($old)||!hash_equals($change['before'],hash_file('sha256',$old))) { throw new RuntimeException('Rollback originals are incomplete or damaged. No further changes were made.'); }
            }
        }
        $job['state']='rollback_pending'; self::save($job); self::writeJson(self::folder().'/active.json',['id'=>$id,'state'=>'rollback_pending']);
        // Restore the bootstrap last; the frozen early gate protects all reads.
        $changes=$job['changes']; uksort($changes,static fn($a,$b)=>($a==='includes/bootstrap.php'?1:0)<=>($b==='includes/bootstrap.php'?1:0) ?: strcmp($a,$b));
        foreach ($changes as $path=>$change) {
            $file=self::target($path,true);
            if ($change['before']===null) { if (is_file($file)&&!@unlink($file)) { throw new RuntimeException('Cannot remove an update-added file; recovery remains locked.'); } }
            else {
                // A killed rollback resumes by skipping originals already
                // restored, instead of endlessly rewriting its first files.
                if (is_file($file) && hash_file('sha256',$file)===$change['before']) { continue; }
                self::atomic($file,(string)file_get_contents(self::folder().'/job-'.$id.'/old/'.hash('sha256',$path)),(int)$change['mode']);
            }
        }
        $job['state']='rolled_back'; $job['rolled_back_at']=gmdate('c'); self::save($job);
        if (!@unlink(self::folder().'/active.json')) { throw new RuntimeException('Code restored; maintenance marker still needs cleanup. Retry recovery.'); }
    }
}
