# Continuous integration and reproducible tests

Workflow `.github/workflows/verify.yml` runs PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 on non-root runners, plus a separate actual Apache PHP 8.4 HTTPS access-rule job. Workflow repository permissions are read-only and actions are pinned. That test token is unrelated to the connector's repository write permission. There is no deployment, production credential, actual hosting provisioning or live payment in this workflow.

## Cumulative verification

Read TEST_RESULTS.md for the exact application commit, successful run and remaining boundaries. The matrix includes foundation/storage, content/history/discovery/media, booking/mail/consent, commerce/customer/payment, staff, backup/restore, code-upgrade/recovery and technical-retention tests. Both database jobs run the pure assertions, packaging tests and guarded SQL/HTTP workflows. All twelve Chromium workflows run on the PHP 8.4 leg rather than being duplicated on PHP 8.2.

The dev8 installer workflow covers one-page form/error/completion at 1440 px, 390 px and 320 px, root/subfolder installs, a no-JavaScript existing-database journey, cPanel-mode selection and secret non-reflection. It verifies actual deletion, permanent locking on denial, replay safety and later owner login. PHP's development server may route a deleted URL to the public homepage; tests verify that fallback cannot restore the installer, alter config/locks or duplicate owners. The separate Apache suite tests the shipped access rules.

## A2/cPanel integration boundary

`tests/cpanel_unit.php` contains 56 input, destination, naming and response-contract checks. `tests/cpanel_transport.py` exercises the real cURL HTTPS client against a disposable loopback TLS server: direct/wrapped success, rejection, unauthorized, redirect, malformed/oversized replies, untrusted certificate and hostname mismatch. It inspects actual token-header/form bytes without logging credentials. Production certificate checks, DNS pinning and redirect refusal are not disabled.

`tests/cpanel_integration.py` runs eight root/subfolder scenarios through a CLI-only cPanel simulator: success, definite user-creation rejection, lost database-creation acknowledgement and later migration failure. The simulator creates actual dedicated databases and users in the disposable SQL service and cleans only resources it created. A SQL administrator credential is scoped to this single test step; neither the website nor its real cPanel client receives a root credential. Ordinary tests retain their application-level SQL user. External cPanel and payment networking are explicitly disabled in CI.

These tests establish the application contract and real local SQL/transport behavior, not acceptance against an actual A2 account. Real account API availability, naming/quota, TLS/port reachability and host permissions remain staging checks.

## Reproduction and artifacts

For guarded SQL tests use `SPIRIT_TESTING=1` and isolated `SPIRIT_DB_*` credentials with database `spirit_ci` and the required random test-prefix shape. Do not point tests at production. The cPanel simulator additionally requires its explicit disposable administrator credential, supplied only to its own step. Test-only transports are restricted to CLI and cannot be enabled by HTTP input.

Browser tests use a development virtual environment with Playwright 1.57.0 and its Chromium. The hosting account does not need these tools. Tests/build scripts stay in Git and are excluded from deployment ZIPs. Current packaging creates no setup code/hash or PRIVATE-SETUP receipt. Private config, passwords, tokens, recovery receipts, sessions and database dumps are not uploaded as artifacts. Screenshot artifacts contain deliberately synthetic test data; source artifacts are tracked public source, not application-verification evidence.

Actual Apache regression success is not a guarantee about every LiteSpeed configuration, arbitrary domain alias, ACL/open_basedir/OPcache/proxy behavior, production provider, complete accessibility/security audit or final design acceptance. See LAUNCH_CHECKLIST.md before cutover.
