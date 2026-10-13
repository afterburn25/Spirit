# Test evidence — 0.1.0-dev.2

Updated 2026-10-13. Development build only; no accepted release or live deployment.

## Latest completed verification

**GitHub Actions run 38456010840: both jobs SUCCESS.** Tested application/package commit: **cb1ce92a4af133e5a9c86fcfd7dce281af4ee336**. This documentation checkpoint does not modify application code or tests.

| Environment | Job | Verified result |
|---|---|---|
| PHP 8.2 / MariaDB 11.4 | 115498795114 | Syntax, 43 original unit checks, 26 storage checks, 10 packaging tests, and SQL/HTTP integration all passed. Chromium intentionally not run on this leg. |
| PHP 8.4 / MySQL 8.4 | 115498794410 | The same checks plus actual Chromium installer/admin smoke passed at 1440px and 390px. |

The 79 unit/storage/package checks are separate from the additional SQL/HTTP and browser assertions; repeated matrix executions are not counted as new unique tests.

## Workflows demonstrated

Real deployment packages were built and extracted for root and subfolder installations. Tests verified setup-code/CSRF rejection, concurrent operation locking, fresh SQL installation, exactly one owner, private config and independent completion lock, real physical installer deletion, retained logical lock when deletion was denied, and successful owner cleanup retry after permission repair.

Owner login/logout, anonymous redirection, non-owner denial, private-page cache policy, cookie flags, stylesheet routing, settings persistence/escaping, password change and revocation of another active session passed.

Recovery tests preserved an existing table-prefix sentinel, retained private pending state after an injected SQL migration failure, rejected a changed-prefix retry and completed a same-install retry without duplicating the owner.

Chromium performed actual setup and sign-in and opened dashboard, settings, account, installation-health and roadmap pages. Checks covered stylesheet loading, one main heading, no horizontal page overflow and no uncaught JavaScript errors at desktop and phone widths. No final design approval, complete accessibility audit or performance score is implied.

Packaging tests verified permitted deployment contents; exclusion of runtime/local data, test/build scripts, symlinks, old receipts and output folders; exclusive mode-0600 receipt; hash-only deployment; manifest integrity; non-overwrite; and unsafe output/version refusal. The revised packages passed the full SQL/HTTP and Chromium journeys above.

## Earlier evidence and corrections

Run 38454993289 on aa01c32bb5b9450579880b8d2372e5582a38c161 also passed both database legs and Chromium before the final packaging revision. Run 38454059128 stopped on a fixture assumption: the application legitimately redirects to /admin/, while the first test expected only /admin/index.php. Accepting both valid routes corrected the fixture; later full runs passed.

Local unprivileged checks on PHP 8.4.24 passed all 26 storage assertions; Python packaging tests passed all 10 assertions as user nobody. Commands: `runuser -u nobody -- php /mnt/data/spirit-work/tests/storage.php` and `runuser -u nobody -- python3 /mnt/data/spirit-work/tests/package_test.py`. The GitHub packager blob matched the locally tested source (09465d006601f8b7e6fc6ad3adaea6e2cd436463).

## Remaining acceptance work

Actual shared hosting/panel extraction; all account aliases and open_basedir restrictions; Apache/LiteSpeed rules; production TLS/reverse-proxy settings; early-DDL crash windows; legacy dev.1 runtime detection/migration; graceful setup diagnostics when no private base is discoverable; final release ZIP handoff and owner acceptance. The approved public design, CMS/media/music/shows/booking/commerce/payments/backup/upgrades remain later milestones.
