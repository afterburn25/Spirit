<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/includes/bootstrap.php';
use Spirit\{Analytics as A, AnalyticsReport as R};

$checks = 0;
function check(bool $ok, string $label): void { global $checks; if (!$ok) { throw new RuntimeException($label); } echo 'PASS ' . $label . "\n"; $checks++; }
function rejects(callable $fn, string $label): void { try { $fn(); } catch (InvalidArgumentException) { check(true, $label); return; } check(false, $label); }

foreach (['/admin/', '/account/', '/shop/cart.php', '/shop/checkout.php', '/newsletter/confirm.php?token=private', '/media/file.php?id=4', '/booking/thanks.php', '/pages/search.php?q=email@example.test', '/install/', '/upgrades/', '/music/%2e%2e/admin/', '/music//release.php?slug=album', '/../../config.php'] as $path) {
    check(A::normalisePath($path) === null, 'Private, asset, query or malformed route is excluded: ' . $path);
}
check(A::normalisePath('/band/music/release.php?slug=real-album&email=private@example.test&token=secret', '/band') === '/music/release.php?slug=real-album', 'Only the validated public slug survives a subfolder detail URL');
check(A::normalisePath('/pages/?slug=music&utm_campaign=unused') === '/music/', 'A public section alias folds into its canonical path');
check(A::normalisePath('/shop/?q=private&customer=42') === '/shop/', 'Catalog query strings do not enter page-path analytics');
check(A::normalisePath('/pages/?slug[]=hidden') === null, 'Array slugs cannot enter page-path analytics');
check(A::normalisePath('/other/music/', '/band') === null, 'A path outside this installation is rejected');

$request = ['REQUEST_METHOD' => 'GET'];
check(A::requestAllowed($request), 'Ordinary public GET can be considered');
foreach ([['REQUEST_METHOD' => 'HEAD'], ['REQUEST_METHOD' => 'POST'], ['HTTP_DNT' => '1'], ['HTTP_SEC_GPC' => '1'], ['HTTP_SEC_PURPOSE' => 'prefetch;prerender'], ['HTTP_PURPOSE' => 'prefetch'], ['HTTP_X_MOZ' => 'prefetch']] as $override) {
    check(!A::requestAllowed(array_replace($request, $override)), 'Non-visit and privacy-preference request is ignored');
}
check(!A::requestAllowed($request, ['user_id' => 1]), 'Signed-in staff traffic is excluded before querying analytics');

$attribution = A::acquisition('https://www.google.com/search?q=live+blues&token=not-for-storage#private', 'spiritualgorillaz.com', ['utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => 'autumn-music', 'email' => 'private@example.test']);
check($attribution === ['referrer_domain' => 'google.com', 'search_term' => 'live blues', 'utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => 'autumn-music'], 'Acquisition retains only domain, supplied known search term and three sanitized UTM fields');
check(A::acquisition('https://www.spiritualgorillaz.com/account/?token=private', 'spiritualgorillaz.com', [])['referrer_domain'] === '', 'Internal navigation does not become an acquisition source');
check(A::acquisition('https://example.test/path?q=secret&email=private', 'band.test', [])['search_term'] === '', 'Unrecognized query parameters are never treated as search terms');
check(A::acquisition('https://search.yahoo.com/search?p=live+music', 'band.test', [])['search_term'] === 'live music', 'Known Yahoo supplied terms are supported');
check(A::acquisition('https://www.google.com/search?ved=token-only', 'band.test', [])['search_term'] === '', 'Unavailable search terms are left unavailable rather than invented');
foreach (['private@example.test', 'https://private.test/token', '<script>attack</script>', "injected\nvalue", str_repeat('a', 64), ['nested'], 'secret=' . str_repeat('b', 40)] as $label) {
    check(A::label($label) === '', 'Sensitive, malformed or token-like attribution is discarded');
}
check(A::label('Música ao vivo', 80) === 'Música ao vivo', 'Safe international campaign/search labels retain valid UTF-8');

$edge = A::clientFamilies('Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0');
check($edge === ['browser_family' => 'Microsoft Edge', 'os_family' => 'Windows', 'device_family' => 'Desktop'], 'Browser classification stores families without full UA or versions');
check(A::clientFamilies('Mozilla/5.0 (Linux; Android 14; Tablet) Chrome/140.0 Safari/537.36')['device_family'] === 'Tablet', 'Android tablet family differs from mobile');
check(A::clientFamilies('Mozilla/5.0 (iPhone; CPU iPhone OS 18_0) AppleWebKit/605.1.15 Version/18 Mobile/15 Safari/604.1')['device_family'] === 'Mobile', 'Phone UA is a mobile family only');

$origin = ['HTTP_ORIGIN' => 'https://band.test', 'HTTP_SEC_FETCH_SITE' => 'same-origin'];
check(A::sameOrigin($origin, 'https://band.test/subfolder'), 'Same-origin collector accepts a legitimate origin for a subfolder installation');
foreach ([['HTTP_ORIGIN' => 'https://band.test.evil.test'], ['HTTP_ORIGIN' => 'null'], ['HTTP_ORIGIN' => 'http://band.test'], ['HTTP_ORIGIN' => 'https://band.test:444'], ['HTTP_ORIGIN' => 'https://band.test/private'], ['HTTP_SEC_FETCH_SITE' => 'cross-site'], ['HTTP_ORIGIN' => '']] as $override) {
    check(!A::sameOrigin(array_replace($origin, $override), 'https://band.test/subfolder'), 'Cross-site, missing or noncanonical Origin is rejected');
}
check(A::sameOrigin(['HTTP_ORIGIN' => 'http://127.0.0.1:8032'], 'http://127.0.0.1:8032/band'), 'Explicit local test origin retains its exact port');

$sample = ['token' => str_repeat('a', 64), 'active_seconds' => 15, 'visible' => true];
check(A::validateHeartbeat($sample) === $sample, 'Bounded cumulative activity sample is accepted');
foreach ([['active_seconds' => -1], ['active_seconds' => 14401], ['active_seconds' => '15'], ['visible' => 'true'], ['token' => ['bad']], ['referrer' => 'https://private.test']] as $override) {
    rejects(fn() => A::validateHeartbeat(array_replace($sample, $override)), 'Malformed or extra collector data is rejected');
}
check(A::validateSettings(['enabled' => '1', 'geo_enabled' => '1', 'bot_tracking_enabled' => '0', 'retention_days' => '90']) === ['enabled' => true, 'geo_enabled' => true, 'bot_tracking_enabled' => false, 'retention_days' => 90], 'Owner settings accept bounded explicit options');
rejects(fn() => A::validateSettings(['retention_days' => 10000]), 'Unbounded retention is rejected');
rejects(fn() => A::validateSettings(['enabled' => ['1']]), 'Nested settings input is rejected');
check(R::dateRange('2026-02-28', '2026-03-01')[2]->format('Y-m-d H:i:s') === '2026-03-02 00:00:00', 'End date includes its complete UTC day');
foreach ([['2026-02-30', '2026-03-01'], ['2026-03-02', '2026-03-01'], ['2025-01-01', '2026-01-02'], ['2026-10-20 01:00', '2026-10-20']] as [$from, $to]) { rejects(fn() => R::dateRange($from, $to), 'Invalid, reversed or excessive date range is rejected'); }

echo $checks . " analytics unit checks passed.\n";
