<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

final class Filesystem
{
    private static function restrict(string $path, int $mode): void
    {
        if (is_link($path)) { throw new RuntimeException('Private storage must not contain symlinks.'); }
        if (!@chmod($path, $mode)) { throw new RuntimeException('Cannot restrict private storage permissions.'); }
        clearstatcache(true, $path);
        if ((fileperms($path) & 0777) !== $mode) { throw new RuntimeException('The host did not apply the required private permissions.'); }
    }
    public static function prepare(string $directory, string $documentRoot): void
    {
        if (Paths::within($directory, $documentRoot) || is_link($directory)) { throw new RuntimeException('Private storage must be outside the document root and must not be a symlink.'); }
        $parent = realpath(dirname($directory));
        if (!$parent || $parent !== dirname($directory)) { throw new RuntimeException('Private storage parent must be a canonical directory.'); }
        if (!is_dir($directory) && !@mkdir($directory, 0700)) { throw new RuntimeException('PHP cannot create account-private storage. The host must grant access to a private base; no files were installed.'); }
        $real = realpath($directory);
        if (!$real || $real !== $directory || Paths::within($real, $documentRoot)) { throw new RuntimeException('Private storage resolved to an unsafe directory.'); }
        self::restrict($directory, 0700);
        if (!self::probe($directory)) { throw new RuntimeException('Private storage failed create/write/read/rename/delete verification.'); }
        // Defense in depth if a server alias is accidentally pointed at private storage.
        // Deployment acceptance must still verify all account domain/alias roots.
        self::privateRules($directory);
        foreach (['sessions', 'logs', 'media', 'backups', 'cache'] as $child) {
            $path = $directory . '/' . $child;
            if (is_link($path) || (!is_dir($path) && !@mkdir($path, 0700))) { throw new RuntimeException('Private runtime subdirectory cannot be provisioned.'); }
            self::restrict($path, 0700);
            if (!self::probe($path)) { throw new RuntimeException('Private runtime subdirectory is not fully writable.'); }
            self::privateRules($path);
        }
    }
    private static function privateRules(string $directory): void
    {
        $path = $directory . '/.htaccess';
        if (is_link($path)) { throw new RuntimeException('Private access rules must not be a symlink.'); }
        $body = "Require all denied\n";
        if (is_file($path)) {
            if (@file_get_contents($path) !== $body) { throw new RuntimeException('Unexpected private access rules; refusing to overwrite them.'); }
        } elseif (@file_put_contents($path, $body, LOCK_EX) !== strlen($body)) { throw new RuntimeException('Cannot create private access rules.'); }
        self::restrict($path, 0600);
    }
    public static function probe(string $directory): bool
    {
        $name = $directory . '/.spirit-probe-' . bin2hex(random_bytes(10));
        $renamed = $name . '-renamed';
        $payload = random_bytes(48);
        $handle = @fopen($name, 'x+b');
        if (!$handle) { return false; }
        $ok = false;
        try {
            $ok = fwrite($handle, $payload) === strlen($payload) && fflush($handle);
            fclose($handle); $handle = null;
            $ok = $ok && @file_get_contents($name) === $payload && @rename($name, $renamed);
            $ok = $ok && @unlink($renamed);
        } finally {
            if (is_resource($handle)) { fclose($handle); }
            if (is_file($name)) { @unlink($name); }
            if (is_file($renamed)) { @unlink($renamed); }
        }
        return $ok;
    }
    public static function atomicPhp(string $file, array $value): void
    {
        if (is_link($file) || is_link(dirname($file)) || realpath(dirname($file)) !== dirname($file)) { throw new RuntimeException('Refusing an unsafe configuration target.'); }
        $temporary = dirname($file) . '/.write-' . bin2hex(random_bytes(12)) . '.php';
        $body = '<?php declare(strict_types=1); return ' . var_export($value, true) . ";\n";
        $handle = @fopen($temporary, 'x+b');
        if (!$handle) { throw new RuntimeException('Cannot create private configuration.'); }
        try {
            // Restrict the file BEFORE writing any secret bytes.
            self::restrict($temporary, 0600);
            if (fwrite($handle, $body) !== strlen($body) || !fflush($handle)) { throw new RuntimeException('Incomplete configuration write.'); }
            if (function_exists('fsync') && !fsync($handle)) { throw new RuntimeException('Cannot flush configuration to disk.'); }
            fclose($handle); $handle = null;
            if (!@rename($temporary, $file)) { throw new RuntimeException('Cannot publish private configuration atomically.'); }
        } finally {
            if (is_resource($handle)) { fclose($handle); }
            if (is_file($temporary)) { @unlink($temporary); }
        }
    }
    /** Delete only known installer files; never recurse through unknown content. */
    public static function cleanup(string $root): array
    {
        $root = realpath($root) ?: throw new RuntimeException('Invalid application root.');
        $directory = $root . '/install';
        if (!file_exists($directory) && !is_link($directory)) { return ['removed' => true, 'message' => 'Installer folder has been removed.']; }
        if (is_link($directory) || realpath($directory) !== $directory || !is_dir($directory)) { return ['removed' => false, 'message' => 'Installer cleanup refused an unsafe target. Installation remains locked.']; }
        $allowed = ['index.php', 'access.php', '.htaccess', 'README.md'];
        $scanned = @scandir($directory);
        if ($scanned === false) { return ['removed' => false, 'message' => 'The host prevented reading the installer folder. Installation remains locked.']; }
        $entries = array_values(array_diff($scanned, ['.', '..']));
        foreach ($entries as $entry) {
            if (!in_array($entry, $allowed, true) || !is_file($directory . '/' . $entry) || is_link($directory . '/' . $entry)) {
                return ['removed' => false, 'message' => 'Unexpected installer content was left untouched. Installation remains locked.'];
            }
        }
        foreach ($entries as $entry) {
            if (!@unlink($directory . '/' . $entry)) { return ['removed' => false, 'message' => 'The host prevented installer-file deletion. Installation remains locked.']; }
        }
        $removed = @rmdir($directory);
        return ['removed' => $removed, 'message' => $removed ? 'Installer folder was safely deleted.' : 'The host prevented installer-folder deletion. Installation remains locked.'];
    }
}
