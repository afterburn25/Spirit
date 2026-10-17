# Spiritual Gorillaz — 1.0.0-rc.10

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, gold and blue crowned-gorilla design. The RC10 application implementation is complete; the remaining path to 1.0.0 is final CI, real-host/external acceptance and explicit owner approval.

## Current software state

Implemented:
- public Home, Music, Shows, Band, Media, Press, Booking, newsletter and search;
- persistent local-audio player and native no-JavaScript audio access;
- owner CMS/media/branding, staff permissions and private draft/preview workflows;
- booking, transactional email, newsletter and customer-account flows;
- merchandise/catalog/cart/checkout/orders with Authorize.net tokenization boundaries;
- inventory reservation/release, payment reconciliation/reversal safeguards and private order history;
- manual A2/cPanel one-page installer;
- encrypted backup/import/restore, guarded cumulative browser upgrades and independent same-schema code rollback;
- owner launch diagnostics, support report, scheduled-worker heartbeat evidence and conservative cleanup;
- deterministic package/release gate.

Recent launch-hardening includes approved copy fallbacks, a confirmed three-member Band fallback, a scalable bundled logo, contained fallback hero presentation, closed-store navigation, dormant-commerce diagnostics and 360 px browser coverage.

## Launch behavior

Missing optional real-world content is never fabricated. Shows may be empty, Part 3 may remain unpublished, portraits may remain unset and the storefront may stay closed. When commerce is closed, Merch/Bag are not advertised globally and merchant sandbox acceptance is deferred until feature activation.

The owner may replace the bundled logo/hero through the existing Branding/Page artwork workflows without a software release.

## Deployment contract

A2/cPanel manual database setup only. No hosting token, no 20i integration, no setup code and no automatic database/user creation.

Build the cumulative package with:

`python3 tools/package.py --output ./dist`

Validate the source/package contract with:

`python3 tools/release_gate.py`

The package excludes tests, development tools, runtime/private state, credentials, sessions, backups and recovery receipts. Existing installations upgrade through backups/guarded updates; never reinstall or replace the existing database/configuration to apply an update.

## Final 1.0 boundary

Do not tag final **1.0.0** until:
- current CI is green on the final source head;
- required A2/real-host acceptance is recorded;
- genuine merchant sandbox acceptance is complete if commerce will be enabled;
- chosen launch content/assets are owner-approved;
- the owner explicitly accepts the final site.

See `PROJECT_STATUS.md`, `SESSION_HANDOFF.md`, `docs/ROADMAP.md`, `docs/LAUNCH_CHECKLIST.md` and `docs/acceptance/CONSOLIDATED-FINAL-PASS.md`.
