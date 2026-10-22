> **Current 1.1.0 layout:** the grouped foundation is unchanged from rc.10–rc.12. Read [DIRECTORY_LAYOUT.md](DIRECTORY_LAYOUT.md) and [LAYOUT_TRANSITION.md](LAYOUT_TRANSITION.md). An rc.8-or-earlier updater must refuse the changed foundation. Preserve existing data; never reinstall.

# Browser installation and updates — 1.1.0

Try a new update on an isolated staging copy before changing the existing live band website. TEST_RESULTS.md records exact automated evidence; it does not establish behavior on an unobserved host. The accepted installation, mail and account results in acceptance/CONSOLIDATED-FINAL-PASS.md remain historical owner-confirmed results and do not need to be restarted for this software release.

## Hosting and fresh installation

PHP 8.2+ with PDO MySQL, OpenSSL, fileinfo and GD; cURL for payment transport and ZIP for browser package upgrades. MySQL/MariaDB; HTTPS; Apache/LiteSpeed honoring supplied access rules. Actual Apache PHP 8.4 HTTPS rules are regression-tested; this does not verify every LiteSpeed/provider configuration. No required SSH/root or host-side Node/Python/Composer build.

Confirmed target: **A2 Hosting with cPanel only. Manual database configuration; no hosting API or token.** Create an empty database and dedicated user using cPanel Database Wizard or Manage My Databases and assign that user to the database. Enter the full names, including account prefixes, and the database-user password. The installer creates the website tables, not the config/database/user. See CPANEL_INSTALLER.md for permissions, extension notices and existing-installation precautions.

PHP cURL and PHP ZIP have separate optional checks. Missing either does not block manual installation, but its dependent payment/upgrade feature stays unavailable until the host enables it. Enable curl/zip for this domain's actual PHP version through cPanel Select PHP Version > Extensions, or ask A2 support. Owner Installation health retains both checks after setup is deleted.

A development checkout can run `python3 app/tools/package.py --output ./dist`. Upload/extract the Full ZIP, including hidden .htaccess files, using the panel file manager. Open `/install/` beneath the intended installation URL. Hosting checks, public URL, database connection and owner details appear immediately in one form. **No setup code, access hash, claim page or separate PRIVATE-SETUP receipt is used.** This user decision supersedes earlier release instructions requiring a setup receipt.

Fill in the form and click **Install website** once. JavaScript shows a busy state but is not required; the same native form works without it. Validation errors remain on that page, preserving non-secret values while clearing passwords. Success also remains on `/install/`, with the actual cleanup result and **Open administration** button. The running request can finish rendering after deleting its own installer folder. Refreshing the old URL afterward may return 404; use the administration link rather than reinstalling. Owner login remains separate; installation does not silently log in or enable live payments.

**Fresh setup is open until completion.** Anyone able to reach an uninstalled deployment can submit their own valid config/database/owner configuration. CSRF and database passwords do not prove the visitor owns the hosting account. Install promptly after upload, or restrict access using hosting-panel directory protection while preparing unattended staging. No substitute setup-code gate is added. Keep all database, owner and unrelated backup/update recovery credentials private.

## Writable private runtime and self-deletion

Runtime is `.spirit-<installation identifier>` beneath a validated account-private base. Process/home and explicit server-side SPIRIT_ACCOUNT_HOME/SPIRIT_PUBLIC_ROOTS configuration identify permitted account/public roots. No web form/header chooses the private destination. Common/current/context public roots are checked, but arbitrary server aliases still require hosting review.

Private directories use 0700 and config 0600, checked before secret bytes. Actual create/write/read/rename/delete probes cover runtime children. No public credential fallback or 0777 is used. PHP cannot override ownership, ACLs or open_basedir; provider configuration may be necessary on incompatible hosts.

Durable setup/prefix ownership and filesystem locks prevent concurrent or destructive reinstall. Interrupted setup resumes with the same database binding instead of silently changing prefixes. Configuration/completion markers precede cleanup. Cleanup targets only the fixed installer and recognized files, refuses symlinks/unknown contents and verifies physical removal. Denied removal leaves the installer permanently locked and offers an owner POST/CSRF cleanup retry. Automated tests cover successful removal, denial and retry after permissions are corrected; no universal host deletion guarantee is made.

An older unfinished automatic attempt is identified by its private cpanel-provision.php record. It is not executed, reused or deleted. Keep it for manual owner/A2 review before attempting a fresh installation. Completed sites do not need replacement databases or reinstallation.

## Existing installations

Choose the path for the version actually installed:

| Installed version | Path to 1.1.0 |
|---|---|
| 1.1.0-rc.10, rc.11 or rc.12 | Guarded same-layout, same-schema **Browser upgrades**, when all existing integrity, backup and recovery checks pass. See UPGRADES.md and TEST_RESULTS.md for the exact verified scope. |
| 1.1.0-rc.8 or an earlier 1.1 candidate | One-time same-root cPanel transition in LAYOUT_TRANSITION.md. The older updater must refuse the changed routing/recovery foundation. |
| 1.0.x | Preserve the existing installation, follow the same-root layout transition, and apply the additive analytics database update described below. The browser code-only updater must refuse this cross-schema/layout update. |
| The earlier unrecovered rc.9 package | Compare the actual installed package and its recovery foundation before choosing an update path; compatibility is not assumed. |

**Analytics migrations 007 and 008 were introduced in 1.1.0-rc.1.** An existing 1.0.x installation retains its configuration, database, owner and original encryption secret. Save verified code/configuration/database/media backups, deploy the cumulative code through cPanel while preserving runtime and uploaded files, and apply **System & maintenance → Database updates**. Do not recreate a removed installer folder or rerun fresh setup. Read ANALYTICS.md together with LAYOUT_TRANSITION.md. There is no additional migration from rc.8, rc.10, rc.11 or rc.12 to final 1.1.0.

The stage background and transparent shell introduced in rc.5, later identity refinements, rc.11 Music wrapping and rc.12 footer placement remain in the cumulative release. Ready-to-use WebP artwork and compiled CSS are included; no server-side image or Node build is required.

Never rerun a fresh installer. Back up config, original encryption secret, database and media before replacing code. Legacy dev.1 runtime placement needs the review in STORAGE.md; do not delete old state to bypass conflicts.

Supported installations with the current maintenance/recovery foundation can use owner **Browser upgrades** for a newer trusted cumulative ZIP with **unchanged migrations/schema and pinned recovery layer**. The owner downloads a separate private update-recovery receipt, confirms it is stored, installs, then verifies the newly started application before public access resumes. The removed installer and private runtime are preserved. Interrupted replacement can recover via `/admin/upgrades/recover.php` and the saved private receipt. Read UPGRADES.md before updating.

The three retired Cpanel paths contain no class or API logic. Their inert files were retained for older cumulative update manifests and remain in this release. Do not remove these compatibility entries or edit a package manifest to bypass validation.

Cross-schema releases or changes to pinned recovery/access files require a reviewed staging deployment and the existing additive Database updates flow; the automatic updater deliberately refuses them. Do not rename versions or strip safety checks to force acceptance. Automatic legacy-runtime relocation and cross-host disaster recovery are not implemented.

## Backups, retention and provider setup

Owner **Backups & safe restore** supports authenticated encrypted snapshots, verification/import/download and guarded same-instance restoration. Keep the separate recovery key offline. Content-only restore preserves payment/account/stock/consent histories; broader site-data restore refuses changed protected operational state. Read BACKUPS.md and keep independent provider backups.

Owner retention tools remove only eligible bounded technical data after review; they do not erase business histories or create a hosting schedule. See RETENTION.md and the administration guidance. Preserve the owner's accepted SMTP and mail schedule; use COMMUNICATION.md when configuring or changing a provider. Commerce remains intentionally disabled. Before activating it, configure and verify Authorize.net sandbox separately using PAYMENTS.md; synthetic tests do not establish actual SDK/API/webhook/merchant acceptance. Live payments require an explicit owner decision after that validation.

## Final staging acceptance

Use the applicable deployment checks in LAUNCH_CHECKLIST.md before a host cutover, preserving the accepted scope recorded in acceptance/CONSOLIDATED-FINAL-PASS.md. Responsive image caches are private, optional and regenerable; original approved media remains authoritative and belongs in backups. Current target-host behavior and any new provider or content changes remain separate from automated software results. A software release or successful package upload does not enable live payments.

## Post-install launch checks

Use owner Admin > Launch checks for current PHP extension/limit observations and a private fixed-field support report. It does not modify PHP settings or perform provider acceptance. Read READINESS.md. Media/backup/ZIP forms now explain PHP whole-request rejection with413 while preserving state; normal permissions/CSRF still apply. Missing ZIP still requires the domain's host extension configuration.
