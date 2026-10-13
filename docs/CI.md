# Continuous integration and reproducible tests

Workflow: `.github/workflows/verify.yml`. Matrix: PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 on non-root runners. Read-only workflow repository permissions, pinned action commits, no deployment step and no production secrets. The GitHub connector's ability to commit code is separate from this deliberately read-only test token.

## Suites

- `php tests/run.php`: original 43 helper/security/IO assertions, isolated from any real account runtime.
- `php tests/storage.php`: 26 account-private storage and permission regressions.
- `python3 tests/package_test.py`: 10 isolated package secrecy/manifest/overwrite tests.
- `python3 tests/http_integration.py`: packages and extracts isolated root/subfolder/recovery sites, then runs actual PHP requests against disposable SQL databases. Exercises claim/CSRF denial, operation locking, installation, private state, deletion success/denial/retry, owner/non-owner access, settings persistence/escaping, password rotation, session revocation, prefix preservation and interrupted-migration recovery.
- `tests/browser_smoke.py`: actual Chromium form interactions at 1440px and 390px, including install, sign-in and admin pages, stylesheet/no-overflow checks and JavaScript error detection. Runs on the PHP 8.4 matrix leg.

For SQL tests set SPIRIT_TESTING=1 and SPIRIT_DB_HOST, SPIRIT_DB_PORT, SPIRIT_DB_NAME=spirit_ci, SPIRIT_DB_USER and SPIRIT_DB_PASSWORD for a disposable database. Never use production credentials. Database names/prefixes and cleanup table names are restricted. For browser smoke use a development virtual environment with Playwright 1.57.0 and Chromium installed, then run its Python interpreter on tests/browser_smoke.py.

These tools are development-only and omitted from the deployment ZIP. CI generates temporary claim receipts but never uploads or logs them. No session/config/database dumps are published as artifacts.

## Evidence

Run **38454993289**, tested commit **aa01c32bb5b9450579880b8d2372e5582a38c161**, passed both database/HTTP matrix jobs and the Chromium step. New package regressions passed locally and are now included in the next full matrix run. docs/TEST_RESULTS.md records the latest verified outcome.

A PHP local-server or Chromium smoke success does not prove Apache/LiteSpeed access rules, actual hosting ownership, production TLS/proxies, final visual acceptance, performance scores or a full accessibility audit.
