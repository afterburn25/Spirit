# Project status — 0.9.0-dev.7

Application **7c5582075b4b47c01e721c2c5e30d4f4a593acbb** passed full verification **38525702729** and focused maintenance **38525702713**. The documentation-only descendant changes no runtime, assets, schema or tests. Inspect live main before resuming.

## Newly implemented and verified

Responsive image delivery provides fixed 320/640/960/1440-pixel candidates, proportional resizing without upscaling, retained transparency and original downloads. Generated copies are private and regenerable. Authorization precedes every cache/304 response; unpublishing withdraws derivatives. Cache corruption, busy generation, restricted permissions and small memory budgets are covered by safe fallback/recovery tests. Retention preserves original media and the generation lock.

Keyboard refinements cover named gallery dialogs, opener focus, hidden full-image cleanup, playback queue boundaries, pressed track state, readable seek time, focus return and clearance above the fixed player. Long titles no longer overlap the controls, including the two-row player at 320px. Server validation errors receive focus without recording field values; ordinary Escape in admin fields no longer steals focus to a closed menu. Intrinsic image ratios, phone form text and forced-color focus styles are preserved.

The owner roadmap now reflects the already-implemented content history, payments and maintenance modules instead of stale planned labels. Genuine merchant, host and final acceptance remain separate.

## Evidence

44 new pure media-quality checks; 442 cumulative pure assertions and 11 packaging tests; real root/subfolder SQL/HTTP image/cache/privacy/permission tests on both database environments; all twelve Chromium workflows and actual Apache HTTPS. The new workflow exercises 1440px, 390px and 320px. Nine new renders were inspected from the verified 99-image artifact at application 238fa431; the subsequent application change is only the owner roadmap text. Canonical source was compared with the local implementation. See MEDIA_QUALITY_CHECKPOINT.md and TEST_RESULTS.md.

The generated 1800×1200 test image measured 412,565 bytes after upload processing and 21,306 bytes at 640px, a 94.8% reduction for that fixture. This is not a measured improvement for the band's actual photographs or a field performance score.

## Preserved cumulative scope

Browser installer/private runtime/self-deletion and permanent locking; approved responsive site, CMS/private history, published metadata/search/sitemap/redirects, music/player/shows/external tickets; booking/email/newsletter; catalog/inventory/cart/customers; token-only checkout/orders/fulfillment; staff permissions; encrypted backups/guarded restore; same-schema browser ZIP upgrades/independent recovery; conservative retention. Migrations 001–006 and pinned access/recovery files are unchanged.

## Remaining before v1.0

Approved production-resolution artwork and real band/store content; broader populated-site accessibility/performance/security acceptance; actual shared-host, merchant sandbox and receiving-mailbox verification; final owner acceptance and cutover. Follow LAUNCH_CHECKLIST.md. Same-schema upgrades and same-instance guarded restoration retain their documented limits. No genuine merchant transaction, inbox delivery, universal accessibility conformance or accepted v1.0 is implied by synthetic tests.
