# Project status — 0.7.0-dev.1

Cumulative application source **6a9396f571a3c8658886f84f1b64b754cd6c7fe6** passed the complete Actions workflow **38483296156** on PR #2. Jobs **115590780970** (PHP8.4/MySQL8.4 plus Chromium) and **115590781333** (PHP8.2/MariaDB11.4) passed. This documentation-only descendant changes no application code or tests. Inspect live main and its exact run before resuming.

## Implemented and exercised

Owner-managed merchandise categories, draft/published products, approved image references, SKU variants with exact USD minor-unit prices, auditable signed inventory adjustments with replay/conflict protection. Public responsive collection/detail pages and published homepage merchandise; images contained rather than cropped. Store starts closed and contains no fake products.

Browser-owned persistent bag with hashed random cookie identity, CSRF and replay controls, server-side price/stock checks, unavailable-line handling and no private-draft disclosure. Bags do not reserve stock. Checkout, shipping/tax totals and orders remain unavailable pending .8.

Separate customer identities with email-first activation, password choice only after mailbox access, hashed expiring single-use reset/activation links, revocable sessions, owner suspension/restoration and customer-bound revision-safe address management. Address book initially supports United States addresses only. Registration is not newsletter consent. Account mail is queued; hosting-panel schedule or owner processing is required.

Additive migration 004; original migrations 001–003 remain unchanged. Existing installer/private runtime/config/secret, CMS/media, music/player, shows/external ticket links and booking/newsletter functions are preserved.

## Verification

35 new money/address unit checks plus the complete existing regression matrix passed. New SQL/HTTP root/subfolder/additive-upgrade and Chromium desktop/mobile suites passed. Forty synthetic screenshots were downloaded; catalog/account/admin contact sheets and selected full-size forms were visually reviewed. Source artifact was compared with the local draft; runtime code matches apart from a non-functional muted-label CSS typo recorded for later cleanup. No local database/browser run, real provider inbox delivery or payment is claimed.

## Next

Continue .8 sandbox-first Authorize.net, guest checkout, atomic inventory reservations, orders/fulfillment and shipping/tax configuration. Follow with staff delegation, full backup/restore/browser ZIP upgrades, retention, advanced CMS/SEO/accessibility/performance and staging acceptance. High-resolution production artwork and real band/product content still require a proper pass and approval. No accepted v1.0 or live deployment has occurred.
