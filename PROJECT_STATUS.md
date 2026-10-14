# Project status — 0.9.0-dev.7 implementation

Current work on **work/media-accessibility**, based on verified .9dev6 application af43d153 and documentation/main4f6005f7. Full38518823266 and focused38518823277 passed for the baseline. Current changed-code verification is pending.

## New implementation

Fixed 320/640/960/1440 image candidates, proportional downsampling without upscaling, alpha preservation, private checksum-keyed regenerable cache, original downloads and safe original fallback when GD/memory/cache writes are unavailable. Publication/role checks precede every cache/304 response. Generation uses a nonblocking allocation lock; damaged generated files are rebuilt. Matching If-Range is representation-specific. Retention recognizes only the new fixed derivative/temporary namespace and preserves original media and the generation lock.

Keyboard/media refinements: named gallery dialog, opener focus restoration, release of hidden full-size image sources, live player queue controls/pressed state/readable seek time, focus return after closing, dynamic player clearance, page-loading state and focused server validation feedback. Escape in ordinary admin fields no longer steals focus to a closed menu. Phone form typography and forced-color focus/borders are improved without another design.

41 new pure checks plus root/subfolder SQL/HTTP alpha/cache/authorization/fallback/retention tests and a twelfth Chromium workflow at1440/390/320px. It measures real fixture image/first-party asset bytes and exercises keyboard navigation, validation feedback and player obstruction. Full current results and render inspection remain pending. Migrations001–006 and pinned access/recovery layer are unchanged.

## Preserved and remaining

All installer/public/CMS/history/discovery/music/show/booking/mail/newsletter/catalog/customer/payment/staff/backup/update/retention features remain cumulative. Remaining: broader accessibility/performance/security acceptance, real master artwork/content, actual shared-host/merchant/mailbox verification and owner approval. Synthetic fixtures and transfer budgets are not field Core Web Vitals, complete WCAG conformance or a real merchant transaction. No final v1.0 release is claimed.
