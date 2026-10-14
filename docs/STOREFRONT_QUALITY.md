# Product-gallery quality — 0.9.0-dev.12

Application checkpoint **eefab5ab98b27397a5be48ba311bf9b9ffe869fb**. The dedicated populated-storefront workflow **38632644731** passed on Chromium, Firefox and WebKit. See TEST_RESULTS.md for the complete cumulative-run evidence and acceptance boundaries.

## Corrected behavior

The previous thumbnail script changed only the main image's fallback src. When a responsive srcset remained, the browser could continue displaying the original photograph. The new selection imports a complete server-rendered image, including its responsive candidates, intrinsic dimensions and alternative text, and waits for decoding before replacement.

The current photograph remains visible on a failed request or bounded load timeout. A per-gallery request identity prevents an older delayed response from replacing a newer selection. Changing photos preserves focus, entered quantity, existing variant controls and the audio node. Enhanced navigation updates the current photo URL without adding a new Back-history entry, and canonical product metadata remains the product URL.

Thumbnail links also work without JavaScript. Their photo parameter is a position in the product's approved image array, never an arbitrary media identifier. Invalid or out-of-range input selects the first approved photograph. Native full-page navigation does not promise preservation of unsaved fields or continuous audio across that page load.

Portrait images previously expanded the intended square main gallery and overflowed small thumbnail cells. Bounded grid tracks and image minimum sizes now keep the intended containers stable while object-fit contains the complete image. Nothing is hidden or cropped to disguise the defect. Small thumbnails use a dedicated size hint instead of requesting display sizes appropriate to a large content image. Original media files and their authorization are unchanged.

## Owner use

Manage approved product photographs through the existing merchandise editor. Selecting a public thumbnail changes only the viewed photograph, not product variants, stock, prices or a customer's bag. The selected thumbnail is marked separately from keyboard focus; a polite status line reports loading, selection or failure. Unpublishing the product still withdraws otherwise-private originals and resized copies.

Low-resolution source photos are not transformed into production-resolution masters by this change. Use approved high-quality source images and meaningful alternative text. The existing manual installer, database configuration, extension notices and maintenance workflows are unchanged.

## Dedicated verification

Workflow38632644731 uses PHP8.4, a disposable MariaDB11.4 database and three Playwright engines. Actual owner forms create synthetic products, variants and three distinct landscape/portrait/small images. The public journeys run at1440px,390px and320px, plus390px with page JavaScript disabled. Each engine completed212 logged browser/integration assertions and the31 gallery unit assertions; repeated engine runs are not counted as new distinct unit tests.

Tests inspect actual currentSrc, responsive IDs, source dimensions, alternative text, visible geometry and selected/focused states. They exercise user-initiated real audio and supported navigation from a release through the catalog to a product, unchanged form/history state, a failed photo and retry, a deliberately delayed earlier request, native no-JavaScript selection, unchanged catalog/account/stock snapshots and withdrawal of unpublished media. Fifteen actual screenshots were inspected across the three engines, including portrait fallback and failed-load views.

The runner supplies a test-only PulseAudio output sink so Firefox has a real audio backend. Application playback time is observed, not fabricated. This does not verify audible speakers, physical phones or every native Safari/OS combination. Synthetic fixtures are never seeded into the deployment ZIP, and payment networking is disabled in the test environment.

## Test corrections recorded

Initial testing incorrectly required a warm thumbnail to reload its smallest candidate; browsers can reuse a larger decoded image. The test now measures an unselected cold thumbnail and still verifies the actual selected source. A delayed-request test now uses a fresh context rather than assuming a decoded image must refetch. With page JavaScript disabled, native image readiness is observed synchronously with a failure bound, not by waiting indefinitely for a document Promise. During an intentionally held network response, DOM readiness replaces a contradictory wait for network-idle; the test still requires a real held response and verifies that releasing it cannot change the latest selection.

Visual inspection, not a waived assertion, found the portrait-cell defects and led to new geometry checks. An unrelated address-card margin omitted during an earlier stylesheet copy was restored to its canonical baseline. No application CSP, publication permission, image ownership or cumulative-upgrade check was weakened.

## Remaining quality and launch review

This is a focused gallery pass, not a declaration that every populated page or browser behavior is finished. Continue broader catalog/content/checkout/accessibility/performance review, approved master artwork/content and actual A2/mailbox/genuine merchant sandbox acceptance. Physical-device review and final owner approval still precede v1.0.

Primary references consulted2026-10-14: https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/srcset ; https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/currentSrc ; https://playwright.dev/python/docs/browsers .
