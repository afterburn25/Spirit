# Browser installation — development draft

Do not replace the live website with this build. The source is 0.1.0-dev.1; database/browser/shared-host acceptance is pending.

## Proposed hosting target
PHP 8.2+ with PDO MySQL and OpenSSL; MySQL/MariaDB; HTTPS; Apache/LiteSpeed honoring the supplied access rules. No required SSH, root access, host-side Python or Node build. Other server configurations need explicit equivalent deny rules and testing.

Create an isolated empty database and user through the hosting panel. The installer needs SELECT/INSERT/UPDATE/DELETE and schema privileges used by its CREATE/ALTER/DROP test and migrations. It does not obtain hosting credentials or provision control-panel accounts.

## Package and browser flow
On the development machine run `python3 tools/package.py --output ./dist`. It creates a cumulative ZIP and a separate PRIVATE setup receipt with a random claim code. The ZIP contains only the code hash. Keep the receipt out of Git and the website.

Extract the ZIP using the hosting file manager, including hidden .htaccess files. Use a staging domain or subfolder. Open `/install/` under that URL, enter the private claim code, review checks, then provide the database and initial owner details. Source checkouts have no claim key and cannot be claimed by the first visitor.

The UI and installer source exist; this browser flow has not yet been validated end to end.

## Writing and private runtime
The installer performs real create/write/read/rename/delete operations rather than trusting only is_writable(). It attempts to create a 0700 per-installation `.spirit-<identifier>` runtime beside DOCUMENT_ROOT and writes 0600 configuration atomically. Sessions, media, logs, cache and backups have private directories. Config/storage in the source tree are not secret destinations.

IMPORTANT OPEN LIMITATION: this algorithm checks the current document root, not every other domain's root on a shared account. Addon/subdomain layouts and open_basedir restrictions require further validation. Do not call it safe for every shared host. There is currently no tested alternate-path/control-panel provisioning fallback.

PHP cannot override ownership, ACL or hosting restrictions. The installer fails rather than storing secrets publicly or applying 0777. Panel-owned extraction can help with ownership but does not guarantee PHP deletion rights.

## Completion and deletion
An installation operation lock prevents concurrent installers for the same runtime. Prefix collision checks refuse existing application tables. Recovery metadata distinguishes retries from reinstall. MySQL DDL is not transactional; crash/retry integration testing is still required.

Successful source flow publishes installed configuration and a completion marker BEFORE attempting cleanup. Cleanup deletes only known files from the exact install directory, refuses symlinks/unknown content, then checks directory removal. It does not recursively delete arbitrary paths. When the host blocks deletion, the installer remains locked and the owner Installation health page reports it and offers a POST/CSRF-protected retry.

Actual host deletion and database-backed completion have not been demonstrated. A successful unit fixture deletion is not proof of deletion on your host.

## Not available yet
Automated upgrades, backup/restore, configured media workflows, payments and the complete public site remain later milestones. Do not delete config or remove lock markers to reinstall. Never reuse production data for tests.
