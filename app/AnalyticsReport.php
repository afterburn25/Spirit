<?php
declare(strict_types=1);
namespace Spirit;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use Throwable;

/** Bounded owner-facing aggregates; all grouping columns are fixed by source. */
final class AnalyticsReport
{
    public static function dateRange(string $start, string $end): array
    {
        $zone = new DateTimeZone('UTC');
        $first = DateTimeImmutable::createFromFormat('!Y-m-d', $start, $zone);
        $last = DateTimeImmutable::createFromFormat('!Y-m-d', $end, $zone);
        if (!$first || !$last || $first->format('Y-m-d') !== $start || $last->format('Y-m-d') !== $end || $last < $first || $first->diff($last)->days > 365) { throw new InvalidArgumentException('Select a valid date range of no more than 366 days.'); }
        return [$first, $last, $last->modify('+1 day')];
    }

    private static function emptyReport(string $start, string $end, array $days): array
    {
        return [
            'available' => false, 'start' => $start, 'end' => $end, 'timezone' => 'UTC',
            'summary' => ['visits' => 0, 'pageviews' => 0, 'completed_visits' => 0, 'bounces' => 0, 'bounce_rate' => null, 'active_seconds' => 0, 'avg_active_seconds' => null, 'measured_pageviews' => 0, 'measured_visits' => 0, 'crawler_hits' => 0],
            'daily' => array_values($days), 'pages' => [], 'referrers' => [], 'search_terms' => [], 'campaigns' => [],
            'browsers' => [], 'devices' => [], 'operating_systems' => [], 'countries' => [], 'regions' => [], 'cities' => [], 'crawlers' => [],
            'channels' => [], 'hourly' => [], 'heatmap' => [], 'entry_pages' => [], 'exit_pages' => [], 'depth' => [], 'engagement' => [],
        ];
    }

    private static function rows(PDO $db, string $sql, array $parameters): array
    {
        $q = $db->prepare($sql); $q->execute($parameters); return $q->fetchAll();
    }

    private static function integers(array $rows, array $keys): array
    {
        foreach ($rows as &$row) { foreach ($keys as $key) { $row[$key] = (int)($row[$key] ?? 0); } } unset($row);
        return $rows;
    }

    public static function report(string $start, string $end): array
    {
        [$first, $last, $until] = self::dateRange($start, $end); $days = [];
        for ($day = $first; $day <= $last; $day = $day->modify('+1 day')) {
            $key = $day->format('Y-m-d'); $days[$key] = ['date' => $key, 'visits' => 0, 'pageviews' => 0, 'active_seconds' => 0, 'crawler_hits' => 0];
        }
        $result = self::emptyReport($start, $end, $days);
        if (!Analytics::available()) { return $result; }
        try {
            $db = Database::connect();
            $visits = Database::table('analytics_visits'); $pages = Database::table('analytics_pageviews'); $bots = Database::table('analytics_crawlers');
            $range = [$first->format('Y-m-d H:i:s'), $until->format('Y-m-d H:i:s')];
            $cohort = ' FROM ' . $visits . ' v WHERE EXISTS (SELECT 1 FROM ' . $pages . ' p WHERE p.visit_id=v.id AND p.viewed_at>=? AND p.viewed_at<?)';
            $cutoff = gmdate('Y-m-d H:i:s', time() - Analytics::IDLE_SECONDS);
            $q = $db->prepare('SELECT COUNT(*) AS visits,COALESCE(SUM(v.last_seen<?),0) AS completed_visits,COALESCE(SUM(v.last_seen<? AND v.page_count=1),0) AS bounces' . $cohort);
            $q->execute([$cutoff, $cutoff, ...$range]); $summary = $q->fetch();
            foreach (['visits', 'completed_visits', 'bounces'] as $key) { $result['summary'][$key] = (int)$summary[$key]; }
            $q = $db->prepare('SELECT COUNT(*) AS pageviews,COALESCE(SUM(active_seconds),0) AS active_seconds,COALESCE(SUM(activity_samples>0),0) AS measured_pageviews,COUNT(DISTINCT CASE WHEN activity_samples>0 THEN visit_id END) AS measured_visits FROM ' . $pages . ' WHERE viewed_at>=? AND viewed_at<?');
            $q->execute($range); $total = $q->fetch();
            $result['summary']['pageviews'] = (int)$total['pageviews']; $result['summary']['active_seconds'] = (int)$total['active_seconds'];
            $result['summary']['measured_pageviews'] = (int)$total['measured_pageviews']; $result['summary']['measured_visits'] = (int)$total['measured_visits'];
            $result['summary']['avg_active_seconds'] = $result['summary']['measured_visits'] ? round($result['summary']['active_seconds'] / $result['summary']['measured_visits'], 1) : null;
            $result['summary']['bounce_rate'] = $result['summary']['completed_visits'] ? round(100 * $result['summary']['bounces'] / $result['summary']['completed_visits'], 1) : null;
            $q = $db->prepare('SELECT COALESCE(SUM(hits),0) FROM ' . $bots . ' WHERE visit_date>=? AND visit_date<?');
            $botRange = [$start, $until->format('Y-m-d')]; $q->execute($botRange); $result['summary']['crawler_hits'] = (int)$q->fetchColumn();

            $daily = self::rows($db, 'SELECT DATE(viewed_at) AS date,COUNT(DISTINCT visit_id) AS visits,COUNT(*) AS pageviews,COALESCE(SUM(active_seconds),0) AS active_seconds FROM ' . $pages . ' WHERE viewed_at>=? AND viewed_at<? GROUP BY DATE(viewed_at) ORDER BY date', $range);
            foreach (self::integers($daily, ['visits', 'pageviews', 'active_seconds']) as $row) { if (isset($days[$row['date']])) { $days[$row['date']] = array_replace($days[$row['date']], $row); } }
            foreach (self::rows($db, 'SELECT visit_date AS date,SUM(hits) AS crawler_hits FROM ' . $bots . ' WHERE visit_date>=? AND visit_date<? GROUP BY visit_date ORDER BY visit_date', $botRange) as $row) { if (isset($days[$row['date']])) { $days[$row['date']]['crawler_hits'] = (int)$row['crawler_hits']; } }
            $result['daily'] = array_values($days);

            $result['pages'] = self::integers(self::rows($db, 'SELECT page_path AS path,COUNT(*) AS pageviews,COUNT(DISTINCT visit_id) AS visits,SUM(active_seconds) AS active_seconds,SUM(activity_samples>0) AS measured_pageviews FROM ' . $pages . ' WHERE viewed_at>=? AND viewed_at<? GROUP BY page_path ORDER BY pageviews DESC,page_path LIMIT 50', $range), ['pageviews', 'visits', 'active_seconds', 'measured_pageviews']);
            foreach ($result['pages'] as &$row) { $row['avg_active_seconds'] = $row['measured_pageviews'] ? round($row['active_seconds'] / $row['measured_pageviews'], 1) : null; } unset($row);
            $result['referrers'] = self::integers(self::rows($db, "SELECT COALESCE(NULLIF(v.referrer_domain,''),'Direct / unknown') AS domain,COUNT(*) AS visits" . $cohort . ' GROUP BY v.referrer_domain ORDER BY visits DESC,domain LIMIT 30', $range), ['visits']);
            $result['search_terms'] = self::integers(self::rows($db, 'SELECT v.search_term AS term,COUNT(*) AS visits' . $cohort . " AND v.search_term<>'' GROUP BY v.search_term ORDER BY visits DESC,term LIMIT 30", $range), ['visits']);
            $result['campaigns'] = self::integers(self::rows($db, 'SELECT v.utm_source AS source,v.utm_medium AS medium,v.utm_campaign AS campaign,COUNT(*) AS visits' . $cohort . " AND (v.utm_source<>'' OR v.utm_medium<>'' OR v.utm_campaign<>'') GROUP BY v.utm_source,v.utm_medium,v.utm_campaign ORDER BY visits DESC,source,medium,campaign LIMIT 30", $range), ['visits']);
            foreach (['browsers' => 'browser_family', 'devices' => 'device_family', 'operating_systems' => 'os_family'] as $key => $column) {
                $result[$key] = self::integers(self::rows($db, 'SELECT v.' . $column . ' AS label,COUNT(*) AS visits' . $cohort . ' GROUP BY v.' . $column . ' ORDER BY visits DESC,label LIMIT 30', $range), ['visits']);
            }
            $result['countries'] = self::integers(self::rows($db, "SELECT v.country_code,COALESCE(NULLIF(v.country_name,''),'Unknown') AS country,COUNT(*) AS visits" . $cohort . ' GROUP BY v.country_code,v.country_name ORDER BY visits DESC,country LIMIT 50', $range), ['visits']);
            $result['regions'] = self::integers(self::rows($db, "SELECT COALESCE(NULLIF(v.country_name,''),'Unknown') AS country,COALESCE(NULLIF(v.region,''),'Unknown') AS region,COUNT(*) AS visits" . $cohort . ' GROUP BY v.country_name,v.region ORDER BY visits DESC,country,region LIMIT 30', $range), ['visits']);
            $result['cities'] = self::integers(self::rows($db, "SELECT COALESCE(NULLIF(v.country_name,''),'Unknown') AS country,COALESCE(NULLIF(v.region,''),'Unknown') AS region,COALESCE(NULLIF(v.city,''),'Unknown') AS city,COUNT(*) AS visits" . $cohort . ' GROUP BY v.country_name,v.region,v.city ORDER BY visits DESC,country,region,city LIMIT 30', $range), ['visits']);
            $result['crawlers'] = self::integers(self::rows($db, 'SELECT family,verification,SUM(hits) AS hits,MAX(last_seen) AS last_seen FROM ' . $bots . ' WHERE visit_date>=? AND visit_date<? GROUP BY family,verification ORDER BY hits DESC,family,verification LIMIT 30', $botRange), ['hits']);
            // These reports use the same selected-page cohort as the headline
            // visit count. Classification is derived from retained attribution,
            // never from inferred keywords or a person's identity.
            $channelCounts = self::rows($db, 'SELECT '.AnalyticsVisitors::channelExpression().' AS channel_key,COUNT(*) AS visits'.$cohort.' GROUP BY channel_key', $range);
            $channelCounts = array_column($channelCounts, 'visits', 'channel_key');
            foreach (AnalyticsVisitors::CHANNELS as $key => $label) {
                $result['channels'][] = ['key' => $key, 'label' => $label, 'visits' => (int)($channelCounts[$key] ?? 0)];
            }
            $hours = []; $heatmap = []; $weekdays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
            for ($hour = 0; $hour < 24; $hour++) {
                $hours[$hour] = ['hour' => $hour, 'label' => sprintf('%02d:00', $hour), 'visits' => 0, 'pageviews' => 0, 'active_seconds' => 0];
            }
            for ($weekday = 0; $weekday < 7; $weekday++) {
                for ($hour = 0; $hour < 24; $hour++) {
                    $heatmap[$weekday * 24 + $hour] = ['weekday' => $weekday, 'day' => $weekdays[$weekday], 'hour' => $hour, 'pageviews' => 0, 'active_seconds' => 0];
                }
            }
            $hourRows = self::integers(self::rows($db, 'SELECT HOUR(viewed_at) AS hour,COUNT(DISTINCT visit_id) AS visits,COUNT(*) AS pageviews,COALESCE(SUM(active_seconds),0) AS active_seconds FROM '.$pages.' WHERE viewed_at>=? AND viewed_at<? GROUP BY HOUR(viewed_at)', $range), ['hour','visits','pageviews','active_seconds']);
            foreach ($hourRows as $row) { $hours[$row['hour']] = array_replace($hours[$row['hour']], $row); }
            $heatRows = self::integers(self::rows($db, 'SELECT WEEKDAY(viewed_at) AS weekday,HOUR(viewed_at) AS hour,COUNT(*) AS pageviews,COALESCE(SUM(active_seconds),0) AS active_seconds FROM '.$pages.' WHERE viewed_at>=? AND viewed_at<? GROUP BY WEEKDAY(viewed_at),HOUR(viewed_at)', $range), ['weekday','hour','pageviews','active_seconds']);
            foreach ($heatRows as $row) { $slot = $row['weekday'] * 24 + $row['hour']; $heatmap[$slot] = array_replace($heatmap[$slot], $row); }
            $result['hourly'] = array_values($hours); $result['heatmap'] = array_values($heatmap);

            foreach (['entry_pages' => 'ASC', 'exit_pages' => 'DESC'] as $key => $direction) {
                $selection = 'SELECT (SELECT p.page_path FROM '.$pages.' p WHERE p.visit_id=v.id ORDER BY p.viewed_at '.$direction.',p.id '.$direction.' LIMIT 1) AS path'.$cohort;
                $parameters = $range;
                if ($key === 'exit_pages') { $selection .= ' AND v.last_seen<?'; $parameters[] = $cutoff; }
                $result[$key] = self::integers(self::rows($db, 'SELECT path,COUNT(*) AS visits FROM ('.$selection.') selected_visits WHERE path IS NOT NULL GROUP BY path ORDER BY visits DESC,path LIMIT 30', $parameters), ['visits']);
            }
            $depth = self::rows($db, "SELECT CASE WHEN v.page_count<=1 THEN '1 page' WHEN v.page_count<=3 THEN '2–3 pages' WHEN v.page_count<=7 THEN '4–7 pages' ELSE '8+ pages' END AS label,COUNT(*) AS visits".$cohort.' GROUP BY label', $range);
            $depth = array_column($depth, 'visits', 'label');
            foreach (['1 page','2–3 pages','4–7 pages','8+ pages'] as $label) { $result['depth'][] = ['label' => $label, 'visits' => (int)($depth[$label] ?? 0)]; }
            $engagement = self::rows($db, "SELECT CASE WHEN measured_pages=0 THEN 'Unmeasured' WHEN seconds<10 THEN 'Under 10s' WHEN seconds<60 THEN '10–59s' WHEN seconds<180 THEN '1–3 min' ELSE '3 min+' END AS label,COUNT(*) AS visits FROM (SELECT visit_id,SUM(activity_samples>0) AS measured_pages,SUM(active_seconds) AS seconds FROM ".$pages.' WHERE viewed_at>=? AND viewed_at<? GROUP BY visit_id) measured GROUP BY label', $range);
            $engagement = array_column($engagement, 'visits', 'label');
            foreach (['Unmeasured','Under 10s','10–59s','1–3 min','3 min+'] as $label) { $result['engagement'][] = ['label' => $label, 'visits' => (int)($engagement[$label] ?? 0)]; }
            $result['available'] = true;
            return $result;
        } catch (Throwable) { return self::emptyReport($start, $end, $days); }
    }

    public static function live(): array
    {
        $result = ['available' => false, 'active_visitors' => 0, 'visitors' => [], 'as_of' => gmdate('Y-m-d H:i:s')];
        if (!Analytics::available()) { return $result; }
        try {
            $db = Database::connect(); $visits = Database::table('analytics_visits'); $pages = Database::table('analytics_pageviews');
            $cutoff = gmdate('Y-m-d H:i:s', time() - Analytics::LIVE_SECONDS);
            $where = ' FROM ' . $visits . ' v WHERE EXISTS (SELECT 1 FROM ' . $pages . ' p WHERE p.visit_id=v.id AND p.is_visible=1 AND p.last_seen>=?)';
            $q = $db->prepare('SELECT COUNT(*)' . $where); $q->execute([$cutoff]); $result['active_visitors'] = (int)$q->fetchColumn();
            $rows = self::rows($db, "SELECT v.id AS record_id,v.visit_key,COALESCE(NULLIF(v.country_name,''),'Unknown') AS country,COALESCE(NULLIF(v.region,''),'Unknown') AS region,COALESCE(NULLIF(v.city,''),'Unknown') AS city,v.browser_family AS browser,v.device_family AS device,v.os_family AS os,COALESCE(NULLIF(v.referrer_domain,''),'Direct / unknown') AS referrer,v.search_term,v.utm_source,v.utm_medium,v.utm_campaign,v.page_count,v.started_at AS first_seen,v.last_seen,v.active_seconds,(SELECT p.page_path FROM " . $pages . ' p WHERE p.visit_id=v.id AND p.is_visible=1 AND p.last_seen>=? ORDER BY p.last_seen DESC,p.id DESC LIMIT 1) AS path' . $where . ' ORDER BY v.last_seen DESC,v.id DESC LIMIT 50', [$cutoff, $cutoff]);
            foreach ($rows as $row) {
                $row['id'] = 'V-' . strtoupper(substr($row['visit_key'], 0, 8)); unset($row['visit_key']);
                $row['record_id'] = (int)$row['record_id']; $row['page_count'] = (int)$row['page_count'];
                $row['detail_url'] = app_url('admin/analytics/visitors.php?visit='.$row['record_id']);
                $row['active_seconds'] = (int)$row['active_seconds']; $result['visitors'][] = $row;
            }
            $result['available'] = true; return $result;
        } catch (Throwable) { return $result; }
    }
}
