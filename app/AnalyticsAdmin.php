<?php
declare(strict_types=1);
namespace Spirit;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/** Owner-only presentation helpers. Analytics calculations live in Analytics. */
final class AnalyticsAdmin
{
    public static function range(array $input): array
    {
        $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
        $days = is_string($input['range'] ?? null) ? $input['range'] : '30';
        if (in_array($days, ['7', '30', '90'], true) && !isset($input['start'], $input['end'])) {
            return [$today->modify('-'.((int)$days - 1).' days')->format('Y-m-d'), $today->format('Y-m-d')];
        }
        $dates = [];
        foreach (['start', 'end'] as $field) {
            $value = $input[$field] ?? '';
            if (!is_string($value)) { throw new InvalidArgumentException('Choose valid report dates.'); }
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));
            if (!$date || $date->format('Y-m-d') !== $value) { throw new InvalidArgumentException('Choose valid report dates.'); }
            $dates[] = $date;
        }
        if ($dates[0] > $dates[1] || $dates[1] > $today || $dates[0]->diff($dates[1])->days > 365) {
            throw new InvalidArgumentException('Choose a date range of up to 366 days ending today or earlier.');
        }
        return array_map(static fn($date) => $date->format('Y-m-d'), $dates);
    }

    public static function duration(int|float|null $seconds): string
    {
        if ($seconds === null) { return '—'; }
        $seconds = max(0, (int)round($seconds));
        if ($seconds < 60) { return $seconds.'s'; }
        if ($seconds < 3600) { return intdiv($seconds, 60).'m '.($seconds % 60).'s'; }
        return intdiv($seconds, 3600).'h '.intdiv($seconds % 3600, 60).'m';
    }

    public static function number(int|float $value): string { return number_format($value); }

    public static function trend(array $days): void
    {
        $peak = max(1, ...array_map(static fn($row) => max((int)$row['visits'], (int)$row['pageviews']), $days));
        $ceiling = max(4, (int)(ceil($peak / 4) * 4));
        $count = count($days);
        $x = static fn(int $i): float => 54 + ($count > 1 ? $i * 722 / ($count - 1) : 361);
        $y = static fn(int $v): float => 226 - min(1, $v / $ceiling) * 190;
        echo '<svg class="analytics-trend" viewBox="0 0 820 276" role="img" aria-labelledby="traffic-chart-title traffic-chart-desc">';
        echo '<title id="traffic-chart-title">Daily visits and pageviews</title><desc id="traffic-chart-desc">Gold shows visits. Blue shows pageviews. Exact values are available in the daily data table below.</desc>';
        for ($i = 0; $i <= 4; $i++) {
            $n = (int)($ceiling * $i / 4); $yy = $y($n);
            echo '<line class="chart-grid" x1="54" x2="776" y1="'.$yy.'" y2="'.$yy.'"/><text class="chart-label" x="42" y="'.($yy + 4).'" text-anchor="end">'.self::number($n).'</text>';
        }
        foreach (['pageviews', 'visits'] as $series) {
            $points = [];
            foreach ($days as $i => $row) { $points[] = round($x($i), 2).','.round($y((int)$row[$series]), 2); }
            if ($points) { echo '<polyline class="chart-line chart-'.$series.'" points="'.implode(' ', $points).'"/>'; }
        }
        foreach ($days as $i => $row) {
            $tip = $row['date'].': '.self::number((int)$row['visits']).' visits, '.self::number((int)$row['pageviews']).' pageviews';
            echo '<circle class="chart-point" cx="'.round($x($i), 2).'" cy="'.round($y((int)$row['visits']), 2).'" r="4" tabindex="0" aria-label="'.e($tip).'"><title>'.e($tip).'</title></circle>';
        }
        foreach (array_unique([0, (int)floor(max(0, $count - 1) / 2), max(0, $count - 1)]) as $i) {
            if (!isset($days[$i])) { continue; }
            echo '<text class="chart-label" x="'.round($x($i), 2).'" y="254" text-anchor="middle">'.e(substr($days[$i]['date'], 5)).'</text>';
        }
        echo '</svg>';
    }

    public static function bars(array $rows, string $label = 'label', string $value = 'visits', string $empty = 'No visits recorded in this period.', ?int $denominator = null): void
    {
        if (!$rows) { echo '<p class="analytics-empty">'.e($empty).'</p>'; return; }
        $total = max(1, $denominator ?? array_sum(array_map(static fn($row) => (int)$row[$value], $rows)));
        $max = max(1, ...array_map(static fn($row) => (int)$row[$value], $rows));
        echo '<ol class="analytics-ranking">';
        foreach ($rows as $row) {
            $name = (string)($row[$label] ?: 'Unknown'); $amount = max(0, (int)$row[$value]);
            echo '<li><div><span>'.e($name).'</span><strong>'.self::number($amount).'<small>'.round($amount * 100 / $total).'%</small></strong></div><svg viewBox="0 0 400 5" preserveAspectRatio="none" aria-hidden="true"><rect class="chart-bar-track" width="400" height="5" rx="2"/><rect class="chart-bar" width="'.round(400 * $amount / $max, 2).'" height="5" rx="2"/></svg></li>';
        }
        echo '</ol>';
    }

    public static function liveRows(array $visitors): void
    {
        if (!$visitors) { echo '<tr><td colspan="6" class="analytics-empty">No visitors active in the last minute.</td></tr>'; return; }
        foreach ($visitors as $visitor) {
            $place = implode(', ', array_filter([$visitor['city'] ?? '', $visitor['region'] ?? '', $visitor['country'] ?? ''], static fn($value) => $value !== '' && $value !== 'Unknown'));
            echo '<tr><td><span class="analytics-live-dot" aria-hidden="true"></span>Visit '.e((string)$visitor['id']).'</td><td>'.e((string)$visitor['path']).'</td><td>'.e($place ?: 'Unknown').'</td><td>'.e((string)$visitor['device']).'<small>'.e((string)$visitor['browser']).'</small></td><td>'.self::duration((int)$visitor['active_seconds']).'</td><td>'.e(substr((string)$visitor['last_seen'], 11, 8)).' UTC</td></tr>';
        }
    }

    public static function export(array $report): void
    {
        if (!$report['available']) { throw new \RuntimeException('Analytics is unavailable. Please retry the export later.'); }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Spiritual-Gorillaz-analytics-'.$report['start'].'-to-'.$report['end'].'.csv"');
        header('Cache-Control: private, no-store');
        header('Referrer-Policy: no-referrer');
        $out = fopen('php://output', 'wb');
        if ($out === false) { throw new \RuntimeException('The analytics export could not be created.'); }
        $write = static function (array $row) use ($out): void {
            foreach ($row as &$value) {
                if (is_string($value) && preg_match('/^[\s]*[=+@\-]/u', $value)) { $value = "'".$value; }
            }
            unset($value); fputcsv($out, $row, ',', '"', '');
        };
        $write(['Spiritual Gorillaz audience analytics', $report['start'], $report['end'], 'UTC']);
        $write(['Visits are browser sessions; a new visit begins after 30 minutes of inactivity.']);
        $write(['Bounce rate is single-page completed visits / completed visits; live visits are excluded.']);
        $write(['Active time counts visible-page heartbeats; unavailable time is not inferred from open tabs.']);
        $write(['Country data: IP Geolocation by DB-IP', 'https://db-ip.com', 'Approximate; not GPS.']);
        foreach (['summary','daily','pages','referrers','search_terms','campaigns','browsers','devices','operating_systems','countries','regions','cities','crawlers'] as $section) {
            $write([]); $write([$section]);
            $rows = $report[$section] ?? [];
            if ($section === 'summary') { foreach ($rows as $key => $value) { $write([$key, $value]); } continue; }
            if (!$rows) { $write(['No data']); continue; }
            $write(array_keys($rows[0])); foreach ($rows as $row) { $write(array_values($row)); }
        }
        fclose($out);
    }
}
