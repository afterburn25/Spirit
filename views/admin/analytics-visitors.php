<?php
use Spirit\AnalyticsAdmin as A;
use Spirit\AnalyticsVisitors as V;
$filters = $explorer['filters'];
$hasActiveFilters = count(array_filter(array_intersect_key($filters,array_flip(['channel','status','country','device','browser','os','source','region','city','path'])),static fn($value)=>$value!==''))>0;
$optionLabels = ['country'=>'All countries','device'=>'All devices','browser'=>'All browsers','os'=>'All operating systems','source'=>'All referring domains'];
$statusLabels = ['live'=>'Live now','recent'=>'Recent','completed'=>'Completed','bounced'=>'Single-page bounce'];
$place = static fn(array $visit): string => implode(', ',array_unique(array_filter([$visit['city'],$visit['region'],$visit['country']],static fn($value)=>$value!=='' && $value!=='Unknown'))) ?: 'Unknown';
?>
<div class="analytics-explorer">
<div class="page-heading analytics-heading"><div><p class="eyebrow">ANALYTICS / VISITOR EXPLORER</p><h1>Every visit, <em>in context.</em></h1><p class="lead">Follow anonymous sessions from their first page to their last recorded activity.</p></div><a class="button secondary" href="<?=e(app_url('admin/analytics/?start='.$start.'&end='.$end))?>">Analytics overview</a></div>
<?php Spirit\View::notice($error); ?>
<?php if (!$explorer['available']): ?><div class="notice error" role="alert">Visitor details are temporarily unavailable. Try again shortly.</div><?php endif; ?>
<details class="panel analytics-explorer-filters analytics-explorer-filter-details" <?=$hasActiveFilters?'open':''?>>
 <summary>Filter visitors <span class="small muted"><?=e($start)?> — <?=e($end)?><?=$hasActiveFilters?' · Filters active':''?></span></summary>
 <form method="get">
 <div class="analytics-filter-grid">
  <label>From<input type="date" name="start" value="<?=e($start)?>" max="<?=gmdate('Y-m-d')?>" required></label>
  <label>Through<input type="date" name="end" value="<?=e($end)?>" max="<?=gmdate('Y-m-d')?>" required></label>
  <label>Traffic channel<select name="channel"><option value="">All channels</option><?php foreach (V::CHANNELS as $key=>$label): ?><option value="<?=e($key)?>" <?=$filters['channel']===$key?'selected':''?>><?=e($label)?></option><?php endforeach; ?></select></label>
  <label>Visit status<select name="status"><option value="">All visits</option><?php foreach (V::STATUSES as $key=>$label): ?><option value="<?=e($key)?>" <?=$filters['status']===$key?'selected':''?>><?=e($label)?></option><?php endforeach; ?></select></label>
  <?php foreach (['country'=>'Country','device'=>'Device','browser'=>'Browser','os'=>'Operating system','source'=>'Referring domain'] as $key=>$label): ?><label><?=e($label)?><select name="<?=e($key)?>"><option value=""><?=e($optionLabels[$key])?></option><?php $seen=[]; foreach ($explorer['options'][$key] as $option): $seen[]=$option['value']; ?><option value="<?=e($option['value'])?>" <?=$filters[$key]===$option['value']?'selected':''?>><?=e($option['label'])?></option><?php endforeach; ?><?php if ($filters[$key]!=='' && !in_array($filters[$key],$seen,true)): ?><option value="<?=e($filters[$key])?>" selected><?=e($filters[$key])?></option><?php endif; ?></select></label><?php endforeach; ?>
  <label>Region<input name="region" value="<?=e($filters['region'])?>" maxlength="100" placeholder="Any region"></label>
  <label>City<input name="city" value="<?=e($filters['city'])?>" maxlength="100" placeholder="Any city"></label>
  <label>Visited page<input name="path" value="<?=e($filters['path'])?>" maxlength="255" placeholder="/music/"></label>
 </div>
 <div class="analytics-filter-actions"><button class="button" type="submit">Apply visitor filters</button><a class="button secondary" href="<?=e(app_url('admin/analytics/visitors.php'))?>">Reset filters</a><p class="small muted">Dates use UTC. Sessions qualify when a page was viewed during these dates; details show their retained session history.</p></div>
 </form>
</details>
<?php if ($explorer['available']): ?>
<?php if ($explorer['selected_missing']): ?><div class="notice" role="status">That visit is not available within these filters. It may be outside the selected dates or its history may have expired.</div><?php endif; ?>
<?php if ($session=$explorer['selected']): ?>
<section class="panel analytics-session-details" id="visit-detail" aria-labelledby="visit-detail-title" data-selected-visit="<?=$session['record_id']?>">
 <div class="section-title"><div><p class="eyebrow">ANONYMOUS SESSION</p><h2 id="visit-detail-title">Visit <?=e($session['label'])?></h2></div><span class="analytics-session-status status-<?=e($session['status'])?>"><?=e($statusLabels[$session['status']])?></span></div>
 <div class="analytics-visitor-summary"><div><span>Pages recorded</span><strong><?=A::number($session['page_count'])?></strong></div><div><span>Measured active time</span><strong><?=A::duration($session['measured_seconds'])?></strong></div><div><span>Traffic channel</span><strong><?=e($session['channel'])?></strong></div><div><span>Approximate location</span><strong><?=e($place($session))?></strong></div></div>
 <div class="analytics-visitor-grid">
  <div><h3>Arrival &amp; discovery</h3><dl><dt>First seen</dt><dd><?=e($session['started_at'])?> UTC</dd><dt>Last activity</dt><dd><?=e($session['last_seen'])?> UTC</dd><dt>Referring domain</dt><dd><?=e($session['referrer_domain']?:'Direct / unknown')?></dd><dt>Supplied search phrase</dt><dd><?=e($session['search_term']?:'Not supplied')?></dd><dt>Campaign source</dt><dd><?=e($session['utm_source']?:'Not supplied')?></dd><dt>Campaign medium</dt><dd><?=e($session['utm_medium']?:'Not supplied')?></dd><dt>Campaign name</dt><dd><?=e($session['utm_campaign']?:'Not supplied')?></dd></dl></div>
  <div><h3>Device &amp; location</h3><dl><dt>Device category</dt><dd><?=e($session['device'])?></dd><dt>Browser family</dt><dd><?=e($session['browser'])?></dd><dt>Operating system</dt><dd><?=e($session['os'])?></dd><dt>Country</dt><dd><?=e($session['country'])?></dd><dt>Region</dt><dd><?=e($session['region'])?></dd><dt>City</dt><dd><?=e($session['city'])?></dd></dl><p class="small muted">Location is an approximate network estimate. Client descriptions are browser-reported families.</p></div>
  <div><h3>Engagement &amp; journey</h3><dl><dt>First retained page</dt><dd><?=e($session['entry_path']??'Unavailable')?></dd><dt><?=$session['completed']?'Last page / exit':'Latest recorded page'?></dt><dd><?=e($session['last_path']??'Unavailable')?></dd><dt>Classic bounce</dt><dd><?=$session['bounce']===null?'Pending completion':($session['bounce']?'Yes — one page':'No — multiple pages')?></dd><dt>Retained pageviews</dt><dd><?=A::number($session['retained_pageviews'])?></dd><dt>Timing coverage</dt><dd><?=A::number($session['measured_pageviews'])?> of <?=A::number($session['retained_pageviews'])?> retained pages measured</dd></dl><p class="small muted">A session completes after 30 minutes without activity. Missing timing remains unmeasured.</p></div>
 </div>
 <div class="section-title"><div><p class="eyebrow">PAGE BY PAGE</p><h3>Session journey</h3></div><span class="small muted"><?=A::number($explorer['journey_total'])?> retained pageviews</span></div>
 <ol class="analytics-journey" start="<?=($explorer['journey_page']-1)*V::JOURNEY_PAGE_SIZE+1?>">
 <?php foreach ($explorer['journey'] as $step): ?><li class="analytics-journey-card"><span class="analytics-journey-step" aria-hidden="true"><?=$step['sequence']?></span><div><strong><?=e($step['page_path'])?></strong><p class="small muted">Viewed <?=e($step['viewed_at'])?> UTC · Last activity <?=e($step['last_seen'])?> UTC</p><?php if (!$step['in_period']): ?><span class="pill">Outside selected dates</span><?php endif; ?><?php if ($step['is_live']): ?><span class="analytics-session-status status-live">Live page</span><?php endif; ?></div><div class="analytics-journey-time"><strong><?=$step['measured']?A::duration($step['active_seconds']):'Unmeasured'?></strong><span class="small muted">active viewing</span></div></li><?php endforeach; ?>
 </ol>
 <?php if ($explorer['journey_pages']>1): ?><nav class="analytics-pagination" aria-label="Session journey pages"><?php if ($explorer['journey_page']>1): ?><a class="button secondary" href="<?=e(V::url($explorer,['journey_page'=>$explorer['journey_page']-1],'#visit-detail'))?>">Earlier pages</a><?php endif; ?><span>Journey page <?=$explorer['journey_page']?> of <?=$explorer['journey_pages']?></span><?php if ($explorer['journey_page']<$explorer['journey_pages']): ?><a class="button secondary" href="<?=e(V::url($explorer,['journey_page'=>$explorer['journey_page']+1],'#visit-detail'))?>">Later pages</a><?php endif; ?></nav><?php endif; ?>
 <p class="small muted">Anonymous session identifiers cannot identify a person or connect their different devices. This report contains retained public page activity; account, cart, checkout and form contents are excluded.</p>
</section>
<?php endif; ?>
<section class="panel analytics-visitor-list" id="visitor-list" aria-labelledby="visitor-list-title">
 <div class="section-title"><div><p class="eyebrow">VISITOR HISTORY</p><h2 id="visitor-list-title"><?=A::number($explorer['total'])?> matching visits</h2></div><span class="small muted">Newest activity first · <?=$explorer['page_size']?> per page</span></div>
 <div class="table-scroll"><table class="content-table"><caption class="sr-only">Anonymous visitor sessions matching the selected date range and filters</caption><thead><tr><th scope="col">Visit / activity</th><th scope="col">Source</th><th scope="col">Location</th><th scope="col">Device / software</th><th scope="col">Engagement</th><th scope="col">Journey</th></tr></thead><tbody>
 <?php if (!$explorer['visitors']): ?><tr><td colspan="6" class="analytics-empty">No visits match these filters. Try a wider date range or reset the filters.</td></tr><?php endif; ?>
 <?php foreach ($explorer['visitors'] as $visit): ?><tr data-visitor-id="<?=$visit['record_id']?>"><td><strong><?=e($visit['label'])?></strong><span class="analytics-session-status status-<?=e($visit['status'])?>"><?=e($statusLabels[$visit['status']])?></span><small><?=e($visit['last_seen'])?> UTC</small></td><td><?=e($visit['referrer_domain']?:'Direct / unknown')?><small><?=e($visit['channel'])?></small></td><td><?=e($place($visit))?></td><td><?=e($visit['device'])?><small><?=e($visit['browser'])?> · <?=e($visit['os'])?></small></td><td><?=A::number($visit['page_count'])?> pages<small><?=A::duration($visit['measured_seconds'])?> active</small></td><td><a class="text-link" href="<?=e(V::url($explorer,['visit'=>(string)$visit['record_id'],'journey_page'=>1],'#visit-detail'))?>" aria-label="<?=e('Inspect visit '.$visit['label'])?>">Inspect visit →</a></td></tr><?php endforeach; ?>
 </tbody></table></div>
 <nav class="analytics-pagination" aria-label="Visitor result pages"><?php if ($explorer['page']>1): ?><a class="button secondary" href="<?=e(V::url($explorer,['page'=>$explorer['page']-1,'visit'=>'','journey_page'=>1],'#visitor-list'))?>">Previous visits</a><?php endif; ?><span>Page <?=$explorer['page']?> of <?=$explorer['pages']?></span><?php if ($explorer['page']<$explorer['pages']): ?><a class="button secondary" href="<?=e(V::url($explorer,['page'=>$explorer['page']+1,'visit'=>'','journey_page'=>1],'#visitor-list'))?>">Next visits</a><?php endif; ?></nav>
</section>
<?php endif; ?>
<p class="small muted analytics-explorer-attribution">Country estimates: <a href="https://db-ip.com" target="_blank" rel="noopener noreferrer">IP Geolocation by DB-IP</a>. Locations are approximate network estimates.</p>
</div>
