<?php
declare(strict_types=1);
namespace Spirit;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

/** First-party, aggregate site measurements. Never persist an IP, full UA or URL query. */
final class Analytics
{
    public const IDLE_SECONDS = 1800;
    public const LIVE_SECONDS = 60;
    public const HEARTBEAT_SECONDS = 15;
    public const MAX_ACTIVE_SECONDS = 14400;
    private const SESSION_KEY = 'analytics_v1';
    private const MAX_PAGE_TOKENS = 24;
    private static ?bool $available = null;

    public static function available(): bool
    {
        if (self::$available !== null) { return self::$available; }
        try {
            $config = Paths::config();
            if (!$config) { return self::$available = false; }
            $db = Database::connect();
            foreach (['analytics_visits', 'analytics_pageviews', 'analytics_crawlers'] as $name) {
                if (!Database::exists($db, $config['db']['prefix'] . $name)) { return self::$available = false; }
            }
            return self::$available = true;
        } catch (Throwable) { return self::$available = false; }
    }

    public static function settings(): array
    {
        $defaults = ['enabled' => true, 'retention_days' => 90, 'geo_enabled' => true, 'bot_tracking_enabled' => true];
        try {
            $q = Database::connect()->prepare('SELECT settings_json FROM ' . Database::table('service_settings') . ' WHERE namespace=?');
            $q->execute(['analytics']); $raw = $q->fetchColumn();
            if ($raw === false) { return $defaults; }
            $saved = json_decode((string)$raw, true, 8, JSON_THROW_ON_ERROR);
            if (!is_array($saved)) { return $defaults; }
            foreach (['enabled', 'geo_enabled', 'bot_tracking_enabled'] as $key) {
                if (is_bool($saved[$key] ?? null)) { $defaults[$key] = $saved[$key]; }
            }
            if (in_array($saved['retention_days'] ?? null, [30, 90, 180, 365], true)) { $defaults['retention_days'] = $saved['retention_days']; }
            return $defaults;
        } catch (Throwable) { return array_replace($defaults, ['enabled' => false]); }
    }

    public static function validateSettings(array $input): array
    {
        $out = [];
        foreach (['enabled', 'geo_enabled', 'bot_tracking_enabled'] as $key) {
            $value = $input[$key] ?? false;
            if (!in_array($value, [true, false, '1', '0', 1, 0, ''], true)) { throw new InvalidArgumentException('Choose a valid analytics option.'); }
            $out[$key] = in_array($value, [true, '1', 1], true);
        }
        $days = filter_var($input['retention_days'] ?? 90, FILTER_VALIDATE_INT);
        if (!in_array($days, [30, 90, 180, 365], true)) { throw new InvalidArgumentException('Choose 30, 90, 180 or 365 days of analytics history.'); }
        $out['retention_days'] = $days;
        return $out;
    }

    public static function saveSettings(array $input, int $actor): void
    {
        $values = self::validateSettings($input);
        if (!self::available()) { throw new RuntimeException('Finish the database update before configuring analytics.'); }
        $db = Database::connect(); $db->beginTransaction();
        try {
            $q = $db->prepare('SELECT id,role,active,session_version FROM ' . Database::table('users') . ' WHERE id=? FOR UPDATE');
            $q->execute([$actor]); $owner = $q->fetch();
            if (!$owner || $owner['role'] !== 'owner' || !(int)$owner['active'] || (int)($_SESSION['user_id'] ?? 0) !== $actor || (int)($_SESSION['session_version'] ?? 0) !== (int)$owner['session_version']) {
                throw new RuntimeException('Owner access changed. Sign in again before saving analytics settings.');
            }
            $q = $db->prepare('INSERT INTO ' . Database::table('service_settings') . ' (namespace,settings_json,updated_at) VALUES (?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE settings_json=VALUES(settings_json),updated_at=UTC_TIMESTAMP()');
            $q->execute(['analytics', json_encode($values, JSON_THROW_ON_ERROR)]);
            Database::audit($db, $actor, 'settings.analytics.updated'); $db->commit();
        } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
    }

    /** Public pure helper: reject private routes and retain only a validated public slug. */
    public static function normalisePath(string $url, string $basePath = ''): ?string
    {
        if ($url === '' || strlen($url) > 4096 || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) { return null; }
        $parts = parse_url($url);
        if (!$parts || isset($parts['user']) || isset($parts['pass'])) { return null; }
        $path = $parts['path'] ?? '/';
        if (preg_match('/%|\.\.|\/\//', $path)) { return null; }
        if ($basePath !== '') {
            if ($path !== $basePath && !str_starts_with($path, $basePath . '/')) { return null; }
            $path = substr($path, strlen($basePath)) ?: '/';
        }
        if ($path === '/index.php') { $path = '/'; }
        $path = preg_replace('#/(music|shows|band|media|press|booking|shop|pages)/index\.php$#D', '/$1/', $path);
        if ($path === '/' || preg_match('#^/(music|shows|band|media|press|booking|shop)/$#D', $path)) { return $path; }
        if (!in_array($path, ['/music/release.php', '/shows/event.php', '/band/member.php', '/shop/product.php', '/pages/'], true)) { return null; }
        parse_str($parts['query'] ?? '', $query);
        $slug = $query['slug'] ?? '';
        if (!is_string($slug) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug) || strlen($slug) > 160) { return null; }
        if ($path === '/pages/') {
            $aliases = ['home' => '/', 'music' => '/music/', 'shows' => '/shows/', 'band' => '/band/', 'media' => '/media/', 'press' => '/press/', 'booking' => '/booking/', 'merch' => '/shop/'];
            if (isset($aliases[$slug])) { return $aliases[$slug]; }
        }
        return $path . '?slug=' . $slug;
    }

    public static function requestAllowed(array $server, array $session = []): bool
    {
        if (($server['REQUEST_METHOD'] ?? '') !== 'GET' || isset($session['user_id'])) { return false; }
        if (($server['HTTP_DNT'] ?? '') === '1' || ($server['HTTP_SEC_GPC'] ?? '') === '1') { return false; }
        foreach (['HTTP_PURPOSE', 'HTTP_SEC_PURPOSE', 'HTTP_X_PURPOSE', 'HTTP_X_MOZ'] as $key) {
            if (preg_match('/prefetch|prerender/i', (string)($server[$key] ?? ''))) { return false; }
        }
        return true;
    }

    /** Reject labels containing contact details, URLs, token-like strings or control bytes. */
    public static function label(mixed $value, int $maximum = 80): string
    {
        if (!is_string($value) || strlen($value) > 2048 || !preg_match('//u', $value) || preg_match('/[\x00-\x1f\x7f<>@\\\\?=&]|https?:|(?:token|password|secret|csrf|api[_-]?key)\s*[:=]|[a-f0-9]{24,}|[A-Za-z0-9_+-]{48,}/i', $value)) { return ''; }
        $value = trim(preg_replace('/\s+/u', ' ', $value));
        if (strlen($value) > $maximum) { return ''; }
        return $value;
    }

    public static function acquisition(string $referrer, string $siteHost, array $query): array
    {
        $out = ['referrer_domain' => '', 'search_term' => '', 'utm_source' => '', 'utm_medium' => '', 'utm_campaign' => ''];
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $key) { $out[$key] = self::label($query[$key] ?? ''); }
        if (strlen($referrer) > 4096) { return $out; }
        $parts = parse_url($referrer);
        if (!$parts || isset($parts['user']) || isset($parts['pass']) || !in_array($parts['scheme'] ?? '', ['https', 'http'], true)) { return $out; }
        $host = strtolower(rtrim((string)($parts['host'] ?? ''), '.'));
        $host = preg_replace('/^www\./', '', $host);
        $siteHost = preg_replace('/^www\./', '', strtolower(rtrim($siteHost, '.')));
        if ($host === $siteHost || strlen($host) > 190 || !str_contains($host, '.') || filter_var($host, FILTER_VALIDATE_IP) || !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) { return $out; }
        $out['referrer_domain'] = $host;
        $key = null;
        if (preg_match('/^(?:[a-z0-9-]+\.)?google\.(?:com|[a-z]{2}|(?:com|co)\.[a-z]{2})$/D', $host)) { $key = 'q'; }
        elseif ($host === 'bing.com' || $host === 'duckduckgo.com' || $host === 'ecosia.org' || $host === 'search.brave.com') { $key = 'q'; }
        elseif ($host === 'search.yahoo.com') { $key = 'p'; }
        if ($key !== null) { parse_str($parts['query'] ?? '', $values); $out['search_term'] = self::label($values[$key] ?? '', 160); }
        return $out;
    }

    public static function clientFamilies(string $userAgent): array
    {
        $ua = substr($userAgent, 0, 1024);
        $browser = match (true) {
            (bool)preg_match('/Edg(?:e|A|iOS)?\//i', $ua) => 'Microsoft Edge',
            (bool)preg_match('/OPR\/|Opera/i', $ua) => 'Opera',
            (bool)preg_match('/SamsungBrowser\//i', $ua) => 'Samsung Internet',
            (bool)preg_match('/Firefox\/|FxiOS\//i', $ua) => 'Firefox',
            (bool)preg_match('/Chrome\/|CriOS\//i', $ua) => 'Chrome',
            (bool)preg_match('/Safari\//i', $ua) => 'Safari',
            default => 'Other',
        };
        $os = match (true) {
            (bool)preg_match('/iPhone|iPad|iPod/i', $ua) => 'iOS',
            (bool)preg_match('/Android/i', $ua) => 'Android',
            (bool)preg_match('/CrOS/i', $ua) => 'Chrome OS',
            (bool)preg_match('/Windows/i', $ua) => 'Windows',
            (bool)preg_match('/Macintosh|Mac OS X/i', $ua) => 'macOS',
            (bool)preg_match('/Linux/i', $ua) => 'Linux',
            default => 'Other',
        };
        $device = preg_match('/iPad|Tablet|Kindle|Silk\//i', $ua) || ($os === 'Android' && !preg_match('/Mobile/i', $ua)) ? 'Tablet' : (preg_match('/Mobile|iPhone|iPod/i', $ua) ? 'Mobile' : 'Desktop');
        return ['browser_family' => $browser, 'os_family' => $os, 'device_family' => $device];
    }

    /** Exact origin comparison; forwarded headers never supply the trusted origin. */
    public static function sameOrigin(array $server, string $siteUrl): bool
    {
        if (($server['HTTP_SEC_FETCH_SITE'] ?? 'same-origin') !== 'same-origin') { return false; }
        $origin = $server['HTTP_ORIGIN'] ?? '';
        if (!is_string($origin) || strlen($origin) > 512) { return false; }
        $parts = parse_url($origin); $site = parse_url($siteUrl);
        if (!$parts || !$site || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || !in_array($parts['path'] ?? '', ['', '/'], true)) { return false; }
        $port = static fn(array $p): int => (int)($p['port'] ?? (($p['scheme'] ?? '') === 'https' ? 443 : 80));
        return strtolower((string)($parts['scheme'] ?? '')) === strtolower((string)($site['scheme'] ?? '')) && strtolower((string)($parts['host'] ?? '')) === strtolower((string)($site['host'] ?? '')) && $port($parts) === $port($site);
    }

    public static function beginPage(?string $canonicalPath = null): ?array
    {
        if (!self::requestAllowed($_SERVER, $_SESSION ?? [])) { return null; }
        try {
            if (!self::available()) { return null; }
            $settings = self::settings();
            if (!$settings['enabled']) { return null; }
            $path = self::normalisePath($canonicalPath ?? (string)($_SERVER['REQUEST_URI'] ?? ''), Paths::basePath());
            if ($path === null) { return null; }
            $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
            $crawler = AnalyticsCrawler::classify($ua, $ip);
            if ($crawler !== null) {
                if ($settings['bot_tracking_enabled']) { self::recordCrawler($crawler, $path); }
                return null;
            }
            // Per-session page bursts are bounded without identifying a browser by its IP or UA.
            $now = time(); $state = $_SESSION[self::SESSION_KEY] ?? [];
            if (!is_array($state) || ($state['last_seen'] ?? 0) < $now - self::IDLE_SECONDS) { $state = []; }
            if (($state['window'] ?? 0) <= $now - 60) { $state['window'] = $now; $state['requests'] = 0; }
            if (($state['requests'] ?? 0) >= 60) { return null; }
            $state['requests'] = (int)($state['requests'] ?? 0) + 1;
            $db = Database::connect(); $db->beginTransaction();
            try {
                $visit = null;
                if (isset($state['visit_id'], $state['visit_key'])) {
                    $q = $db->prepare('SELECT id FROM ' . Database::table('analytics_visits') . ' WHERE id=? AND visit_key=? AND last_seen>=? FOR UPDATE');
                    $q->execute([(int)$state['visit_id'], $state['visit_key'], gmdate('Y-m-d H:i:s', $now - self::IDLE_SECONDS)]); $visit = $q->fetchColumn();
                }
                if ($visit === false || $visit === null) {
                    $state['visit_key'] = hash('sha256', random_bytes(32)); $state['pages'] = [];
                    $config = Paths::config(); $host = (string)(parse_url($config['site_url'], PHP_URL_HOST) ?: '');
                    $data = array_merge(self::acquisition((string)($_SERVER['HTTP_REFERER'] ?? ''), $host, $_GET), self::clientFamilies($ua));
                    $geo = $settings['geo_enabled'] ? AnalyticsGeo::lookup($ip) : [];
                    $data['country_code'] = preg_match('/^[A-Z]{2}$/D', (string)($geo['country_code'] ?? '')) ? $geo['country_code'] : '';
                    foreach (['country_name' => 80, 'region' => 100, 'city' => 100] as $key => $limit) { $data[$key] = self::label($geo[$key] ?? '', $limit); }
                    $columns = array_keys($data); $marks = implode(',', array_fill(0, count($data), '?'));
                    $q = $db->prepare('INSERT INTO ' . Database::table('analytics_visits') . ' (visit_key,started_at,last_seen,active_clock_at,' . implode(',', $columns) . ') VALUES (?,UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP(),' . $marks . ')');
                    $q->execute([$state['visit_key'], ...array_values($data)]); $visit = (int)$db->lastInsertId();
                }
                $q = $db->prepare('INSERT INTO ' . Database::table('analytics_pageviews') . ' (visit_id,page_path,viewed_at,last_seen) VALUES (?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())');
                $q->execute([(int)$visit, $path]); $page = (int)$db->lastInsertId();
                $q = $db->prepare('UPDATE ' . Database::table('analytics_visits') . ' SET page_count=page_count+1,last_seen=UTC_TIMESTAMP() WHERE id=?'); $q->execute([(int)$visit]);
                $db->commit();
            } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
            $token = bin2hex(random_bytes(32));
            $state['visit_id'] = (int)$visit; $state['last_seen'] = $now;
            foreach ($state['pages'] ?? [] as $key => $previous) { if (($previous['issued'] ?? 0) < $now - self::MAX_ACTIVE_SECONDS) { unset($state['pages'][$key]); } }
            while (count($state['pages'] ?? []) >= self::MAX_PAGE_TOKENS) { unset($state['pages'][array_key_first($state['pages'])]); }
            $state['pages'][$token] = ['id' => $page, 'issued' => $now, 'received' => $now, 'client_seconds' => 0, 'requests' => 0, 'window' => $now];
            $_SESSION[self::SESSION_KEY] = $state;
            return ['endpoint' => app_url('pages/analytics.php'), 'token' => $token, 'heartbeat_seconds' => self::HEARTBEAT_SECONDS, 'max_active_seconds' => self::MAX_ACTIVE_SECONDS];
        } catch (Throwable) { return null; }
    }

    private static function recordCrawler(array $crawler, string $path): void
    {
        $family = self::label($crawler['family'] ?? '', 32);
        if ($family === '') { $family = 'Other crawler'; }
        $verification = ($crawler['verification'] ?? '') === 'verified_range' ? 'verified_range' : 'reported_ua';
        $q = Database::connect()->prepare('INSERT INTO ' . Database::table('analytics_crawlers') . ' (visit_date,family,verification,page_path,hits,last_seen) VALUES (UTC_DATE(),?,?,?,1,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE hits=LEAST(hits+1,4294967295),last_seen=UTC_TIMESTAMP()');
        $q->execute([$family, $verification, $path]);
    }

    public static function validateHeartbeat(array $payload): array
    {
        if (array_diff(array_keys($payload), ['token', 'active_seconds', 'visible'])) { throw new InvalidArgumentException('Unexpected analytics fields.'); }
        if (!is_string($payload['token'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $payload['token'])) { throw new InvalidArgumentException('Invalid page token.'); }
        if (!is_int($payload['active_seconds'] ?? null) || $payload['active_seconds'] < 0 || $payload['active_seconds'] > self::MAX_ACTIVE_SECONDS || !is_bool($payload['visible'] ?? null)) { throw new InvalidArgumentException('Invalid activity sample.'); }
        return $payload;
    }

    /** A replay can never add elapsed time twice; concurrent tabs share one wall-clock budget. */
    public static function heartbeat(array $payload): bool
    {
        try {
            $payload = self::validateHeartbeat($payload);
            if (isset($_SESSION['user_id']) || ($_SERVER['HTTP_DNT'] ?? '') === '1' || ($_SERVER['HTTP_SEC_GPC'] ?? '') === '1' || !self::available() || !self::settings()['enabled']) { return false; }
            $state = $_SESSION[self::SESSION_KEY] ?? []; $page = $state['pages'][$payload['token']] ?? null; $now = time();
            if (!is_array($page) || !isset($state['visit_id'], $state['visit_key']) || ($state['last_seen'] ?? 0) < $now - self::IDLE_SECONDS || $page['issued'] < $now - self::MAX_ACTIVE_SECONDS || $payload['active_seconds'] < $page['client_seconds']) { return false; }
            if ($page['window'] <= $now - 60) { $page['window'] = $now; $page['requests'] = 0; }
            if ($page['requests'] >= 12) { return false; }
            $page['requests']++;
            // Reserve the budget before touching SQL, including invalid or unavailable attempts.
            $_SESSION[self::SESSION_KEY]['pages'][$payload['token']] = $page;
            $requested = $payload['active_seconds'] - $page['client_seconds'];
            if ($payload['active_seconds'] > $now - $page['issued'] + 2 || $requested > max(0, $now - $page['received']) + 2) { return false; }
            $db = Database::connect(); $db->beginTransaction();
            try {
                $q = $db->prepare('SELECT id,active_clock_at FROM ' . Database::table('analytics_visits') . ' WHERE id=? AND visit_key=? AND last_seen>=? FOR UPDATE');
                $q->execute([(int)$state['visit_id'], $state['visit_key'], gmdate('Y-m-d H:i:s', $now - self::IDLE_SECONDS)]); $visit = $q->fetch();
                if (!$visit) { $db->rollBack(); return false; }
                $q = $db->prepare('SELECT id FROM ' . Database::table('analytics_pageviews') . ' WHERE id=? AND visit_id=? FOR UPDATE');
                $q->execute([$page['id'], $visit['id']]);
                if (!$q->fetchColumn()) { $db->rollBack(); return false; }
                $wall = max(0, $now - strtotime($visit['active_clock_at'] . ' UTC'));
                $credit = min($requested, $wall, 60);
                $q = $db->prepare('UPDATE ' . Database::table('analytics_pageviews') . ' SET active_seconds=active_seconds+?,activity_samples=LEAST(activity_samples+1,4294967295),last_seen=UTC_TIMESTAMP(),is_visible=? WHERE id=?');
                $q->execute([$credit, $payload['visible'] ? 1 : 0, $page['id']]);
                // A new tab's initial zero sample can arrive before the previous
                // tab's final sample. Do not spend that earlier interval until
                // actual cumulative activity has been credited.
                $q = $db->prepare('UPDATE ' . Database::table('analytics_visits') . ' SET active_seconds=active_seconds+?,active_clock_at=IF(?>0,UTC_TIMESTAMP(),active_clock_at),last_seen=UTC_TIMESTAMP() WHERE id=?');
                $q->execute([$credit, $credit, $visit['id']]); $db->commit();
            } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
            $page['client_seconds'] = $payload['active_seconds'];
            if ($requested > 0) { $page['received'] = $now; }
            $_SESSION[self::SESSION_KEY]['pages'][$payload['token']] = $page;
            $_SESSION[self::SESSION_KEY]['last_seen'] = $now;
            return true;
        } catch (Throwable) { return false; }
    }

    public static function report(string $start, string $end): array { return AnalyticsReport::report($start, $end); }
    public static function live(): array { return AnalyticsReport::live(); }

    /** Read-only owner maintenance inventory; visits wait for their expired page batches. */
    public static function retentionPreview(): array
    {
        if (!self::available()) { return []; }
        $days = self::settings()['retention_days'];
        $cutoff = gmdate('Y-m-d 00:00:00', time() - $days * 86400);
        $db = Database::connect(); $pages = Database::table('analytics_pageviews');
        $queries = [
            'analytics_pageviews' => ['Anonymous pageviews older than '.$days.' days', 'SELECT COUNT(*) FROM '.$pages.' WHERE viewed_at<?', $cutoff],
            'analytics_visits' => ['Anonymous visits older than '.$days.' days (after their old pageview batches clear)', 'SELECT COUNT(*) FROM '.Database::table('analytics_visits').' v WHERE v.last_seen<? AND NOT EXISTS (SELECT 1 FROM '.$pages.' p WHERE p.visit_id=v.id AND p.viewed_at>=?)', $cutoff],
            'analytics_crawlers' => ['Crawler daily records older than '.$days.' days', 'SELECT COUNT(*) FROM '.Database::table('analytics_crawlers').' WHERE visit_date<?', substr($cutoff, 0, 10)],
        ];
        $rows = [];
        foreach ($queries as $kind => [$label, $sql, $date]) {
            $q = $db->prepare($sql); $q->execute($kind === 'analytics_visits' ? [$date, $date] : [$date]);
            $rows[] = ['kind' => $kind, 'label' => $label, 'count' => (int)$q->fetchColumn()];
        }
        return $rows;
    }

    /** Explicit owner/scheduled maintenance only; bounded analytics rows, never operational data. */
    public static function prune(int $limit = 200): array
    {
        if ($limit < 1 || $limit > 500) { throw new InvalidArgumentException('Analytics cleanup accepts 1–500 rows per table.'); }
        $result = ['pageviews' => 0, 'visits' => 0, 'crawlers' => 0];
        if (!self::available()) { return $result; }
        $cutoff = gmdate('Y-m-d 00:00:00', time() - self::settings()['retention_days'] * 86400);
        $db = Database::connect(); $db->beginTransaction();
        try {
            $q = $db->prepare('DELETE FROM ' . Database::table('analytics_pageviews') . ' WHERE viewed_at<? ORDER BY viewed_at,id LIMIT ' . $limit); $q->execute([$cutoff]); $result['pageviews'] = $q->rowCount();
            $q = $db->prepare('DELETE FROM ' . Database::table('analytics_visits') . ' WHERE last_seen<? AND id NOT IN (SELECT visit_id FROM ' . Database::table('analytics_pageviews') . ') ORDER BY last_seen,id LIMIT ' . $limit); $q->execute([$cutoff]); $result['visits'] = $q->rowCount();
            $q = $db->prepare('DELETE FROM ' . Database::table('analytics_crawlers') . ' WHERE visit_date<? ORDER BY visit_date,id LIMIT ' . $limit); $q->execute([substr($cutoff, 0, 10)]); $result['crawlers'] = $q->rowCount();
            $db->commit(); return $result;
        } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
    }
}
