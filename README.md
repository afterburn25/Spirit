# Spiritual Gorillaz

Authoritative repository **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md. Preserve the approved navy/gold/blue crowned-gorilla design, organized shared-host PHP/MySQL application, external venue ticket links and existing configuration/media/data. No new mockups, native ticketing, secrets in Git or destructive reinstall.

Current work **0.9.0-dev.2** adds owner-operated encrypted application snapshots, archive/media verification, separate recovery-key receipts, import and guarded same-installation content/site-data restore. It also includes .9 staff roles, invitations, recovery and activity history. New full verification is pending; code presence is not test evidence. Read docs/BACKUPS.md and docs/STAFF.md for limits.

Verified main baseline **0.8.0-dev.1**, eb995810e35f23af28080f7f7b2b65f3021085c2, passed exact-main Actions38493945223. Staff application4611afd6 passed38495719613 on both databases and six browser suites; the subsequent invisible-honeypot correction871ce07 is included here and requires current regression verification. This is not a completed .9 maintenance milestone or accepted1.0.

Cumulative modules: browser installer/private runtime, owner/staff administration, selected public pages/CMS/media, music/persistent audio, shows/external ticket links, booking/transactional mail/newsletter consent, catalog/variants/stock/cart, customer accounts, token-only Authorize.net checkout/private orders and guarded reconciliation/reversal/fulfillment. Actual provider SDK/API/webhook sandbox and mailbox acceptance remain separate; CI never submits real payment transactions.

Deployment: PHP8.2+, PDO MySQL/OpenSSL/fileinfo/GD/cURL, MySQL/MariaDB, HTTPS and tested access rules. No required SSH/root or host-side Node/Python/Composer. Build a cumulative Full ZIP on the development machine with `python3 tools/package.py --output ./dist`; upload only that ZIP and keep the separate PRIVATE setup receipt offline. Never overwrite old migrations or reset private runtime to bypass an upgrade issue. See INSTALLATION.md and OWNER_GUIDE.md.

Remaining work includes browser ZIP code upgrades/rollback, retention, advanced content editing, final metadata/accessibility/performance and high-resolution artwork. Hosting/provider/content and owner acceptance are required before1.0.
