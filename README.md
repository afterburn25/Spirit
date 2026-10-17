# Spiritual Gorillaz — 1.0.1

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, gold and blue crowned-gorilla design.

## 1.0.1 concept rebuild update

Version 1.0.1 is the first supported in-place upgrade after the installed 1.0.0 release. It carries the approved Spiritual Gorillaz concept rebuild into the real application rather than handing an unusable same-version replacement to an existing 1.0.0 site.

The update includes:
- native-resolution Home, Music, Shows, Band, Booking and Merch artwork;
- the approved black/deep-navy, electric-blue stage-light and gold visual direction;
- strict title containment at desktop, tablet and mobile widths;
- real HTML/CSS/PHP navigation, headings, buttons, forms, music controls and cards kept separate from artwork;
- cache-busted bundled CSS, JavaScript, logos and artwork so a completed upgrade cannot continue showing the old browser-cached presentation;
- preserved CMS, content, media, booking, commerce, customer, backup and recovery data.

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

An existing 1.0.0 installation must receive the 1.0.1 cumulative Full ZIP. The upgrade validator intentionally refuses same-version replacements and downgrades.

## Release boundary

Version 1.0.1 changes the installed application code and bundled visual assets while preserving owner data and the guarded recovery workflow. Commerce remains intentionally disabled unless the owner activates it after genuine provider acceptance.

Items not directly re-observed—such as a particular recent cron execution, a fresh production rollback drill, or host-specific performance measurements—are operational follow-ups and are not claimed as passed.

See `PROJECT_STATUS.md`, `SESSION_HANDOFF.md`, `docs/ROADMAP.md`, `docs/LAUNCH_CHECKLIST.md` and `docs/acceptance/CONSOLIDATED-FINAL-PASS.md`.
