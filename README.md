# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before changing anything.

## Current development work

**0.7.0-dev.1** adds merchandise categories, private drafts/public product snapshots, SKU variants and inventory history, a private browser bag, verified customer accounts and a United States address book to the cumulative installer, website, CMS, music, shows and communication modules.

New verification is pending on **work/catalog-accounts**. Exact-main baseline .6 commit 6b00769fee969570872c88446110429047dc0499 passed Actions 38479822564; that result must not be presented as verification of this new source. See PROJECT_STATUS.md and docs/TEST_RESULTS.md. This is not an accepted 1.0 release.

Only owner-published approved content appears. No fabricated band members, releases, shows, products, sales or testimonials are seeded. Show tickets link to the venue's external seller. The merchandise catalog starts closed; an owner can enable approved products, but checkout/payments/orders remain disabled until .8. Customer signup never subscribes a person to marketing.

## Deployment

PHP 8.2+, PDO MySQL, OpenSSL, fileinfo and GD; MySQL/MariaDB; HTTPS; Apache/LiteSpeed rules as documented. No host-side Node, Composer, Python, SSH or root requirement. Browser installer and initial owner account; actual private-storage probes, permanent lock and restricted installer-folder deletion attempt.

On a development machine run `python3 tools/package.py --output ./dist`. This creates a cumulative deployment ZIP and a **separate private setup receipt**. Upload/extract only the ZIP. Keep receipt and credentials off the website and out of Git. Existing supported installations preserve config/secret/data/uploads and use owner-only additive database updates, never reinstall. Read docs/INSTALLATION.md.

## Administration

Content and media: structured drafts, publication, owner previews, band/gallery/press, releases/tracks and shows. Communication: saved booking inquiries, owner notes/assignment, encrypted TLS SMTP queue, newsletter confirmation/unsubscribe. Store: categories/products, variants/stock, customer suspension/restoration and settings. Read docs/OWNER_GUIDE.md, docs/COMMUNICATION.md and docs/COMMERCE.md.

Customer activation/reset messages are queued. Configure a hosting-panel PHP schedule or use owner queue processing. SMTP acceptance is not proof of inbox delivery. No payment credentials or production mailbox tests are included in CI.

## Verification and remaining scope

The Actions matrix exercises PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, real SQL/HTTP installation/migration/content/communication/commerce flows, loopback TLS mail transport and Chromium at 1440px/390px. Test fixtures refuse non-test databases. Test data, scripts, secrets and setup receipts never enter the public installation ZIP.

The approved navy/gold/blue crowned-gorilla design remains fixed. Current reference-crop art still needs a high-resolution production pass. Authorize.net checkout/orders, delegated staff permissions, full backup/restore/browser ZIP upgrades, retention, broader quality checks and actual owner/provider/host acceptance remain. Never equate a version number with completion of those features.
