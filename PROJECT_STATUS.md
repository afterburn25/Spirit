# Project status — 0.9.0-dev.6 implementation

New work on **work/site-quality**; full current verification pending. Baseline exact-main **11a50387514f0f744363a660c0ff86f3d79024ff** passed **38514355067** with both databases, ten Chromium suites and actual Apache HTTPS.

## Implemented in the current checkpoint

Published-only optional SEO/share fields, approved sharing images, canonical configured URLs and Unicode-safe descriptions; owner global indexing control; real public search with filters/20-row pages; bounded XML sitemap index/parts using installation-root URLs; GET/HEAD/ETag handling; unique former-published-slug redirects without draft leaks; explicitly confirmed public-event JSON-LD using supplied venue/location/date/status only. Internal navigation updates all metadata and preserves actual audio playback. Public images carry actual dimensions and phone menu Escape restores focus.

No SQL migration, pinned access/recovery file or external server configuration is changed. Search exclusion/noindex is not authentication. Old unrelated website URL migration is not automatic. Existing indexing defaults are preserved; staging owners should explicitly disable indexing.

## Verification

25 new discovery units and all existing pure/package checks passed locally. Root/subfolder SQL/HTTP privacy, redirect, sitemap/category/pagination tests and an eleventh desktop/mobile Chromium workflow are added; exact current CI and render inspection remain pending. Read TEST_RESULTS.md.

## Cumulative and remaining scope

All original installer, public/CMS/history/music/shows, booking/communication, catalog/customer/payment/order/staff, encrypted backup/restore, same-schema ZIP recovery and retention features are preserved. Still required: broader image/accessibility/performance work, master artwork and genuine approved content, actual merchant/email/host acceptance and final owner review. No real provider payment or accepted v1.0 exists merely because application tests pass.
