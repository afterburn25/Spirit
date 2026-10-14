<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$user = Spirit\Auth::requireOwner();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    $result = Spirit\Filesystem::cleanup(Spirit\Paths::root());
    Spirit\Filesystem::atomicPhp(Spirit\Paths::runtime() . '/cleanup.php', $result);
    Spirit\Database::audit(Spirit\Database::connect(), (int)$user['id'], $result['removed'] ? 'installer.removed' : 'installer.cleanup_blocked');
    flash($result['message']); go('admin/installation.php');
}
$removed = !file_exists(Spirit\Paths::root() . '/install') && !is_link(Spirit\Paths::root() . '/install');
$writable = Spirit\Filesystem::probe(Spirit\Paths::runtime());
Spirit\View::start('Installation health', 'admin', 'installation', $user);
?>
<div class="page-heading"><div><p class="eyebrow">SYSTEM / INSTALLATION HEALTH</p><h1>A protected <em>foundation.</em></h1><p class="lead">Actual installation state. No permission bypasses or destructive reinstall.</p></div></div>
<?php Spirit\View::notice(); ?>
<section class="panel"><div class="check-row"><span class="check-icon pass">✓</span><div><strong>Installation permanently locked</strong><p>Configuration and the private completion marker block the installer.</p></div><span class="pill">LOCKED</span></div><div class="check-row"><span class="check-icon <?= $removed ? 'pass' : 'warn' ?>"><?= $removed ? '✓' : '!' ?></span><div><strong>Installer folder <?= $removed ? 'removed' : 'still present' ?></strong><p><?= $removed ? 'Physical directory removal has been verified.' : 'Your host may restrict deletion. The installer remains unusable; retry cleanup below.' ?></p></div></div><div class="check-row"><span class="check-icon <?= $writable ? 'pass' : 'warn' ?>"><?= $writable ? '✓' : '!' ?></span><div><strong>Private storage <?= $writable ? 'verified' : 'needs attention' ?></strong><p>Create, write, read, rename and delete probe.</p></div></div><dl class="details"><dt>Application version</dt><dd><?= e(Spirit\Paths::version()) ?></dd><dt>Private runtime</dt><dd><code><?= e(Spirit\Paths::runtime()) ?></code></dd><dt>Public base path</dt><dd><?= e(Spirit\Paths::basePath() ?: '/') ?></dd></dl><?php if (!$removed): ?><form method="post"><?= csrf_field() ?><button class="button" type="submit">Retry safe installer cleanup</button></form><?php endif; ?></section>
<section class="panel" aria-labelledby="optional-extensions"><h2 id="optional-extensions">Optional PHP features</h2><p>These checks use the PHP environment serving this page: <strong><?= e(PHP_VERSION) ?></strong>. Installation does not need either extension. Payment connections need cURL; browser ZIP upgrades need ZIP.</p>
<?php foreach (Spirit\Installer::optionalChecks() as $check): ?>
<div class="check-row"><span class="check-icon <?= $check['ok'] ? 'pass' : 'warn' ?>" aria-hidden="true"><?= $check['ok'] ? '✓' : '!' ?></span><div><strong><?= e($check['name']) ?> · <?= $check['ok'] ? 'Passed' : 'Notice' ?></strong><p><?= e($check['detail']) ?></p></div></div>
<?php endforeach; ?>
<p class="small muted">In A2 cPanel, use Select PHP Version → Extensions for this domain. If the controls are unavailable or the notice remains, ask A2 support to enable the extension for the site's actual PHP version. No API token is needed. Reload this page to recheck.</p></section>
<section class="panel"><h2>Keep these boundaries intact</h2><p>Do not move private runtime files into the website folder or delete configuration to reinstall. Owner backups and same-schema browser ZIP upgrades are available under their documented checks. Keep the separate backup and update recovery keys, the original configuration, database and uploads.</p></section>
<?php Spirit\View::end(); ?>
