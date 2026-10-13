# Project status — 0.7.0-dev.1

Cumulative development work on **work/catalog-accounts**, based on exact-main verified .6 commit **6b00769fee969570872c88446110429047dc0499**, run **38479822564**. Main remains the source of truth; this new checkpoint must pass its own tests before promotion. No accepted v1.0 or production deployment.

## Implemented in the new source

Owner-managed merchandise categories, draft/published products, approved image references, SKU variants with exact USD minor-unit prices, auditable signed inventory adjustments with replay/conflict protection. Public responsive collection/detail pages and published homepage merchandise; images contained rather than cropped. Store starts closed and contains no fake products.

Browser-owned persistent bag with hashed random cookie identity, CSRF and replay controls, server-side price/stock checks, unavailable-line handling and no private-draft disclosure. Bags do not reserve stock. Checkout, shipping/tax totals and orders remain unavailable pending .8.

Separate customer identities with email-first activation, a password chosen only after mailbox access, hashed expiring single-use reset/activation links, revocable sessions, owner suspension/restoration and customer-bound revision-safe address management. Address book initially supports United States addresses only. Registration is not newsletter consent. Account mail is queued; hosting-panel schedule or owner processing is required.

Additive migration 004; original migrations 001–003 remain unchanged. Existing installer/private runtime/config/secret, CMS/media, music/player, shows/external ticket links and booking/newsletter functions are preserved.

## Verification

Local PHP lint, 35 new money/address unit checks, prior schema/path/communication units and packaging tests pass. New full SQL/HTTP root/subfolder/additive-upgrade and Chromium desktop/mobile suites have been added and are awaiting their exact Actions run. Local PHP lacks pdo_mysql/GD and no browser/database run is claimed locally. Fix regressions rather than weakening security or skipping tests. See docs/COMMERCE.md and docs/TEST_RESULTS.md.

## Next

Verify and promote .7, then implement .8 sandbox-first Authorize.net, guest checkout, atomic inventory reservations, orders/fulfillment and shipping/tax configuration. Follow with staff delegation, full backup/restore/browser ZIP upgrades, retention, advanced CMS/SEO/accessibility/performance and staging acceptance. High-resolution production artwork and real band/product content still require a proper pass and approval. No provider inbox delivery or production payment is established by synthetic tests.
