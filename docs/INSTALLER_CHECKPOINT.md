# One-page A2/cPanel installer —0.9.0-dev.8

Base verified main1df65ce90124adda5ddb3fd5936be9f006dcee9a. Work:work/single-page-installer, PR11. Exact integrated CI pending. Never replace main with unverified code or label a source archive as application verification.

User decisions: no package setup code/receipt; checks, owner/database configuration and completion on one page; A2 with cPanel only, no20i. Existing DB mode and optional cPanel-created database/user/generated password are integrated. Token handling, naming, TLS/network restrictions, least necessary database grants and partial/uncertain recovery are specified in CPANEL_INSTALLER.md. Read that file before extending supported retry behavior.

Test all cumulative modules plus56 cPanel units, actual TLS transport, real dedicated SQL-user/database boundary tests, and the installer at1440/390/320px plus no-JavaScript. Inspect automatic-mode, error and completion renders. Preserve migrations001–006 and pinned recovery/access files. On green, update all continuity docs with exact evidence, promote safely and package an immutable Full ZIP without PRIVATE-SETUP.

No genuine A2 account, mailbox or merchant result is established. Fresh setup is open until completed; use hosting-panel directory protection for unattended staging. Existing installations remain locked and require upgrades, not reinstall. Backup/update recovery keys and original private encryption secret are not removed.
