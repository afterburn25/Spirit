<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
$user=Spirit\Auth::requirePermission('dashboard');$db=Spirit\Database::connect();$ready=Spirit\Migrations::ready();
if(!Spirit\Permissions::allows($user,'owner')){Spirit\View::start('Your workspace','admin','dashboard',$user);require Spirit\Paths::root().'/app/views/admin/staff-dashboard.php';Spirit\View::end();exit;}
$activity=$db->query('SELECT action,created_at FROM '.Spirit\Database::table('audit').' ORDER BY id DESC LIMIT 8')->fetchAll();
$cleanup=!file_exists(Spirit\Paths::root().'/install');$counts=['release'=>0,'event'=>0,'page'=>0];
if($ready){$q=$db->query('SELECT kind,COUNT(*) AS total FROM '.Spirit\Database::table('content').' WHERE published_json IS NOT NULL AND deleted_at IS NULL GROUP BY kind');foreach($q as $row){$counts[$row['kind']]=(int)$row['total'];}}
$audienceSnapshot = null;
$audienceLive = ['available' => false, 'active_visitors' => 0];
$audienceEnabled = true;
[$audienceStart, $audienceEnd] = Spirit\AnalyticsAdmin::range(['range' => '7']);
try {
    $audienceSnapshot = Spirit\Analytics::report($audienceStart, $audienceEnd);
    $audienceLive = Spirit\Analytics::live();
    $audienceEnabled = Spirit\Analytics::settings()['enabled'];
} catch (Throwable) {
    // A failed optional audience report cannot stop the owner's workspace.
    $audienceSnapshot = null;
}
$audienceAsOf = gmdate('Y-m-d H:i:s');
header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
Spirit\View::start('Overview','admin','dashboard',$user);
?>
<div class="page-heading overview-heading"><div><p class="eyebrow">BACKSTAGE / OVERVIEW</p><h1>Welcome back, <em><?=e($user['name'])?>.</em></h1><p class="lead">Your audience, your music, and the next thing that needs attention.</p></div><a class="button" href="<?=e(app_url('admin/content/?kind=page'))?>">Manage website content</a></div>
<?php Spirit\View::notice();if(!$ready):?><div class="notice error">The new modules need an additive database update. <a href="<?=e(app_url('admin/updates.php'))?>">Open database updates</a>. Existing owner and website settings remain intact.</div><?php endif;?>
<?php require Spirit\Paths::root().'/app/views/admin/analytics-overview.php'; ?>
<div class="stats"><article class="stat"><span>PUBLISHED RELEASES</span><strong><?=$counts['release']?></strong><p>Approved music in your database</p></article><article class="stat"><span>PUBLISHED SHOWS</span><strong><?=$counts['event']?></strong><p>Venue ticket links, not internal ticket sales</p></article><article class="stat"><span>INSTALLER</span><strong><?=$cleanup?'Removed':'Locked'?></strong><p><?=$cleanup?'Physical folder removal verified':'Review cleanup in installation health'?></p></article></div>
<div class="content-grid"><section class="panel"><div class="section-title"><h2>Make the site your own</h2><span class="pill">v<?=e(Spirit\Paths::version())?></span></div><p>Upload approved artwork and photographs, add a release, publish a show, or introduce the band. Draft changes stay private until you publish them.</p><div class="button-row"><a class="button" href="<?=e(app_url('admin/media/'))?>">Upload media</a><a class="button secondary" href="<?=e(app_url('admin/content/edit.php?kind=event'))?>">Add a show</a></div><p class="small muted">Booking inquiries and the delivery queue are available in the navigation. Configure and test SMTP before enabling newsletter signup. Configure checkout under Orders & payments; test and production orders remain clearly separated.</p></section><section class="panel"><h2>Recent activity</h2><div class="activity"><?php if(!$activity):?><p class="muted">No activity recorded yet.</p><?php endif;foreach($activity as $item):?><div><strong><?=e(str_replace('.',' · ',$item['action']))?></strong><span><?=e($item['created_at'])?> UTC</span></div><?php endforeach;?></div></section></div>
<section class="panel"><h2>Check the installation before launch.</h2><p>Review PHP features, upload limits, email and checkout configuration, and published sections. Download a support report without exposing credentials or customer information. Local checks do not replace actual provider and owner acceptance.</p><a class="text-link" href="<?=e(app_url('admin/launch/'))?>">Open launch checks ↗</a></section>
<section class="panel"><h2>The selected design stays selected.</h2><p>The public website uses actual responsive components, an audio player and content from this administration area. Concept names, releases, shows and merchandise are not seeded as real band information.</p><a class="text-link" href="<?=e(app_url())?>" target="_blank" rel="noopener">Open the public website ↗</a></section>
<?php Spirit\View::end();?>
