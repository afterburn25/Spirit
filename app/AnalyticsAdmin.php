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

    public static function trend(array $days, bool $compact = false): void
    {
        AnalyticsCharts::trend($days, $compact);
    }

    public static function bars(array $rows, string $label = 'label', string $value = 'visits', string $empty = 'No visits recorded in this period.', ?int $denominator = null): void
    {
        AnalyticsCharts::bars($rows, $label, $value, $empty, $denominator);
    }

    public static function liveRows(array $visitors): void
    {
        if (!$visitors) { echo '<tr><td colspan="7" class="analytics-empty">No visitors active in the last minute.</td></tr>'; return; }
        foreach ($visitors as $visitor) {
            $place = implode(', ', array_filter([$visitor['city'] ?? '', $visitor['region'] ?? '', $visitor['country'] ?? ''], static fn($value) => $value !== '' && $value !== 'Unknown'));
            $label = 'Visit '.e((string)$visitor['id']);
            if (!empty($visitor['record_id'])) { $label = '<a class="analytics-session-link" href="'.e(app_url('admin/analytics/visitors.php?visit='.(int)$visitor['record_id'])).'">'.$label.' <span aria-hidden="true">↗</span></a>'; }
            echo '<tr><td><span class="analytics-live-dot" aria-hidden="true"></span>'.$label.'</td><td class="analytics-path">'.e((string)$visitor['path']).'<small>'.self::number((int)($visitor['page_count'] ?? 1)).' pages in session</small></td><td>'.e((string)($visitor['referrer'] ?? 'Direct / unknown')).'</td><td>'.e($place ?: 'Unknown').'</td><td>'.e((string)$visitor['device']).'<small>'.e(implode(' · ', array_filter([(string)$visitor['browser'], (string)($visitor['os'] ?? '')]))).'</small></td><td>'.self::duration((int)$visitor['active_seconds']).'</td><td>'.e(substr((string)$visitor['last_seen'], 11, 8)).' UTC</td></tr>';
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
        foreach (['summary','daily','pages','referrers','search_terms','campaigns','browsers','devices','operating_systems','countries','regions','cities','crawlers','channels','hourly','heatmap','entry_pages','exit_pages','depth','engagement'] as $section) {
            $write([]); $write([$section]);
            $rows = $report[$section] ?? [];
            if ($section === 'summary') { foreach ($rows as $key => $value) { $write([$key, $value]); } continue; }
            if (!$rows) { $write(['No data']); continue; }
            $write(array_keys($rows[0])); foreach ($rows as $row) { $write(array_values($row)); }
        }
        fclose($out);
    }
}
