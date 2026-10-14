# Spiritual Gorillaz

Authoritative repository **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before changing anything. Preserve the selected navy/gold/blue crowned-gorilla design, organized shared-host PHP/MySQL application and external venue ticket links. No new mockups or native ticketing.

**0.9.0-dev.1 — staff access and audit checkpoint, verification pending.** Separate content-editor/store-manager capabilities, owner-confirmed email-first invitations, single-use staff recovery, session revocation, fulfillment-only order access and owner activity history. Migration 006 is additive; 001–005 unchanged. See STAFF.md and TEST_RESULTS.md. This is not completion of the full .9 maintenance roadmap or an accepted 1.0 release.

Last verified application: 0.8 source cd3d645bf031e7ab8cf2a7c32b44278e8bc8d89e, full Actions 38493194973. Both database environments and five browser suites passed, using synthetic gateway/SDK boundaries and real loopback TLS transport. Real Authorize.net SDK/API/webhooks, mailbox delivery, actual hosting, merchant compliance and final art/content acceptance remain separate.

Cumulative modules: browser installer/private runtime/owner authentication, selected public pages, structured content/media, releases/persistent audio, external-ticket shows, booking/transactional email/newsletter consent, catalog/variants/stock/cart, verified customer accounts, token-only checkout/private orders and guarded reporting/reversal/fulfillment. No fake public content, raw card storage, automatic retry of uncertain charges or destructive reinstall.

Deployment: PHP8.2+, PDO MySQL/OpenSSL/fileinfo/GD/cURL, MySQL/MariaDB, HTTPS and tested access rules. No required SSH/root or host-side Node/Python/Composer. Build Full ZIP on the development machine with `python3 tools/package.py --output ./dist`; keep its separate PRIVATE setup receipt off the server and out of Git. Preserve private config/encryption secret/media/data during additive updates. Full backup/restore/browser ZIP upgrades remain next work.
