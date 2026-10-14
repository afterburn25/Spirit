# Browser installation and updates — 0.9.0-dev.7

Use isolated staging first, not the existing live band website. TEST_RESULTS.md records exact automated evidence. Actual shared-host ownership, domain aliases, execution limits, TLS/proxies, OPcache and provider behavior require staging acceptance.

## Hosting and fresh installation

PHP 8.2+ with PDO MySQL, OpenSSL, fileinfo and GD; cURL for payment transport and ZIP for browser package upgrades. MySQL/MariaDB; HTTPS; Apache/LiteSpeed honoring supplied access rules. Actual Apache PHP 8.4 HTTPS rules are regression-tested; this does not verify every LiteSpeed/provider configuration. No required SSH/root or host-side Node/Python/Composer build.

Create an empty staging database/user through the hosting panel. Installer probes exercise required CRUD/schema permissions. The application does not create hosting accounts, obtain panel credentials or elevate permissions.

A development checkout can run `python3 tools/package.py --output ./dist`. Save the separate PRIVATE setup receipt offline. Upload/extract only the Full ZIP, including hidden .htaccess files, using the panel file manager. Open `/install/` beneath the intended installation URL; enter the receipt's unique setup code, review the checks, and enter database/initial-owner details. Only the code hash is in the ZIP. Success redirects to owner sign-in; review Installation health for actual storage and cleanup state.

## Writable private runtime and self-deletion

Runtime is `.spirit-<installation identifier>` beneath a validated account-private base. Process/home and explicit server-side SPIRIT_ACCOUNT_HOME/SPIRIT_PUBLIC_ROOTS configuration identify permitted account/public roots. No web form/header chooses the private destination. Common/current/context public roots are checked, but arbitrary server aliases still require hosting review.

Private directories use 0700 and config 0600, checked before secret bytes. Actual create/write/read/rename/delete probes cover runtime children. No public credential fallback or 0777 is used. PHP cannot override ownership, ACLs or open_basedir; provider configuration may be necessary on incompatible hosts.

Durable setup/prefix ownership and filesystem locks prevent concurrent or destructive reinstall. Interrupted setup resumes with the same database binding instead of silently changing prefixes. Configuration/completion markers precede cleanup. Cleanup targets only the fixed installer and recognized files, refuses symlinks/unknown contents and verifies physical removal. Denied removal leaves the installer permanently locked and offers an owner POST/CSRF cleanup retry. Automated tests cover successful removal, denial and retry after permissions are corrected; no universal host deletion guarantee is made.

## Existing installations

Never rerun a fresh installer. Back up config, original encryption secret, database and media before replacing code. Legacy dev.1 runtime placement needs the review in STORAGE.md; do not delete old state to bypass conflicts.

Supported installations with the current maintenance/recovery foundation can use owner **Browser upgrades** for a newer trusted cumulative ZIP with **unchanged migrations/schema and pinned recovery layer**. The owner downloads a separate private update-recovery receipt, confirms it is stored, installs, then verifies the newly started application before public access resumes. The removed installer and private runtime are preserved. Interrupted replacement can recover via `/admin/upgrades/recover.php` and the saved private receipt. Read UPGRADES.md before updating.

Cross-schema releases or changes to pinned recovery/access files require a reviewed staging deployment and the existing additive Database updates flow; the automatic updater deliberately refuses them. Do not rename versions or strip safety checks to force acceptance. Automatic legacy-runtime relocation and cross-host disaster recovery are not implemented.

## Backups, retention and provider setup

Owner **Backups & safe restore** supports authenticated encrypted snapshots, verification/import/download and guarded same-instance restoration. Keep the separate recovery key offline. Content-only restore preserves payment/account/stock/consent histories; broader site-data restore refuses changed protected operational state. Read BACKUPS.md and keep independent provider backups.

Owner retention tools remove only eligible bounded technical data after review; they do not erase business histories or create a hosting schedule. See RETENTION.md if present and the administration guidance. Configure TLS SMTP and verify delivery with the real mailbox/provider using COMMUNICATION.md. Configure Authorize.net sandbox separately using PAYMENTS.md; synthetic tests do not establish actual SDK/API/webhook/merchant acceptance. Production enablement requires an explicit owner decision after staging validation.

## Final staging acceptance

Follow LAUNCH_CHECKLIST.md before cutover. Responsive image caches are private, optional and regenerable; original approved media remains authoritative and belongs in backups. Real provider/host/master-art/content acceptance remains separate from regression results. Never treat the development version or a successful package upload as owner approval to enable live payments.
