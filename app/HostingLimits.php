<?php
declare(strict_types=1);
namespace Spirit;

/** Read the PHP environment serving this request. Never change host settings. */
final class HostingLimits
{
    public static function bytes(string|false $value): ?int
    {
        if ($value === false) { return null; }
        $value = trim($value);
        if (!preg_match('/^-?(?:0[xX][0-9a-fA-F]+|0[bB][01]+|0[oO][0-7]+|[0-9]+)[kKmMgG]?$/D', $value)) { return null; }
        set_error_handler(static function(): never { throw new \RuntimeException('Invalid PHP quantity.'); });
        try { $bytes = ini_parse_quantity($value); }
        catch (\Throwable $error) { return null; }
        finally { restore_error_handler(); }
        return $bytes >= 0 || $value === '-1' ? $bytes : null;
    }

    public static function current(): array
    {
        return [
            'file_uploads' => in_array(strtolower((string)ini_get('file_uploads')), ['1','on','true','yes'], true),
            'upload_max_bytes' => self::bytes(ini_get('upload_max_filesize')),
            'post_max_bytes' => self::bytes(ini_get('post_max_size')),
            'memory_limit_bytes' => self::bytes(ini_get('memory_limit')),
        ];
    }

    public static function label(?int $bytes): string
    {
        if ($bytes === null) { return 'Unknown'; }
        if ($bytes === -1) { return 'No PHP limit'; }
        if ($bytes === 0) { return '0 bytes (review the PHP setting)'; }
        return number_format($bytes / 1048576, 2) . ' MiB';
    }

    public static function exceedsPost(mixed $length, ?int $limit): bool
    {
        if ($limit === null || $limit <= 0 || !is_string($length) || !preg_match('/^[0-9]+$/D', $length)) { return false; }
        // Decimal comparison avoids overflow when a request length exceeds int64.
        $length = ltrim($length, '0') ?: '0'; $maximum = (string)$limit;
        return strlen($length) > strlen($maximum) || (strlen($length) === strlen($maximum) && strcmp($length, $maximum) > 0);
    }

    public static function postTooLarge(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && self::exceedsPost($_SERVER['CONTENT_LENGTH'] ?? null, self::current()['post_max_bytes']);
    }

    public static function uploadHint(): string
    {
        $limits = self::current();
        return ($limits['file_uploads'] ? '' : 'PHP file uploads are disabled. ')
            . 'Current PHP file limit: ' . self::label($limits['upload_max_bytes'])
            . '; whole-request limit: ' . self::label($limits['post_max_bytes'])
            . '. The request also includes form fields and upload framing. Application limits and hosting-server limits still apply.';
    }

    public static function oversizedMessage(): string
    {
        return 'This upload exceeds the PHP whole-request limit. PHP discarded the submitted form before the website could read it; no requested action was performed. Choose a smaller file or ask A2 support to review this domain’s upload_max_filesize and post_max_size. Reload the form before trying again.';
    }
}
