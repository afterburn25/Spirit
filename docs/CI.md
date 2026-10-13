# Continuous integration and reproducible tests

Workflow `.github/workflows/verify.yml`; PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 on non-root runners. Read-only workflow repository permissions, pinned actions, no deployment or production secrets. This deliberately read-only test token is separate from the connector's working repository write access.

## Suites

- `php tests/run.php`: 43 original helper/security/IO checks isolated from real account state.
- `php tests/storage.php`: 26 account-private storage/permission regressions.
- `python3 tests/package_test.py`: 10 isolated packaging secrecy/manifest/overwrite tests.
- `python3 tests/http_integration.py`: real packaged root/subfolder/recovery installs, claim/CSRF denial, operation locking, private state, deletion success/denial/retry, owner/non-owner access, settings escaping, password/session changes, prefix preservation and interrupted-migration recovery against guarded disposable databases.
- `tests/browser_smoke.py`: real Chromium form interactions and installer/admin checks at 1440px and 390px; runs on the PHP8.4 leg.

For SQL tests set SPIRIT_TESTING=1 and SPIRIT_DB_HOST, SPIRIT_DB_PORT, SPIRIT_DB_NAME=spirit_ci, SPIRIT_DB_USER and SPIRIT_DB_PASSWORD for a disposable database. Never use production credentials. Fixture names/prefixes/cleanup tables are restricted. Browser smoke uses a development virtual environment with Playwright 1.57.0 and Chromium; run its interpreter on tests/browser_smoke.py.

Tests and build tools stay in Git, not the deployment ZIP. Temporary setup receipts, passwords, configs, sessions and database dumps are not uploaded as artifacts.

## Latest verified run

**38456010840**, tested code/package **cb1ce92a4af133e5a9c86fcfd7dce281af4ee336**: both jobs successful, including the revised packager, all 79 unit/storage/package checks per matrix execution, real SQL/HTTP integration and Chromium on PHP8.4. The prior run 38454993289 also passed before the packaging revision. See TEST_RESULTS.md for job IDs and detailed boundaries.

These tests do not prove Apache/LiteSpeed rules, actual shared-host ownership, production TLS/proxies, final visual acceptance, measured performance or a complete accessibility/security audit.
