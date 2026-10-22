<?php
declare(strict_types=1);
// Synthetic analytics only. This fixture is never included in a release ZIP.
if (PHP_SAPI !== 'cli' || getenv('SPIRIT_TESTING') !== '1') { http_response_code(404); exit(1); }
$root = (string)getenv('SPIRIT_FIXTURE_ROOT');
$_SERVER['DOCUMENT_ROOT'] = (string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if (!is_file($root.'/includes/bootstrap.php')) { throw new RuntimeException('Missing disposable application.'); }
require $root.'/includes/bootstrap.php';
use Spirit\{Analytics, AnalyticsGeo, AnalyticsSearchConsole, AnalyticsVisitors, Database, Paths};
$configuration = Paths::config();
if (!$configuration || !preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D', $configuration['db']['name'])
    || !preg_match('/^sgci_[a-f0-9]{8}_$/D', $configuration['db']['prefix'])
    || $configuration['db']['prefix'] !== getenv('SPIRIT_DB_PREFIX')) { throw new RuntimeException('Refusing non-test analytics configuration.'); }
$db = Database::connect();
$input = json_decode(stream_get_contents(STDIN) ?: '{}',true,16,JSON_THROW_ON_ERROR);
switch ($argv[1] ?? '') {
case 'explore':
    $start = $input['start'] ?? gmdate('Y-m-d',time()-6*86400); $end = $input['end'] ?? gmdate('Y-m-d');
    $result = ['report'=>Analytics::report($start,$end),'explorer'=>AnalyticsVisitors::report($start,$end,$input['filters'] ?? [])];
    break;
case 'classify-sources':
    $query = $db->prepare("SELECT ".AnalyticsVisitors::channelExpression()." FROM (SELECT ? AS referrer_domain,'' AS utm_source,'' AS utm_medium,'' AS utm_campaign) v");
    $result = [];
    foreach ($input['sources'] as $source) { $query->execute([$source]); $result[$source] = $query->fetchColumn(); }
    break;
case 'detail-paths-fixture':
    foreach (['analytics_pageviews','analytics_visits','analytics_crawlers'] as $name) { $db->exec('DELETE FROM '.Database::table($name)); }
    $paths = ['/music/release.php?slug=real-album','/shows/event.php?slug=real-show','/band/member.php?slug=chad-lindsey','/shop/product.php?slug=band-shirt','/pages/?slug=privacy-policy','/music/release.php?slug=other-album'];
    $stamp = gmdate('Y-m-d H:i:s',time()-3600); $date = substr($stamp,0,10);
    $visit = $db->prepare('INSERT INTO '.Database::table('analytics_visits').' (visit_key,started_at,last_seen,active_clock_at,page_count) VALUES (?,?,?,?,1)');
    $page = $db->prepare('INSERT INTO '.Database::table('analytics_pageviews').' (visit_id,page_path,viewed_at,last_seen) VALUES (?,?,?,?)');
    $ids = [];
    foreach ($paths as $path) {
        if (Analytics::normalisePath($path) !== $path) { throw new RuntimeException('Fixture requires an actual canonical public page identifier.'); }
        $visit->execute([hash('sha256','detail-path-'.$path),$stamp,$stamp,$stamp]);
        $ids[$path] = (int)$db->lastInsertId(); $page->execute([$ids[$path],$path,$stamp,$stamp]);
    }
    $result = ['ids'=>$ids,'paths'=>$paths,'start'=>$date,'end'=>$date];
    break;
case 'explorer-fixture':
    foreach (['analytics_pageviews','analytics_visits','analytics_crawlers'] as $name) { $db->exec('DELETE FROM '.Database::table($name)); }
    $visit = $db->prepare('INSERT INTO '.Database::table('analytics_visits').' (visit_key,started_at,last_seen,active_clock_at,page_count,active_seconds,referrer_domain,search_term,utm_source,utm_medium,utm_campaign,browser_family,os_family,device_family,country_code,country_name,region,city) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $page = $db->prepare('INSERT INTO '.Database::table('analytics_pageviews').' (visit_id,page_path,viewed_at,last_seen,active_seconds,activity_samples,is_visible) VALUES (?,?,?,?,?,?,?)');
    $today = gmdate('Y-m-d'); $yesterday = gmdate('Y-m-d',time()-86400); $previous = gmdate('Y-m-d',time()-2*86400);
    $fixtures = [
        'journey' => ['time'=>$yesterday.' 00:05:00','source'=>'google.com','medium'=>'email','campaign'=>'synthetic-journey','paths'=>['/','/music/','/shows/'],'active'=>30],
        'unmeasured' => ['time'=>$yesterday.' 03:00:00','source'=>'','medium'=>'','campaign'=>'<script>alert(1)</script>','paths'=>['/band/'],'active'=>0,'measured'=>0,'country'=>'','name'=>''],
        'live' => ['time'=>gmdate('Y-m-d H:i:s',time()-5),'source'=>'www.google.com','medium'=>'','paths'=>['/music/'],'active'=>0,'visible'=>1],
        'recent' => ['time'=>gmdate('Y-m-d H:i:s',time()-90),'source'=>'','medium'=>'cpc','paths'=>['/','/booking/'],'active'=>6],
        'lookalike' => ['time'=>$yesterday.' 05:00:00','source'=>'google.com.evil','medium'=>'','paths'=>['/press/'],'active'=>15],
        'long' => ['time'=>$yesterday.' 12:00:00','source'=>'facebook.com','medium'=>'','paths'=>array_fill(0,105,'/music/'),'active'=>105],
        'outside' => ['time'=>gmdate('Y-m-d',time()-10*86400).' 12:00:00','source'=>'other.example','medium'=>'','paths'=>['/'],'active'=>15],
    ];
    $ids = [];
    foreach ($fixtures as $key=>$fixture) {
        $stamp = $fixture['time']; $paths = $fixture['paths']; $seconds = $fixture['active'];
        $first = $key==='journey' ? $previous.' 23:55:00' : $stamp;
        $visit->execute([hash('sha256','explorer-'.$key),$first,$stamp,$stamp,count($paths),$seconds,$fixture['source'],
            $key==='journey'?'live blues':'',$key==='journey'?'newsletter':'',$fixture['medium'],$fixture['campaign']??'',
            $key==='unmeasured'?'Firefox':'Chrome',$key==='unmeasured'?'Linux':'Windows','Desktop',
            $fixture['country']??'US',$fixture['name']??'United States','Louisiana','Lafayette']);
        $id = $ids[$key] = (int)$db->lastInsertId();
        foreach ($paths as $i=>$path) {
            $observed = $key==='journey' && $i===0 ? $first : $stamp;
            $page->execute([$id,$path,$observed,$stamp,(int)floor($seconds/count($paths)), $fixture['measured']??1,$fixture['visible']??0]);
        }
    }
    $result = ['ids'=>$ids,'start'=>$yesterday,'end'=>$today];
    break;
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
