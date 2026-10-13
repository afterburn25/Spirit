# Session handoff — resume here

Read AGENTS.md first. Repository **afterburn25/Spirit**, authoritative **main**. Approved navy/gold/blue crowned-gorilla design remains fixed; no new mockups, native ticketing, fake content, secrets in Git or destructive reinstall.

## Current checkpoint

**0.7.0-dev.1** on **work/catalog-accounts**, based on exact-main green .6 source **6b00769fee969570872c88446110429047dc0499** and run **38479822564**. Adds catalog/variants/stock/bag/verified customer accounts and address book. Read PROJECT_STATUS.md and docs/COMMERCE.md. Existing migrations 001–003 must remain unchanged; 004 is additive. Preserve original private runtime/config/encryption secret, content/media, bookings and subscribers.

## First actions

1. Inspect live branch/main and exact newest verification run. Fix real failures without weakening CSP, skipping tests or changing server permissions. Never claim new work green from the .6 run. Verify root/subfolder, upgrade, all existing regressions and new commerce suites.
2. Inspect actual desktop/mobile screenshot artifacts. Ensure product images are contained, forms/tables reflow, and user-visible flows work. Keep the selected visual identity.
3. Promote only a tested checkpoint; record commit/run/jobs, update status/handoff/changelog, and create a cumulative immutable installation ZIP with a separate PRIVATE setup receipt. Read back main after writing.
4. Continue .8 Authorize.net sandbox, server-priced inventory reservations and orders/fulfillment/shipping/tax/guest checkout. Bags alone do not reserve stock or establish payment. No payment-return URL is proof of payment. Never store cards or put credentials in chat/Git.
5. Continue .9 staff roles, full backup/restore/ZIP upgrades/retention, advanced CMS/SEO/accessibility/performance and staging acceptance. Production artwork and actual band data still require a proper pass. No accepted v1.0 without complete tested scope and explicit acceptance.

## Important .7 boundaries

Customer accounts are independent from staff users. Email-first activation chooses a password only after email possession; no attacker-preselected password. Tokens are hashed/encrypted in queue bodies, expiring, single use and scanner-safe GET/POST. Customer password changes and owner suspension invalidate sessions/tokens. Address operations bind customer ownership and revision. US address book only in this phase. Customer creation never opts into newsletter marketing.

Catalog drafts preserve public snapshots. Price/stock/variant availability are explicit immediate operational edits. Bag calculations use integer cents and current server values; duplicate add/stock submissions are idempotent; stale/unpublished lines remain removable without leaking drafts. Cookies are installation-scoped and random; customer logout forgets the browser bag. Checkout is closed until .8. Expired bags cannot be accessed, but retention cleanup remains .9.

## Environment

Container source is obtained from canonical GitHub source artifact. Local PHP has no pdo_mysql/GD and no network DNS; use GitHub Actions for actual disposable SQL/HTTP/TLS/Chromium tests. Never describe local lint as database execution. Full archive packaging excludes tests/runtime/secrets, and source archives contain no setup code. Optional hosting-panel PHP mail schedule or owner queue processing is needed on a quiet site. Real provider/host acceptance remains distinct from loopback/CI results.
