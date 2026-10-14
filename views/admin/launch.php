<?php declare(strict_types=1); ?>
<div class="page-heading"><div><p class="eyebrow">SYSTEM / LAUNCH CHECKS</p><h1>Prepare the <em>next chapter.</em></h1><p class="lead">Read-only observations from this installation—not a certificate of production readiness and not a connection to your hosting or merchant account.</p></div><a class="button secondary" href="<?=e(app_url('admin/launch/'))?>">Refresh checks</a></div>
<div class="notice info"><strong>Observations are not launch approval.</strong> This screen does not send email, contact a payment provider, create databases, change settings or verify backups. An observed extension or a published record is not proof of a working end-to-end workflow.</div>
<div class="launch-summary" aria-label="Check summary">
<?php foreach(Spirit\LaunchChecks::STATES as $state=>$label):?><section class="panel"><span class="small muted"><?=e($label)?></span><p class="launch-count"><?=(int)$snapshot['counts'][$state]?></p></section><?php endforeach;?>
</div>
<section class="panel"><h2>Support report without secrets</h2><p>The report includes software versions, extension availability, numeric PHP limits and check states. It leaves out hostnames, paths, credentials, database names, logs, customer details and content text. Review it before sharing with A2 support.</p><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="export"><button class="button" type="submit">Download support report</button></form><p class="small muted">Checked <?=e($snapshot['checked_at'])?> · v<?=e($snapshot['version'])?>. No report is saved on the website.</p></section>
<?php foreach(Spirit\LaunchChecks::GROUPS as $group=>$heading):?>
<section class="panel launch-group" aria-labelledby="group-<?=e($group)?>"><h2 id="group-<?=e($group)?>"><?=e($heading)?></h2>
<?php foreach($snapshot['checks'] as $check):if($check['group']!==$group){continue;}?>
<article class="launch-check" id="check-<?=e($check['id'])?>"><div><h3><?=e($check['title'])?></h3><p><?=e($check['detail'])?></p><a class="text-link" href="<?=e(app_url($check['path']))?>">Open relevant settings ↗</a></div><span class="pill launch-state state-<?=e($check['state'])?>"><?=e(Spirit\LaunchChecks::STATES[$check['state']])?></span></article>
<?php endforeach;?></section>
<?php endforeach;?>
