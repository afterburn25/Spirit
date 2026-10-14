# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before implementation. Preserve the approved navy/black/gold/blue crowned-gorilla design and external venue ticket links.

## Current cumulative development release

**0.9.0-dev.8 — one-page, code-free installer with optional A2/cPanel database creation.** Hosting is **A2 with cPanel only; no 20i integration**.

No package setup code, access hash, claim stage or PRIVATE-SETUP receipt is generated. Hosting checks, URL/database/owner settings, correction and completion share one browser page. Choose an existing database or authorize automatic creation of a new database, dedicated user and random password. The installer verifies the resulting connection, applies application tables, saves private configuration, creates the owner and attempts verified self-deletion. Permanent locks remain even when removal is denied.

Application **b9d32849e9b556dbc2b6d4f2adb7526703b46562** passed full workflow **38560767553** and focused maintenance **38560767558**. Both database environments, all twelve Chromium workflows and actual Apache HTTPS checks passed. This final documentation-only descendant changes no runtime, assets, database migrations or tests. See docs/TEST_RESULTS.md for exact evidence and limits. Synthetic cPanel tests do not establish acceptance against a real A2 account.

## Installation and upgrades

On a development checkout run `python3 tools/package.py --output ./dist`. Upload/extract the cumulative Full ZIP including hidden access rules, and open `/install/`. No setup receipt is needed. PHP 8.2+, PDO MySQL, OpenSSL, fileinfo and GD are required; cURL supports automatic cPanel/payment connections and ZIP supports browser upgrades. HTTPS and reviewed server access rules are required. The host needs no Node, Python, Composer build, SSH or root.

For automatic setup, enter a short-lived cPanel API token only into the HTTPS installer and revoke it afterward. The token is not stored in sessions, permanent config or recovery records. Generated DB credentials are saved privately. See docs/CPANEL_INSTALLER.md for quotas, local-MySQL/network limits and safe handling of partial or uncertain results.

Fresh setup is open until completed: install promptly or use cPanel Directory Privacy while unattended. Existing sites must upgrade, not reinstall or create a replacement database. Preserve the original config, encryption secret, database/media and separate backup/update recovery keys.

## Preserved functionality and final acceptance

The cumulative release retains CMS/history/discovery/media, music/player/shows, booking/email/newsletter, catalog/cart/customers/checkout/orders, staff roles, encrypted backups/guarded restore, same-schema browser ZIP updates/pinned recovery and retention. Migrations 001–006 and pinned access/recovery files are unchanged.

Approved production artwork/content, populated-site quality, actual host/cPanel/mailbox/merchant acceptance and owner review remain. Same-schema updates and same-instance guarded restoration retain their documented limits. No real payment, receiving-mailbox result or accepted v1.0 is implied. Follow docs/LAUNCH_CHECKLIST.md before cutover.
