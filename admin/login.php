<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
if (!Spirit\Paths::installed()) { go('install/'); }
if (Spirit\Auth::current()) { go('admin/'); }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    if (Spirit\Auth::login(post('email'), password_input('password'))) { go('admin/'); }
    $error = 'Unable to sign in. Check your details, or wait 15 minutes after repeated attempts.';
}
Spirit\View::start('Staff sign in', 'login');
?>
<div class="auth-grid"><section class="auth-intro"><p class="eyebrow">WELCOME BACKSTAGE</p><h1>YOUR BAND.<br>YOUR <em>CONTROL.</em></h1><p class="lead">One home for the website you are building. Sign in with your owner account or an accepted staff invitation.</p><div class="gold-line"></div><p class="muted">Private access. No public administrator registration.</p></section>
<section class="panel auth-panel"><span class="step-label">STAFF ACCESS</span><h2>Sign in</h2><?php Spirit\View::notice($error); ?><form method="post" class="stack"><?= csrf_field() ?><label>Email address<input type="email" name="email" autocomplete="username" required maxlength="190" value="<?= e(post('email')) ?>"></label><label>Password<input type="password" name="password" autocomplete="current-password" required maxlength="72"></label><button class="button" type="submit">Enter backstage <span aria-hidden="true">→</span></button></form><p class="small muted"><a href="<?=e(app_url('admin/staff/recover.php'))?>">Forgot your staff password?</a></p></section></div>
<?php Spirit\View::end(); ?>
