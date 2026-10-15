<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
$user = Spirit\ContentAdmin::boot('owner'); $error = null;
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'POST'], true)) { header('Allow: GET, HEAD, POST'); http_response_code(405); exit('Method not allowed.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    try {
        Spirit\Branding::save($_POST, (int)$user['id'], post('action'));
        flash(match (post('action')) { 'publish'=>'Approved logo published in the website header and footer.', 'withdraw'=>'Published logo withdrawn. The text wordmark is active; your saved draft is preserved.', default=>'Logo draft saved privately. The public website is unchanged.' });
        go('admin/branding/');
    } catch (InvalidArgumentException|RuntimeException $e) { $error = $e instanceof PDOException ? 'The save could not complete. No branding changes were committed.' : $e->getMessage(); }
}
try { $state = Spirit\Branding::state(); }
catch (Throwable $e) { http_response_code(503); Spirit\View::start('Site logo', 'admin', 'settings', $user); echo '<section class="panel"><h1>Branding unavailable.</h1><p>The stored branding could not be read. Its original value has been preserved; review it before making changes.</p></section>'; Spirit\View::end(); exit; }
$values = $error ? ['logo_id'=>post('logo_id'), 'revision'=>post('revision'), 'restore_epoch'=>post('restore_epoch')] : ['logo_id'=>$state['draft_logo_id'], 'revision'=>$state['revision'], 'restore_epoch'=>Spirit\Branding::restoreEpoch()];
$settings = Spirit\Settings::all();
Spirit\View::start('Site logo', 'admin', 'settings', $user);
require Spirit\Paths::root().'/views/admin/branding.php';
Spirit\View::end();
