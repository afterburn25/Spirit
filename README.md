# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md. Preserve the approved navy/black/gold/blue design, real components and external venue ticket links.

## 0.9.0-dev.9 — verified manual database installer

User-confirmed hosting: **A2 with cPanel only, no 20i**. Automatic database/user creation is removed at the user's request. The installer accepts an existing database host, port, full database name, database username/password and table prefix. No cPanel API token, package setup code, claim stage or PRIVATE-SETUP receipt.

One page contains hosting checks, manual database/owner details, corrections and completion. It creates the application tables, saves private configuration, creates the owner, locks setup and attempts verified self-deletion. Native forms work without JavaScript. Existing tables and uploads are not reset.

cURL and ZIP have separate optional notices in setup and owner Installation health. Manual installation can continue without them. Payment-server connections require cURL and browser ZIP upgrades require ZIP. Each notice names the missing extension and explains enabling it for the domain's actual PHP version in cPanel or through A2 support; no API token is involved.

## Installation

Run `python3 tools/package.py --output ./dist` on a development checkout to produce the cumulative Full ZIP. Upload/extract it including hidden access rules and open `/install/`. The host needs PHP 8.2+, PDO MySQL, OpenSSL, fileinfo and GD, HTTPS, private writable storage and the supplied/equivalent access rules. No host-side Node/Python/Composer build, SSH or root.

Create an empty database and dedicated user through cPanel Database Wizard or Manage My Databases; copy their complete prefixed names and the database-user password to the installer. See docs/CPANEL_INSTALLER.md. Install promptly or protect unattended staging with cPanel Directory Privacy. No setup-code gate remains.

Existing sites must upgrade, not reinstall. Preserve original private config, encryption secret, database/media and separate backup/update recovery keys. Same-schema browser upgrades do not recreate the removed installer. An earlier unfinished automatic attempt is retained for manual owner/host review; the new installer never executes its saved PHP state or contacts a hosting API.

The three retired Cpanel file paths are inert declaration/comment-only compatibility files. They define no classes and provide no API functionality; they let supported dev8 updaters replace the old code without omitting tracked paths or weakening cumulative-package validation.

## Verification and boundaries

Application **4736764ef681103c7d1b02b3851861a43c9201e5** passed full workflow **38567553630** and focused maintenance **38567553677**. Both SQL environments, twelve Chromium suites and actual Apache HTTPS rules passed. Tests include real manual installation with cURL and ZIP actually disabled. The final documentation-only descendant changes no runtime, assets, migrations, workflows or tests. See docs/TEST_RESULTS.md for evidence.

All public/admin/CMS/music/show/booking/email/store/account/order/staff/backup/update/retention functionality is retained. Immutable migrations 001–006 and pinned recovery/access files are unchanged. Actual A2 hosting, receiving-mailbox and merchant sandbox acceptance, production artwork/content, broader quality review and owner approval remain. No final v1.0 or live-provider result is claimed.
