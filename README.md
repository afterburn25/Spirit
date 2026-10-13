# Spiritual Gorillaz

Authoritative repository **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before changing anything.

## Current tested checkpoint

**0.7.0-dev.1** adds merchandise categories, private drafts/public product snapshots, SKU variants/inventory history, a private browser bag, verified customer accounts and a United States address book to the cumulative installer, website, CMS, music, shows and communication modules.

Application6a9396f571a3c8658886f84f1b64b754cd6c7fe6 passed complete workflow38483296156: PHP8.2/MariaDB11.4, PHP8.4/MySQL8.4 and all configured1440px/390px Chromium journeys. See TEST_RESULTS.md for evidence/boundaries. A documentation-only descendant does not change runtime/test code. This is not an accepted1.0 release.

Only owner-published approved content appears. No fabricated people, releases, shows, merchandise or sales are seeded. Tickets link to external venue sellers. The merchandise catalog starts closed; owners may enable published products, but checkout/payments/orders remain disabled until .8. Customer signup never subscribes someone to marketing.

## Deployment

PHP8.2+, PDO MySQL, OpenSSL, fileinfo and GD; MySQL/MariaDB; HTTPS; Apache/LiteSpeed honoring documented access rules. No host-side Node, Composer, Python, SSH or root requirement. Browser installer creates the initial owner, probes actual private storage and permanently locks before safely attempting restricted installer-folder deletion.

On a development machine: `python3 tools/package.py --output ./dist`. This creates a cumulative Full ZIP and **separate PRIVATE setup receipt**. Upload/extract only the ZIP. Never publish the receipt or credentials. Existing supported installs preserve configuration/encryption secret/data/uploads and use the owner additive-schema update, never reinstall. Read INSTALLATION.md.

## Administration and verification

Structured CMS/media/drafts/previews, releases/tracks, shows, band/gallery/press, saved booking inquiries and private notes, encrypted TLS email queue, newsletter confirmation/unsubscribe, catalog/variants/stock/customer access/settings. Read OWNER_GUIDE.md, COMMUNICATION.md and COMMERCE.md.

Account activation/reset email is queued. Configure a hosting-panel PHP schedule or owner queue processing. SMTP acceptance does not prove inbox delivery. CI uses only synthetic data/disposable databases and loopback TLS, never live providers. Test scripts, runtime data and setup receipts are excluded from deployment packages.

## Remaining

The approved navy/gold/blue crowned-gorilla design stays fixed; current reference-crop art needs a production-quality pass. .8 Authorize.net checkout/orders, staff delegation, full backup/restore/browser ZIP upgrades, retention, advanced quality checks and actual provider/host/owner acceptance remain. No version number proves those unfinished features.
