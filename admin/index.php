<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$user = Spirit\Auth::requireOwner();
$db = Spirit\Database::connect();
$owners = (int)$db->query('SELECT COUNT(*) FROM ' . Spirit\Database::table('users') . ' WHERE active=1')->fetchColumn();
$events = $db->query('SELECT action,created_at FROM ' . Spirit\Database::table('audit') . ' ORDER BY id DESC LIMIT 6')->fetchAll();
$cleanup = !file_exists(Spirit\Paths::root() . '/install');
Spirit\View::start('Overview', 'admin', 'dashboard', $user);
?>
<div class="page-heading"><div><p class="eyebrow">BACKSTAGE / OVERVIEW</p><h1>Welcome back, <em><?= e($user['name']) ?>.</em></h1><p class="lead">Your website foundation is connected. Here is what is ready today.</p></div><a class="button secondary" href="<?= e(app_url('admin/settings.php')) ?>">Edit website settings</a></div>
<?php Spirit\View::notice(); ?>
<div class="stats"><article class="stat"><span>DATABASE</span><strong>Connected</strong><p>Real connection verified</p></article><article class="stat"><span>ACTIVE ACCOUNTS</span><strong><?= $owners ?></strong><p>From your database</p></article><article class="stat"><span>INSTALLER</span><strong><?= $cleanup ? 'Removed' : 'Locked' ?></strong><p><?= $cleanup ? 'Directory no longer exists' : 'Review cleanup in installation health' ?></p></article></div>
<div class="content-grid"><section class="panel"><div class="section-title"><h2>Foundation is in place</h2><span class="pill">v<?= e(Spirit\Paths::version()) ?></span></div><p>Owner authentication, editable website settings, private runtime storage, the browser installer and cleanup diagnostics are implemented in this development build.</p><div class="notice info">The complete public design, music, shows, booking and store are not implemented yet. No sample sales or fabricated activity are shown.</div><a class="button" href="<?= e(app_url('admin/roadmap.php')) ?>">View the release roadmap →</a></section>
<section class="panel"><h2>Recent activity</h2><div class="activity"><?php if (!$events): ?><p class="muted">No activity recorded yet.</p><?php endif; foreach ($events as $event): ?><div><strong><?= e(str_replace('.', ' · ', $event['action'])) ?></strong><span><?= e($event['created_at']) ?> UTC</span></div><?php endforeach; ?></div></section></div>
<section class="panel next-module"><div><p class="eyebrow">NEXT MILESTONE / v0.2.0</p><h2>The approved design. Built for real.</h2><p>Black and navy, gold controls, blue lighting and the crowned-gorilla identity. The selected visual direction stays fixed.</p></div><span class="status-dot">PLANNED</span></section>
<?php Spirit\View::end(); ?>
