<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$user = Spirit\Auth::requireOwner(); $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    $submitted = ['site_name' => post('site_name'), 'tagline' => post('tagline'), 'welcome' => post('welcome')];
    if (array_key_exists('footer_copyright', $_POST)) { $submitted['footer_copyright'] = $_POST['footer_copyright']; }
    try { Spirit\Settings::save($submitted, (int)$user['id']); flash('Website settings saved.'); go('admin/settings.php'); }
    catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
$values = Spirit\Settings::all();
if ($error) {
    foreach (['site_name', 'tagline', 'welcome', 'footer_copyright'] as $key) {
        if (isset($submitted[$key]) && is_string($submitted[$key])) { $values[$key] = $submitted[$key]; }
    }
}
Spirit\View::start('Website settings', 'admin', 'settings', $user);
?>
<div class="page-heading"><div><p class="eyebrow">SETTINGS &amp; ACCESS / WEBSITE SETTINGS</p><h1>Make it <em>yours.</em></h1><p class="lead">Update the website name, tagline, default introduction and footer copyright. Manage the approved logo separately.</p></div></div>
<?php Spirit\View::notice($error); ?><section class="panel"><h2>Site logo</h2><p>Choose approved artwork for the public header and footer, preview it privately, then publish.</p><a class="button outline" href="<?=e(app_url('admin/branding/'))?>">Manage site logo →</a></section>
<form class="panel stack form-width" method="post"><?= csrf_field() ?><label>Website name <span class="muted">120 bytes maximum</span><input name="site_name" required maxlength="120" value="<?= e($values['site_name']) ?>"></label><label>Tagline <span class="muted">200 bytes maximum</span><input name="tagline" required maxlength="200" value="<?= e($values['tagline']) ?>"></label><label>Welcome message <span class="muted">1200 bytes maximum; plain text</span><textarea name="welcome" rows="5" maxlength="1200" required><?= e($values['welcome']) ?></textarea></label><label>Footer copyright <span class="muted"><?=Spirit\Settings::FOOTER_COPYRIGHT_LIMIT?> bytes maximum; one line of plain text</span><input name="footer_copyright" maxlength="<?=Spirit\Settings::FOOTER_COPYRIGHT_LIMIT?>" value="<?=e($values['footer_copyright'] ?? '')?>" placeholder="<?=e(Spirit\Settings::footerCopyright(['site_name' => $values['site_name']]))?>" aria-describedby="footer-copyright-help"></label><p id="footer-copyright-help" class="small muted">Enter the complete copyright line, including any © symbol and year. Leave blank to use the current year and website name automatically. The website developer credit beneath it is fixed.</p><button class="button" type="submit">Save website settings</button><p class="small muted">These text settings publish immediately. Use Pages &amp; homepage for section drafts and previews, or Site logo for a private logo draft.</p></form>
<?php Spirit\View::end(); ?>
