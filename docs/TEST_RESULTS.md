# Test evidence — 0.1.0-dev.2

Updated 2026-10-13. Development build only; no accepted release or live deployment.

## Completed CI evidence

**GitHub Actions run 38454993289: SUCCESS.** Tested commit: **aa01c32bb5b9450579880b8d2372e5582a38c161**.

- Job 115495304915, PHP 8.2 / MariaDB 11.4: syntax, original 43 unit checks, 26 storage checks, and real database/HTTP integration passed.
- Job 115495305263, PHP 8.4 / MySQL 8.4: the same suites plus actual Chromium installer/admin smoke passed at 1440px and 390px.
- HTTP scenarios installed real packaged sites at root and subfolder URLs; verified one owner, private config/lock, actual installer deletion, retained lock when deletion was denied, successful owner cleanup retry, login/logout, owner-only access, settings persistence/escaping, password changes and other-session revocation.
- Recovery scenarios preserved existing-prefix data, retained pending state after an injected SQL migration failure, rejected changed-prefix retries, and completed a same-install retry without duplicating the owner.
- Chromium completed real setup/sign-in and opened settings, account, installation-health and roadmap pages, checking stylesheet loading, main headings, no horizontal page overflow and no uncaught JavaScript errors. This is smoke coverage, not final visual approval.

The earlier run 38454059128 stopped on a fixture URL assumption after successful installation. The application redirects to /admin/; the old test expected only /admin/index.php. The fixture now accepts both valid routes. The corrected full run above passed.

## Local checks / package revision

As unprivileged user nobody, PHP 8.4.24: `php /mnt/data/spirit-work/tests/storage.php` passed all 26 assertions. The initial storage suite caught a nested-public_html selection mistake; the regression passed after including conventional public ancestors.

As unprivileged user nobody: `python3 /mnt/data/spirit-work/tests/package_test.py` passed all 10 isolated package tests. They cover permitted deployment contents, exclusion of runtime/local data/test tools/symlinks/old receipts/output folders, mode-0600 private receipt, hash-only deployment, manifest integrity, non-overwrite and unsafe output/version refusal.

The package revision is now being committed with these tests in the matrix. Full matrix verification of that revision must be confirmed from its subsequent Actions run; do not attribute this later code to the earlier green commit.

## Still unverified / later scope

Actual shared hosting and panel extraction; all account aliases and open_basedir restrictions; Apache/LiteSpeed protection; production TLS/reverse-proxy configuration; early-DDL crash windows; migration from dev.1 storage; final release ZIP handoff; visual acceptance, measured performance and a full accessibility audit. The public redesign, CMS/media/music/shows/booking/commerce/payments/backup/upgrades remain later milestones.
