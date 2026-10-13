# Browser installation — development build 0.6.0-dev.1

Use an isolated staging domain or subfolder, not the existing live band website. Real root/subfolder installations now pass automated MySQL/MariaDB and Chromium tests, but actual shared-host ownership, access rules, aliases and TLS/proxies still need staging acceptance.

## Hosting target

PHP 8.2+ with PDO MySQL, OpenSSL, fileinfo and GD; MySQL/MariaDB; HTTPS; Apache/LiteSpeed honoring supplied access rules. No required SSH/root, Python or Node build on the shared server. Other server configurations require explicit equivalent access rules and their own verification.

Create an empty staging database/user through the hosting panel. Required privileges are the SELECT/INSERT/UPDATE/DELETE and schema privileges exercised by CREATE/ALTER/DROP probes and migrations. The application does not provision hosting accounts or retrieve control-panel credentials.

## Package and browser flow

On a development checkout run `python3 tools/package.py --output ./dist`. Keep the separate PRIVATE setup receipt off the server and out of Git. Upload/extract only the Full ZIP, including hidden .htaccess files, using the hosting file manager. Open `/install/` under the staging URL, enter the receipt's unique setup code, review the checks, and enter the database and initial owner details.

The package contains the setup-code hash, never the plaintext code. Source checkouts cannot be claimed by the first public visitor. The deployment ZIP excludes tests, build tools, local/runtime data and existing setup receipts. The package manifest records payload hashes; it is not a mandatory user-entered hash step.

Successful setup redirects to owner sign-in, not back to an unlocked installer. Sign in and check Admin > Installation health for actual storage and cleanup status.

## Writing and private runtime

Runtime is provisioned under a validated account-private base as `.spirit-<installation identifier>`. Compatible per-account PHP hosting is detected from the process account home or host-provided HOME; an atypical host may need server-configured SPIRIT_ACCOUNT_HOME and SPIRIT_PUBLIC_ROOTS. No web form or HTTP header is trusted as a filesystem location.

The current document root, common public-directory ancestors/children, context document root and explicit public-root list are checked. PHP cannot discover every arbitrary server alias: verify all domain mappings during staging. Ambiguous or publicly located candidates fail closed. See docs/STORAGE.md for details.

The installer creates private directories with 0700 and writes config with verified 0600 permissions before secret bytes. It performs actual create/write/read/rename/delete operations in runtime and each child. Defensive deny rules are added as another protection layer. It never falls back to a public credential file or chmod 0777.

PHP cannot override provider ownership, ACL or open_basedir restrictions. Panel extraction can help ownership compatibility, but does not guarantee deletion rights. A host that forbids private writable storage requires provider configuration; no misleading success message is acceptable.

## Completion and self-deletion

A filesystem operation lock prevents concurrent setup. Existing table prefixes are preserved. Recovery metadata supports retries with the same database configuration after an interrupted migration; switching databases/prefixes during recovery is rejected. MySQL DDL is not transactional. The table-ownership sentinel also covers interruption between its CREATE and initial INSERT. Actual host interruption behavior remains a staging review item.

The installed configuration and completion marker are published before cleanup. Cleanup targets only the application's fixed install directory and known files, refuses symlinks/unknown content, and verifies physical removal. When deletion is denied, the installer returns a locked response and owner administration offers a POST/CSRF-protected cleanup retry.

Automated tests verified successful physical removal, denied deletion with retained lock, and successful owner retry after permission repair. This does not guarantee that a specific hosting provider permits deletion.

## Existing installations and communication setup

Do not extract this build over dev.1 without reviewing docs/STORAGE.md: nested-document-root runtime placement changed. Preserve the old runtime and database; never erase state to bypass a prefix conflict. For supported account-private foundation installations, preserve all configuration, uploaded media and data when uploading reviewed code; sign in and use Admin > Database updates for the additive schema. The update creates the new tables without reinstalling or resetting content. Do not remove the private installation secret: SMTP credentials and queued message bodies depend on it.

After updating, follow COMMUNICATION.md to configure SMTP, verify a real mailbox test and optionally set up the hosting-panel PHP queue schedule. Automated ZIP upgrades, full backup/restore, automatic legacy-runtime relocation and merchandise payments remain later work. TEST_RESULTS.md identifies the exact automated evidence; provider and host acceptance are separate.
