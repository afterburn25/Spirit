<?php declare(strict_types=1); ?>
<section class="installer-heading">
    <div><p class="eyebrow">SPIRITUAL GORILLAZ / WEBSITE SETUP</p><h1>SET THE <em>STAGE.</em></h1><p class="lead">Hosting checks, database connection and your owner account—all on this page. No setup code required.</p></div>
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
                <label>Database setup method<select name="db_mode" id="db-mode"><option value="existing" <?= post('db_mode')!=='cpanel'?'selected':'' ?>>Use an existing database</option><option value="cpanel" <?= post('db_mode')==='cpanel'?'selected':'' ?>>Create automatically — A2 Hosting / cPanel</option></select></label>
                <div id="cpanel-fields" class="stack">
                    <p>Authorize cPanel to create a new database and dedicated user. A strong database password is generated and saved privately. No 20i connection is used.</p>
                    <div class="field-grid">
                        <label>cPanel server hostname<input name="cpanel_host" value="<?= e(post('cpanel_host')) ?>" maxlength="253" placeholder="Your server hostname from A2" autocomplete="off" data-mode-required></label>
                        <label>cPanel username<input name="cpanel_user" value="<?= e(post('cpanel_user')) ?>" maxlength="32" autocomplete="off" data-mode-required></label>
                    </div>
                    <label>cPanel API token<input type="password" name="cpanel_token" autocomplete="off" maxlength="512" data-mode-required></label>
                    <p class="small muted">In cPanel: Security → Manage API Tokens. Create a short-lived token and enter it here, not in chat. Use the public server hostname only; HTTPS port 2083 is selected automatically. The token is not saved in website settings, recovery files or logs. Revoke it in cPanel after setup.</p>
                    <label class="checkbox-label"><input type="checkbox" name="cpanel_consent" value="1" <?= post('cpanel_consent')==='1'?'checked':'' ?> data-mode-required> I authorize a new database and dedicated user in my A2 cPanel account.</label>
                    <details><summary>Local database connection</summary><label>Local MySQL host<select name="cpanel_db_host"><option value="localhost" <?= post('cpanel_db_host')!=='127.0.0.1'?'selected':'' ?>>localhost (usual cPanel setting)</option><option value="127.0.0.1" <?= post('cpanel_db_host')==='127.0.0.1'?'selected':'' ?>>127.0.0.1 (local TCP)</option></select></label></details>
                    <p class="small muted">Automatic mode uses local MySQL on port 3306 and grants only the required privileges on the new database. API access and available database quota are required. Interrupted requests preserve their private state; uncertain creation is paused rather than repeated.</p>
                </div>
                <div id="existing-db-fields" class="stack">
                <p class="small muted">Create an empty MySQL/MariaDB database and database user in your hosting panel, then enter those details. Existing tables are never replaced.</p>
                <div class="field-grid">
                    <label>Database host<input name="db_host" value="<?= e(post('db_host') ?: 'localhost') ?>" data-mode-required maxlength="253" autocomplete="off"></label>
                    <label>Port<input type="number" name="db_port" value="<?= e(post('db_port') ?: '3306') ?>" min="1" max="65535" data-mode-required></label>
                    <label>Database name<input name="db_name" value="<?= e(post('db_name')) ?>" data-mode-required maxlength="64" autocomplete="off"></label>
                    <label>Database username<input name="db_user" value="<?= e(post('db_user')) ?>" data-mode-required maxlength="128" autocomplete="off"></label>
                </div>
                <label>Database password<input type="password" name="db_password" autocomplete="off"></label>
                </div>
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
