<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** Bounded, regenerable image copies. Media::serve authorizes every request first. */
final class ImageVariants
{
    public const WIDTHS = [320, 640, 960, 1440];
    private const MAX_PIXELS = 20000000;

    public static function requestWidth(mixed $value): int
    {
        if ($value === null) { return 0; }
        if (!is_string($value) || !in_array($value, array_map('strval', self::WIDTHS), true)) {
            throw new InvalidArgumentException('Unsupported image size.');
        }
        return (int)$value;
    }

    public static function geometry(int $width, int $height, int $target): array
    {
        if ($width < 1 || $height < 1 || $width > 10000 || $height > 10000 || $width * $height > self::MAX_PIXELS || !in_array($target, self::WIDTHS, true)) {
            throw new InvalidArgumentException('Unsupported image dimensions.');
        }
        $outWidth = min($width, $target);
        return [$outWidth, max(1, (int)round($height * $outWidth / $width))];
    }

    /** GD allocations are not uniformly accounted for by PHP. Budget conservatively. */
    public static function memoryFits(int $width, int $height, int $target, int $available): bool
    {
        try { [$outWidth, $outHeight] = self::geometry($width, $height, $target); }
        catch (InvalidArgumentException $e) { return false; }
        return ($width * $height + $outWidth * $outHeight) * 8 + 24 * 1024 * 1024 <= $available;
    }

    public static function canDecode(int $width, int $height): bool
    {
        return self::memoryFits($width, $height, self::WIDTHS[0], self::availableMemory());
    }

    private static function availableMemory(): int
    {
        $setting = trim((string)ini_get('memory_limit'));
        $limit = 256 * 1024 * 1024; // Also bound work when PHP is unlimited.
        if (preg_match('/^(\d+)\s*([KMG]?)$/iD', $setting, $match)) {
            $power = match(strtoupper($match[2])) { 'K'=>1, 'M'=>2, 'G'=>3, default=>0 };
            $limit = min($limit, (int)$match[1] * (1024 ** $power));
        }
        return max(0, $limit - memory_get_usage(true));
    }

    public static function cacheName(string $checksum, int $width, string $extension): string
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $checksum) || !in_array($width, self::WIDTHS, true) || !in_array($extension, ['jpg','png','webp'], true)) {
            throw new InvalidArgumentException('Invalid image-cache identity.');
        }
        return 'image-v1-'.$checksum.'-'.$width.'.'.$extension;
    }

    public static function disposableName(string $name): bool
    {
        return (bool)preg_match('/^(?:image-v1-[a-f0-9]{64}-(?:320|640|960|1440)\.(?:jpg|png|webp)|\.image-v1-[a-f0-9]{24}\.tmp)$/D', $name);
    }

    private static function original(array $media, string $file): array
    {
        return ['file'=>$file, 'mime'=>$media['mime'], 'etag'=>'"'.$media['checksum'].'"', 'resized'=>false];
    }

    private static function cached(string $file, int $width, int $height, string $mime): ?array
    {
        clearstatcache(true, $file);
        if (is_link($file) || !is_file($file) || (fileperms($file) & 0077) !== 0) { return null; }
        $size = filesize($file);
        if ($size === false || $size < 1 || $size > 12 * 1024 * 1024) { return null; }
        $info = @getimagesize($file);
        if (!$info || $info[0] !== $width || $info[1] !== $height || $info['mime'] !== $mime) { return null; }
        $hash = hash_file('sha256', $file);
        if ($hash === false) { return null; }
        return ['file'=>$file, 'mime'=>$mime, 'etag'=>'"'.$hash.'"', 'resized'=>true];
    }

    public static function resolve(array $media, int $width): array
    {
        $source = Media::path($media); // A cache hit never conceals a missing original.
        $fallback = self::original($media, $source);
        if ($width === 0 || $media['kind'] !== 'image') { return $fallback; }
        if (!in_array($width, self::WIDTHS, true)) { throw new InvalidArgumentException('Unsupported image size.'); }
        $info = @getimagesize($source);
        if (!$info || $width >= $info[0]) { return $fallback; } // Never upscale.
        try { [$outWidth, $outHeight] = self::geometry($info[0], $info[1], $width); }
        catch (InvalidArgumentException $e) { return $fallback; }
        if (!extension_loaded('gd')) { return $fallback; }
        $originalExtension = pathinfo($media['storage_name'], PATHINFO_EXTENSION);
        $extension = function_exists('imagewebp') && (imagetypes() & IMG_WEBP) ? 'webp' : $originalExtension;
        $mime = match($extension) { 'webp'=>'image/webp', 'png'=>'image/png', default=>'image/jpeg' };
        $directory = Paths::runtime().'/cache';
        if (is_link($directory) || !is_dir($directory) || realpath($directory) !== $directory || (fileperms($directory) & 0077) !== 0) { return $fallback; }
        $target = $directory.'/'.self::cacheName($media['checksum'], $width, $extension);
        if ($cached = self::cached($target, $outWidth, $outHeight, $mime)) { return $cached; }
        if (is_link($target) || !is_writable($directory) || !self::memoryFits($info[0], $info[1], $width, self::availableMemory())) { return $fallback; }

        // One nonblocking generation lock bounds concurrent GD allocations on
        // shared hosting. Busy requests receive the original rather than wait.
        $lockPath = $directory.'/.image-variants.lock';
        if (is_link($lockPath) || (file_exists($lockPath) && !is_file($lockPath))) { return $fallback; }
        $lock = @fopen($lockPath, 'c+b');
        if (!$lock) { return $fallback; }
        if (!@chmod($lockPath, 0600) || !flock($lock, LOCK_EX | LOCK_NB)) { fclose($lock); return $fallback; }
        $temporary = null; $input = $output = null;
        try {
            if ($cached = self::cached($target, $outWidth, $outHeight, $mime)) { return $cached; }
            if (is_link($target) || filesize($source) !== (int)$media['byte_size'] || hash_file('sha256', $source) !== $media['checksum']) { return $fallback; }
            $input = match($originalExtension) {
                'jpg'=>@imagecreatefromjpeg($source), 'png'=>@imagecreatefrompng($source),
                'webp'=>function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
                default=>false
            };
            if (!$input) { return $fallback; }
            $output = imagecreatetruecolor($outWidth, $outHeight);
            imagealphablending($output, false); imagesavealpha($output, true);
            imagefill($output, 0, 0, imagecolorallocatealpha($output, 0, 0, 0, 127));
            if (!imagecopyresampled($output, $input, 0, 0, 0, 0, $outWidth, $outHeight, $info[0], $info[1])) { return $fallback; }
            $temporary = $directory.'/.image-v1-'.bin2hex(random_bytes(12)).'.tmp';
            $handle = @fopen($temporary, 'x+b');
            if (!$handle) { return $fallback; }
            try {
                if (!@chmod($temporary, 0600)) { return $fallback; }
                $ok = match($extension) { 'webp'=>imagewebp($output,$handle,84), 'png'=>imagepng($output,$handle,6), default=>imagejpeg($output,$handle,86) };
                if (!$ok || !fflush($handle)) { return $fallback; }
            } finally { fclose($handle); }
            // Some GD encoders can return true despite failed output; validate
            // actual image bytes and dimensions before publishing the cache.
            if (!self::cached($temporary, $outWidth, $outHeight, $mime) || is_link($target) || !@rename($temporary, $target)) { return $fallback; }
            $temporary = null;
            return self::cached($target, $outWidth, $outHeight, $mime) ?? $fallback;
        } catch (Throwable $e) {
            // Optimization failure must not take a valid approved image offline.
            return $fallback;
        } finally {
            if ($input instanceof \GdImage) { imagedestroy($input); }
            if ($output instanceof \GdImage) { imagedestroy($output); }
            if ($temporary !== null && is_file($temporary) && !is_link($temporary)) { @unlink($temporary); }
            flock($lock, LOCK_UN); fclose($lock);
        }
    }
}
