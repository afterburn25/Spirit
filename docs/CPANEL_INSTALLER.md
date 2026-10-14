# A2 Hosting / cPanel automatic database setup

User-confirmed target: **A2 Hosting with cPanel only. No 20i integration.** Development version 0.9.0-dev.8. Consult TEST_RESULTS.md for the exact verification state.

## One page, two database choices

The installer no longer has a package setup code, claim screen or PRIVATE-SETUP receipt. Hosting checks, website URL, database method, owner details, errors and completion stay on one browser page. The existing-database choice uses the connection details supplied by the owner. Automatic mode uses the cPanel account API to create a new database, a dedicated user and a strong random password, assign database-scoped privileges, and verify the resulting real PDO connection before applying application tables.

Choose **Create automatically — A2 Hosting / cPanel**. Enter the actual public server hostname (not an IP, scheme, cPanel session URL or path), cPanel account username and API token. HTTPS port2083 is fixed. In cPanel use **Security > Manage API Tokens** to create a short-lived token; enter it only in the HTTPS installer and revoke it after setup. Never post it in chat or Git. A cPanel token can authorize broad account operations; it is not a narrowly scoped database password or package activation code. The host must enable API access and have available database/user quota. No SSH, root, WHM or reseller credentials are used by the website.

Automatic MySQL connection is local, normally `localhost` on3306; the same page offers127.0.0.1 for local TCP. Remote MySQL uses the existing-database option instead. The generated names include the actual account prefix and fit the limits reported by Mysql/get_restrictions; they have a random suffix rather than assuming `spirit` is available. The generated password is never displayed or reflected in a correction page.

## Credential handling and scope

The token is used only in the current PHP request. It is not saved in sessions, permanent settings, private recovery records or logs. Request parameters use an HTTPS POST body, not a password-bearing query string. TLS peer/hostname verification remains enabled. Redirects and proxies are disabled, destinations resolve to validated public IPv4 addresses pinned to that request, and responses/time are bounded. IPv6-only panels and nonstandard/proxied cPanel ports are not supported by this automatic mode.

The dedicated database user receives SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,DROP,INDEX,REFERENCES on the newly created database only. There are no server-wide grants, password-reset functions, delete-database or delete-user APIs in the client. Existing databases/users are not adopted or altered. These restrictions do not reduce the powers of the owner-created cPanel token itself.

The generated database credentials and a minimal progress record are saved mode0600 in the account-private runtime, outside the website's public folders, before resource mutations. After installation they live in the original private application config; the temporary provisioning record is removed. Neither record stores the cPanel token. Never delete a partial record to force setup to start over.

## Failure and retry behavior

Owner details, password confirmation and explicit provisioning consent are checked before a cPanel request creates resources. The existing install-operation lock serializes attempts. Authenticated read-only inventory checks precede creation and resume. Confirmed steps are not repeated; a definite rejection can be retried after the host-side quota/permission issue is repaired. A later PDO/migration failure reuses the same generated user/password instead of creating another pair. Resume with the original cPanel hostname/account, website URL, owner email, local database host and table prefix.

If a create/grant request times out, the worker dies, or its acknowledgement cannot be durably recorded, its result is uncertain. Automatic provisioning pauses instead of blindly replaying or resetting a password. Ask A2 support to inspect the private cpanel-provision.php state and actual account inventory. This checkpoint deliberately has no automatic adoption/reset/deletion or interactive uncertainty-reconciliation tool. Unrelated resources are never deleted as rollback. Once automatic state exists, switching to an unrelated existing database is refused; preserve that state for inspection rather than guessing which resources are safe.

After success, permanent installation locks are written before self-deletion is attempted. A retained installer stays locked. Actual host ACL restrictions cannot be overridden. Backup/update recovery keys are unrelated and are retained under their existing protections.

## Test and acceptance boundary

Tests use official cPanel result shapes and a CLI-only simulator that creates real dedicated users/databases in a disposable local MySQL/MariaDB service. Only the test-owned simulator uses its disposable SQL administrator; application code never receives that credential. Separate tests run the actual cURL HTTPS/token/form transport against loopback TLS servers with valid and invalid certificates. CI blocks external cPanel network calls. No test calls the user's A2 account or establishes real provider compatibility.

Verify the real A2 account token, naming limits, quota, database privileges, certificate/2083 reachability and local database connection on protected staging before relying on automatic setup. For code-free fresh installation, install promptly after extraction or use cPanel Directory Privacy while unattended: CSRF and database access alone do not authenticate the hosting owner.

## Official contracts consulted

cPanel token authentication: https://docs.cpanel.net/knowledge-base/security/how-to-use-cpanel-api-tokens/
Mysql/get_restrictions: https://api.docs.cpanel.net/specifications/cpanel.openapi/user-management/mysql-get_restrictions
Mysql/create_database: https://api.docs.cpanel.net/specifications/cpanel.openapi/database-management/mysql-create_database
Mysql/create_user: https://api.docs.cpanel.net/specifications/cpanel.openapi/user-management/mysql-create_user
Mysql/list_databases: https://api.docs.cpanel.net/specifications/cpanel.openapi/database-management/mysql-list_databases
Mysql/list_users: https://api.docs.cpanel.net/specifications/cpanel.openapi/user-management/mysql-list_users
Mysql/set_privileges_on_database: https://api.docs.cpanel.net/specifications/cpanel.openapi/user-management/mysql-set_privileges_on_database
