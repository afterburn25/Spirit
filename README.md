# Spiritual Gorillaz — 1.0.13

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, metallic-gold and blue-stage-light identity.

## 1.0.13 contained continuous-image heroes

Version 1.0.13 keeps the centered, shortened hero dimensions established in 1.0.12, but corrects the split-window mistake. Each hero is now one continuous artwork field across the complete contained window, including behind the real HTML heading and copy. Production artwork is deterministically extended to provide genuine dark atmospheric space on the left while preserving the complete right-side focal subject. Copy overlays the naturally dark portion of the same image with one soft readability fade—there is no separate text box, right-hand image box, hard seam, blurred duplicate or stretched low-resolution layer.

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

The packaging workflow installs Pillow 12.3.0 and runs `tools/generate_extended_heroes.py` before collecting the immutable ZIP payload. The generator creates the wide WebP masters from the reviewed native PNG artwork using deterministic dark-atmosphere extension and a feathered join.

Validate the source/package contract with:

`python3 tools/release_gate.py`

The package excludes tests, development tools, runtime/private state, credentials, sessions, backups and recovery receipts. Existing installations upgrade through backups/guarded updates; never reinstall or replace the existing database/configuration to apply an update.

See `PROJECT_STATUS.md`, `SESSION_HANDOFF.md`, `docs/DESIGN_LOCK.md`, `docs/ROADMAP.md` and `docs/LAUNCH_CHECKLIST.md`.
