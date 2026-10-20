# Continuous integration and reproducible tests

Workflow `.github/workflows/verify.yml` runs PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 on non-root runners, plus a separate actual Apache PHP 8.4 HTTPS access-rule job. Workflow repository permissions are read-only and actions are pinned. That test token is unrelated to the connector's repository write permission. There is no deployment, production credential, actual hosting provisioning or live payment in this workflow.

## Cumulative verification

Read TEST_RESULTS.md for the exact application commit, observed runs and remaining boundaries. The matrix includes foundation/storage, content/history/discovery/media, booking/mail/consent, commerce/customer/payment, staff, backup/restore, code-upgrade/recovery and technical-retention tests. Both database jobs run the pure assertions, packaging tests and guarded SQL/HTTP workflows. The cumulative Chromium journeys in verify.yml run on its PHP 8.4 leg. Separate navigation, storefront, branding and authoring workflows cover Chromium, Firefox and WebKit.

The manual installer checks cover the one-page form, errors and completion, root/subfolder installs, native no-JavaScript forms, secret non-reflection, actual deletion, permanent locking on denial, replay safety and later owner login. PHP's development server may route a deleted URL to the public homepage; tests verify that fallback cannot restore the installer, alter config/locks or duplicate owners. The separate Apache suite tests the shipped access rules.

## A2/cPanel integration boundary

The installed application uses manual database configuration. It has no hosting API token, automatic database/user provisioning or cPanel mode selector. Tests use the disposable application database account supplied by their workflow; they do not provision a real hosting account.

Earlier CI documentation described a cPanel API client and provisioning simulator. That scope was retired by the owner's manual-setup decision. Those descriptions are historical and are not evidence of a callable feature or a current verification step.

Real A2 account naming, quotas, domain-specific PHP extensions, TLS and file permissions remain staging checks.

## Analytics candidate verification

Workflow `.github/workflows/analytics.yml` runs the analytics collection, geography, parser, SQL/HTTP and native upload suites on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4. It waits for the disposable database account before testing. Its PHP file/request limits are pinned to 2M/8M because the upload stress tests intentionally cross those boundaries. The PHP 8.4 leg also runs the analytics dashboard and visitor browser journeys.

The software release gate verifies committed public bundles using the pinned npm lockfile and the existing 130,000-byte CSS/JS budget. Candidate packaging installs Pillow 12.3.0, builds the cumulative versioned ZIP and publishes its checksum. These workflow definitions do not establish successful execution; record observed results for the exact candidate in TEST_RESULTS.md.

## Reproduction and artifacts

The existing logo workflow also covers footer copyright. It runs the focused branding and footer PHP units, installed SQL/HTTP checks, and the existing branding browser journey on its database/browser matrix. The copyright report/log are retained in the same artifact directory. The branding integration suite can accept an immutable candidate with --archive and emit --report; it covers legacy-source retention, explicit source selection and supported restoration. The separate tests/capture_footer_refresh.py produces actual responsive screenshots and normal-speed logo/smoke previews for a frozen ZIP. Workflow configuration is not evidence of a completed run; record actual outcomes for the published head.

For guarded SQL tests use `SPIRIT_TESTING=1` and isolated `SPIRIT_DB_*` credentials with database `spirit_ci` and the required random test-prefix shape. Do not point tests at production. Test-only transports are restricted to CLI and cannot be enabled by HTTP input.

Browser tests use a development virtual environment with Playwright 1.57.0 and its Chromium. The hosting account does not need these tools. Tests/build scripts stay in Git and are excluded from deployment ZIPs. Current packaging creates no setup code/hash or PRIVATE-SETUP receipt. Private config, passwords, tokens, recovery receipts, sessions and database dumps are not uploaded as artifacts. Screenshot artifacts contain deliberately synthetic test data; source artifacts are tracked public source, not application-verification evidence.

Actual Apache regression success is not a guarantee about every LiteSpeed configuration, arbitrary domain alias, ACL/open_basedir/OPcache/proxy behavior, production provider, complete accessibility/security audit or final design acceptance. See LAUNCH_CHECKLIST.md before cutover.
