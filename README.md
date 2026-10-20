# Spiritual Gorillaz — 1.1.0-rc.5

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, metallic-gold and blue-stage-light identity.

## 1.1.0-rc.5 — fixed concert-stage background

The user-selected blue-lit concert stage now sits behind the public website as a fixed viewport background. Content scrolls over the stationary artwork. The main site shell is transparent so the stage remains visible through page gutters and section gaps. Existing contained hero artwork is preserved, and exposed headings, introductions, detail copy and captions receive matching dark content panels.

The sticky header's navy background and gold lower border extend to both screen edges. Its inner layout stays in place: the logo, navigation, search and Book the Band controls retain their existing positions. The panels use dark navy surfaces, subtle blue borders and gold accents to keep content legible over the stage.

The decorative image cannot intercept clicks or keyboard interaction and needs no scroll JavaScript. Phone framing favors the drum kit and stage equipment on the right. Print and forced-colors modes omit the decorative image. The background is scoped to the public site; administration and the installer keep their existing surfaces.

The selected native 1672 × 941px source is exported to a 243,104-byte WebP without retouching, cropping or upscaling. This is generated music-themed artwork, not a photograph of a real band performance. The source master, prompt and reproducible export are retained in development source. See `docs/STAGE_BACKGROUND.md` and `docs/TEST_RESULTS.md` for implementation and observed verification.

This cumulative candidate retains the favicon and analytics work. Installed rc.1/rc.2/rc.3/rc.4 use the existing guarded same-schema Browser upgrades path when its checks pass; no new migration or reinstall is required.

## Retained rc.4 — transparent crowned-gorilla favicon

The selected sculpted gold gorilla head now supplies the website favicon. Its visible crown-to-chin silhouette fills 98% of a square canvas, preserving natural proportions and genuine transparency while removing excess empty space. The browser still controls its tab-icon area; larger source files improve available resolution rather than enlarging browser controls.

Public, administration and account layouts share versioned icon links. The cumulative package includes native 16/32/48/64px PNGs, a matching multi-size ICO, a 180px touch icon, and 192/512px assets. The previous SVG path remains available with matching artwork. Source artwork and a reproducible browser export tool are retained in the repository; the PHP host needs no image build.

This is a favicon presentation update. Existing analytics, data, migrations, public navigation and site content retain their rc.3 behavior. Use the normal same-schema Browser upgrades path from installed rc.1/rc.2/rc.3 when its standard checks pass. See `docs/FAVICONS.md` and the scoped verification in `docs/TEST_RESULTS.md`.

## Retained rc.3 — accurate page drilldowns and search attribution

Page-performance links now open Visitor Explorer for the exact recorded release, show, member, product or custom page, including its validated `slug` identifier. The same page/date filters remain active when inspecting a visit. Unrelated query fields and private paths are still rejected. Supported Ecosia referrals now appear as Organic search, consistent with the collector's existing recognition of supplied search terms.

These are read-only reporting corrections: retained traffic becomes navigable without data conversion. Migrations, collection rules, configuration, media and the pinned recovery layer remain unchanged. The complete delivered rc.3 verification is archived under `docs/history/VERIFICATION-1.1.0-rc.3.json`.

## Retained graphical analytics, visitor exploration and Overview snapshots

Analytics has its own owner-only administration section. The dashboard puts traffic charts and consistent color-coded metrics near the top, adds graphical acquisition/device/engagement breakdowns and hourly activity, and exposes retained visit details through a filterable visitor explorer with chronological page journeys. The main owner Overview includes a seven-day analytics snapshot and a compact traffic chart.

The public search button sits immediately beside **Book the Band**, with larger navigation text and responsive spacing through tablet and phone widths.

The retained rc.2 analytics refinements added no database migration or changes to the public hero artwork. Existing `007.sql` and `008.sql` remain byte-for-byte unchanged. Empty or unavailable observations remain explicitly labeled; review screenshots use synthetic data only.

The owner administration now includes visitor analytics with charts, inclusive date filters and CSV export: visits, pageviews, completed-visit bounce rate, active viewing time, approximate live visitors, referral domains, supplied search terms, campaign tags, countries, browsers, operating systems, devices and separate crawler traffic. Country lookup works locally using the included, attributed DB-IP data. Optional private City database upload enables available region/city labels. Google Search Console Queries CSV imports provide aggregate search performance with their own dates.

Public navigation now has one controller and reproducible CSS/JavaScript bundles within the existing 130,000-byte budget. The approved contained artwork composition, persistent audio, accessible search and real CMS overrides are preserved. Uploaded section artwork is bounded and contained instead of being cropped by fallback-art rules.

This is a cumulative review candidate on `work/1.1.0-analytics-and-navigation`, based on the latest 1.0.13 work. Screenshot approval and real-host acceptance remain separate from automated verification. Exact observed results are in `docs/TEST_RESULTS.md`.

**Updating an installed website:** installed `1.1.0-rc.1`, `1.1.0-rc.2`, `1.1.0-rc.3` and `1.1.0-rc.4` sites already have the unchanged analytics schema, so the normal guarded same-schema **Browser upgrades** path applies when its checks pass. Updating a `1.0.x` site adds migrations `007.sql` and `008.sql`; the browser updater deliberately refuses that schema transition. For `1.0.x`, preserve the existing private configuration, owner, database and uploads, deploy the cumulative code through the hosting panel and apply **Database updates** with the existing owner account. Do not reinstall. Detailed steps are in `docs/ANALYTICS.md`.

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
