<?php
declare(strict_types=1);
namespace Spirit;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

/** An owner-imported aggregate query snapshot, never an individual-visitor lookup. */
final class AnalyticsSearchConsole
{
    public const MAX_BYTES = 2097152;
    public const MAX_ROWS = 5000;

    public static function parse(string $path): array
    {
        if (!is_file($path) || is_link($path) || filesize($path) > self::MAX_BYTES || filesize($path) < 10) {
            throw new InvalidArgumentException('Choose a Search Console Queries CSV no larger than 2 MiB.');
        }
        $handle = fopen($path, 'rb');
        if ($handle === false) { throw new RuntimeException('The query export could not be read.'); }
        try {
            $head = fgetcsv($handle, 8192, ',', '"', '');
            if (!is_array($head)) { throw new InvalidArgumentException('The CSV is empty.'); }
            $head = array_map(static fn($value) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$value))), $head);
            if (count($head) !== 5 || !in_array($head[0], ['top queries', 'query', 'queries'], true) || array_slice($head, 1) !== ['clicks', 'impressions', 'ctr', 'position']) {
                throw new InvalidArgumentException('Use the English Queries CSV from Search Console: Top queries, Clicks, Impressions, CTR, Position.');
            }
            $rows = []; $seen = [];
            while (($values = fgetcsv($handle, 8192, ',', '"', '')) !== false) {
                if ($values === [null] || $values === []) { continue; }
                if (count($rows) >= self::MAX_ROWS || count($values) !== 5) { throw new InvalidArgumentException('The query export has too many rows or an unexpected row format.'); }
                $query = trim((string)$values[0]);
                if ($query === '' || strlen($query) > 255 || preg_match('/[\x00-\x1f\x7f]/', $query) || !preg_match('//u', $query)) {
                    throw new InvalidArgumentException('A query is empty, too long, or contains invalid text.');
                }
                if (isset($seen[$query])) { throw new InvalidArgumentException('The CSV contains duplicate queries. Export one Queries table without combining periods.'); }
                $seen[$query] = true;
                $numbers = [];
                foreach ([1, 2] as $index) {
                    $value = trim((string)$values[$index]);
                    if (!preg_match('/^(?:\d{1,9}|\d{1,3}(?:,\d{3}){1,2})$/D', $value)) { throw new InvalidArgumentException('Clicks and impressions must be nonnegative whole numbers with valid thousands separators.'); }
                    $numbers[] = (int)str_replace(',', '', $value);
                }
                $ctr = trim((string)$values[3]);
                if (!preg_match('/^(?:\d{1,3})(?:\.\d{1,6})?%?$/D', $ctr) || (float)rtrim($ctr, '%') > 100) { throw new InvalidArgumentException('A click-through rate is invalid.'); }
                $position = trim((string)$values[4]);
                if (!preg_match('/^\d{1,5}(?:\.\d{1,6})?$/D', $position) || (float)$position > 10000 || ($numbers[1] > 0 && (float)$position < 1) || $numbers[0] > $numbers[1]) {
                    throw new InvalidArgumentException('A query has invalid position, clicks, or impressions.');
                }
                $rows[] = ['query' => $query, 'clicks' => $numbers[0], 'impressions' => $numbers[1], 'position' => round((float)$position, 4)];
            }
            if (!$rows) { throw new InvalidArgumentException('The query export has no data rows.'); }
            return $rows;
        } finally { fclose($handle); }
    }

    public static function import(array $upload, array $input, int $actor): int
    {
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'] ?? null) || !is_uploaded_file($upload['tmp_name'])) {
            throw new InvalidArgumentException('Choose the Queries CSV again. Check the hosting upload limit if it did not arrive.');
        }
        [$start, $end] = AnalyticsAdmin::range(['start' => $input['query_start'] ?? '', 'end' => $input['query_end'] ?? '']);
        $rows = self::parse($upload['tmp_name']);
        $db = Database::connect(); $db->beginTransaction();
        try {
            self::lockOwner($db, $actor);
            $db->exec('DELETE FROM '.Database::table('analytics_search_queries'));
            $insert = $db->prepare('INSERT INTO '.Database::table('analytics_search_queries').' (period_start,period_end,query_text,clicks,impressions,average_position,imported_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP())');
            foreach ($rows as $row) { $insert->execute([$start,$end,$row['query'],$row['clicks'],$row['impressions'],$row['position']]); }
            Database::audit($db, $actor, 'analytics.search_queries_imported'); $db->commit();
            return count($rows);
        } catch (Throwable $error) { if ($db->inTransaction()) { $db->rollBack(); } throw $error; }
    }

    public static function clear(int $actor): void
    {
        $db = Database::connect(); $db->beginTransaction();
        try { self::lockOwner($db, $actor); $db->exec('DELETE FROM '.Database::table('analytics_search_queries')); Database::audit($db, $actor, 'analytics.search_queries_cleared'); $db->commit(); }
        catch (Throwable $error) { if ($db->inTransaction()) { $db->rollBack(); } throw $error; }
    }

    private static function lockOwner(PDO $db, int $actor): void
    {
        $query = $db->prepare('SELECT id,role,active,session_version FROM '.Database::table('users').' WHERE id=? FOR UPDATE'); $query->execute([$actor]); $owner = $query->fetch();
        if (!$owner || !Permissions::allows($owner, 'owner') || (int)($_SESSION['user_id'] ?? 0) !== $actor || (int)($_SESSION['session_version'] ?? 0) !== (int)$owner['session_version']) {
            throw new RuntimeException('Owner access changed. Sign in again before updating search query reports.');
        }
    }

    public static function snapshot(): array
    {
        $empty = ['available'=>false,'start'=>'','end'=>'','imported_at'=>'','clicks'=>0,'impressions'=>0,'ctr'=>null,'query_count'=>0,'rows'=>[]];
        try {
            $db = Database::connect();
            $summary = $db->query('SELECT MIN(period_start) AS start,MAX(period_end) AS end,MAX(imported_at) AS imported_at,COUNT(*) AS query_count,COALESCE(SUM(clicks),0) AS clicks,COALESCE(SUM(impressions),0) AS impressions FROM '.Database::table('analytics_search_queries'))->fetch();
            $rows = $db->query('SELECT query_text AS query,clicks,impressions,average_position AS position FROM '.Database::table('analytics_search_queries').' ORDER BY clicks DESC,impressions DESC,query_text LIMIT 100')->fetchAll();
            foreach (['query_count','clicks','impressions'] as $key) { $summary[$key]=(int)$summary[$key]; }
            foreach ($rows as &$row) { $row['clicks']=(int)$row['clicks']; $row['impressions']=(int)$row['impressions']; $row['position']=(float)$row['position']; $row['ctr']=$row['impressions']?round($row['clicks']*100/$row['impressions'],2):0; } unset($row);
            return array_replace($empty, $summary, ['available'=>true,'ctr'=>$summary['impressions']?round($summary['clicks']*100/$summary['impressions'],2):null,'rows'=>$rows]);
        } catch (Throwable) { return $empty; }
    }
}
