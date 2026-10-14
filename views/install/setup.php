<?php declare(strict_types=1); ?>
<section class="installer-heading">
    <div><p class="eyebrow">SPIRITUAL GORILLAZ / WEBSITE SETUP</p><h1>SET THE <em>STAGE.</em></h1><p class="lead">Hosting checks, your existing database and your owner account—all on this page. No setup code or API token required.</p></div>
    <span class="version-stamp">DEVELOPMENT BUILD<br><strong>v<?= e(Spirit\Paths::version()) ?></strong></span>
</section>
<?php Spirit\View::notice($error); ?>
<div class="install-grid">
    <aside>
        <section class="panel" aria-labelledby="hosting-heading">
            <p class="step-label">HOSTING CHECKS</p><h2 id="hosting-heading">Ready for your stage?</h2>
            <?php foreach ($checks as $check): ?>
            <div class="check-row"><span class="check-icon <?= $check['ok'] ? 'pass' : ($check['required'] ? 'fail' : 'warn') ?>" aria-hidden="true"><?= $check['ok'] ? '✓' : '!' ?></span><div><strong><?= e($check['name']) ?> · <?= $check['ok'] ? 'Passed' : ($check['required'] ? 'Required' : 'Notice') ?></strong><p><?= e($check['detail']) ?></p></div></div>
            <?php endforeach; ?>
            <p class="small muted">A notice is optional for installation. cURL and ZIP are separate PHP extensions, not API tokens. Check the PHP version serving this domain; changing a different version will not resolve its notice. Refresh after enabling an extension.</p>
            <a class="text-link" href="<?= e(app_url('install/')) ?>">Refresh hosting checks ↻</a>
        </section>
        <div class="notice info"><strong>Install promptly after uploading.</strong><br>Setup is open until installation finishes. Use your hosting panel’s directory protection to restrict access while preparing an unattended staging site.</div>
        <p class="small muted">This cumulative build includes the website, administration, music, shows, booking and store modules. Live payment, email, content and hosting acceptance remain separate launch checks.</p>
    </aside>
    <section class="panel">
        <form method="post" action="<?= e(app_url('install/')) ?>" class="stack" id="install-form" data-ready="<?= $ready ? 'true' : 'false' ?>">
            <?= csrf_field() ?><input type="hidden" name="action" value="install">
            <p class="step-label">YOUR WEBSITE</p><h2>Connect. Create. Install.</h2>
            <label>Public website URL<input type="url" name="site_url" value="<?= e(post('site_url') ?: $defaultUrl) ?>" required placeholder="https://example.com/spirit" autocomplete="url"></label>
            <fieldset><legend>Database connection</legend>
                <p class="small muted">Use an existing MySQL/MariaDB database and its assigned database user. No cPanel API token or setup code is needed. The installer creates the website tables, not a database or database user.</p>
                <details><summary>Where to get the database details in A2 cPanel</summary><p>Open <strong>Database Wizard</strong> (or MySQL Database Wizard) or <strong>Manage My Databases</strong> in cPanel. Create an empty database, create a dedicated database user with a strong password, then assign that user to this database. Grant its table permissions on this database only. Copy the complete database and user names, including any cPanel account prefix.</p><p>Use the database user's password here, not your cPanel sign-in password. Keep an existing site's original database and private configuration when upgrading; do not create a replacement database.</p></details>
                <div class="field-grid">
                    <label>Database host<input name="db_host" value="<?= e(post('db_host') ?: 'localhost') ?>" required maxlength="253" autocomplete="off"></label>
                    <label>Port<input type="number" name="db_port" value="<?= e(post('db_port') ?: '3306') ?>" min="1" max="65535" required></label>
                    <label>Database name<input name="db_name" value="<?= e(post('db_name')) ?>" required maxlength="64" autocomplete="off"></label>
                    <label>Database username<input name="db_user" value="<?= e(post('db_user')) ?>" required maxlength="128" autocomplete="off"></label>
                </div>
                <label>Database password<input type="password" name="db_password" autocomplete="off"></label>
                <label>Table prefix<input name="db_prefix" value="<?= e(post('db_prefix') ?: 'sg_') ?>" required pattern="[a-z][a-z0-9_]{0,15}" maxlength="16"></label>
            </fieldset>
            <fieldset><legend>Your owner account</legend>
                <div class="field-grid">
                    <label>Your name<input name="owner_name" value="<?= e(post('owner_name')) ?>" required maxlength="120" autocomplete="name"></label>
                    <label>Email address<input type="email" name="email" value="<?= e(post('email')) ?>" required maxlength="190" autocomplete="email"></label>
                    <label>Owner password<input type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password"></label>
                    <label>Confirm password<input type="password" name="password_confirm" required minlength="12" maxlength="72" autocomplete="new-password"></label>
                </div>
                <p class="small muted">Use 12–72 bytes. There is no default password. Password fields are cleared if a submission needs correction.</p>
            </fieldset>
            <div class="notice info">One submission creates the website, locks installation, then attempts to delete the installer folder. The result and cleanup status appear here.</div>
            <?php if (!$ready): ?><div class="notice error" role="alert">Resolve the required hosting checks, then refresh this page. Nothing has been installed.</div><?php endif; ?>
            <button class="button wide" type="submit" <?= !$ready ? 'disabled' : '' ?>>Install website →</button>
            <p id="install-status" class="small muted" role="status" aria-live="polite" hidden>Checking the database, creating your owner account and securing the installation. Please keep this page open.</p>
        </form>
    </section>
</div>
