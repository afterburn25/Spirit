# Spiritual Gorillaz — 1.0.12

## 1.0.12 centered, shortened hero containers

Version 1.0.12 corrects the misunderstanding in the 1.0.9–1.0.11 visual experiments. The sticky header remains full width, but Home and the interior page heroes no longer run edge to edge. Each hero is now centered at the same 1320-pixel maximum width as the sections below it, with a shorter editorial height. Copy occupies a dedicated left column and production artwork occupies a dedicated right column, so the two no longer overlap or resemble stacked boxes. The complete right-side focal subject remains visible, Search remains available in the sticky header, and mobile keeps copy first with the artwork beneath it.

Spiritual Gorillaz is a cumulative PHP/MySQL band website for shared hosting with a cinematic black/deep-navy, gold and blue crowned-gorilla design.

## 1.0.11 hero copy-safe zone

Version 1.0.11 corrects the remaining overlap between hero copy and visual focal areas. Desktop headings and supporting copy now use a narrower left-side safe zone with one continuous dark-to-transparent fade, while the centered native-resolution artwork and full-width atmospheric canvas remain intact. No visible text panel or overlapping box is introduced.

## 1.0.10 centered native-art hero stage

Version 1.0.10 corrects the visual problem introduced by stretching the source artwork to the viewport. The hero canvas still spans edge to edge, but each 1672×941 production master now remains centered, uncropped and no larger than its native width. Matching dark navy atmosphere extends beyond the image on wider displays. Headings and controls are anchored inside the artwork's left negative space, so the text no longer appears outside the image or like a separate box overlapping it. The same centered-stage treatment applies to Home, Music, Shows, Band, Media, Press, Merch and the Booking presentation. The sticky centered navigation, visible Search icon and dropdown Search interaction remain intact.

## 1.0.9 full-span hero and Search correction

Version 1.0.9 corrected the desktop Music/full-width geometry issue and guaranteed that the Search control is visible in the real sticky header. It is retained as part of the cumulative build, but its viewport-stretched image sizing is superseded by the contained 1.0.12 layout.

## 1.0.8 full-width atmospheric hero system

Version 1.0.8 introduced the approved cinematic treatment across Home, Music, Shows, Band, Media, Press, Booking and Merch. Version 1.0.12 keeps that visual identity while returning the hero geometry to the normal page container.

## 1.0.7 complete interior-hero framing correction

Version 1.0.7 corrected interior-page hero framing and established that the complete artwork must remain visible rather than destructively cropped.

## 1.0.6 navigation and hero regression correction

Version 1.0.6 restored the larger centered sticky navigation, dropdown Search and dimensional Home headline.

## 1.0.5 animated identity update

Version 1.0.5 added the owner-selected Concept 2 transparent animated identity. The public header uses the APNG logo, the footer and reduced-motion preference use its matching static PNG, and owner-published Media-library branding continues to override the bundled mark without cropping or stretching.

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

## Launch behavior

Missing optional real-world content is never fabricated. Shows may be empty, Part 3 may remain unpublished, portraits may remain unset and the storefront may stay closed. When commerce is closed, Merch/Bag and merchandise search affordances are not advertised globally. Merchant sandbox acceptance is deferred until commerce activation.

The owner may replace bundled hero artwork or branding through the existing administration workflows. Published CMS artwork continues to inherit the same contained, non-destructive framing rules.

## Deployment contract

A2/cPanel manual database setup only. No hosting token, no 20i integration, no setup code and no automatic database/user creation.

Build the cumulative package with:

`python3 tools/package.py --output ./dist`

Validate the source/package contract with:

`python3 tools/release_gate.py`

The package excludes tests, development tools, runtime/private state, credentials, sessions, backups and recovery receipts. Existing installations upgrade through backups/guarded updates; never reinstall or replace the existing database/configuration to apply an update.

## 1.0 release boundary

The owner explicitly instructed the project to proceed to final 1.0.0. The RC10 software matrix was fully green before promotion. Commerce remains intentionally disabled, so genuine Authorize.net sandbox acceptance is deferred until commerce activation.

Items that were not directly re-observed from this chat—such as a particular recent cron execution, a fresh production rollback drill, or host-specific performance measurements—are not claimed as passed. They are operational follow-ups, not unresolved software defects in this visual correction.

See `PROJECT_STATUS.md`, `SESSION_HANDOFF.md`, `docs/ROADMAP.md`, `docs/LAUNCH_CHECKLIST.md` and `docs/acceptance/CONSOLIDATED-FINAL-PASS.md`.
