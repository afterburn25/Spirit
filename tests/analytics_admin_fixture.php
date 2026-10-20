<?php
declare(strict_types=1);
// Synthetic analytics only. This fixture is never included in a release ZIP.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { http_response_code(404); exit(1); }
$root = (string)getenv('SPIRIT_FIXTURE_ROOT');
$_SERVER['DOCUMENT_ROOT'] = (string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if (!is_file($root.'/includes/bootstrap.php')) { throw new RuntimeException('Missing disposable application.'); }
require $root.'/includes/bootstrap.php';
use Spirit\{Analytics, AnalyticsGeo, AnalyticsSearchConsole, Database, Paths};
$configuration = Paths::config();
if (!$configuration || !preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $configuration['db']['name'])
    || !preg_match('/^sgci_[a-f0-9]{8}_$/D', $configuration['db']['prefix'])
    || $configuration['db']['prefix'] !== getenv('SPIRIT_DB_PREFIX')) { throw new RuntimeException('Refusing non-test analytics configuration.'); }
$db = Database::connect();
switch ($argv[1] ?? '') {
case 'snapshot':
    $queries = $db->query('SELECT * FROM '.Database::table('analytics_search_queries').' ORDER BY id')->fetchAll();
    $counts = [];
    foreach (['analytics_visits', 'analytics_pageviews', 'analytics_crawlers'] as $name) { $counts[$name] = (int)$db->query('SELECT COUNT(*) FROM '.Database::table($name))->fetchColumn(); }
    $result = ['queries' => AnalyticsSearchConsole::snapshot(), 'query_digest' => hash('sha256', json_encode($queries, JSON_THROW_ON_ERROR)),
        'counts' => $counts, 'geography' => AnalyticsGeo::status(), 'config_digest' => hash_file('sha256', Paths::runtime().'/config.php')];
    break;
case 'stale-owner':
    $owner = $db->query('SELECT id,session_version FROM '.Database::table('users')." WHERE role='owner' ORDER BY id LIMIT 1")->fetch();
    $_SESSION = ['user_id' => (int)$owner['id'], 'session_version' => (int)$owner['session_version'] + 1];
    $rejected = false;
    try { AnalyticsSearchConsole::clear((int)$owner['id']); } catch (RuntimeException) { $rejected = true; }
    $result = ['rejected' => $rejected];
    break;
case 'seed':
    foreach (['analytics_pageviews', 'analytics_visits', 'analytics_crawlers'] as $name) { $db->exec('DELETE FROM '.Database::table($name)); }
    $visit = $db->prepare('INSERT INTO '.Database::table('analytics_visits').' (visit_key,started_at,last_seen,active_clock_at,page_count,active_seconds,referrer_domain,search_term,utm_source,utm_medium,utm_campaign,browser_family,os_family,device_family,country_code,country_name,region,city) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $page = $db->prepare('INSERT INTO '.Database::table('analytics_pageviews').' (visit_id,page_path,viewed_at,last_seen,active_seconds,activity_samples,is_visible) VALUES (?,?,?,?,?,1,0)');
    $bot = $db->prepare('INSERT INTO '.Database::table('analytics_crawlers').' (visit_date,family,verification,page_path,hits,last_seen) VALUES (?,?,?,?,?,?)');
    $places = [['US','United States','Louisiana','Lafayette'],['US','United States','Texas','Houston'],['GB','United Kingdom','England','London'],['CA','Canada','Ontario','Toronto'],['','','','']];
    $paths = ['/', '/music/', '/shows/', '/band/', '/booking/'];
    $sources = ['google.com','facebook.com','instagram.com','','spiritualgorillaz.example'];
    for ($day = 6; $day >= 0; $day--) {
        // Dates are relative to the test run, not fixed or future production events.
        $date = gmdate('Y-m-d', time() - $day * 86400);
        $count = [12, 9, 15, 7, 10, 6, 8][$day];
        for ($i = 0; $i < $count; $i++) {
            $stamp = $day === 0 ? gmdate('Y-m-d H:i:s', time() - 3600 - $i * 30) : $date.' 12:00:00';
            $pageCount = ($i % 3) + 1; $seconds = 15 + ($i % 6) * 12;
            [$country, $name, $region, $city] = $places[$i % 5];
            $visit->execute([hash('sha256','admin-preview-'.$day.'-'.$i), $stamp, $stamp, $stamp, $pageCount, $pageCount * $seconds,
                $sources[$i % 5], $i % 5 === 0 ? 'live music louisiana' : '', $i % 4 === 0 ? 'newsletter' : '',
                $i % 4 === 0 ? 'email' : '', $i % 4 === 0 ? 'autumn-listening' : '',
                ['Chrome','Safari','Firefox'][$i % 3], ['Windows','iOS','Android'][$i % 3], ['Desktop','Mobile','Mobile'][$i % 3], $country, $name, $region, $city]);
            $id = (int)$db->lastInsertId();
            for ($p = 0; $p < $pageCount; $p++) { $page->execute([$id,$paths[($i+$p)%5],$stamp,$stamp,$seconds]); }
        }
        foreach (['Googlebot', 'Bingbot', 'Yahoo Slurp'] as $index => $family) { $bot->execute([$date,$family,'reported_ua','/',5+$day+$index,$date.' 00:00:00']); }
    }
    $result = ['seeded' => true];
    break;
default: throw new RuntimeException('Unknown analytics admin fixture command.');
}
echo json_encode($result, JSON_THROW_ON_ERROR);
