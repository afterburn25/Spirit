<?php declare(strict_types=1); ?>
<section class="panel form-width" id="install-complete" tabindex="-1" aria-labelledby="complete-heading">
    <p class="eyebrow">SPIRITUAL GORILLAZ / INSTALLATION COMPLETE</p>
    <h1 id="complete-heading">Your stage.<br><em>Ready to manage.</em></h1>
    <div class="notice success" role="status">The database and owner account were created. Private configuration is saved and installation is permanently locked.</div>
    <h2>Installer cleanup</h2>
    <div id="install-cleanup" class="notice <?= $completed['removed'] ? 'success' : 'info' ?>"><strong><?= $completed['removed'] ? 'Installer folder removed.' : 'Installer locked; cleanup needs attention.' ?></strong><br><?= e($completed['message']) ?></div>
    <?php if (!$completed['removed']): ?><p>The host prevented complete cleanup or the folder contains an unexpected file. Sign in and use <strong>Installation health</strong> to review the result and retry after the cause is corrected. Do not rerun setup.</p><?php endif; ?>
    <p>Sign in with the owner email and password you just chose. Configure your content and services before public launch; installation does not enable live payments.</p>
    <a class="button wide" id="owner-signin" href="<?= e(app_url('admin/login.php')) ?>">Open administration →</a>
    <p class="small muted">This result stays on the installer page. After removal, refreshing the old installer URL may return a missing-page response; use the administration button to continue.</p>
</section>
