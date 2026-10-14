# Project status — 0.9.0-dev.5

**Verified cumulative application:** 9546b7c298b462d7457c0e911a71bf5fedb5bc81. Full run **38513423793** and focused maintenance **38513423736** passed. The following documentation-only descendant changes no runtime, assets, schema or tests. Read the live main ref before resuming.

## Newly implemented and tested

Literal title/URL search, visibility filters, sorting and 25-row pagination; private per-item revision lists and escaped field comparison; transactional restore as a **new private draft**, preserving current public snapshots, later revisions and media visibility; active role/session checks; typed input; missing/damaged historical-file refusal; historical selected files/releases retained beyond 1,000 newer items. New drafts record sort order, so private edits cannot prematurely reorder the live snapshot. Legacy revisions retain current order. No migration was changed.

39 new pure checks, root/subfolder SQL/HTTP large-library/restore/permission/replay/media tests, and a tenth desktop/mobile Chromium workflow passed. Both database jobs and actual Apache HTTPS passed. Six new screenshots were inspected from the 84-render artifact. See CONTENT_HISTORY_CHECKPOINT.md and TEST_RESULTS.md.

## Preserved cumulative scope

Browser installer/private runtime/installer-folder deletion and permanent lock; selected public website, music/player, shows/external ticket links; booking/email/newsletter; catalog/inventory/cart/customer accounts; token-only checkout/orders/fulfillment; staff permissions; encrypted backups and guarded restoration; owner-operated same-schema ZIP upgrades, independent recovery and conservative retention.

## Still required

Final metadata, sitemap/public search/redirect handling, wider media/accessibility/performance quality, high-resolution production artwork and approved real content, actual hosting and payment/mail-provider acceptance. Same-schema upgrade and same-instance backup limitations remain explicit. No genuine merchant transaction or real inbox result is claimed by synthetic tests. No final v1.0 acceptance or release has occurred.
