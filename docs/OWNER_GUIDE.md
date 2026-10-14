# Owner guide — cumulative 0.9.0-dev.11

Sign in with the owner created during browser installation. No public administrator signup/default password exists. Back up before updates and preserve the private installation secret. See INSTALLATION.md for fresh versus existing installations.

## Fresh installation on A2 Hosting / cPanel

Use the one-page browser installer for a new deployment only. No package setup code, PRIVATE-SETUP receipt or cPanel API token is required. Automatic database creation has been removed. Create an empty database and dedicated user through cPanel Database Wizard or Manage My Databases, assign the user to that database and copy the complete prefixed names and database-user password into the installer. See CPANEL_INSTALLER.md for permissions and precautions.

Checks, database/owner details, corrections and the completion result remain on the same page. The result distinguishes actual installer-folder deletion from a retained, permanently locked directory. Use **Open administration** after completion. Install promptly after uploading or use cPanel Directory Privacy while unattended. An already installed website must follow the upgrade instructions instead; never create a new database to replace its existing data.

PHP cURL and PHP ZIP are reported separately in setup and in owner **Installation health**. Neither blocks manual installation. Payment connections require cURL and browser ZIP upgrades require ZIP. Enable missing extensions for the domain's actual PHP version through cPanel Select PHP Version > Extensions, or ask A2 support; then refresh the check. No API token is involved. The owner health page remains available after the installer is deleted.

## Launch checks and support report

Open **Launch checks** from the owner navigation or overview. The43 observations distinguish local configuration from real launch acceptance. Review PHP limits/extensions, installation/private-storage flags, approved publication counts, service settings and unresolved delivery/payment work. The page is passive: it cannot send, charge, retry, enable services or launch the site. Six external acceptance tasks remain manual.

Use **Download support report** for fixed software/extension/limit/status information without raw hostnames, paths, credentials, customer details or content. Review it before sharing. The old preview Launch readiness URL opens the same protected dashboard. See READINESS.md for scope and privacy.

Media upload, backup import and ZIP staging show current PHP limits. A request rejected for exceeding PHP's whole-body limit gets a fresh form and an actionable413 explanation instead of a misleading form-expiry error. Select a smaller file or have A2 review this domain's limits; application rules and server limits still apply. No host setting changes automatically.

## Content, music and media

Pages, releases, tracks, shows, band members, galleries, videos and press downloads use structured fields that preserve the selected design. Save private draft leaves the live snapshot untouched. Authorized content staff can preview saved drafts privately, compare earlier versions, and restore one as a new private draft. Explicitly publish after reviewing. Stale edits are refused. Search/filter/sort and 25-row pages reach older items in large libraries. Read CONTENT_HISTORY.md.

Upload authorized JPEG/PNG/WebP, MP3/OGG/WAV and PDFs; scripts/SVG/archives are not accepted. Images are decoded/re-encoded, and unreferenced files remain private. Published content/products authorize their selected images/audio/documents. Provide meaningful alternative text and usage-rights confirmation. Historical damaged/missing media blocks restoration instead of silently dropping it.

Reserved page URL names home/band/booking/press feed the designed sections; other pages have individual URLs. Website settings controls name/tagline/welcome. Publish a release and its authorized tracks for the user-initiated persistent player. External streaming platforms use verified outbound links.

## Search, sharing and responsive media

Search & sharing controls site indexing and canonical sharing defaults. Published content can supply approved search/share descriptions and images. Public search, sitemap parts and former-published-slug redirects use published records only; noindex is not access control. See DISCOVERY.md.

Large image displays provide bounded responsive copies while preserving original files, transparency and downloads. A small or read-only hosting cache falls back to the original rather than breaking the image. Unpublishing withdraws authorized generated copies too. Keep meaningful alternative text and verify focal-point crops on real phones. Gallery and player controls support keyboard focus/return, and validation summaries receive focus. See MEDIA_QUALITY.md for the measured test boundary.

## Shows and booking

Enter complete local date/time, timezone and venue/location. Paste the venue's HTTPS ticket URL. Missing links, free admission, sold-out/postponed/cancelled states are explicit; past shows archive. No internal tickets/scanning exist.

Booking inquiries are saved before notification work. Owners can review/assign/update status and private notes. Inquiry confirmation is not a reservation, payment, automatically published show or marketing enrollment.

## Email and subscribers

Configure TLS SMTP, then verify an owner test with both the real provider and mailbox. Inspect queue failures and uncertain acceptance; do not retry a possible accepted message without checking duplicate risk. Newsletter signup requires separate consent and confirmation; unsubscribe and confirmed-only export are supported. No bulk campaign composer is included. COMMUNICATION.md explains optional hosting-panel queue schedules; merely saving a preference does not create a schedule.

## Merchandise, accounts and orders

Manage categories/product drafts/photos/SKU variants/prices/stock adjustments with reasons. Publish descriptive snapshots separately from immediate price/stock changes. The catalog starts closed with no invented products. Customer enrollment is email-first and never grants staff access; address book is US-only initially. Owner suspension revokes sessions.

Orders & payments includes explicit delivery/tax/terms configuration, environment-specific encrypted Authorize.net settings, order review, reporting/notifications, fulfillment/tracking and guarded full reversals. Start in sandbox and complete actual merchant/provider validation. Never share live keys through chat/Git. Payment and fulfillment are different; do not ship held/uncertain/unverified orders or blindly repeat a charge/refund. Read COMMERCE.md and PAYMENTS.md.

## Staff, backups and upgrades

Owners can invite content editors/store managers and manage their scoped access without sharing the owner password. They cannot inherit owner backup/payment credential controls. See STAFF.md.

Backups & safe restore creates/verifies/downloads/imports encrypted snapshots. Save the separate key offline. Same-instance content-only restore preserves operational identities/payments/stock/consent; broader restoration refuses changed protected business state. Keep provider backups too. See BACKUPS.md.

Browser upgrades accepts a trusted newer cumulative same-schema ZIP. Download the separate private recovery receipt before replacement, verify new code in the next request, and use the pinned recovery entrance when bootstrap cannot start. This is not an unattended cross-schema update service. The three retired hosting API file paths are inert compatibility files so existing dev8 updates can replace their old contents; they no longer define callable classes. See UPGRADES.md and MAINTENANCE_CHECKPOINT.md.

Retention offers previewed/bounded technical cleanup, preserving business history and recovery files. New hosting schedules require actual panel configuration and verification.

## Still pending before final release

Full populated-site accessibility/performance/security acceptance, approved high-resolution artwork and real content, genuine provider/host tests and owner acceptance remain. Search/sharing/sitemap and targeted responsive-media/keyboard work are implemented; consult TEST_RESULTS.md for the exact verified checkpoint. LAUNCH_CHECKLIST.md lists the remaining approval and real-environment checks. A tested development checkpoint is not an accepted v1.0 website.
