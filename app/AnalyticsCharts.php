<?php
declare(strict_types=1);
namespace Spirit;

/** Dependency-free, server-rendered charts. Exact values remain available without scripting. */
final class AnalyticsCharts
{
    private static int $sequence = 0;

    private static function id(string $kind): string { return 'audience-'.$kind.'-'.(++self::$sequence); }
    private static function n(float|int $value): string { return number_format($value, 2, '.', ''); }
    private static function compact(int $value): string
    {
        if ($value >= 1000000) { return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.').'m'; }
        if ($value >= 10000) { return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'k'; }
        return number_format($value);
    }

    public static function trend(array $days, bool $compact = false): void
    {
        $id = self::id('traffic'); $count = count($days); $bottom = $compact ? 140 : 232; $height = $bottom + 40;
        $peak = max([1, ...array_map(static fn($row) => max(0, (int)$row['visits'], (int)$row['pageviews']), $days)]);
        $ceiling = max(4, (int)(ceil($peak / 4) * 4));
        $x = static fn(int $i): float => 52 + ($count > 1 ? $i * 730 / ($count - 1) : 365);
        $y = static fn(int $value): float => $bottom - min(1, max(0, $value) / $ceiling) * ($bottom - 22);
        echo '<svg class="analytics-trend'.($compact ? ' analytics-trend-compact' : '').'" viewBox="0 0 820 '.$height.'" role="img" aria-labelledby="'.$id.'-title '.$id.'-desc">';
        echo '<title id="'.$id.'-title">Daily visits and pageviews</title><desc id="'.$id.'-desc">Gold shows visits. Blue shows pageviews. Focus a point for exact values. The daily data table gives the complete series.</desc>';
        echo '<defs><linearGradient id="'.$id.'-fill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#62b9f7" stop-opacity=".27"/><stop offset="1" stop-color="#62b9f7" stop-opacity=".015"/></linearGradient></defs>';
        for ($i = 0; $i <= 4; $i++) {
            $amount = (int)($ceiling * $i / 4); $yy = self::n($y($amount));
            echo '<line class="chart-grid" x1="52" x2="782" y1="'.$yy.'" y2="'.$yy.'"/><text class="chart-label" x="40" y="'.self::n($y($amount) + 4).'" text-anchor="end">'.number_format($amount).'</text>';
        }
        foreach (['pageviews', 'visits'] as $series) {
            $points = [];
            foreach ($days as $i => $row) { $points[] = self::n($x($i)).','.self::n($y((int)$row[$series])); }
            if ($points && $series === 'pageviews') {
                echo '<polygon class="chart-area" fill="url(#'.$id.'-fill)" points="'.self::n($x(0)).','.$bottom.' '.implode(' ', $points).' '.self::n($x($count - 1)).','.$bottom.'"/>';
            }
            if ($points) { echo '<polyline class="chart-line chart-'.$series.'" points="'.implode(' ', $points).'"/>'; }
        }
        foreach ($days as $i => $row) {
            $tip = $row['date'].': '.number_format((int)$row['visits']).' visits · '.number_format((int)$row['pageviews']).' pageviews';
            $hitWidth = $count > 1 ? min(44, 730 / ($count - 1)) : 44;
            echo '<g class="chart-datapoint" tabindex="0" data-chart-tip="'.e($tip).'" aria-label="'.e($tip).'"><title>'.e($tip).'</title><rect class="chart-hit" x="'.self::n($x($i) - $hitWidth / 2).'" y="18" width="'.self::n($hitWidth).'" height="'.($bottom - 14).'" fill="transparent"/><circle class="chart-point chart-point-view" cx="'.self::n($x($i)).'" cy="'.self::n($y((int)$row['pageviews'])).'" r="3"/><circle class="chart-point" cx="'.self::n($x($i)).'" cy="'.self::n($y((int)$row['visits'])).'" r="3.5"/></g>';
        }
        foreach (array_unique([0, (int)floor(max(0, $count - 1) / 3), (int)floor(max(0, $count - 1) * 2 / 3), max(0, $count - 1)]) as $i) {
            if (isset($days[$i])) { echo '<text class="chart-label" x="'.self::n($x($i)).'" y="'.($bottom + 26).'" text-anchor="middle">'.e(substr((string)$days[$i]['date'], 5)).'</text>'; }
        }
        echo '</svg>';
        if (!$compact) { echo '<p class="analytics-chart-tooltip" data-chart-readout aria-live="polite">Hover or focus a chart point to see its exact values.</p>'; }
    }

    public static function donut(array $rows, string $label = 'label', string $value = 'visits', string $title = 'Visitor breakdown', ?int $denominator = null): void
    {
        $id = self::id('mix'); $clean = [];
        foreach ($rows as $row) {
            $amount = max(0, (int)($row[$value] ?? 0));
            if ($amount > 0) { $clean[] = ['label' => (string)(($row[$label] ?? '') ?: 'Unknown'), 'value' => $amount]; }
        }
        $total = max(array_sum(array_column($clean, 'value')), $denominator ?? 0);
        if (count($clean) > 6) {
            $rest = array_sum(array_column(array_slice($clean, 5), 'value'));
            $clean = array_slice($clean, 0, 5); $clean[] = ['label' => 'Other groups', 'value' => $rest];
        }
        $remainder = $total - array_sum(array_column($clean, 'value'));
        if ($remainder > 0) { $clean[] = ['label' => 'Other / unlisted', 'value' => $remainder]; }
        echo '<div class="analytics-donut-layout"><svg class="analytics-donut" viewBox="0 0 216 216" role="img" aria-labelledby="'.$id.'-title '.$id.'-desc"><title id="'.$id.'-title">'.e($title).'</title><desc id="'.$id.'-desc">'.number_format($total).' total. Labels, counts and shares are listed alongside the chart.</desc><circle class="chart-ring" cx="108" cy="108" r="77" fill="none" stroke-width="19"/>';
        $circumference = 2 * M_PI * 77; $offset = 0.0;
        foreach ($clean as $i => $row) {
            $length = $row['value'] / max(1, $total) * $circumference;
            $tip = $row['label'].': '.number_format($row['value']).' ('.round(100 * $row['value'] / max(1, $total), 1).'%)';
            echo '<circle class="chart-segment chart-color-'.($i % 7).'" cx="108" cy="108" r="77" fill="none" stroke="currentColor" stroke-width="19" stroke-dasharray="'.self::n($length).' '.self::n($circumference - $length).'" stroke-dashoffset="'.self::n(-$offset).'" transform="rotate(-90 108 108)" tabindex="0" data-chart-tip="'.e($tip).'" aria-label="'.e($tip).'"><title>'.e($tip).'</title></circle>';
            $offset += $length;
        }
        echo '<text class="chart-donut-total" x="108" y="109" text-anchor="middle">'.self::compact($total).'</text><text class="chart-donut-caption" x="108" y="133" text-anchor="middle">'.($total ? 'in this period' : 'No data yet').'</text></svg><ul class="analytics-donut-legend">';
        foreach ($clean as $i => $row) {
            echo '<li class="chart-color-'.($i % 7).'"><span class="analytics-swatch" aria-hidden="true"></span><span class="analytics-donut-name">'.e($row['label']).'</span><strong>'.number_format($row['value']).'<small>'.round(100 * $row['value'] / max(1, $total), 1).'%</small></strong></li>';
        }
        if (!$clean) { echo '<li class="analytics-empty">The next recorded visits will populate this chart.</li>'; }
        echo '</ul></div>';
    }

    public static function bars(array $rows, string $label = 'label', string $value = 'visits', string $empty = 'No visits recorded in this period.', ?int $denominator = null): void
    {
        if (!$rows) { echo '<p class="analytics-empty">'.e($empty).'</p>'; return; }
        $total = max(1, $denominator ?? array_sum(array_map(static fn($row) => (int)$row[$value], $rows)));
        $maximum = max(1, ...array_map(static fn($row) => (int)$row[$value], $rows));
        echo '<ol class="analytics-ranking">';
        foreach ($rows as $i => $row) {
            $name = (string)($row[$label] ?: 'Unknown'); $amount = max(0, (int)$row[$value]); $percentage = round(100 * $amount / $total, 1);
            echo '<li class="chart-color-'.($i % 7).'"><div><span>'.e($name).'</span><strong>'.number_format($amount).'<small>'.$percentage.'%</small></strong></div><svg viewBox="0 0 400 12" preserveAspectRatio="none" aria-hidden="true"><rect class="chart-bar-track" width="400" height="12" rx="6"/><rect fill="currentColor" width="'.self::n(400 * $amount / $maximum).'" height="12" rx="6"/></svg></li>';
        }
        echo '</ol>';
    }

    public static function hourly(array $rows): void
    {
        $id = self::id('hours'); $maximum = max([1, ...array_map(static fn($row) => (int)$row['pageviews'], $rows)]);
        $ceiling = max(4, (int)(ceil($maximum / 4) * 4));
        echo '<svg class="analytics-hourly" viewBox="0 0 820 230" role="img" aria-labelledby="'.$id.'-title"><title id="'.$id.'-title">Pageviews by hour of day in UTC, across the selected dates</title>';
        for ($i = 0; $i <= 4; $i++) { $yy = 190 - $i * 40; echo '<line class="chart-grid" x1="52" x2="787" y1="'.$yy.'" y2="'.$yy.'"/><text class="chart-label" x="40" y="'.($yy + 4).'" text-anchor="end">'.number_format((int)($ceiling * $i / 4)).'</text>'; }
        foreach ($rows as $row) {
            $hour = (int)$row['hour']; if ($hour < 0 || $hour > 23) { continue; }
            $value = max(0, (int)$row['pageviews']); $height = 160 * $value / $ceiling; $x = 58 + $hour * 30.5;
            $tip = sprintf('%02d:00 UTC: %s pageviews · %s visits', $hour, number_format($value), number_format((int)$row['visits']));
            echo '<g tabindex="0" class="chart-datapoint" data-chart-tip="'.e($tip).'" aria-label="'.e($tip).'"><title>'.e($tip).'</title><rect class="chart-hour-track" x="'.self::n($x).'" y="30" width="19" height="160" rx="4"/><rect class="chart-hour-bar" x="'.self::n($x).'" y="'.self::n(190 - $height).'" width="19" height="'.self::n($height).'" rx="4"/></g>';
            if (in_array($hour, [0, 6, 12, 18, 23], true)) { echo '<text class="chart-label" x="'.self::n($x + 9.5).'" y="216" text-anchor="middle">'.sprintf('%02d:00', $hour).'</text>'; }
        }
        echo '</svg><p class="analytics-chart-tooltip" data-chart-readout aria-live="polite">Hover or focus an hour for pageviews and visits.</p>';
    }

    public static function heatmap(array $rows): void
    {
        $id = self::id('heat'); $maximum = max([1, ...array_map(static fn($row) => (int)$row['pageviews'], $rows)]);
        echo '<div class="analytics-heat-scroll"><svg class="analytics-heatmap" viewBox="0 0 820 258" role="img" aria-labelledby="'.$id.'-title"><title id="'.$id.'-title">Pageview activity by weekday and hour, UTC</title>';
        foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $day) { echo '<text class="chart-label" x="42" y="'.(57 + $i * 27).'" text-anchor="end">'.$day.'</text>'; }
        foreach ([0,6,12,18,23] as $hour) { echo '<text class="chart-label" x="'.(67 + $hour * 30.5).'" y="23" text-anchor="middle">'.sprintf('%02d:00', $hour).'</text>'; }
        foreach ($rows as $row) {
            $day = (int)$row['weekday']; $hour = (int)$row['hour']; if ($day < 0 || $day > 6 || $hour < 0 || $hour > 23) { continue; }
            $value = max(0, (int)$row['pageviews']); $level = $value ? max(1, min(4, (int)ceil(4 * $value / $maximum))) : 0;
            $tip = (string)$row['day'].' '.sprintf('%02d:00', $hour).' UTC: '.number_format($value).' pageviews';
            echo '<rect class="chart-heat heat-'.$level.'" x="'.self::n(54 + $hour * 30.5).'" y="'.(39 + $day * 27).'" width="25" height="21" rx="4" tabindex="0" data-chart-tip="'.e($tip).'" aria-label="'.e($tip).'"><title>'.e($tip).'</title></rect>';
        }
        echo '</svg></div><div class="analytics-heat-legend"><span>Fewer pageviews</span><i class="heat-0"></i><i class="heat-1"></i><i class="heat-2"></i><i class="heat-3"></i><i class="heat-4"></i><span>More pageviews</span></div><p class="analytics-chart-tooltip" data-chart-readout aria-live="polite">Hover or focus a cell for its exact pageview count.</p>';
        echo '<details class="analytics-detail"><summary>View weekday / hour counts</summary><div class="table-scroll"><table class="content-table"><caption class="sr-only">Pageviews by weekday and hour in UTC</caption><thead><tr><th scope="col">Day / UTC</th>';
        for ($hour = 0; $hour < 24; $hour++) { echo '<th scope="col">'.sprintf('%02d:00', $hour).'</th>'; }
        echo '</tr></thead><tbody>'; $matrix = [];
        foreach ($rows as $row) { $matrix[(int)$row['weekday']][(int)$row['hour']] = max(0, (int)$row['pageviews']); }
        foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day => $label) {
            echo '<tr><th scope="row">'.$label.'</th>';
            for ($hour = 0; $hour < 24; $hour++) { echo '<td>'.number_format($matrix[$day][$hour] ?? 0).'</td>'; }
            echo '</tr>';
        }
        echo '</tbody></table></div></details>';
    }
}
