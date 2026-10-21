<?php
declare(strict_types=1);
// Disposable SQL fixture only; excluded from every install package.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { http_response_code(404); exit(1); }
$root = (string)getenv('SPIRIT_FIXTURE_ROOT'); $_SERVER['DOCUMENT_ROOT'] = (string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if (!is_file($root . '/includes/bootstrap.php')) { throw new RuntimeException('Missing disposable application.'); }
require $root . '/includes/bootstrap.php';
use Spirit\{Analytics as A, Database, Paths};
$config = Paths::config();
if (!$config || !preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $config['db']['name']) || !preg_match('/^sgci_[a-f0-9]{8}_$/D', $config['db']['prefix']) || $config['db']['prefix'] !== getenv('SPIRIT_DB_PREFIX')) { throw new RuntimeException('Refusing non-test analytics configuration.'); }
$input = json_decode(stream_get_contents(STDIN) ?: '{}', true, 8, JSON_THROW_ON_ERROR);
$db = Database::connect(); $checks = [];
function ok(bool $value, string $label): void { global $checks; if (!$value) { throw new RuntimeException($label); } $checks[] = $label; }
function tableCount(string $name): int { global $db; return (int)$db->query('SELECT COUNT(*) FROM ' . Database::table($name))->fetchColumn(); }
function clearAnalytics(): void { global $db; foreach (['analytics_pageviews', 'analytics_visits', 'analytics_crawlers'] as $name) { $db->exec('DELETE FROM ' . Database::table($name)); } }
function sampleTables(): array { global $db; $out = []; foreach (['analytics_visits', 'analytics_pageviews', 'analytics_crawlers'] as $name) { $out[$name] = $db->query('SELECT * FROM ' . Database::table($name) . ' ORDER BY id')->fetchAll(); } return $out; }
function options(array $input): void {
    global $db;
    $owner = $db->query('SELECT id,session_version FROM ' . Database::table('users') . " WHERE role='owner' ORDER BY id LIMIT 1")->fetch();
    $prior = $_SESSION ?? []; $_SESSION = ['user_id' => (int)$owner['id'], 'session_version' => (int)$owner['session_version']];
    try { A::saveSettings(array_replace(['enabled' => '1', 'geo_enabled' => '1', 'bot_tracking_enabled' => '1', 'retention_days' => '90'], $input), (int)$owner['id']); } finally { $_SESSION = $prior; }
}
function publicRequest(string $route = ''): void {
    global $config, $root;
    $base = rtrim((string)parse_url($config['site_url'], PHP_URL_PATH), '/');
    $_SERVER['SCRIPT_FILENAME'] = $root . '/index.php'; $_SERVER['SCRIPT_NAME'] = $base . '/index.php';
    $_SERVER['REQUEST_URI'] = $base . '/' . $route; $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '203.0.113.42';
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/140.0.0.0 Safari/537.36';
    foreach (['HTTP_DNT', 'HTTP_SEC_GPC', 'HTTP_PURPOSE', 'HTTP_SEC_PURPOSE', 'HTTP_REFERER'] as $key) { unset($_SERVER[$key]); }
    $_GET = [];
}

switch ($argv[1] ?? '') {
case 'state': $result = ['tables' => sampleTables(), 'settings' => A::settings(), 'live' => A::live()]; break;
case 'clear': clearAnalytics(); $result = ['ok' => true]; break;
case 'settings': options($input); $result = A::settings(); break;
case 'break': $db->exec('RENAME TABLE ' . Database::table('analytics_crawlers') . ' TO ' . Database::table('analytics_crawlers_missing')); $result = ['ok' => true]; break;
case 'restore': $db->exec('RENAME TABLE ' . Database::table('analytics_crawlers_missing') . ' TO ' . Database::table('analytics_crawlers')); $result = ['ok' => true]; break;
case 'exercise':
    clearAnalytics(); options([]); publicRequest(); $_SESSION = [];
    $_SERVER['HTTP_REFERER'] = 'https://www.google.com/search?q=live+blues&token=PRIVATE-NOT-SAVED';
    $_GET = ['utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => 'autumn-music', 'customer' => 'PRIVATE-NOT-SAVED'];
    $one = A::beginPage();
    ok(is_array($one) && preg_match('/^[a-f0-9]{64}$/D', $one['token']), 'Real SQL pageview issues a session-bound random page token');
    ok(tableCount('analytics_visits') === 1 && tableCount('analytics_pageviews') === 1, 'Initial server request creates exactly one visit and pageview');
    $unmeasured = A::report(gmdate('Y-m-d'), gmdate('Y-m-d'));
    ok($unmeasured['summary']['measured_pageviews'] === 0 && $unmeasured['summary']['avg_active_seconds'] === null, 'Missing JavaScript activity is unknown duration rather than a fabricated zero-second visit');
    $token = $one['token']; $visit = (int)$_SESSION['analytics_v1']['visit_id'];
    $_SESSION['analytics_v1']['pages'][$token]['issued'] -= 20; $_SESSION['analytics_v1']['pages'][$token]['received'] -= 20;
    $db->exec('UPDATE ' . Database::table('analytics_visits') . ' SET active_clock_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 20 SECOND),started_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 20 SECOND) WHERE id=' . $visit);
    ok(A::heartbeat(['token' => $token, 'active_seconds' => 0, 'visible' => true]), 'An initial zero sample does not consume another page final-sample timing budget');
    ok(A::heartbeat(['token' => $token, 'active_seconds' => 15, 'visible' => true]), 'Bounded visible activity records its cumulative delta');
    $state = sampleTables(); ok((int)$state['analytics_visits'][0]['active_seconds'] === 15, 'Visit contains fifteen observed active seconds');
    $measured = A::report(gmdate('Y-m-d'), gmdate('Y-m-d'));
    ok($measured['summary']['measured_pageviews'] === 1 && $measured['summary']['measured_visits'] === 1 && $measured['summary']['avg_active_seconds'] === 15.0, 'Duration reports explicitly count their measured coverage');
    ok(A::heartbeat(['token' => $token, 'active_seconds' => 15, 'visible' => true]), 'Replayed cumulative heartbeat is harmless');
    ok((int)sampleTables()['analytics_visits'][0]['active_seconds'] === 15, 'Replay never adds the same active seconds twice');
    ok(!A::heartbeat(['token' => $token, 'active_seconds' => 2400, 'visible' => true]), 'Claimed activity exceeding wall time is rejected');
    ok(!A::heartbeat(['token' => $token, 'active_seconds' => 3, 'visible' => true]), 'Activity cannot move backwards');
    publicRequest('music/'); $two = A::beginPage(); $tokenTwo = $two['token'];
    ok(tableCount('analytics_visits') === 1 && tableCount('analytics_pageviews') === 2, 'Another public page continues the same browser-session visit');
    $_SESSION['analytics_v1']['pages'][$tokenTwo]['issued'] -= 20; $_SESSION['analytics_v1']['pages'][$tokenTwo]['received'] -= 20;
    ok(A::heartbeat(['token' => $tokenTwo, 'active_seconds' => 15, 'visible' => true]), 'A second tab can report its own cumulative sample');
    ok((int)sampleTables()['analytics_visits'][0]['active_seconds'] <= 16, 'Concurrent tabs share a single elapsed-time budget');
    ok(A::live()['active_visitors'] === 1, 'Two visible tabs count as one approximate live browser session');
    A::heartbeat(['token' => $tokenTwo, 'active_seconds' => 15, 'visible' => false]);
    A::heartbeat(['token' => $token, 'active_seconds' => 15, 'visible' => false]);
    ok(A::live()['active_visitors'] === 0, 'Hidden pages leave the live list');
    $ownSession = $_SESSION; $_SESSION = [];
    ok(!A::heartbeat(['token' => $token, 'active_seconds' => 15, 'visible' => true]), 'Another session cannot reuse a captured page token');
    $_SESSION = $ownSession; $_SESSION['analytics_v1']['last_seen'] = time() - 1801;
    ok(!A::heartbeat(['token' => $token, 'active_seconds' => 15, 'visible' => true]), 'A page heartbeat cannot revive an inactive expired visit');
    $new = A::beginPage(); ok(is_array($new) && tableCount('analytics_visits') === 2, 'A new real navigation after inactivity starts a new visit');
    ok(!A::heartbeat(['token' => $token, 'active_seconds' => 15, 'visible' => true]), 'Previous-visit page tokens remain invalid after a new visit begins');
    $before = tableCount('analytics_pageviews');
    foreach ([['HTTP_DNT' => '1'], ['HTTP_SEC_GPC' => '1'], ['HTTP_SEC_PURPOSE' => 'prefetch'], ['REQUEST_METHOD' => 'HEAD'], ['REQUEST_METHOD' => 'POST']] as $override) { publicRequest(); $_SERVER = array_replace($_SERVER, $override); ok(A::beginPage() === null, 'Non-visit/privacy traffic is ignored by the real model'); }
    publicRequest(); $session = $_SESSION; $_SESSION['user_id'] = 1;
    ok(A::beginPage() === null, 'Authenticated staff are excluded from real SQL metrics'); $_SESSION = $session;
    foreach (['account/', 'shop/cart.php', 'pages/search.php?q=PRIVATE-NOT-SAVED', 'booking/thanks.php', 'admin/content/preview.php?id=2'] as $route) { publicRequest($route); ok(A::beginPage() === null, 'Private or query-bearing service route is not tracked: ' . $route); }
    ok(tableCount('analytics_pageviews') === $before, 'Excluded requests cause no analytics rows');
    publicRequest(); options(['enabled' => '0']);
    ok(A::beginPage() === null && !A::heartbeat(['token' => $new['token'], 'active_seconds' => 0, 'visible' => true]), 'Owner pause stops both pageviews and activity');
    options([]); publicRequest(); $humans = tableCount('analytics_visits');
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
    ok(A::beginPage() === null && A::beginPage() === null, 'Crawler requests receive no human activity tokens');
    $state = sampleTables(); ok(tableCount('analytics_visits') === $humans && count($state['analytics_crawlers']) === 1 && (int)$state['analytics_crawlers'][0]['hits'] === 2, 'Repeated crawler hits aggregate separately from human sessions');
    ok($state['analytics_crawlers'][0]['verification'] === 'reported_ua', 'Spoofable crawler UA is not falsely verified');
    $serialized = json_encode($state, JSON_THROW_ON_ERROR);
    ok(!str_contains($serialized, '203.0.113.42') && !str_contains($serialized, 'Chrome/140') && !str_contains($serialized, 'PRIVATE-NOT-SAVED') && !str_contains($serialized, 'https://www.google.com'), 'Raw IP, full UA, referrer URL and arbitrary query data are absent from every analytics row');
    ok($state['analytics_visits'][0]['referrer_domain'] === 'google.com' && $state['analytics_visits'][0]['search_term'] === 'live blues' && $state['analytics_visits'][0]['utm_campaign'] === 'autumn-music', 'Acquisition is retained only as allowed sanitized fields');
    $owner = $db->query('SELECT id,session_version FROM ' . Database::table('users') . " WHERE role='owner' ORDER BY id LIMIT 1")->fetch();
    $_SESSION = ['user_id' => (int)$owner['id'], 'session_version' => (int)$owner['session_version'] + 1];
    try { A::saveSettings(['enabled' => '0'], (int)$owner['id']); ok(false, 'Stale owner access must fail'); } catch (RuntimeException) { ok(A::settings()['enabled'], 'Locked current owner-session recheck blocks a stale settings write'); }

    // Exact reporting cohort: a cross-midnight session, one finished bounce and one open visit.
    clearAnalytics(); $today = gmdate('Y-m-d'); $yesterday = gmdate('Y-m-d', time() - 86400); $twoDays = gmdate('Y-m-d', time() - 172800);
    $insertVisit = $db->prepare('INSERT INTO ' . Database::table('analytics_visits') . ' (visit_key,started_at,last_seen,active_clock_at,page_count,active_seconds,referrer_domain,browser_family,device_family,country_code,country_name) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $pageInsert = $db->prepare('INSERT INTO ' . Database::table('analytics_pageviews') . ' (visit_id,page_path,viewed_at,last_seen,active_seconds,is_visible,activity_samples) VALUES (?,?,?,?,?,?,1)');
    $finished = gmdate('Y-m-d H:i:s', time() - 1900); $now = gmdate('Y-m-d H:i:s');
    $insertVisit->execute([hash('sha256', 'fixture-a'), $yesterday . ' 23:59:00', $finished, $finished, 2, 30, 'google.com', 'Chrome', 'Desktop', 'US', 'United States']); $a = (int)$db->lastInsertId();
    $pageInsert->execute([$a, '/', $yesterday . ' 23:59:00', $finished, 10, 0]); $pageInsert->execute([$a, '/music/', $today . ' 00:01:00', $finished, 20, 0]);
    $insertVisit->execute([hash('sha256', 'fixture-b'), $today . ' 00:01:00', $finished, $finished, 1, 12, '', 'Safari', 'Mobile', 'GB', 'United Kingdom']); $b = (int)$db->lastInsertId(); $pageInsert->execute([$b, '/shows/', $today . ' 00:01:00', $finished, 12, 0]);
    $insertVisit->execute([hash('sha256', 'fixture-c'), $now, $now, $now, 1, 0, '', 'Firefox', 'Desktop', '', '']); $c = (int)$db->lastInsertId(); $pageInsert->execute([$c, '/band/', $now, $now, 0, 1]);
    $q = $db->prepare('INSERT INTO ' . Database::table('analytics_crawlers') . ' (visit_date,family,verification,page_path,hits,last_seen) VALUES (?,?,?,?,?,?)');
    $q->execute([$today, 'Googlebot', 'reported_ua', '/', 5, $now]);
    $report = A::report($twoDays, $today);
    ok($report['available'] && $report['summary']['visits'] === 3 && $report['summary']['pageviews'] === 4, 'Selected inclusive range counts distinct visits separately from pageviews');
    ok($report['summary']['completed_visits'] === 2 && $report['summary']['bounces'] === 1 && $report['summary']['bounce_rate'] === 50.0, 'Bounce rate includes only completed visits after thirty minutes inactivity');
    ok($report['summary']['active_seconds'] === 42 && $report['summary']['avg_active_seconds'] === 14.0, 'Active duration uses observed foreground seconds, not time between page loads');
    ok(count($report['daily']) === 3 && $report['daily'][0]['visits'] === 0 && $report['daily'][1]['visits'] === 1 && $report['daily'][2]['visits'] === 3, 'Daily chart fills empty days and handles a visit spanning midnight honestly');
    ok($report['summary']['crawler_hits'] === 5 && count($report['crawlers']) === 1, 'Crawler totals stay outside human visit and bounce totals');
    ok(A::live()['active_visitors'] === 1 && A::live()['visitors'][0]['path'] === '/band/', 'Live view lists only a recently visible anonymous visit');
    $db->exec('UPDATE ' . Database::table('analytics_pageviews') . ' SET last_seen=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 61 SECOND) WHERE visit_id=' . $c);
    ok(A::live()['active_visitors'] === 0, 'Live session expires after sixty seconds without a visible-page sample');
    $onlyOpen = A::report($today, $today); ok($onlyOpen['summary']['visits'] === 3, 'A visit beginning before the range still counts when it views a page inside it');
    $older = gmdate('Y-m-d H:i:s', time() - 120 * 86400);
    for ($i = 0; $i < 3; $i++) { $insertVisit->execute([hash('sha256', 'old-' . $i), $older, $older, $older, 1, 0, '', 'Other', 'Desktop', '', '']); $old = (int)$db->lastInsertId(); $pageInsert->execute([$old, '/', $older, $older, 0, 0]); }
    $q->execute([substr($older, 0, 10), 'Bingbot', 'reported_ua', '/', 9, $older]);
    $pruned = A::prune(2); ok($pruned === ['pageviews' => 2, 'visits' => 2, 'crawlers' => 1], 'Retention removes only a bounded oldest batch from analytics tables');
    $pruned = A::prune(2); ok($pruned['pageviews'] === 1 && $pruned['visits'] === 1 && tableCount('analytics_visits') === 3 && tableCount('analytics_pageviews') === 4, 'Repeated retention clears expired history and preserves recent analytics');
    $result = ['checks' => $checks, 'count' => count($checks)]; break;
default: throw new RuntimeException('Unknown analytics fixture command.');
}
echo json_encode($result, JSON_THROW_ON_ERROR);
