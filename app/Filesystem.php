<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

final class Filesystem
{
    public static function prepare(string $directory, string $documentRoot): void
    {
        if (Paths::within($directory, $documentRoot) || is_link($directory)) { throw new RuntimeException('Private storage must be outside the document root and must not be a symlink.'); }
        if (!is_dir($directory) && !@mkdir($directory, 0700)) { throw new RuntimeException('PHP cannot create private storage outside the document root. Ask the host for an account-private writable directory; no files were installed.'); }
        $real = realpath($directory);
        if (!$real || Paths::within($real, $documentRoot)) { throw new RuntimeException('Private storage resolved inside the public directory.'); }
        @chmod($directory, 0700);
        clearstatcache(true, $directory);
        if ((fileperms($directory) & 0077) !== 0) { throw new RuntimeException('The host cannot restrict private storage permissions.'); }
        if (!self::probe($directory)) { throw new RuntimeException('Private storage failed create/write/read/rename/delete verification.'); }
        foreach (['sessions', 'logs', 'media', 'backups', 'cache'] as $child) {
            $path = $directory . '/' . $child;
            if (is_link($path) || (!is_dir($path) && !@mkdir($path, 0700))) { throw new RuntimeException('Private runtime subdirectory cannot be provisioned.'); }
            @chmod($path, 0700);
        }
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
        if (is_link($file) || is_link(dirname($file))) { throw new RuntimeException('Refusing a symlink configuration target.'); }
        $temporary = dirname($file) . '/.write-' . bin2hex(random_bytes(12)) . '.php';
        $body = '<?php declare(strict_types=1); return ' . var_export($value, true) . ";\n";
        $handle = @fopen($temporary, 'x+b');
        if (!$handle) { throw new RuntimeException('Cannot create private configuration.'); }
        try {
            if (fwrite($handle, $body) !== strlen($body) || !fflush($handle)) { throw new RuntimeException('Incomplete configuration write.'); }
            if (function_exists('fsync') && !fsync($handle)) { throw new RuntimeException('Cannot flush configuration to disk.'); }
            fclose($handle); $handle = null;
            @chmod($temporary, 0600);
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
        if (is_link($directory) || realpath($directory) !== $directory) { return ['removed' => false, 'message' => 'Installer cleanup refused a symlink. Installation remains locked.']; }
        $allowed = ['index.php', 'access.php', '.htaccess', 'README.md'];
        $entries = array_values(array_diff(scandir($directory) ?: [], ['.', '..']));
        foreach ($entries as $entry) {
            if (!in_array($entry, $allowed, true) || is_dir($directory . '/' . $entry) || is_link($directory . '/' . $entry)) {
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
