# Project status — 0.9.0-dev.8

Application **b9d32849e9b556dbc2b6d4f2adb7526703b46562** passed full workflow **38560767553** and focused maintenance **38560767558**. Both database environments, all twelve Chromium workflows and actual Apache HTTPS checks passed. This final documentation-only descendant changes no runtime, assets, database migrations or tests.

## Landed scope

User-confirmed target: **A2 Hosting with cPanel only, no 20i**. No package setup code, claim gate, access hash or PRIVATE-SETUP receipt. One page contains hosting/write checks, URL/database/owner configuration, validation feedback and completion. Native forms remain usable without JavaScript; password/token values are not reflected after correction.

The automatic option reads cPanel naming restrictions and inventories, generates an unused database/dedicated user with a strong random password, grants necessary privileges on that database only, and verifies PDO before migrations. The cPanel token is request-only. HTTPS on port 2083, public IPv4 DNS pinning, peer/hostname verification, no proxy/redirect and bounded responses/time are enforced.

Private mode-0600 progress preserves confirmed operations on rejection or later migration failure. Unknown mutation outcomes pause for inspection rather than duplicate resources, adopt unrelated accounts, reset passwords or delete databases. Completion publishes locked private config before self-deletion; retained installer folders remain permanently locked. Existing-database mode remains available.

All cumulative website/admin/commerce/maintenance functionality is retained. Migrations 001–006 and pinned access/recovery files are unchanged. An existing audio accessibility timing regression was also corrected: native metadata updates elapsed/duration/ARIA immediately rather than waiting for a throttled timeupdate.

## Verification

498 pure assertions (including 56 cPanel checks), 12 package tests, real SQL/HTTP workflows on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4, actual Apache HTTPS access rules and all twelve Chromium suites passed. One-page installation covers 1440 / 390 / 320 px and a no-JavaScript path. Nine loopback TLS cPanel scenarios verify real HTTPS/token/body handling. Eight API-boundary scenarios create actual dedicated SQL users/databases in disposable services; this is not a real A2 account test.

Eleven installer renders were inspected from artifact 11891257992 at a0f0f380; installer runtime/CSS is unchanged in the subsequent playback fix. Forms, automatic-mode controls and completion were checked across the tested widths and no-JavaScript path. Final canonical-byte/package checks are recorded with the delivered release.

## Remaining

Actual A2 API/host account, mailbox, genuine merchant sandbox, approved artwork/content, populated-site quality and final owner acceptance remain. Fresh code-free setup is open until completion; protect unattended staging. Preserve original encryption/config/data/media and unrelated backup/update recovery keys. Same-schema updates and same-instance guarded restoration retain their documented limits. No accepted v1.0 yet. See CPANEL_INSTALLER.md and LAUNCH_CHECKLIST.md.
