<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;

/** USD minor units only. Never calculate a price using binary floating point. */
final class Money
{
    public const MAX_CENTS = 9999999;
    public static function parse(string $value): int
    {
        if (!preg_match('/^(0|[1-9][0-9]{0,4})(?:\.([0-9]{1,2}))?$/D', $value, $m)) {
            throw new InvalidArgumentException('Use a USD price such as 25.00, without commas or a currency symbol.');
        }
        return ((int)$m[1] * 100) + (int)str_pad($m[2] ?? '', 2, '0');
    }
    public static function decimal(int $cents): string
    {
        if ($cents < 0) { throw new InvalidArgumentException('Negative price.'); }
        return intdiv($cents, 100) . '.' . str_pad((string)($cents % 100), 2, '0', STR_PAD_LEFT);
    }
    public static function format(int $cents): string { return '$' . self::decimal($cents); }
}
