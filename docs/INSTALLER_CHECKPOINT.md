# Verified one-page A2/cPanel installer — 0.9.0-dev.8

Application **b9d32849e9b556dbc2b6d4f2adb7526703b46562** passed full workflow **38560767553** and focused maintenance **38560767558**. Both database environments, all twelve Chromium workflows and actual Apache HTTPS checks passed. This final documentation-only descendant changes no runtime, assets, database migrations or tests.

User decisions: no package setup code/receipt; checks, owner/database configuration and completion on one page; A2 with cPanel only, no 20i. Existing DB mode and optional cPanel-created database/user/generated password are implemented. Token handling, naming, TLS/network restrictions, database-scoped grants and partial/uncertain recovery are specified in CPANEL_INSTALLER.md.

498 pure assertions, 12 package tests, nine loopback TLS cases, eight simulated-UAPI/real-SQL scenarios, all cumulative SQL/HTTP/browser tests and actual Apache access checks passed. Eleven installer renders inspected at a0f0f380 / artifact 11891257992. Migrations 001–006 and pinned recovery/access files unchanged.

The cumulative Full ZIP has no PRIVATE-SETUP. Existing installations update, never reinstall. Fresh setup is open until completion; protect unattended staging. Backup/update recovery keys remain. Actual A2 account/mailbox/merchant, real content/master artwork, broader quality and owner acceptance remain before v1.0.
