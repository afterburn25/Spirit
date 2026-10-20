# Spiritual Gorillaz — 1.1.0-rc.1

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, metallic-gold and blue-stage-light identity.

## 1.1.0-rc.1 — audience analytics and reliable phone navigation

The owner administration now includes visitor analytics with charts, inclusive date filters and CSV export: visits, pageviews, completed-visit bounce rate, active viewing time, approximate live visitors, referral domains, supplied search terms, campaign tags, countries, browsers, operating systems, devices and separate crawler traffic. Country lookup works locally using the included, attributed DB-IP data. Optional private City database upload enables available region/city labels. Google Search Console Queries CSV imports provide aggregate search performance with their own dates.

Public navigation now has one controller and reproducible CSS/JavaScript bundles within the existing 130,000-byte budget. The approved contained artwork composition, persistent audio, accessible search and real CMS overrides are preserved. Uploaded section artwork is bounded and contained instead of being cropped by fallback-art rules.

This is a cumulative review candidate on `work/1.1.0-analytics-and-navigation`, based on the latest 1.0.13 work. Screenshot approval and real-host acceptance remain separate from automated verification. Exact observed results are in `docs/TEST_RESULTS.md`.

**Updating an installed website:** migrations `007.sql` and `008.sql` add analytics tables. The current browser ZIP updater deliberately refuses schema changes. Preserve the existing private configuration, owner, database and uploads; deploy the cumulative code through the hosting panel and apply **Database updates** using the existing owner account. Do not reinstall. Detailed steps are in `docs/ANALYTICS.md`.

## Preserved contained continuous-image heroes

The centered, shortened hero windows remain capped at the same 1320px width as the content sections. Each window presents a continuous artwork field behind the real HTML copy and focal subject, with one soft readability fade. The package generates wide WebP background compositions from the existing PNG source artwork. Resizing and dark atmospheric extension do not create new source detail; this build does not claim an increase in the artwork's native resolution. The original foreground assets remain available. Detail already cropped in a source image is not reconstructed by CSS.

The same treatment is applied to Home, Music, Shows, Band, Media, Press, Booking and Merch. The header remains full-width and sticky; Search remains visible and opens its dropdown. Mobile remains a contained copy-first/artwork-second card.

## Preserved application scope

- public Home, Music, Shows, Band, Media, Press, Booking, newsletter and search;
- persistent local-audio player and native no-JavaScript audio access;
- owner CMS/media/branding, staff permissions and private draft/preview workflows;
- booking, transactional email, newsletter and customer-account flows;
- merchandise/catalog/cart/checkout/orders with Authorize.net tokenization boundaries;
- inventory reservation/release, payment reconciliation/reversal safeguards and private order history;
- manual A2/cPanel one-page installer;
- encrypted backup/import/restore, guarded cumulative browser upgrades and independent same-schema code rollback;
- owner launch diagnostics, support report, scheduled-worker heartbeat support and conservative cleanup;
- deterministic package/release gate.

## Launch behavior

Missing optional real-world content is never fabricated. Shows may be empty, Part 3 may remain unpublished, portraits may remain unset and the storefront may stay closed. When commerce is closed, Merch/Bag and merchandise search affordances are not advertised globally. Merchant sandbox acceptance is deferred until commerce activation.

The owner may replace bundled hero artwork or branding through the existing administration workflows. Published CMS artwork remains separate from real HTML content.

## Deployment contract

A2/cPanel manual database setup only. No hosting token, no 20i integration, no setup code and no automatic database/user creation.

Build the cumulative package with:

`python3 tools/package.py --output ./dist`

The packaging workflow installs Pillow 12.3.0 and runs `tools/generate_extended_heroes.py` before collecting the immutable ZIP payload. The generator creates reproducible wide WebP background compositions with dark-atmosphere extension and a feathered join.

Validate the source/package contract with:

`python3 tools/release_gate.py`

The package excludes tests, development tools, runtime/private state, credentials, sessions, backups and recovery receipts. Existing installations upgrade through backups/guarded updates; never reinstall or replace the existing database/configuration to apply an update.

Public bundles are committed. Rebuild after source edits with `npm ci --prefix tools/public-assets --ignore-scripts` and `node tools/build_public_assets.mjs`. CI verifies them with `--check`; the PHP host needs no Node/npm build.

See `docs/ANALYTICS.md`, `docs/ANALYTICS_GEO.md`, `PROJECT_STATUS.md`, `SESSION_HANDOFF.md`, `docs/DESIGN_LOCK.md`, `docs/ROADMAP.md` and `docs/LAUNCH_CHECKLIST.md`.
