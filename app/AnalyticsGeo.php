<?php
declare(strict_types=1);
namespace Spirit;

use Spirit\ThirdParty\MaxMind\Db\Reader;
use Throwable;

/** Offline, request-local IP geography. This class never sends or stores an IP. */
final class AnalyticsGeo
{
    public const CITY_MAX_BYTES = 268435456;
    private const COUNTRY_MAX_BYTES = 33554432;
    private static array $databases = [];

    /** Empty strings mean unavailable; a country result never invents a region or city. */
    public static function lookup(string $ip): array
    {
        $result = [
            'country' => '', 'country_code' => '', 'country_name' => '',
            'region' => '', 'city' => '', 'precision' => 'unavailable',
            'source' => 'DB-IP Lite Country', 'data_version' => '', 'status' => 'not_found',
        ];
        $normalized = self::normalizeIp($ip);
        if ($normalized === null) { $result['status'] = 'invalid_address'; return $result; }
        if (!self::isPublicIp($normalized)) { $result['status'] = 'private_or_reserved'; return $result; }
        $country = self::database('country');
        $result['data_version'] = $country['data_version'];
        if ($country['reader'] instanceof Reader) {
            try {
                $record = $country['reader']->get($normalized);
                if (is_array($record)) { $result = self::countryFields($record, $result); }
            } catch (Throwable) { $result['status'] = 'database_unavailable'; }
        } else { $result['status'] = 'database_unavailable'; }

        $city = self::database('city');
        if ($city['reader'] instanceof Reader) {
            try {
                $record = $city['reader']->get($normalized);
                if (is_array($record)) {
                    $candidate = self::countryFields($record, $result);
                    // Use the country attached to the same city record; never mix two countries.
                    $cityCountry = $record['country']['iso_code'] ?? '';
                    if (is_string($cityCountry) && preg_match('/^[A-Z]{2}$/D', $cityCountry)
                        && !in_array($cityCountry, ['ZZ', 'XX'], true) && $candidate['country_code'] === $cityCountry) {
                        $candidate['region'] = self::label($record['subdivisions'][0]['names']['en'] ?? '');
                        $candidate['city'] = self::label($record['city']['names']['en'] ?? '');
                        $candidate['precision'] = $candidate['city'] !== '' ? 'city' : ($candidate['region'] !== '' ? 'region' : 'country');
                        $candidate['source'] = $city['source'];
                        $candidate['data_version'] = $city['data_version'];
                        $result = $candidate;
                    }
                }
            } catch (Throwable) { /* A bad optional record cannot discard a valid country result. */ }
        }
        return $result;
    }

    public static function status(): array
    {
        $country = self::database('country'); $city = self::database('city');
        return [
            'available' => $country['status'] === 'ready',
            'status' => $country['status'],
            'source' => 'DB-IP Lite Country',
            'data_version' => $country['data_version'],
            'country_available' => $country['status'] === 'ready',
            'country_status' => $country['status'],
            'country_version' => $country['data_version'],
            'city_available' => $city['status'] === 'ready',
            'city_status' => $city['status'],
            'city_source' => $city['source'],
            'city_version' => $city['data_version'],
            'precision' => $city['status'] === 'ready' ? 'city_when_available' : 'country',
            'attribution_label' => 'IP Geolocation by DB-IP',
            'attribution_url' => 'https://db-ip.com',
            'license' => 'CC-BY-4.0',
            'license_url' => 'https://creativecommons.org/licenses/by/4.0/',
            'approximate' => true,
            'external_lookup' => false,
        ];
    }

    /** Fixed owner-private destination; never obtained from a request parameter. */
    public static function cityPath(): string { return Paths::runtime() . '/analytics-geo/city.mmdb'; }

    /** Validate an owner-selected local upload before private placement. Does not publish or move it. */
    public static function validateCityFile(string $path): array
    {
        $database = self::openDatabase($path, 'city');
        if (!($database['reader'] instanceof Reader)) {
            throw new \InvalidArgumentException('Choose a valid City MMDB file no larger than 256 MiB.');
        }
        $database['reader']->close();
        unset($database['reader']);
        return $database;
    }

    /** Call after a successful owner-side data replacement, never for visitor input. */
    public static function reset(): void
    {
        foreach (self::$databases as $database) {
            if (($database['reader'] ?? null) instanceof Reader) {
                try { $database['reader']->close(); } catch (Throwable) { }
            }
        }
        self::$databases = [];
    }

    public static function crawler(string $ua, string $ip): ?array { return AnalyticsCrawler::classify($ua, $ip); }

    public static function normalizeIp(string $ip): ?string
    {
        if (strlen($ip) > 45 || filter_var($ip, FILTER_VALIDATE_IP) === false) { return null; }
        $packed = inet_pton($ip);
        if ($packed === false) { return null; }
        if (strlen($packed) === 16 && substr($packed, 0, 12) === str_repeat("\0", 10) . "\xff\xff") {
            return inet_ntop(substr($packed, 12)) ?: null;
        }
        return inet_ntop($packed) ?: null;
    }

    public static function isPublicIp(string $ip): bool
    {
        $ip = self::normalizeIp($ip) ?? '';
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) { return false; }
        $packed = inet_pton($ip);
        if (strlen($packed) === 4) {
            $first = ord($packed[0]); $second = ord($packed[1]); $third = ord($packed[2]);
            return !($first >= 224 || $first === 0 || ($first === 100 && $second >= 64 && $second <= 127)
                || ($first === 192 && $second === 0 && ($third === 0 || $third === 2))
                || ($first === 198 && ($second === 18 || $second === 19 || ($second === 51 && $third === 100)))
                || ($first === 203 && $second === 0 && $third === 113));
        }
        // Global unicast only. Exclude documentation, benchmarking, ORCHID and transition ranges.
        if ((ord($packed[0]) & 0xe0) !== 0x20) { return false; }
        $hex = bin2hex($packed);
        return !(str_starts_with($hex, '20010db8') || str_starts_with($hex, '3fff0')
            || str_starts_with($hex, '20010000') || str_starts_with($hex, '200100020000')
            || str_starts_with($hex, '2001001') || str_starts_with($hex, '2001002')
            || str_starts_with($hex, '2002'));
    }

    private static function countryFields(array $record, array $result): array
    {
        $country = $record['country'] ?? [];
        $code = is_array($country) ? ($country['iso_code'] ?? '') : '';
        if (!is_string($code) || !preg_match('/^[A-Z]{2}$/D', $code) || in_array($code, ['ZZ', 'XX'], true)) { return $result; }
        $result['country'] = $result['country_code'] = $code;
        $result['country_name'] = self::label($country['names']['en'] ?? '') ?: $code;
        $result['precision'] = 'country'; $result['status'] = 'ready';
        return $result;
    }

    private static function label(mixed $value): string
    {
        if (!is_string($value) || strlen($value) > 160 || !preg_match('//u', $value)) { return ''; }
        return trim(preg_replace('/[\x00-\x1f\x7f]/u', '', $value) ?? '');
    }

    private static function database(string $kind): array
    {
        if (isset(self::$databases[$kind])) { return self::$databases[$kind]; }
        $result = ['reader' => null, 'status' => 'missing', 'source' => '', 'data_version' => ''];
        try {
            $path = $kind === 'country' ? __DIR__ . '/GeoData/country.mmdb' : self::cityPath();
            if (!is_file($path) || is_link($path) || realpath($path) !== $path) { return self::$databases[$kind] = $result; }
            if ($kind === 'city' && ((fileperms($path) & 0077) !== 0 || (fileperms(dirname($path)) & 0077) !== 0)) {
                $result['status'] = 'unsafe_permissions'; return self::$databases[$kind] = $result;
            }
            $result = self::openDatabase($path, $kind);
        } catch (Throwable) { $result['status'] = 'unavailable'; }
        return self::$databases[$kind] = $result;
    }

    private static function openDatabase(string $path, string $kind): array
    {
        $result = ['reader' => null, 'status' => 'invalid', 'source' => '', 'data_version' => ''];
        if (PHP_INT_SIZE < 8) { $result['status'] = 'unsupported_platform'; return $result; }
        if (str_contains($path, "\0") || !str_starts_with($path, DIRECTORY_SEPARATOR) || !is_file($path)
            || is_link($path) || realpath($path) !== $path || !is_readable($path)) { return $result; }
        $size = filesize($path);
        $max = $kind === 'country' ? self::COUNTRY_MAX_BYTES : self::CITY_MAX_BYTES;
        if (!is_int($size) || $size < 512 || $size > $max) { return $result; }
        self::loadReader();
        $reader = null;
        // Corrupt metadata may otherwise emit PHP notices. Do not expose database paths in logs/output.
        set_error_handler(static function (int $severity, string $message): never { throw new \RuntimeException('Invalid offline geography data.'); });
        try {
            $reader = new Reader($path);
            $metadata = $reader->metadata();
            if ($metadata->binaryFormatMajorVersion !== 2 || !in_array($metadata->ipVersion, [4, 6], true)
                || !in_array($metadata->recordSize, [24, 28, 32], true) || !is_int($metadata->nodeCount)
                || $metadata->nodeCount < 1 || $metadata->searchTreeSize + 16 >= $size
                || !is_int($metadata->buildEpoch) || $metadata->buildEpoch < 946684800 || $metadata->buildEpoch > time() + 86400
                || !is_string($metadata->databaseType) || strlen($metadata->databaseType) > 100
                || ($kind === 'country' ? $metadata->databaseType !== 'DBIP-Country-Lite' : !preg_match('/(?:^|[-_])City(?:[-_]|$)/i', $metadata->databaseType))) {
                throw new \RuntimeException('Unsupported offline geography data.');
            }
            // Exercise both lookup paths before exposing availability. Record decoding remains bounded by upstream limits.
            $reader->get('8.8.8.8');
            if ($metadata->ipVersion === 6) { $reader->get('2001:4860:4860::8888'); }
            $result = [
                'reader' => $reader, 'status' => 'ready',
                'source' => $kind === 'country' ? 'DB-IP Lite Country' : self::label($metadata->databaseType),
                'data_version' => gmdate($kind === 'country' ? 'Y-m' : 'Y-m-d', $metadata->buildEpoch),
            ];
        } catch (Throwable) {
            if ($reader instanceof Reader) { try { $reader->close(); } catch (Throwable) { } }
        } finally { restore_error_handler(); }
        return $result;
    }

    private static function loadReader(): void
    {
        if (class_exists(Reader::class, false)) { return; }
        foreach (['Reader/InvalidDatabaseException.php', 'Reader/Util.php', 'Reader/Metadata.php', 'Reader/Decoder.php', 'Reader.php'] as $file) {
            require_once __DIR__ . '/ThirdParty/MaxMind/' . $file;
        }
    }
}
