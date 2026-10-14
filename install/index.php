<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) {
    header('Allow: GET, HEAD, POST'); http_response_code(405); exit('Method not allowed.');
}
if (Spirit\Paths::installed()) {
    http_response_code(410);
    Spirit\View::start('Installation locked', 'install');
    echo '<section class="panel form-width"><p class="eyebrow">INSTALLATION COMPLETE</p><h1>Already <em>protected.</em></h1><p>This installer is permanently locked. Sign in as the owner to review or retry installer-folder cleanup.</p><a class="button" href="' . e(app_url('admin/login.php')) . '">Owner sign in</a></section>';
    Spirit\View::end(); exit;
}
$error = null;
$completed = null;
if ($method === 'POST') {
    if (!Spirit\Security::validCsrf(post('csrf'))) {
        http_response_code(403);
        $error = 'This form expired. Review the details and submit again from this page.';
    } elseif (post('action') !== 'install') {
        http_response_code(400);
        $error = 'Use the installation form below. No installation was performed.';
    } else {
        try {
            $completed = Spirit\Installer::install([
                'db_mode' => post('db_mode') ?: 'existing',
                'cpanel' => ['host'=>post('cpanel_host'),'user'=>post('cpanel_user'),'token'=>password_input('cpanel_token')],
                'cpanel_consent'=>post('cpanel_consent'),
                'cpanel_db_host'=>post('cpanel_db_host') ?: 'localhost',
                'db' => ['host' => post('db_host'), 'port' => post('db_port'), 'name' => post('db_name'), 'user' => post('db_user'), 'password' => password_input('db_password'), 'prefix' => post('db_prefix')],
                'email' => post('email'), 'owner_name' => post('owner_name'), 'password' => password_input('password'), 'password_confirm' => password_input('password_confirm'), 'site_url' => post('site_url'),
            ]);
        } catch (InvalidArgumentException | RuntimeException $e) { $error = $e->getMessage(); }
    }
}
// No redirect or additional installer stage. Rendering can finish even after
// this running script's installer directory has been successfully removed.
Spirit\View::start($completed !== null ? 'Installation complete' : 'Set up your website', 'install');
if ($completed !== null) {
    require Spirit\Paths::root() . '/views/install/complete.php';
} else {
    $checks = Spirit\Installer::preflight();
    $ready = !array_filter($checks, static fn(array $check): bool => $check['required'] && !$check['ok']);
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');
    $defaultUrl = preg_match('/^[A-Za-z0-9.:-]+$/D', $host)
        ? (Spirit\Security::isHttps() ? 'https://' : 'http://') . $host . Spirit\Paths::basePath() : '';
    require Spirit\Paths::root() . '/views/install/setup.php';
}
Spirit\View::end();
