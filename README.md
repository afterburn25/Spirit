# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before implementation. Preserve the selected navy/black/gold/blue crowned-gorilla design and external venue ticket links.

## Current development release

**0.9.0-dev.8 — single-page, code-free installation.** The user's updated installation flow removes the package setup code and separate receipt. Checks, database/owner configuration, correction and completion are one browser page. Private storage, CSRF, installation locks, self-deletion and all cumulative website modules remain. New full regression verification is pending; see docs/TEST_RESULTS.md. The previous dev7 checkpoint remains documented in MEDIA_QUALITY_CHECKPOINT.md.

The cumulative application includes the browser installer, public site/CMS/history/discovery/music/player/shows, booking/email/newsletter, catalog/inventory/cart/customer accounts, token-only payments/orders/fulfillment, staff permissions, encrypted backups/guarded restoration, same-schema browser ZIP upgrades/pinned recovery and conservative retention.

## Shared-host delivery

PHP 8.2+, PDO MySQL, OpenSSL, fileinfo and GD; cURL for payment transport and ZIP for browser updates; MySQL/MariaDB, HTTPS and reviewed server access rules. No required SSH/root or host-side Node, Python or Composer build.

On a development checkout, `python3 tools/package.py --output ./dist` creates one cumulative Full ZIP. No setup code, access hash or PRIVATE-SETUP receipt is created. Upload/extract the ZIP including hidden access-rule files, then open `/install/`. Install immediately or restrict staging through hosting-panel directory protection until ready. Backup/update recovery keys remain separate and private; preserve the original installation secret. Follow docs/INSTALLATION.md for fresh staging versus existing installations.

## Guides and final acceptance

Read docs/OWNER_GUIDE.md, DISCOVERY.md, MEDIA_QUALITY.md, CONTENT_HISTORY.md, STAFF.md, PAYMENTS.md, BACKUPS.md, UPGRADES.md and LAUNCH_CHECKLIST.md. The documented same-schema update and same-instance restore limits are intentional, not universal disaster recovery.

Approved production artwork/content, broader populated-site quality review, actual host/provider verification and final owner acceptance remain. Synthetic payment tests do not establish a genuine merchant transaction, SMTP acceptance is not guaranteed inbox delivery, and fixture bandwidth measurements are not field Core Web Vitals. **No accepted v1.0 is claimed.**
