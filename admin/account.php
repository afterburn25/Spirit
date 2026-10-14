<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$user = Spirit\ContentAdmin::boot('account'); $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    try {
        Spirit\Auth::changePassword($user,password_input('current_password'),password_input('new_password'),password_input('confirm_password'));
        flash('Password updated. Other signed-in sessions have been revoked.'); go('admin/account.php');
    } catch (InvalidArgumentException|RuntimeException $e) { $error = Spirit\Storefront::error($e); }
}
Spirit\View::start('Account security', 'admin', 'account', $user);
?>
<div class="page-heading"><div><p class="eyebrow">STAFF / SECURITY</p><h1>Keep backstage <em>private.</em></h1><p class="lead">Signed in as <?= e($user['email']) ?>.</p></div></div>
<?php Spirit\View::notice($error); ?>
<form class="panel stack form-width" method="post"><?= csrf_field() ?><h2>Change your password</h2><label>Current password<input type="password" name="current_password" autocomplete="current-password" required maxlength="72"></label><label>New password<input type="password" name="new_password" autocomplete="new-password" required minlength="12" maxlength="72"></label><label>Confirm new password<input type="password" name="confirm_password" autocomplete="new-password" required minlength="12" maxlength="72"></label><p class="small muted">Use 12–72 bytes. Updating the password invalidates your other sessions.</p><button class="button" type="submit">Update password</button></form>
<?php Spirit\View::end(); ?>
