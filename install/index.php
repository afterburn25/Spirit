<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
header('Cache-Control: no-store');
if (Spirit\Paths::installed()) {
    http_response_code(410);
    Spirit\View::start('Installation locked', 'install');
    echo '<section class="panel form-width"><p class="eyebrow">INSTALLATION COMPLETE</p><h1>Already <em>protected.</em></h1><p>This installer is permanently locked. Sign in as the owner to review or retry installer-folder cleanup.</p><a class="button" href="' . e(app_url('admin/login.php')) . '">Owner sign in</a></section>';
    Spirit\View::end(); exit;
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Spirit\Security::checkPost();
    if (post('action') === 'claim') {
        if (Spirit\Installer::claim(post('setup_code'))) { go('install/'); }
        $error = 'The setup code is incorrect. Use the private setup receipt supplied with this package.';
    } elseif (post('action') === 'install') {
        try {
            Spirit\Installer::install([
                'db' => ['host' => post('db_host'), 'port' => post('db_port'), 'name' => post('db_name'), 'user' => post('db_user'), 'password' => password_input('db_password'), 'prefix' => post('db_prefix')],
                'email' => post('email'), 'owner_name' => post('owner_name'), 'password' => password_input('password'), 'password_confirm' => password_input('password_confirm'), 'site_url' => post('site_url'),
            ]);
            go('admin/login.php');
        } catch (InvalidArgumentException | RuntimeException $e) { $error = $e->getMessage(); }
    }
}
Spirit\View::start('Set up your website', 'install');
$claimed = Spirit\Installer::claimed();
?>
<section class="installer-heading"><div><p class="eyebrow">SPIRITUAL GORILLAZ / WEBSITE SETUP</p><h1>SET THE <em>STAGE.</em></h1><p class="lead">Your website starts here. A guided setup, private storage and a protected owner account.</p></div><span class="version-stamp">DEVELOPMENT BUILD<br><strong>v<?= e(Spirit\Paths::version()) ?></strong></span></section>
<div class="stepper" aria-label="Installation progress"><span class="<?= !$claimed ? 'current' : 'done' ?>">01 <b>Authorize</b></span><span class="<?= $claimed ? 'current' : '' ?>">02 <b>Verify & configure</b></span><span>03 <b>Install & secure</b></span></div>
<?php Spirit\View::notice($error); ?>
<?php if (!$claimed): ?>
<div class="content-grid"><section class="panel"><p class="step-label">01 / VERIFY OWNERSHIP</p><h2>Only you can start setup.</h2><p>Enter the unique setup code delivered with your installation ZIP. It prevents another visitor from claiming the first owner account.</p>
<?php if (Spirit\Installer::setupHash() === null): ?><div class="notice error">This source checkout has no installation key. Build a deployment package using tools/package.py on your development machine. The shared server does not need Python or SSH.</div>
<?php else: ?><form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="claim"><label>Private setup code<input type="password" name="setup_code" autocomplete="off" required maxlength="128" placeholder="From your private setup receipt"></label><button class="button" type="submit">Verify & continue →</button></form><?php endif; ?></section><aside class="panel quiet"><p class="step-label">BUILT FOR SHARED HOSTING</p><h2>No terminal required.</h2><div class="feature-lines"><p><strong>Private by default</strong><span>Credentials stay outside the public web root.</span></p><p><strong>Writes are tested</strong><span>Real create, write, read, rename and delete checks.</span></p><p><strong>Protected after setup</strong><span>Permanent lock and verified installer cleanup.</span></p></div><p class="small muted">Keep the setup receipt private. Upload only the website files, not the receipt.</p></aside></div>
<?php else:
$checks = Spirit\Installer::preflight(); $ready = true;
foreach ($checks as $check) { if ($check['required'] && !$check['ok']) { $ready = false; } }
$host = (string)($_SERVER['HTTP_HOST'] ?? '');
$defaultUrl = preg_match('/^[A-Za-z0-9.:-]+$/D', $host) ? (Spirit\Security::isHttps() ? 'https://' : 'http://') . $host . Spirit\Paths::basePath() : '';
?>
<div class="install-grid"><aside><section class="panel"><p class="step-label">HOSTING PREFLIGHT</p><h2>Check. Then connect.</h2><?php foreach ($checks as $check): ?><div class="check-row"><span class="check-icon <?= $check['ok'] ? 'pass' : ($check['required'] ? 'fail' : 'warn') ?>"><?= $check['ok'] ? '✓' : '!' ?></span><div><strong><?= e($check['name']) ?></strong><p><?= e($check['detail']) ?></p></div></div><?php endforeach; ?><a class="text-link" href="<?= e(app_url('install/')) ?>">Run checks again ↻</a></section><div class="notice info">This development release installs the foundation and admin. Music, events, booking and commerce arrive in later milestones.</div></aside>
<section class="panel"><form method="post" class="stack" id="install-form"><?= csrf_field() ?><input type="hidden" name="action" value="install"><p class="step-label">02 / YOUR CONFIGURATION</p><h2>Connect your website.</h2><label>Public website URL<input type="url" name="site_url" value="<?= e(post('site_url') ?: $defaultUrl) ?>" required placeholder="https://example.com/spirit"></label><fieldset><legend>Database connection</legend><p class="small muted">Create an empty MySQL/MariaDB database and database user in your hosting panel, then enter those details here. Existing tables are never replaced.</p><div class="field-grid"><label>Database host<input name="db_host" value="<?= e(post('db_host') ?: 'localhost') ?>" required maxlength="253"></label><label>Port<input type="number" name="db_port" value="<?= e(post('db_port') ?: '3306') ?>" min="1" max="65535" required></label><label>Database name<input name="db_name" value="<?= e(post('db_name')) ?>" required maxlength="64" autocomplete="off"></label><label>Database username<input name="db_user" value="<?= e(post('db_user')) ?>" required maxlength="128" autocomplete="off"></label></div><label>Database password<input type="password" name="db_password" autocomplete="off"></label><label>Table prefix<input name="db_prefix" value="<?= e(post('db_prefix') ?: 'sg_') ?>" required pattern="[a-z][a-z0-9_]{0,15}" maxlength="16"></label></fieldset><fieldset><legend>Your owner account</legend><div class="field-grid"><label>Your name<input name="owner_name" value="<?= e(post('owner_name')) ?>" required maxlength="120" autocomplete="name"></label><label>Email address<input type="email" name="email" value="<?= e(post('email')) ?>" required maxlength="190" autocomplete="email"></label><label>Owner password<input type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password"></label><label>Confirm password<input type="password" name="password_confirm" required minlength="12" maxlength="72" autocomplete="new-password"></label></div><p class="small muted">Use 12–72 bytes. There is no default password.</p></fieldset><div class="notice info">Completion locks the installer first, then attempts to delete its folder. If the host blocks deletion, the owner dashboard reports it and offers a safe retry.</div><button class="button wide" type="submit" <?= !$ready ? 'disabled' : '' ?>>Install & secure website →</button><p id="install-status" class="small muted" role="status" hidden>Verifying the database and provisioning private storage. Please keep this page open.</p></form></section></div>
<?php endif; Spirit\View::end(); ?>
