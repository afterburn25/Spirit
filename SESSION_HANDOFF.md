# Resume here — product-gallery quality, dev12

Repository **afterburn25/Spirit**, main authoritative. Current version **0.9.0-dev.12**. Read live refs, AGENTS.md, PROJECT_STATUS.md, docs/TEST_RESULTS.md and docs/STOREFRONT_QUALITY.md before editing. Preserve concurrent changes; never force-push.

## Current application checkpoint

Application **eefab5ab98b27397a5be48ba311bf9b9ffe869fb**, tree **7efabaa53a80676f04c841706f436c286fa7ba35**. Required full run38632645453, focused38632645480 and three-engine storefront38632644731. Consult TEST_RESULTS.md for the recorded conclusions; a source artifact is not application verification. A later documentation-only descendant must have identical runtime/assets/schema/workflows/tests to this application.

The earlier interrupted main write succeeded: main21e9bd6f was the verified dev11 baseline, with exact-main full38591939605. Continue cumulatively; do not restart from dev9 or the distinct local dev10 preview. Launch checks/413 upload feedback and all previously delivered features remain.

## What changed and must be preserved

app/ProductGallery.php renders approved-array positional links and complete responsive source templates. The photo query selects only a position1–8 in that published product, not an arbitrary media ID. assets/js/commerce.js imports and decodes the complete image, checks the current per-gallery request identity, preserves the previous photograph on failure and prevents stale completion overwrites. It retains keyboard focus, entered quantity, variant controls, canonical metadata and the audio node. Enhanced selection replaces the current history entry rather than filling Back history.

Native target_self links intentionally avoid the generic persistent-navigation handler and work without JavaScript. Native full-page navigation does not promise preservation of unsaved fields or uninterrupted audio. All ordinary product/media publication permissions remain. PublicSite has separate main/thumbnail sizing hints. Grid minimums keep portraits within stable square main and small thumbnail cells without cropping or hiding overflow.

## Verification discipline

575 distinct pure assertions =544 retained +31 gallery checks;12 packaging tests and213 PHP/inc syntax checks. Dedicated storefront tests create actual synthetic catalog/media fixtures and run Chromium/Firefox/WebKit at1440/390/320 plus390 no-JavaScript. Each engine completed212 browser/integration assertions. Full regression retains both SQL engines, all13 Chromium suites and Apache HTTPS. See TEST_RESULTS.md for exact job/artifact identities and whether an additional exact-main rerun has completed.

Do not restore invalid test assumptions. A warm thumbnail may reuse a larger decoded image: measure an unselected cold thumbnail. A cache hit is not a new network request: use a fresh context for delayed-response tests. Observe image readiness synchronously in a page with JavaScript disabled rather than waiting indefinitely for document Promise callbacks. While a response is deliberately held, do not wait for network-idle before releasing it. Tests still require real loaded currentSrc/dimensions, real held responses and unchanged last selection after release.

The CI PulseAudio sink supplies Firefox's media backend only on the disposable runner; never add a host-side audio-service requirement or fake playback state. Linux engine verification is not physical-device/native-Safari or audible-speaker certification. Fifteen storefront screenshots were inspected; no test products belong in a deployment database.

## Fixed user decisions and next work

A2/cPanel manual database only, no20i, no setup code/PRIVATE-SETUP, no hosting token or automatic database/user creation. One-page checks/database/owner/correction/completion, protected private writes, permanent locks and safe self-deletion remain unchanged. Keep six migrations, five pinned access/recovery files, original configuration/encryption secret/data/media and separate backup/update recovery keys. Existing sites upgrade rather than recreate databases or remove locks.

Continue broader populated-site review, including catalog/card photo layouts and native form appearance across browsers, approved high-resolution artwork and genuine band/store content. Complete protected A2/PHP extension/private-root/ACL/cleanup/scheduled-task staging, actual receiving-mailbox and genuine merchant sandbox acceptance when authorized access is available. No test can substitute for those gates or final owner approval of v1.0. Do not silently enable production services.

Before delivering, compare canonical Git source and application bytes, preserve every earlier deployment path, verify manifest hashes and package an immutable Full ZIP with instructions/report. No setup receipt. Container paths from this session must be re-established in future sessions; Git is the source of truth. Local PHP lacks PDO MySQL/GD/cURL/ZIP, so real SQL/HTTP/browser/Apache evidence comes from Actions, not local lint.
