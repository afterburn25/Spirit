<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
$user = Spirit\ContentAdmin::boot();
header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
$error = null;
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'POST'], true)) { header('Allow: GET, HEAD, POST'); http_response_code(405); exit; }
if (Spirit\HostingLimits::postTooLarge()) { http_response_code(413); $error = Spirit\HostingLimits::oversizedMessage(); }
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    try {
        $action = post('action');
        if ($action === 'settings') {
            Spirit\Analytics::saveSettings($_POST, (int)$user['id']);
            flash('Analytics settings saved.'); go('admin/analytics/');
        } elseif ($action === 'prune') {
            Spirit\Auth::reauthenticateOwner((int)$user['id'], password_input('owner_password'));
            $removed = Spirit\Analytics::prune(500);
            Spirit\Database::audit(Spirit\Database::connect(), (int)$user['id'], 'analytics.retention_pruned');
            flash('Removed '.array_sum($removed).' expired analytics records in this bounded cleanup. Repeat if a backlog remains.'); go('admin/analytics/');
        } elseif ($action === 'query-import') {
            $count = Spirit\AnalyticsSearchConsole::import(is_array($_FILES['queries'] ?? null) ? $_FILES['queries'] : [], $_POST, (int)$user['id']);
            flash('Imported '.$count.' Search Console queries. The snapshot shows the dates you selected.'); go('admin/analytics/#search-console');
        } elseif ($action === 'query-clear') {
            Spirit\AnalyticsSearchConsole::clear((int)$user['id']); flash('The imported query snapshot was removed. Visitor analytics is unchanged.'); go('admin/analytics/#search-console');
        } elseif ($action === 'export') {
            [$start, $end] = Spirit\AnalyticsAdmin::range($_POST);
            Spirit\AnalyticsAdmin::export(Spirit\Analytics::report($start, $end)); exit;
        } else { throw new InvalidArgumentException('Choose a supported analytics action.'); }
    } catch (InvalidArgumentException|RuntimeException $exception) {
        $error = $exception instanceof PDOException ? 'Analytics could not be updated. Please try again.' : $exception->getMessage();
    }
}
try { [$start, $end] = Spirit\AnalyticsAdmin::range($_GET); }
catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); [$start, $end] = Spirit\AnalyticsAdmin::range([]); }
$report = Spirit\Analytics::report($start, $end);
$live = Spirit\Analytics::live();
$options = Spirit\Analytics::settings();
$geo = Spirit\AnalyticsGeo::status();
$searchConsole = Spirit\AnalyticsSearchConsole::snapshot();
Spirit\View::start('Audience analytics', 'admin', 'analytics', $user);
require Spirit\Paths::root().'/views/admin/analytics.php';
Spirit\View::end();
