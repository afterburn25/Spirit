<?php
use Spirit\AnalyticsAdmin as Audience;
$audienceAvailable = is_array($audienceSnapshot) && ($audienceSnapshot['available'] ?? false);
$audienceUrl = app_url('admin/analytics/?range=7');
?>
<section class="overview-audience" aria-labelledby="overview-audience-title" data-analytics-snapshot>
 <header class="overview-audience-heading">
  <div><p class="eyebrow">ANALYTICS SNAPSHOT</p><h2 id="overview-audience-title">Audience at a glance</h2><p class="overview-period">Last 7 days · <?=e($audienceStart)?> — <?=e($audienceEnd)?> UTC</p></div>
  <a class="button secondary" href="<?=e($audienceUrl)?>">Open full analytics <span aria-hidden="true">↗</span></a>
 </header>
 <?php if (!$audienceEnabled): ?><p class="overview-audience-notice" role="status"><span aria-hidden="true">●</span> Collection is paused. This snapshot shows previously recorded traffic.</p><?php endif; ?>
 <?php if (!$audienceAvailable): ?>
  <div class="overview-audience-unavailable"><h3>Audience snapshot is unavailable</h3><p>Reporting may need a database update or a fresh connection. Your other administration tools remain available.</p><a class="text-link" href="<?=e(app_url('admin/analytics/'))?>">Open Analytics for details →</a></div>
 <?php else:
  $audienceSummary = $audienceSnapshot['summary'];
  $audienceMetrics = [
   ['visits', 'Visits', Audience::number((int)$audienceSummary['visits']), 'Anonymous browser sessions', '<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-4-5"/>'],
   ['pageviews', 'Pageviews', Audience::number((int)$audienceSummary['pageviews']), 'Public pages viewed', '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>'],
   ['bounce', 'Bounce rate', $audienceSummary['bounce_rate'] === null ? '—' : number_format((float)$audienceSummary['bounce_rate'], 1).'%', 'Completed single-page visits', '<path d="M4 5h7a6 6 0 0 1 0 12H4m0 0 4-4m-4 4 4 4"/>'],
   ['duration', 'Active time / visit', Audience::duration($audienceSummary['avg_active_seconds']), 'Measured foreground time', '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6M12 2v3"/>'],
   ['live', 'Live visitors', $audienceLive['available'] ? Audience::number((int)$audienceLive['active_visitors']) : '—', 'Recent activity · at page load', '<path d="M2 12h4l3-8 5 16 3-8h5"/>'],
   ['crawlers', 'Crawler requests', Audience::number((int)$audienceSummary['crawler_hits']), 'Separate from audience totals', '<rect x="4" y="7" width="16" height="13" rx="3"/><path d="M12 3v4M9 3h6M8 12h1M15 12h1M9 16h6"/>'],
  ];
 ?>
 <div class="overview-audience-metrics">
 <?php foreach ($audienceMetrics as [$key, $label, $value, $note, $icon]): ?>
  <article class="overview-metric tone-<?=e($key)?>"><div class="overview-metric-label"><span><?=e($label)?></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?=$icon?></svg></div><strong data-overview-metric="<?=e($key)?>"><?=e($value)?></strong><p><?=e($note)?></p></article>
 <?php endforeach; ?>
 </div>
 <div class="overview-audience-body">
  <div class="overview-traffic">
   <div class="overview-traffic-heading"><h3>Traffic over the week</h3><div class="overview-chart-legend"><span class="overview-key-visits">Visits</span><span class="overview-key-pageviews">Pageviews</span></div></div>
   <?php Audience::trend($audienceSnapshot['daily'], true); ?>
   <?php if (!$audienceSummary['pageviews']): ?><p class="overview-audience-empty">No public traffic recorded in these dates. New visits will appear here.</p><?php endif; ?>
   <details class="overview-daily"><summary>Daily numbers</summary><div class="table-scroll"><table class="content-table"><caption class="sr-only">Daily visits and pageviews for the seven-day overview snapshot</caption><thead><tr><th scope="col">Date (UTC)</th><th scope="col">Visits</th><th scope="col">Pageviews</th></tr></thead><tbody><?php foreach ($audienceSnapshot['daily'] as $day): ?><tr><td><?=e($day['date'])?></td><td><?=Audience::number((int)$day['visits'])?></td><td><?=Audience::number((int)$day['pageviews'])?></td></tr><?php endforeach; ?></tbody></table></div></details>
  </div>
  <aside class="overview-audience-highlights" aria-label="Audience highlights">
   <?php $topSource = $audienceSnapshot['referrers'][0] ?? null; $topCountry = $audienceSnapshot['countries'][0] ?? null; $topPage = $audienceSnapshot['pages'][0] ?? null; ?>
   <div><span>TOP REFERRER</span><strong><?=e($topSource['domain'] ?? 'No traffic yet')?></strong><small><?=$topSource ? Audience::number((int)$topSource['visits']).' visits' : 'Source appears when supplied'?></small></div>
   <div><span>TOP LOCATION</span><strong><?=e($topCountry['country'] ?? 'No location data')?></strong><small><?=$topCountry ? Audience::number((int)$topCountry['visits']).' visits · approximate country' : 'Approximate country estimates'?></small></div>
   <div><span>MOST VIEWED PAGE</span><strong><?=e($topPage['path'] ?? 'No pageviews yet')?></strong><small><?=$topPage ? Audience::number((int)$topPage['pageviews']).' pageviews' : 'Public page paths only'?></small></div>
  </aside>
 </div>
 <?php endif; ?>
 <footer class="overview-audience-footer"><p>Snapshot at <?=e($audienceAsOf)?> UTC. Live counts reflect visible-page activity within the last 60 seconds; reload to refresh. Country estimates: <a href="https://db-ip.com" target="_blank" rel="noopener noreferrer">IP Geolocation by DB-IP</a>.</p><nav aria-label="Explore audience reports"><a href="<?=e(app_url('admin/analytics/visitors.php?range=7'))?>">Explore visitors <span aria-hidden="true">→</span></a><a href="<?=e($audienceUrl.'#live-visitors')?>">Live report <span aria-hidden="true">→</span></a></nav></footer>
</section>
