# Test evidence

Development work in progress: 0.1.0-dev.2. No production release or live-host test is claimed.

## Verified in this session
- Baseline CI run 38454059128 executed on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4. Syntax and the original 43 checks passed. HTTP tests reached successful fresh database installation, exactly one owner, private config/lock creation and actual installer deletion.
- That run failed at the test's owner-dashboard URL assertion because the application correctly redirects to `/admin/`, while the fixture expected only `/admin/index.php`. The fixture now accepts both valid dashboard routes; this was a test expectation error, not evidence of a broken login.
- The new storage suite passed all 26 checks locally as user nobody on PHP 8.4.24. Exact command: `runuser -u nobody -- php /mnt/data/spirit-work/tests/storage.php`. The two changed PHP classes and storage test also passed syntax checks.
- The storage suite initially caught a nested public_html base-selection error; conventional public ancestors are now included, and the failing regression passes.

## Pending verification at this commit
The updated matrix and new Chromium tests must finish before full SQL/HTTP/browser success is claimed. Root/subfolder auth, settings, password revocation, cleanup retry and migration recovery have executable tests, not yet a final green result. Tests run only against guarded disposable database names and prefixes.

Still not verified: actual user hosting/ownership/open_basedir, Apache/LiteSpeed protection, production reverse-proxy/TLS configuration, full visual acceptance, measured performance/accessibility, migration from an existing dev.1 runtime, early-DDL crash windows, package secrecy under arbitrary output layouts, and later feature milestones.
