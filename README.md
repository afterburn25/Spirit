# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before implementation. Preserve the approved navy/black/gold/blue crowned-gorilla design and external venue ticket links.

## Current development work

**0.9.0-dev.8 — one-page installation with optional A2/cPanel database creation.** No package setup code, access hash, claim stage or PRIVATE-SETUP receipt. Checks, URL/database/owner settings, corrections and completion remain on one page. Existing-database mode and automatic cPanel-created database/user/random password are integrated. Private writes, CSRF, concurrency/prefix ownership, permanent locks and safe self-deletion remain. Exact new verification is pending; see TEST_RESULTS.md rather than interpreting source presence as a passing run.

Hosting is **A2 with cPanel only, no20i**. See docs/CPANEL_INSTALLER.md for token handling, supported naming/local connections and partial/uncertain outcomes. The panel token is request-only. Real A2 acceptance is not established by simulation.

All cumulative public site/CMS/history/search/metadata/music/player/shows, booking/email/newsletter, catalog/inventory/cart/customers, token-only checkout/orders/fulfillment, staff permissions, encrypted backups/guarded restore, same-schema browser ZIP updates/pinned recovery and retention are preserved.

## Delivery and staging

PHP8.2+, PDO MySQL, OpenSSL, fileinfo and GD; cURL for cPanel/payment transport, ZIP for browser upgrades; MySQL/MariaDB, HTTPS and reviewed server rules. No host-side Node/Python/Composer build, SSH or root needed. On a development checkout `python3 tools/package.py --output ./dist` creates one cumulative Full ZIP without a setup receipt. Upload/extract including hidden access rules and open `/install/`.

Fresh code-free setup is open until completion: install promptly or restrict unattended staging through cPanel Directory Privacy. Existing sites use the documented upgrade path, never fresh reinstall. Preserve original config/encryption secret, data/media and separate backup/update recovery keys.

## Final acceptance

Read OWNER_GUIDE, INSTALLATION, CPANEL_INSTALLER, DISCOVERY, MEDIA_QUALITY, CONTENT_HISTORY, STAFF, PAYMENTS, BACKUPS, UPGRADES and LAUNCH_CHECKLIST in docs. Same-schema updates and same-instance guarded restore retain explicit limits. Approved artwork/content, broader populated-site quality, actual host/provider verification and final owner review remain. Synthetic payments do not establish real transactions, SMTP acceptance is not guaranteed inbox delivery, and fixture budgets are not field Core Web Vitals. **No acceptedv1.0 is claimed.**
