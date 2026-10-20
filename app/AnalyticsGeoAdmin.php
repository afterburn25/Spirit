<?php
declare(strict_types=1);
namespace Spirit;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

/** Owner-managed offline geography. Uploaded names never become filesystem paths. */
final class AnalyticsGeoAdmin
{
    private const ROUTE = 'admin/analytics/geography.php';

    public static function render(): void
    {
        $user = ContentAdmin::boot(); $error = null;
        header('Cache-Control: private, no-store');
        header('X-Robots-Tag: noindex, nofollow');
        header('Referrer-Policy: no-referrer');
        if (HostingLimits::postTooLarge()) {
            http_response_code(413); $error = HostingLimits::oversizedMessage();
        } elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            Security::checkPost();
            try {
                $action = Input::choice($_POST, 'action', ['upload', 'remove'], '');
                if ($action === 'upload') {
                    $info = self::upload(is_array($_FILES['city_database'] ?? null) ? $_FILES['city_database'] : [], (int)$user['id']);
                    flash('City database installed privately. New visits can use available region and city estimates.');
                } elseif ($action === 'remove') {
                    $removed = self::remove((int)$user['id']);
                    flash($removed ? 'City database removed. Country reporting continues and saved visit history is unchanged.' : 'No City database is installed. Saved visit history is unchanged.');
                } else { throw new InvalidArgumentException('Choose an available geography action.'); }
                go(self::ROUTE);
            } catch (InvalidArgumentException|RuntimeException $exception) { $error = Storefront::error($exception); }
        } elseif (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
            header('Allow: GET, HEAD, POST'); http_response_code(405); exit('Method not allowed.');
        }
        $geography = AnalyticsGeo::status(); $limits = HostingLimits::current();
        $gzipAvailable = function_exists('inflate_init') && function_exists('inflate_add') && function_exists('inflate_get_status') && function_exists('inflate_get_read_len');
        View::start('Analytics geography', 'admin', 'analytics', $user);
        require Paths::root() . '/views/admin/analytics-geography.php';
        View::end();
    }

    public static function upload(array $upload, int $actor): array
    {
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'] ?? null)
            || !is_string($upload['name'] ?? null) || !is_uploaded_file($upload['tmp_name'])) {
            throw new InvalidArgumentException('The upload did not complete. Choose the City database again and check the hosting file and whole-request limits below.');
        }
        $name = $upload['name']; $source = $upload['tmp_name'];
        if (strlen($name) > 255 || str_contains($name, "\0") || !preg_match('/\.mmdb(?:\.gz)?$/iD', $name)) {
            throw new InvalidArgumentException('Choose a City database ending in .mmdb or .mmdb.gz. CSV, ZIP and other archives are not supported.');
        }
        if (is_link($source) || realpath($source) !== $source || !is_file($source)) { throw new InvalidArgumentException('The uploaded database is unavailable. Choose the file again.'); }
        $size = filesize($source);
        if (!is_int($size) || $size < 1 || $size > AnalyticsGeo::CITY_MAX_BYTES) {
            throw new InvalidArgumentException('The upload and its expanded database must each be no larger than 256 MiB. Your hosting limit may be lower.');
        }
        $directory = self::directory(true); $lock = self::lock($directory); $temporary = null;
        try {
            self::target($directory);
            if (!Filesystem::probe($directory)) { throw new RuntimeException('Private geography storage failed its create, write, read, rename and delete checks. Ask A2 to check private storage access.'); }
            self::cleanInterruptedUploads($directory);
            $temporary = $directory . '/.city-stage-' . bin2hex(random_bytes(16)) . '.mmdb';
            self::stage($source, $temporary, str_ends_with(strtolower($name), '.gz'), $size);
            // The permission check observed this file before streaming its contents.
            // Validate the completed file, not PHP's cached zero-byte stat result.
            clearstatcache(true, $temporary);
            $info = AnalyticsGeo::validateCityFile($temporary);
            self::withOwnerLock($actor, static function () use ($directory, $temporary): void {
                // Recheck both directories and the fixed target immediately before publication.
                if (self::directory(false) !== $directory) { throw new RuntimeException('Private geography storage changed. Reload and try again.'); }
                $target = self::target($directory);
                if (!@rename($temporary, $target)) { throw new RuntimeException('The host could not replace the City database atomically. The previous database is unchanged.'); }
            });
            $temporary = null; AnalyticsGeo::reset();
            return $info;
        } finally {
            if ($temporary !== null && is_file($temporary) && !is_link($temporary)) { @unlink($temporary); }
            flock($lock, LOCK_UN); fclose($lock);
        }
    }

    public static function remove(int $actor): bool
    {
        $directory = self::directory(false);
        if ($directory === null) { return self::withOwnerLock($actor, static fn(): bool => false); }
        $lock = self::lock($directory);
        try {
            $removed = self::withOwnerLock($actor, static function () use ($directory): bool {
                if (self::directory(false) !== $directory) { throw new RuntimeException('Private geography storage changed. Reload and try again.'); }
                $target = self::target($directory);
                if (!is_file($target)) { return false; }
                if (!@unlink($target)) { throw new RuntimeException('The host could not remove the City database. The existing file is still installed.'); }
                return true;
            });
            AnalyticsGeo::reset(); return $removed;
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }

    /** A read-only transaction holds the current owner's row through the filesystem action. */
    private static function withOwnerLock(int $actor, callable $operation): mixed
    {
        $db = Database::connect(); $db->beginTransaction();
        try {
            $query = $db->prepare('SELECT id,role,active,session_version FROM ' . Database::table('users') . ' WHERE id=? FOR UPDATE');
            $query->execute([$actor]); $owner = $query->fetch();
            if (!$owner || !Permissions::allows($owner, 'owner') || (int)($_SESSION['user_id'] ?? 0) !== $actor
                || (int)($_SESSION['session_version'] ?? 0) !== (int)$owner['session_version']) {
                throw new RuntimeException('Owner access changed. Sign in again before changing geography data.');
            }
            return $operation();
        } finally {
            // This transaction never writes SQL. Releasing its lock cannot undo a published file.
            if ($db->inTransaction()) { try { $db->rollBack(); } catch (Throwable) { } }
        }
    }

    private static function directory(bool $create): ?string
    {
        $home = Paths::accountHome(); $runtime = Paths::runtime();
        // Canonical paths exclude symlinks in every ancestor. Other-writable ancestors
        // are unsafe unless protected by the sticky bit (for example a private test home in /tmp).
        for ($ancestor = $home; ; $ancestor = dirname($ancestor)) {
            $mode = @fileperms($ancestor);
            if (is_link($ancestor) || realpath($ancestor) !== $ancestor || !is_dir($ancestor) || $mode === false
                || (($mode & 0022) !== 0 && ($mode & 01000) === 0)) {
                throw new RuntimeException('Private geography storage has an unsafe ancestor. Ask A2 to check the account-private storage location.');
            }
            if ($ancestor === DIRECTORY_SEPARATOR) { break; }
        }
        self::privateDirectory($runtime);
        $directory = $runtime . '/analytics-geo';
        if (!file_exists($directory) && !is_link($directory)) {
            if (!$create) { return null; }
            if (!@mkdir($directory, 0700)) { throw new RuntimeException('PHP could not create private geography storage. Ask A2 to check account-private write access.'); }
        }
        self::privateDirectory($directory);
        if ($create) {
            $rules = $directory . '/.htaccess';
            if (file_exists($rules) || is_link($rules)) {
                self::privateFile($rules);
                if (@file_get_contents($rules) !== "Require all denied\n") { throw new RuntimeException('Unexpected private geography access rules were left unchanged.'); }
            } else {
                $handle = @fopen($rules, 'x+b');
                if (!$handle) { throw new RuntimeException('Private geography access rules could not be created.'); }
                try {
                    self::restrictNewFile($rules);
                    if (fwrite($handle, "Require all denied\n") !== 19 || !fflush($handle)) { throw new RuntimeException('Private geography access rules could not be written.'); }
                } finally { fclose($handle); }
            }
        }
        return $directory;
    }

    private static function privateDirectory(string $directory): void
    {
        clearstatcache(true, $directory);
        if (!is_dir($directory) || is_link($directory) || realpath($directory) !== $directory || (fileperms($directory) & 0777) !== 0700) {
            throw new RuntimeException('Private geography storage requires canonical directories with private 0700 permissions. Unsafe storage was left unchanged.');
        }
    }

    private static function privateFile(string $path): void
    {
        clearstatcache(true, $path);
        if (!is_file($path) || is_link($path) || realpath($path) !== $path || (fileperms($path) & 0777) !== 0600) {
            throw new RuntimeException('Private geography data requires a regular file with private 0600 permissions. Unsafe files were left unchanged.');
        }
    }

    private static function restrictNewFile(string $path): void
    {
        if (is_link($path) || !@chmod($path, 0600)) { throw new RuntimeException('The host could not protect the staged geography file.'); }
        self::privateFile($path);
    }

    private static function target(string $directory): string
    {
        $target = $directory . '/city.mmdb';
        if (file_exists($target) || is_link($target)) { self::privateFile($target); }
        return $target;
    }

    private static function lock(string $directory): mixed
    {
        $path = $directory . '/.update.lock';
        $exists = file_exists($path) || is_link($path);
        if ($exists) { self::privateFile($path); }
        $handle = @fopen($path, $exists ? 'r+b' : 'x+b');
        if (!$handle) { throw new RuntimeException('Private geography storage is busy or unavailable. Try again shortly.'); }
        try {
            if (!$exists) { self::restrictNewFile($path); }
            $opened = fstat($handle); $named = lstat($path);
            if (!$opened || !$named || $opened['ino'] !== $named['ino'] || $opened['dev'] !== $named['dev'] || !flock($handle, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('A geography update is already running or its storage changed. Try again shortly.');
            }
            return $handle;
        } catch (Throwable $exception) { fclose($handle); throw $exception; }
    }

    private static function stage(string $source, string $destination, bool $gzip, int $sourceSize): void
    {
        if ($gzip && (!function_exists('inflate_init') || !function_exists('inflate_add') || !function_exists('inflate_get_status') || !function_exists('inflate_get_read_len'))) {
            throw new RuntimeException('This PHP host cannot expand gzip files. Extract the .mmdb on your computer and upload that file, or enable PHP zlib in cPanel.');
        }
        $input = @fopen($source, 'rb'); $output = @fopen($destination, 'x+b');
        if (!$input || !$output) {
            if (is_resource($input)) { fclose($input); } if (is_resource($output)) { fclose($output); }
            throw new RuntimeException('The database could not be staged in private storage. The previous database is unchanged.');
        }
        try {
            self::restrictNewFile($destination); $total = 0; $read = 0; $deadline = microtime(true) + 20;
            $inflate = $gzip ? @inflate_init(ZLIB_ENCODING_GZIP) : null;
            if ($gzip && $inflate === false) { throw new RuntimeException('PHP could not start gzip extraction. Upload the extracted .mmdb file.'); }
            // Small compressed chunks bound each inflate allocation to approximately 1 MiB.
            while (!feof($input)) {
                $chunk = fread($input, $gzip ? 1024 : 65536);
                if ($chunk === false || ($chunk === '' && !feof($input))) { throw new InvalidArgumentException('The uploaded database could not be read completely.'); }
                if ($chunk === '') { break; }
                $read += strlen($chunk);
                $bytes = $gzip ? @inflate_add($inflate, $chunk, ZLIB_SYNC_FLUSH) : $chunk;
                if ($bytes === false) { throw new InvalidArgumentException('The gzip file is damaged. Download the City MMDB again.'); }
                $total += strlen($bytes);
                if ($total > AnalyticsGeo::CITY_MAX_BYTES || $read > AnalyticsGeo::CITY_MAX_BYTES) { throw new InvalidArgumentException('The upload and its expanded database must each be no larger than 256 MiB.'); }
                if (microtime(true) > $deadline) { throw new RuntimeException('The upload exceeded the processing time limit. Try the extracted .mmdb file or ask A2 to check hosting resources.'); }
                $offset = 0; $length = strlen($bytes);
                while ($offset < $length) {
                    $written = fwrite($output, substr($bytes, $offset));
                    if (!is_int($written) || $written < 1) { throw new RuntimeException('The host could not finish writing the database. The previous database is unchanged.'); }
                    $offset += $written;
                }
                if ($gzip && inflate_get_status($inflate) === ZLIB_STREAM_END) { break; }
            }
            if ($read !== $sourceSize || $total < 512 || ($gzip && (inflate_get_status($inflate) !== ZLIB_STREAM_END || inflate_get_read_len($inflate) !== $sourceSize))) {
                throw new InvalidArgumentException('Choose a complete City MMDB or a single, undamaged .mmdb.gz file.');
            }
            if (!fflush($output) || (function_exists('fsync') && !fsync($output))) { throw new RuntimeException('The host could not flush the staged database. The previous database is unchanged.'); }
        } finally { fclose($input); fclose($output); }
    }

    /** Remove only our abandoned staging files, never an installed or unknown file. */
    private static function cleanInterruptedUploads(string $directory): void
    {
        $handle = @opendir($directory); if (!$handle) { return; }
        $checked = 0;
        try {
            while (($name = readdir($handle)) !== false && $checked++ < 100) {
                if (!preg_match('/^\.city-stage-[a-f0-9]{32}\.mmdb$/D', $name)) { continue; }
                $path = $directory . '/' . $name;
                if (!is_link($path) && is_file($path) && realpath($path) === $path && (fileperms($path) & 0777) === 0600 && filemtime($path) < time() - 86400) { @unlink($path); }
            }
        } finally { closedir($handle); }
    }
}
