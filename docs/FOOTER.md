# Footer and copyright — 1.1.0-rc.8

## Public layout

The footer surface extends to both viewport edges while its contents stay aligned with the site. Its background is rgba(2,7,12,.75): 25% transparent, with fully opaque text, links and logo. The approved 3D logo shares the header's continuous CSS motion and reduced-motion fallback.

Both footer tagline occurrences are removed: the configured tagline below the logo and the separate Music. People. Purpose. line. The site tagline remains available elsewhere.

A three-column desktop grid contains the 168 × 56px logo, wrapping navigation, and copyright/credit. Vertical padding is 10px. There is no forced minimum or maximum footer height. At 820px and below, logo and copyright share the top row and navigation uses a full-width second row. Phone links retain 44px targets. Long copyright and optional links wrap; no links are removed to manufacture a shorter footer.

The existing Search, Listen, Shows, Press kit, Customer account and Booking links remain, together with Your bag when commerce is enabled and any configured policy links.

## Owner copyright field

Open **Settings & access → Website settings** and edit **Footer copyright**.

The optional field accepts the complete line, including a copyright symbol and year if desired. It supports one line of plain text up to 240 UTF-8 bytes. The form explains this limit; the server enforces it and rejects non-string fields, malformed UTF-8, control characters and longer input before any write.

Blank uses the current UTC year and configured website name, for example: © 2026 Spiritual Gorillaz. Leaving the field blank lets future years and website-name changes follow automatically. A manually entered year is retained until edited.

The setting publishes when the existing Website settings form is saved. Output is escaped in the admin input and public footer. The row is created only on an explicit valid save; existing installations need no migration, initialization write or reinstall. Older submissions that omit the new field preserve a saved copyright value.

Existing active-owner/session and CSRF checks protect the form. The setting is saved with prepared SQL in the existing transaction and audit flow.

## Fixed developer credit

Immediately below copyright, the template renders:

Website designed & developed by [Cajun Web Pro LLC](https://cajunwebpro.com/)

Only the company name is linked, using the exact HTTPS destination. There is no setting, form field, hide switch or submitted value that can change this line through administration. Unrecognized credit-related POST fields are ignored. As with any source-code text, a server owner with permission to edit the template can alter it; this is a fixed application-admin credit, not an external lock on server files.

## Verification and updates

Focused unit, installed SQL/HTTP and browser checks cover default/blank behavior, persistence, escaping, byte boundaries, invalid input, CSRF, staff-role denial, ignored credit edits, responsive geometry and all optional links. Maximum-length text and optional-content screenshots use explicitly labeled disposable fixtures.

Current observed counts and capture provenance belong in TEST_RESULTS.md. The normal guarded same-schema Browser upgrades path applies from rc.1 through rc.7 when its integrity checks pass. Preserve the existing database, owner, configuration/encryption identity, private state and uploads.
