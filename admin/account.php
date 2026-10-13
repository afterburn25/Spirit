<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$user = Spirit\Auth::requireOwner(); $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    try {
        $db = Spirit\Database::connect();
        $q = $db->prepare('SELECT password_hash FROM ' . Spirit\Database::table('users') . ' WHERE id=?'); $q->execute([$user['id']]);
        if (!password_verify(password_input('current_password'), $q->fetchColumn())) { throw new InvalidArgumentException('The current password is incorrect.'); }
        $hash = Spirit\Security::password(password_input('new_password'));
        if (password_input('new_password') !== password_input('confirm_password')) { throw new InvalidArgumentException('New passwords do not match.'); }
        $db->beginTransaction();
        try {
            $q = $db->prepare('UPDATE ' . Spirit\Database::table('users') . ' SET password_hash=?,session_version=session_version+1 WHERE id=?'); $q->execute([$hash, $user['id']]);
            Spirit\Database::audit($db, (int)$user['id'], 'owner.password_changed'); $db->commit();
        } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
        $_SESSION['session_version'] = (int)$user['session_version'] + 1; session_regenerate_id(true);
        flash('Password updated. Other signed-in sessions have been revoked.'); go('admin/account.php');
    } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
Spirit\View::start('Account security', 'admin', 'account', $user);
?>
<div class="page-heading"><div><p class="eyebrow">OWNER / SECURITY</p><h1>Keep backstage <em>private.</em></h1><p class="lead">Signed in as <?= e($user['email']) ?>.</p></div></div>
<?php Spirit\View::notice($error); ?>
<form class="panel stack form-width" method="post"><?= csrf_field() ?><h2>Change your password</h2><label>Current password<input type="password" name="current_password" autocomplete="current-password" required maxlength="72"></label><label>New password<input type="password" name="new_password" autocomplete="new-password" required minlength="12" maxlength="72"></label><label>Confirm new password<input type="password" name="confirm_password" autocomplete="new-password" required minlength="12" maxlength="72"></label><p class="small muted">Use 12–72 bytes. Updating the password invalidates your other sessions.</p><button class="button" type="submit">Update password</button></form>
<?php Spirit\View::end(); ?>
