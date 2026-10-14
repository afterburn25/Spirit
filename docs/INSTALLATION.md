# Browser installation — development build0.8.0-dev.1

Use an isolated staging domain/subfolder, not the existing live band website. Automated database/browser evidence is recorded in TEST_RESULTS.md; actual shared-host ownership, access rules, aliases, TLS/proxies and provider behavior require separate staging acceptance.

## Hosting target

PHP8.2+ with PDO MySQL, OpenSSL, fileinfo, GD and cURL for payments; MySQL/MariaDB; HTTPS; Apache/LiteSpeed honoring supplied access rules. No required SSH/root, Python, Node or Composer build on the host. Other server configurations need equivalent verified access rules. Create an empty staging database/user through the hosting panel. Schema/CRUD privileges are exercised by installer probes; the app does not provision hosting accounts or obtain panel credentials.

## Package and browser flow

On a development checkout run `python3 tools/package.py --output ./dist`. Keep the separate PRIVATE setup receipt off the server and out of Git. Upload/extract only the Full ZIP, including hidden .htaccess files, with the hosting file manager. Open `/install/` under the staging URL, enter the receipt's unique setup code, review checks and enter database/initial-owner details.

The package contains only the code hash; source checkouts cannot be claimed by the first public visitor. Tests/build tools/local runtime/old receipts are excluded. The manifest records payload hashes, not a mandatory user-entered checksum. Success redirects to owner sign-in. Review Installation health for actual storage/cleanup state.

## Writable private runtime

Runtime is provisioned as `.spirit-<installation identifier>` under a validated account-private base. Per-account PHP hosting is detected from process home or host-provided HOME; atypical hosts may require server-configured SPIRIT_ACCOUNT_HOME/SPIRIT_PUBLIC_ROOTS. No web input or HTTP header chooses a filesystem destination.

Current/context roots, common public ancestors/children and declared roots are checked. PHP cannot discover arbitrary web aliases; verify domain mappings on staging. Ambiguous/public candidates fail closed. Private directories use0700, config0600 verified before secret bytes; actual create/write/read/rename/delete probes run in runtime/children. Defensive deny rules add another layer. No public credential fallback or0777.

PHP cannot override provider ownership, ACLs or open_basedir. Panel extraction can improve ownership compatibility but cannot guarantee deletion. A provider prohibiting private writable storage needs hosting configuration, not a misleading success state. See STORAGE.md.

## Completion and self-deletion

Filesystem locks prevent concurrent setup. Existing database prefixes are preserved; durable recovery supports retries with the same database configuration after interruption and rejects silent switches. DDL is not transactional; ownership sentinel covers interruption between CREATE and initial INSERT. Actual host crash behavior still requires acceptance.

Configuration and completion marker precede cleanup. Only the fixed install directory and known files are targeted. Symlinks/unexpected contents are refused. Physical removal is verified separately from permanent locking. If deletion is denied, setup remains locked and the owner receives a CSRF-protected cleanup retry. Tests cover successful removal, denied removal and retry after permission repair, not a guarantee about every host.

## Supported updates

Do not overwrite original dev.1 without reviewing STORAGE.md: private-runtime placement changed. Preserve the old runtime/database; never delete state to bypass conflicts. For supported account-private installations, back up config/encryption secret/database/media, upload reviewed application code and use owner Database updates. Never rerun a fresh installer. Migrations001–004 remain unchanged;005 is additive and must not reset owners, content or customers.

Full automatic browser ZIP upgrades/restore and automatic legacy-runtime relocation are not complete yet. Preserve the installation encryption secret: SMTP/payment credentials, message bodies and private order data require it. Do not overwrite runtime with a new blank configuration.

## Communication and payment staging

Follow COMMUNICATION.md to configure/test SMTP and a hosting-panel PHP queue schedule. Follow PAYMENTS.md for separate bounded payment maintenance/reporting, sandbox-only staging, actual SDK/API/signed-webhook verification and explicit merchant shipping/tax/terms. No genuine provider transaction has been verified by this development build. Never enable production based on synthetic tests alone.
