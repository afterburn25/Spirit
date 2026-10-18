# Spiritual Gorillaz — 1.0.7

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, gold and blue crowned-gorilla design.

## 1.0.7 complete interior-hero framing correction

Version 1.0.7 corrects the remaining interior-page hero framing problem found during review of 1.0.6. Music, Shows, Band and Merch now display the complete native artwork at a deliberately reduced scale with breathing room instead of presenting the subject as zoomed or clipped. Desktop heroes retain the approved side-by-side editorial composition; mobile stacks the full uncropped 16:9 artwork below the copy. Booking's 4:3 equipment scene is also preserved without cropping.

## 1.0.6 navigation and hero regression correction

Version 1.0.6 corrects presentation regressions found in 1.0.5: the desktop navigation is restored to a larger centered layout, the complete navigation strip remains sticky while scrolling, Search opens as an accessible dropdown, interior hero artwork no longer uses destructive full-bleed zooming, and the Home headline receives a deeper metallic dimensional treatment.

## 1.0.5 animated identity update

Version 1.0.5 keeps the verified final application foundation and adds the owner-selected Concept 2 transparent animated identity. The public header uses the APNG logo, the footer and reduced-motion preference use its matching static PNG, and owner-published Media-library branding continues to override the bundled mark without cropping or stretching.

Implemented:
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

Final launch hardening includes approved copy fallbacks, the confirmed Chad Lindsey / Ron Green / Charlie Savell lineup fallback, scalable bundled logo, contained fallback hero presentation, closed-store navigation/account/search behavior, dormant-commerce diagnostics, and 360 px/390 px cross-browser coverage.

## Launch behavior

Missing optional real-world content is never fabricated. Shows may be empty, Part 3 may remain unpublished, portraits may remain unset and the storefront may stay closed. When commerce is closed, Merch/Bag and merchandise search affordances are not advertised globally. Merchant sandbox acceptance is deferred until commerce activation.

The owner may replace the bundled logo/hero and publish approved content through the existing administration workflows without a software release.

## Deployment contract

A2/cPanel manual database setup only. No hosting token, no 20i integration, no setup code and no automatic database/user creation.

Build the cumulative package with:

`python3 tools/package.py --output ./dist`

Validate the source/package contract with:

`python3 tools/release_gate.py`

The package excludes tests, development tools, runtime/private state, credentials, sessions, backups and recovery receipts. Existing installations upgrade through backups/guarded updates; never reinstall or replace the existing database/configuration to apply an update.

## 1.0 release boundary

The owner explicitly instructed the project to proceed to final 1.0.0. The RC10 software matrix was fully green before promotion. Commerce remains intentionally disabled, so genuine Authorize.net sandbox acceptance is deferred until that feature is later activated.

Items that were not directly re-observed from this chat—such as a particular recent cron execution, a fresh production rollback drill, or host-specific performance measurements—are not claimed as passed. They are operational follow-ups, not unresolved software defects in this final release.

See `PROJECT_STATUS.md`, `SESSION_HANDOFF.md`, `docs/ROADMAP.md`, `docs/LAUNCH_CHECKLIST.md` and `docs/acceptance/CONSOLIDATED-FINAL-PASS.md`.
