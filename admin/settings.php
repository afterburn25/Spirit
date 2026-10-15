<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$user = Spirit\Auth::requireOwner(); $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    try { Spirit\Settings::save(['site_name' => post('site_name'), 'tagline' => post('tagline'), 'welcome' => post('welcome')], (int)$user['id']); flash('Website settings saved.'); go('admin/settings.php'); }
    catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
$values = $error ? ['site_name' => post('site_name'), 'tagline' => post('tagline'), 'welcome' => post('welcome')] : Spirit\Settings::all();
Spirit\View::start('Website settings', 'admin', 'settings', $user);
?>
<div class="page-heading"><div><p class="eyebrow">CONTENT / WEBSITE SETTINGS</p><h1>Make it <em>yours.</em></h1><p class="lead">Update the website name, tagline and default introduction. Manage the approved logo separately.</p></div></div>
<?php Spirit\View::notice($error); ?><section class="panel"><h2>Site logo</h2><p>Choose approved artwork for the public header and footer, preview it privately, then publish.</p><a class="button outline" href="<?=e(app_url('admin/branding/'))?>">Manage site logo →</a></section>
<form class="panel stack form-width" method="post"><?= csrf_field() ?><label>Website name <span class="muted">120 bytes maximum</span><input name="site_name" required maxlength="120" value="<?= e($values['site_name']) ?>"></label><label>Tagline <span class="muted">200 bytes maximum</span><input name="tagline" required maxlength="200" value="<?= e($values['tagline']) ?>"></label><label>Welcome message <span class="muted">1200 bytes maximum; plain text</span><textarea name="welcome" rows="5" maxlength="1200" required><?= e($values['welcome']) ?></textarea></label><button class="button" type="submit">Save website settings</button><p class="small muted">These text settings publish immediately. Use Pages &amp; homepage for section drafts and previews, or Site logo for a private logo draft.</p></form>
<?php Spirit\View::end(); ?>
