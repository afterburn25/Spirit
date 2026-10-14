<?php declare(strict_types=1); ?>
<div class="page-heading"><div><p class="eyebrow">SYSTEM / LAUNCH CHECKS</p><h1>Check the details.<br><em>Before the spotlight.</em></h1><p class="lead">Current configuration, useful next steps and the checks that still require your real hosting, mailbox and merchant account.</p></div><a class="button secondary" href="<?=e(app_url('admin/launch/'))?>">Refresh checks</a></div>
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
<section class="panel" aria-labelledby="acceptance-heading"><h2 id="acceptance-heading">Still requires real-world acceptance</h2><p>These are review tasks, not unchecked features that this screen can turn green. Keep the development release on staging until the applicable evidence and owner approval are complete.</p><div class="launch-review-grid">
<article><h3>A2 staging and recovery</h3><p>Verify private storage against every public mapping, manual installation, actual installer deletion, HTTPS, permissions, uploads and supported backup/restore/upgrade recovery. Protect unattended setup; no setup code or hosting API token is used.</p></article>
<article><h3>Receiving mailbox</h3><p>Receive a real booking notice, account message and subscriber confirmation. Exercise unsubscribe and quiet-site scheduled delivery. SMTP settings alone do not prove inbox delivery.</p></article>
<article><h3>Genuine merchant sandbox</h3><p>Test actual tokenization, approval/decline, signed notifications, reporting, order recovery and reversals. Do not enable live payments based on simulated results or this screen.</p></article>
<article><h3>Approved content and final review</h3><p>Approve original artwork, band information, media rights, shows, merchandise and store policies. Review the populated site on phones and with keyboard/zoom, measure real-host performance, and obtain final owner approval.</p></article>
</div></section>
