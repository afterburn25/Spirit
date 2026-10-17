# Spiritual Gorillaz — 1.0.2

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, gold and blue crowned-gorilla design.

## 1.0.2 concept-header refinement

Version 1.0.2 continues the approved-concept rebuild with the header and Home hero geometry shown in the owner-approved reference panels.

The update includes:
- a larger stacked crown-and-wordmark logo that is closer to the approved reference identity;
- a substantially larger, centered desktop navigation system;
- a larger Search control with an accessible expandable search box;
- a full-width cinematic Home hero instead of a visibly split text/image presentation;
- a left-side content container layered over the full hero artwork;
- preserved strict title containment at desktop, tablet, 390 px and 360 px widths;
- release-version cache busting for bundled CSS, JavaScript, logos and artwork;
- preserved CMS, content, media, booking, commerce, customer, backup and recovery data.

Version 1.0.2 is a cumulative in-place upgrade and includes all 1.0.1 native-resolution artwork and layout work.

## Implemented application

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

The owner may replace bundled artwork and publish approved content through the existing administration workflows without a software release.

## Deployment contract

A2/cPanel manual database setup only. No hosting token, no 20i integration, no setup code and no automatic database/user creation.

Build the cumulative package with:

`python3 tools/package.py --output ./dist`

Validate the source/package contract with:

`python3 tools/release_gate.py`

The package excludes tests, development tools, runtime/private state, credentials, sessions, backups and recovery receipts. Existing installations upgrade through **System & maintenance → Browser upgrades**; never reinstall or replace the existing database/configuration to apply an update.

An existing 1.0.0 or 1.0.1 installation must receive the cumulative `Spiritual-Gorillaz-1.0.2-Full.zip`. The upgrade validator intentionally refuses same-version replacements and downgrades.

## Release boundary

Version 1.0.2 changes the bundled logo, public header, Search disclosure and Home hero layout while preserving owner data and the guarded recovery workflow. Commerce remains intentionally disabled unless the owner activates it after genuine provider acceptance.

Items not directly re-observed—such as a particular recent cron execution, a fresh production rollback drill, or host-specific performance measurements—are operational follow-ups and are not claimed as passed.

See `PROJECT_STATUS.md`, `SESSION_HANDOFF.md`, `docs/ROADMAP.md`, `docs/LAUNCH_CHECKLIST.md` and `docs/acceptance/CONSOLIDATED-FINAL-PASS.md`.
