<?php
declare(strict_types=1);
$_SERVER['SCRIPT_NAME'] = '/tests/analytics_geo_unit.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

use Spirit\AnalyticsCrawler as C;
use Spirit\AnalyticsGeo as G;
use Spirit\ThirdParty\MaxMind\Db\Reader;
use Spirit\ThirdParty\MaxMind\Db\Reader\Decoder;
use Spirit\ThirdParty\MaxMind\Db\Reader\InvalidDatabaseException;

$checks = 0;
function geoCheck(bool $ok, string $label): void
{
    global $checks;
    if (!$ok) { throw new RuntimeException($label); }
    ++$checks; echo 'PASS ' . $label . "\n";
}
function geoRejected(callable $fn, string $label): void
{
    try { $fn(); } catch (InvalidArgumentException|RuntimeException|InvalidDatabaseException $error) { geoCheck(true, $label); return; }
    throw new RuntimeException('Accepted: ' . $label);
}

$home = sys_get_temp_dir() . '/spirit-geo-' . bin2hex(random_bytes(8));
mkdir($home, 0700); putenv('SPIRIT_ACCOUNT_HOME=' . $home);
$runtime = Spirit\Paths::runtime();
mkdir($runtime, 0700); mkdir($runtime . '/analytics-geo', 0700);
$cityPath = G::cityPath();
$fixtures = __DIR__ . '/fixtures/analytics-geo';

try {
    $status = G::status();
    geoCheck($status['country_available'] && !$status['city_available'], 'Shipped country data works without an optional city database');
    geoCheck($status['city_status'] === 'missing' && $status['external_lookup'] === false, 'Missing city data and offline mode are explicit');
    $us = G::lookup('8.8.8.8');
    geoCheck($us['country_code'] === 'US' && $us['country_name'] === 'United States', 'Known IPv4 record resolves country code and English name from shipped data');
    geoCheck($us['precision'] === 'country' && $us['region'] === '' && $us['city'] === '', 'Country-only records never invent a city or region');
    geoCheck($us === G::lookup('::ffff:8.8.8.8'), 'IPv4-mapped IPv6 uses the same country record');
    $ipv6 = G::lookup('2001:4860:4860::8888');
    geoCheck($ipv6['status'] === 'ready' && strlen($ipv6['country_code']) === 2, 'Actual shipped IPv6 country lookup succeeds');
    $provenance = json_decode((string)file_get_contents(dirname(__DIR__, 2) . '/app/GeoData/country.json'), true, 16, JSON_THROW_ON_ERROR);
    geoCheck($status['data_version'] === $provenance['data_version'], 'Packaged country metadata agrees with recorded release provenance');
    geoCheck(hash_file('sha256', dirname(__DIR__, 2) . '/app/GeoData/country.mmdb') === $provenance['sha256'], 'Packaged country bytes match the recorded source checksum');
    geoCheck(!array_key_exists('ip', $us) && !array_key_exists('ip_address', $us) && !in_array('8.8.8.8', $us, true), 'Geography result does not contain the connection IP');

    foreach (['', 'garbage', '8.8.8.8,1.1.1.1', '8.8.8.8:443', 'fe80::1%eth0', str_repeat('a', 1000)] as $ip) {
        geoCheck(G::lookup($ip)['status'] === 'invalid_address', 'Malformed address has unavailable geography');
    }
    foreach (['127.0.0.1', '10.2.3.4', '172.16.0.1', '192.168.1.1', '100.64.0.1', '169.254.1.1', '192.0.2.1', '198.51.100.2', '203.0.113.3', '224.0.0.1', '255.255.255.255', '::', '::1', 'fc00::1', 'fe80::1', 'ff02::1', '2001:db8::1', '3fff::1', '::ffff:127.0.0.1'] as $ip) {
        $record = G::lookup($ip);
        geoCheck($record['status'] === 'private_or_reserved' && $record['country_code'] === '', 'Non-public address does not acquire false geography');
    }

    foreach ([
        ['203.0.113.16', '203.0.113.16/28', true],
        ['203.0.113.31', '203.0.113.16/28', true],
        ['203.0.113.15', '203.0.113.16/28', false],
        ['203.0.113.32', '203.0.113.16/28', false],
        ['255.255.255.255', '255.255.255.255/32', true],
        ['2001:db8:1234:8000::', '2001:db8:1234:8000::/49', true],
        ['2001:db8:1234:ffff:ffff:ffff:ffff:ffff', '2001:db8:1234:8000::/49', true],
        ['2001:db8:1234:7fff:ffff:ffff:ffff:ffff', '2001:db8:1234:8000::/49', false],
        ['2001:db8:1235::', '2001:db8:1234:8000::/49', false],
        ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128', true],
        ['8.8.8.8', '::/128', false], ['8.8.8.8', '0.0.0.0/0', false],
        ['8.8.8.8', '8.8.8.8/33', false], ['8.8.8.8', '8.8.8.8/-1', false],
    ] as [$ip, $cidr, $expected]) {
        geoCheck(C::ipInCidr($ip, $cidr) === $expected, 'CIDR exact boundaries and address widths are enforced');
    }

    geoCheck(C::classify('Mozilla/5.0 AppleWebKit/537.36 Chrome/130.0 Safari/537.36', '66.249.66.1') === null, 'Provider IP alone does not classify a browser as a crawler');
    $google = C::classify('Mozilla/5.0 (compatible; Googlebot/2.1)', '66.249.66.1');
    geoCheck($google['family'] === 'Google' && $google['verified'], 'Google crawler claim matches its dated official IPv4 range');
    geoCheck(C::classify('Googlebot/2.1', '2001:4860:4801:10::1')['verified'], 'Google crawler claim matches its dated official IPv6 range');
    geoCheck(!C::classify('Googlebot/2.1', '8.8.8.8')['verified'], 'Unrelated Google-owned IP does not verify a Googlebot claim');
    geoCheck(C::classify('bingbot/2.0', '157.55.39.1')['verified'], 'Bing crawler claim matches its official range');
    geoCheck(!C::classify('bingbot/2.0', '8.8.8.8')['verified'], 'Spoofed Bing user agent remains reported only');
    $yahoo = C::classify('Mozilla/5.0 (compatible; Yahoo! Slurp)', '157.55.39.1');
    geoCheck($yahoo['family'] === 'Yahoo Slurp' && $yahoo['verification'] === 'reported_ua', 'Yahoo Slurp is reported rather than mislabeled verified');
    geoCheck(C::classify('curl/8.0.1', '8.8.8.8')['family'] === 'Other automation', 'Recognizable automation remains distinct from people');
    geoCheck($google['snapshot_at'] !== '' && $google['data_version'] !== '' && !array_key_exists('ip', $google), 'Crawler provenance is returned without retaining IP');

    // Upstream synthetic MMDBs are fixture data, never a deployed country/city source.
    foreach ([
        ['MaxMind-DB-test-ipv4-24.mmdb', '1.1.1.2', '1.1.1.2', 31],
        ['MaxMind-DB-test-ipv4-24.mmdb', '1.1.1.3', '1.1.1.2', 31],
        ['MaxMind-DB-test-ipv4-24.mmdb', '1.1.1.4', '1.1.1.4', 30],
        ['MaxMind-DB-test-ipv6-24.mmdb', '::1:ffff:ffff', '::1:ffff:ffff', 128],
        ['MaxMind-DB-test-ipv6-24.mmdb', '::2:0:0', '::2:0:0', 122],
        ['MaxMind-DB-test-ipv6-24.mmdb', '::2:0:3f', '::2:0:0', 122],
    ] as [$file, $ip, $expectedIp, $expectedPrefix]) {
        $reader = new Reader($fixtures . '/' . $file);
        [$record, $prefix] = $reader->getWithPrefixLen($ip);
        geoCheck(($record['ip'] ?? null) === $expectedIp && $prefix === $expectedPrefix, 'MMDB IPv4/IPv6 range endpoints decode correctly');
        $reader->close();
    }

    geoRejected(fn() => G::validateCityFile(dirname(__DIR__, 2) . '/app/GeoData/country.mmdb'), 'Country-only database cannot masquerade as a city dataset');
    $validated = G::validateCityFile($fixtures . '/GeoIP2-City-Test.mmdb');
    geoCheck($validated['source'] === 'GeoIP2-City' && !array_key_exists('reader', $validated), 'Optional city validation exposes only safe metadata');
    copy($fixtures . '/GeoIP2-City-Test.mmdb', $cityPath); chmod($cityPath, 0600); G::reset();
    $london = G::lookup('81.2.69.142');
    geoCheck($london['country_code'] === 'GB' && $london['region'] === 'England' && $london['city'] === 'London' && $london['precision'] === 'city', 'Private city database supplies matching country, region and city');
    geoCheck(!isset($london['latitude'], $london['longitude'], $london['postal']), 'Location coordinates and postal information are not exposed for storage');
    $norway = G::lookup('2a02:cf40::');
    geoCheck($norway['country_code'] === 'NO' && $norway['city'] === '' && $norway['precision'] === 'country', 'City database without a city record remains country-only');
    chmod($cityPath, 0644); G::reset(); clearstatcache(true, $cityPath);
    geoCheck(G::status()['city_status'] === 'unsafe_permissions' && G::lookup('8.8.8.8')['country_code'] === 'US', 'Broad city-file permissions fail closed while country data still works');
    unlink($cityPath); symlink($fixtures . '/GeoIP2-City-Test.mmdb', $cityPath); G::reset();
    geoCheck(!G::status()['city_available'], 'City symlink cannot select external file content');
    unlink($cityPath);
    file_put_contents($cityPath, str_repeat("\0", 1024)); chmod($cityPath, 0600); G::reset();
    geoRejected(fn() => G::validateCityFile($cityPath), 'Malformed city metadata is rejected');
    geoCheck(G::lookup('8.8.8.8')['country_code'] === 'US', 'Malformed optional file cannot break a valid country lookup');
    unlink($cityPath);
    $oversized = fopen($cityPath, 'wb'); ftruncate($oversized, G::CITY_MAX_BYTES + 1); fclose($oversized);
    geoRejected(fn() => G::validateCityFile($cityPath), 'Oversized city source is rejected before parsing');
    unlink($cityPath);
    copy($fixtures . '/GeoIP2-City-Test-Broken-Double-Format.mmdb', $cityPath); chmod($cityPath, 0600); G::reset();
    ob_start(); $broken = G::lookup('2001:220::'); $visible = ob_get_clean();
    geoCheck($visible === '' && $broken['city'] === '' && $broken['precision'] === 'country', 'Malformed optional record stays silent and falls back to valid country data');

    // A cyclic array pointer must terminate within the pinned decoder's depth budget.
    $stream = fopen('php://memory', 'w+b'); fwrite($stream, "\x01\x04\x20\x00"); rewind($stream);
    $decoder = new Decoder($stream);
    geoRejected(fn() => $decoder->decode(0), 'Cyclic MMDB data is bounded instead of looping or exhausting memory');
    fclose($stream);
} finally {
    G::reset();
    if (is_file($cityPath) || is_link($cityPath)) { unlink($cityPath); }
    rmdir(dirname($cityPath)); rmdir($runtime); rmdir($home); putenv('SPIRIT_ACCOUNT_HOME');
}
echo $checks . " offline geography and crawler checks passed.\n";
