# 1.0.0-rc.1 — candidate preparation

This is the code candidate for the user-requested 1.0 milestone, not permission to replace the live band website or enable production payments. Final 1.0 is not released. The branch's exact automated results are pending; inspect Actions before promotion.

## Implemented in the candidate

- Portrait merchandise cards keep a square image area without cropping or expanding their flex/grid containers. Product prices no longer inherit the small general paragraph style. Phone catalog/show filters and bag quantity controls use 16px entry text.
- Image-only release and product links retain useful escaped names when artwork is absent.
- Without page JavaScript, mobile navigation is visible, photo links open the same authorized original-media endpoint, and published tracks provide native non-autoplay/non-preloading audio controls. With JavaScript the existing menu, photo dialog, persistent audio and product gallery remain; modified photo clicks retain native navigation. Native full-page navigation cannot preserve uninterrupted audio or unsaved form fields.
- Owner-published Pages named privacy, terms, shipping or returns appear in footer policy navigation. Private drafts are not advertised. Unpublishing withdraws the link and endpoint; supported persistent navigation also refreshes the footer. No legal copy or claim of compliance is invented, and no database migration is needed.
- The new release review exercises public pages, structural control names, mobile field sizes, portrait cards, native navigation/media/booking, and checkout review/cancellation across Chromium/Firefox/WebKit on domain-root and subfolder installations. It never sends a charge. Existing cumulative SQL/HTTP, installer, provider-boundary, maintenance and recovery suites remain required.

## Fixed deployment decisions

A2 Hosting/cPanel, one manual installation page, no setup code, no hosting token, no automatic database/user creation and no20i. Existing sites upgrade rather than reinstall. Six migrations, five pinned recovery/access files and installer bytes remain unchanged. Keep original private configuration/encryption secret, database, media and separate recovery keys. Missing PHP ZIP/cURL still requires the host's actual extension configuration.

## What closes final 1.0

Candidate testing supplies a package to complete staging; it cannot fabricate external acceptance. Record actual protected A2 operation, receiving-mailbox delivery, genuine Authorize.net sandbox workflow and recovery/operating-scope acceptance. Approve production-resolution artwork, actual band/store content and policies, review populated pages on real devices, and obtain final owner approval. The concept-derived image crops remain reference-resolution artwork, not replacement masters. See LAUNCH_CHECKLIST.md, DESIGN_LOCK.md and TEST_RESULTS.md.

Structural browser checks and text-size stress tests are not complete WCAG certification, physical-device tests, a penetration test or field performance measurements. Those boundaries must remain explicit in the final release record.

Primary guidance consulted: https://www.w3.org/WAI/tutorials/menus/structure/ ; https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Values/minmax ; https://playwright.dev/docs/accessibility-testing .
