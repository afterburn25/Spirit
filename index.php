<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
if (!Spirit\Paths::installed()) { go('install/'); }
$settings = Spirit\Settings::all();
Spirit\View::start($settings['site_name']);
?>
<section class="holding-hero"><p class="eyebrow">MUSIC. PEOPLE. PURPOSE.</p><h1>MUSIC WITH<br>A HIGHER <em>PURPOSE.</em></h1><div class="gold-line"></div><p class="lead"><?= e($settings['welcome']) ?></p><span class="pill">NEW WEBSITE IN DEVELOPMENT</span></section>
<section class="panel public-note"><h2><?= e($settings['site_name']) ?></h2><p><?= e($settings['tagline']) ?></p><p class="muted">This is the installation foundation. The approved full band website is being built next.</p><a class="button secondary" href="<?= e(app_url('admin/login.php')) ?>">Owner sign in</a></section>
<?php Spirit\View::end(); ?>
