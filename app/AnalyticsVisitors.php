<?php
declare(strict_types=1);
namespace Spirit;

use InvalidArgumentException;
use PDO;
use Throwable;

/** Read-only exploration of retained anonymous sessions. No new data collection. */
final class AnalyticsVisitors
{
    public const PAGE_SIZE = 25;
    public const JOURNEY_PAGE_SIZE = 100;
    public const CHANNELS = [
        'direct' => 'Direct / unknown', 'organic' => 'Organic search', 'social' => 'Social',
        'email' => 'Email', 'paid' => 'Paid campaign', 'referral' => 'Referral', 'campaign' => 'Other campaign',
    ];
    public const STATUSES = ['live' => 'Live now', 'recent' => 'Recent activity', 'completed' => 'Completed visits', 'bounced' => 'Single-page bounces'];
    // Deliberately supported host suffixes, not a wildcard for every string
    // following a provider's name. Unlisted domains remain visible as referrals.
    private const SEARCH_HOSTS = 'google[.](com|ca|co[.]uk|com[.]au|co[.]nz|de|fr|es|it|nl|ie|co[.]in|co[.]jp|co[.]za|com[.]br|com[.]mx)|bing[.]com|search[.]yahoo[.]com|duckduckgo[.]com|ecosia[.]org|yandex[.](com|ru|by|kz|com[.]tr)|baidu[.]com|search[.]brave[.]com';

    /** Fixed SQL expression: no caller-supplied identifiers or values. */
    public static function channelExpression(): string
    {
        return "CASE WHEN LOWER(v.utm_medium) IN ('cpc','ppc','paid','paidsearch','paid-search','paid_social','paid-social','display','cpm') THEN 'paid'"
            ." WHEN LOWER(v.utm_medium) IN ('email','e-mail') THEN 'email'"
            ." WHEN LOWER(v.utm_medium) IN ('social','social-network','social-media','organic-social') OR LOWER(v.referrer_domain) REGEXP '(^|[.])(facebook[.]com|instagram[.]com|t[.]co|x[.]com|twitter[.]com|tiktok[.]com|youtube[.]com|pinterest[.]com|linkedin[.]com|reddit[.]com)$' THEN 'social'"
            ." WHEN LOWER(v.utm_medium) IN ('organic','organic-search') OR LOWER(v.referrer_domain) REGEXP '(^|[.])(".self::SEARCH_HOSTS.")$' THEN 'organic'"
            ." WHEN v.utm_source<>'' OR v.utm_medium<>'' OR v.utm_campaign<>'' THEN 'campaign'"
            ." WHEN v.referrer_domain<>'' THEN 'referral' ELSE 'direct' END";
    }

    public static function filters(array $input): array
    {
        $result = [];
        foreach (['browser'=>32,'os'=>24,'device'=>12,'country'=>7,'source'=>190,'region'=>100,'city'=>100,'path'=>255,'channel'=>12,'status'=>12] as $key => $limit) {
            $value = $input[$key] ?? '';
            if (!is_string($value) || strlen($value) > $limit || preg_match('/[\x00-\x1F\x7F]/', $value) || !preg_match('//u', $value)) {
                throw new InvalidArgumentException('Choose valid visitor filters.');
            }
            $result[$key] = trim($value);
        }
        if ($result['country'] !== '' && $result['country'] !== 'unknown' && !preg_match('/^[A-Z]{2}$/D', $result['country'])) { throw new InvalidArgumentException('Choose a valid country filter.'); }
        if ($result['channel'] !== '' && !isset(self::CHANNELS[$result['channel']])) { throw new InvalidArgumentException('Choose a valid traffic channel.'); }
        if ($result['status'] !== '' && !isset(self::STATUSES[$result['status']])) { throw new InvalidArgumentException('Choose a valid visit status.'); }
        // Detail pages retain their validated public slug. Require the exact
        // collector format so those drill-downs work without accepting private
        // routes, arbitrary query fields, fragments or noncanonical aliases.
        if ($result['path'] !== '' && Analytics::normalisePath($result['path']) !== $result['path']) { throw new InvalidArgumentException('Enter the exact public page path shown in Analytics, including its slug when present.'); }
        foreach (['page','journey_page'] as $key) {
            $value = $input[$key] ?? '1';
            if (!is_string($value) || !preg_match('/^[1-9][0-9]{0,6}$/D', $value)) { throw new InvalidArgumentException('Choose a valid report page.'); }
            $result[$key] = (int)$value;
        }
        $visit = $input['visit'] ?? '';
        if (!is_string($visit) || ($visit !== '' && !preg_match('/^[1-9][0-9]{0,17}$/D', $visit))) { throw new InvalidArgumentException('Choose a valid anonymous visit.'); }
        $result['visit'] = $visit;
        return $result;
    }

    private static function rows(PDO $db, string $sql, array $parameters): array
    {
        $query = $db->prepare($sql); $query->execute($parameters); return $query->fetchAll();
    }

    private static function fields(string $pages): string
    {
        return "v.id AS record_id,v.visit_key,v.started_at,v.last_seen,v.page_count,v.active_seconds,v.referrer_domain,v.search_term,v.utm_source,v.utm_medium,v.utm_campaign,v.browser_family AS browser,v.os_family AS os,v.device_family AS device,v.country_code,COALESCE(NULLIF(v.country_name,''),'Unknown') AS country,COALESCE(NULLIF(v.region,''),'Unknown') AS region,COALESCE(NULLIF(v.city,''),'Unknown') AS city,"
            .self::channelExpression().' AS channel_key,'
            .'(SELECT p.page_path FROM '.$pages.' p WHERE p.visit_id=v.id ORDER BY p.viewed_at,p.id LIMIT 1) AS entry_path,'
            .'(SELECT p.page_path FROM '.$pages.' p WHERE p.visit_id=v.id ORDER BY p.viewed_at DESC,p.id DESC LIMIT 1) AS last_path,'
            .'(SELECT COUNT(*) FROM '.$pages.' p WHERE p.visit_id=v.id) AS retained_pageviews,'
            .'(SELECT COUNT(*) FROM '.$pages.' p WHERE p.visit_id=v.id AND p.activity_samples>0) AS measured_pageviews,'
            .'EXISTS(SELECT 1 FROM '.$pages.' p WHERE p.visit_id=v.id AND p.is_visible=1 AND p.last_seen>=?) AS is_live';
    }

    private static function visit(array $row, string $idle): array
    {
        $row['label'] = 'V-'.strtoupper(substr((string)$row['visit_key'], 0, 8));
        unset($row['visit_key']);
        foreach (['record_id','page_count','active_seconds','retained_pageviews','measured_pageviews'] as $key) { $row[$key] = (int)$row[$key]; }
        $row['is_live'] = (bool)$row['is_live']; $row['completed'] = $row['last_seen'] < $idle;
        $row['bounce'] = $row['completed'] ? $row['page_count'] === 1 : null;
        $row['status'] = $row['is_live'] ? 'live' : ($row['completed'] ? ($row['bounce'] ? 'bounced' : 'completed') : 'recent');
        $row['channel'] = self::CHANNELS[$row['channel_key']] ?? 'Direct / unknown';
        $row['measured_seconds'] = $row['measured_pageviews'] ? $row['active_seconds'] : null;
        return $row;
    }

    /** All visit attributes describe the retained session; the date filter
     * selects sessions with at least one counted page in the requested period. */
    public static function report(string $start, string $end, array $input): array
    {
        [$first, , $until] = AnalyticsReport::dateRange($start, $end);
        $filters = self::filters($input);
        $result = ['available'=>false, 'start'=>$start, 'end'=>$end, 'filters'=>$filters,
            'total'=>0,'page'=>1,'pages'=>1,'page_size'=>self::PAGE_SIZE,'visitors'=>[],
            'options'=>['browser'=>[],'os'=>[],'device'=>[],'country'=>[],'source'=>[]],
            'selected'=>null,'selected_missing'=>false,'journey'=>[],'journey_page'=>1,'journey_pages'=>1,'journey_total'=>0];
        if (!Analytics::available()) { return $result; }
        try {
            $db = Database::connect(); $visits = Database::table('analytics_visits'); $pages = Database::table('analytics_pageviews');
            $range = [$first->format('Y-m-d H:i:s'),$until->format('Y-m-d H:i:s')];
            $idle = gmdate('Y-m-d H:i:s',time()-Analytics::IDLE_SECONDS);
            $live = gmdate('Y-m-d H:i:s',time()-Analytics::LIVE_SECONDS);
            $cohort = ' FROM '.$visits.' v WHERE EXISTS(SELECT 1 FROM '.$pages.' p WHERE p.visit_id=v.id AND p.viewed_at>=? AND p.viewed_at<?)';
            // Choices describe the selected-date cohort, so changing another
            // filter never silently removes the user's available selections.
            foreach (['browser'=>'browser_family','os'=>'os_family','device'=>'device_family'] as $key=>$column) {
                $rows = self::rows($db,'SELECT DISTINCT v.'.$column.' AS value'.$cohort.' ORDER BY value LIMIT 100',$range);
                $result['options'][$key] = array_map(static fn($row)=>['value'=>(string)$row['value'],'label'=>(string)$row['value']],$rows);
            }
            $result['options']['country'] = self::rows($db,"SELECT DISTINCT COALESCE(NULLIF(v.country_code,''),'unknown') AS value,COALESCE(NULLIF(v.country_name,''),'Unknown') AS label".$cohort.' ORDER BY label LIMIT 250',$range);
            $result['options']['source'] = self::rows($db,"SELECT DISTINCT COALESCE(NULLIF(v.referrer_domain,''),'__direct__') AS value,COALESCE(NULLIF(v.referrer_domain,''),'Direct / unknown') AS label".$cohort.' ORDER BY label LIMIT 200',$range);
            $where = $cohort; $parameters = $range;
            foreach (['browser'=>'browser_family','os'=>'os_family','device'=>'device_family','region'=>'region','city'=>'city'] as $key=>$column) {
                if ($filters[$key] !== '') { $where .= ' AND v.'.$column.'=?'; $parameters[] = $filters[$key]; }
            }
            if ($filters['country'] !== '') { $where .= ' AND v.country_code=?'; $parameters[] = $filters['country']==='unknown'?'':$filters['country']; }
            if ($filters['source'] !== '') { $where .= ' AND v.referrer_domain=?'; $parameters[] = $filters['source']==='__direct__'?'':$filters['source']; }
            if ($filters['channel'] !== '') { $where .= ' AND ('.self::channelExpression().')=?'; $parameters[] = $filters['channel']; }
            if ($filters['path'] !== '') {
                $where .= ' AND EXISTS(SELECT 1 FROM '.$pages.' p WHERE p.visit_id=v.id AND p.page_path=? AND p.viewed_at>=? AND p.viewed_at<?)';
                array_push($parameters,$filters['path'],...$range);
            }
            if ($filters['status'] === 'live' || $filters['status'] === 'recent') {
                $where .= ' AND '.($filters['status']==='recent'?'NOT ':'').'EXISTS(SELECT 1 FROM '.$pages.' p WHERE p.visit_id=v.id AND p.is_visible=1 AND p.last_seen>=?)';
                $parameters[] = $live;
                if ($filters['status']==='recent') { $where .= ' AND v.last_seen>=?'; $parameters[] = $idle; }
            } elseif ($filters['status'] !== '') {
                $where .= ' AND v.last_seen<?'; $parameters[] = $idle;
                if ($filters['status']==='bounced') { $where .= ' AND v.page_count=1'; }
            }
            $query = $db->prepare('SELECT COUNT(*)'.$where); $query->execute($parameters);
            $result['total'] = (int)$query->fetchColumn();
            $result['pages'] = max(1,(int)ceil($result['total']/self::PAGE_SIZE));
            $result['page'] = min($filters['page'],$result['pages']);
            $offset = ($result['page']-1)*self::PAGE_SIZE;
            $rows = self::rows($db,'SELECT '.self::fields($pages).$where.' ORDER BY v.last_seen DESC,v.id DESC LIMIT '.self::PAGE_SIZE.' OFFSET '.$offset,[$live,...$parameters]);
            $result['visitors'] = array_map(static fn($row)=>self::visit($row,$idle),$rows);

            if ($filters['visit'] !== '') {
                // Drill-down retains every filter, including the date cohort;
                // an unrelated ID cannot bypass a selected comparison scope.
                $rows = self::rows($db,'SELECT '.self::fields($pages).$where.' AND v.id=? LIMIT 1',[$live,...$parameters,$filters['visit']]);
                if (!$rows) { $result['selected_missing'] = true; }
                else {
                    $result['selected'] = self::visit($rows[0],$idle);
                    $result['journey_total'] = $result['selected']['retained_pageviews'];
                    $result['journey_pages'] = max(1,(int)ceil($result['journey_total']/self::JOURNEY_PAGE_SIZE));
                    $result['journey_page'] = min($filters['journey_page'],$result['journey_pages']);
                    $journeyOffset = ($result['journey_page']-1)*self::JOURNEY_PAGE_SIZE;
                    $rows = self::rows($db,'SELECT page_path,viewed_at,last_seen,active_seconds,activity_samples,is_visible FROM '.$pages.' WHERE visit_id=? ORDER BY viewed_at,id LIMIT '.self::JOURNEY_PAGE_SIZE.' OFFSET '.$journeyOffset,[$filters['visit']]);
                    foreach ($rows as $i=>$row) {
                        $row['sequence'] = $journeyOffset+$i+1;
                        $row['active_seconds'] = (int)$row['active_seconds'];
                        $row['measured'] = (int)$row['activity_samples']>0;
                        $row['is_live'] = (bool)$row['is_visible'] && $row['last_seen'] >= $live;
                        $row['in_period'] = $row['viewed_at'] >= $range[0] && $row['viewed_at'] < $range[1];
                        unset($row['activity_samples'],$row['is_visible']);
                        $result['journey'][] = $row;
                    }
                }
            }
            $result['available'] = true;
            return $result;
        } catch (Throwable) { return $result; }
    }

    /** Build links only from validated report state; arbitrary URL fields drop. */
    public static function url(array $report, array $changes = [], string $anchor = ''): string
    {
        $parameters = ['start'=>$report['start'],'end'=>$report['end'],...$report['filters'],...$changes];
        $parameters = array_filter($parameters,static fn($value)=>$value!=='' && $value!==null);
        return app_url('admin/analytics/visitors.php?'.http_build_query($parameters,'','&',PHP_QUERY_RFC3986)).$anchor;
    }
}
