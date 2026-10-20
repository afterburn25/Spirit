# Verification — 1.1.0-rc.8 visible motion and compact footer

## Scope and baseline

Baseline is published rc.7 commit 8eb75d7e8073a72bdc4b1fd8ca432408711ee3ea, tree 0f3531236f3f46da56198472244645c095c8012d. Live main was inspected before writes at 6ef03a24ea87fdbfb0d053f7b54b4c16fce42098 and rechecked before publication. Draft PR 37 remained open, draft and unmerged.

This candidate corrects the active approved-logo source, continuous logo/footer motion, invisible smoke, compact/translucent footer, editable copyright and fixed developer credit. Ten runtime files change: VERSION, six PHP files and three CSS files. Original artwork, JavaScript, migrations, authentication/installer scaffolding and pinned recovery/access files remain unchanged. Historical rc.7 results and its delivered verification receipt are preserved under history/.

## Current installed checks

The current browser, copyright and branding-source passes use the same frozen candidate ZIP:

SHA-256: 3767c829562bb2877cda272a82721e4e46901adcc8f5376748235e71ec74743d

They ran on disposable root and /band installations. No production site was accessed.

| Current check | Observed result |
|---|---|
| Installed browser footer, identity, smoke, controls and admin workflow | **1,131 assertions passed**, Chromium 143.0.7499.4, with no uncaught page errors. |
| Footer copyright SQL/HTTP | **88 assertions passed**, including persistence, automatic fallback, escaped text, ASCII/UTF-8 byte boundaries, malformed values, CSRF, anonymous/staff-role denial and ignored developer-credit changes. |
| Branding source SQL/HTTP | **142 assertions passed**, including retained legacy IDs, read-only defaults, explicit bundled/custom actions, private drafts, media authorization, stale forms, owner/CSRF boundaries and supported backup restoration. |
| Branding PHP unit checks | **48 passed**. |
| Copyright PHP unit checks | **19 passed**. |
| Package unit checks | **15 passed**. |
| Changed PHP/Python syntax and whitespace | Passed. |
| Public bundle reproduction | Passed at **129,735 /130,000 bytes**. JavaScript is unchanged. |
| Local software release gate | Passed, including deterministic repeated packaging and private/development payload exclusions. |
| Source continuity | **347 protected source files byte-identical** to rc.7. All 339 source-backed runtime files were compared; 329 remain identical and exactly 10 authorized paths change. |

Installed checks total **1,361**, separate from the 67 focused PHP units and 15 packaging units. Database/browser tests cover application behavior; they do not certify production hosting or external providers. The GitHub workflow definition now includes the footer units/HTTP suite in its existing branding matrix. Exact published-head Actions outcomes belong in the delivery receipt; a queued or running workflow is not a pass.

## Actual page and motion evidence

The capture manifest contains **20 full-viewport screenshots**, of which three maximum-content footer views are explicitly synthetic. The other 17 contain no seeded band content; administration views use a disposable test owner. There are also **24 native header-logo crops**, clearly identified as details from the actual page rather than full-screen screenshots.

Two installed-browser MP4s are **10.000 seconds**, H.264, 25fps, at native 1920×1080 desktop and 390×844 phone viewports. They start from ordinary page entry with no animation.currentTime seeking, no playback acceleration and no CSS alteration. Only leading load/setup time is trimmed. The native CSS -4-second smoke delay is part of the shipped animation, not a recording-time seek.

The image source is the unchanged 2172×724 PNG. Actual gold-logo pixels change substantially during ordinary elapsed time, with maximum 8,707 desktop and 4,742 phone changed gold pixels in the recorded crop comparisons. The 3.8-second light sweep and four-second alternating depth motion remain inside the rendered logo. The natural smoke transform progresses during each recording while the underlying stage stays fixed.

Root visual review inspected actual desktop/phone Home and footer frames, owner settings, maximum-content layout and unaltered video frames at 0, 5 and 9 seconds. Smoke travel is visibly stronger in exposed stage areas, especially desktop gutters; phone content exposes narrower background strips. The logo remains legible and the compact footer fits its content. These observations are visual review, not a performance benchmark.

The initial 390px settings capture caught the existing sidebar's 180ms transition while resizing from desktop. Only that screenshot was recaptured after the native drawer is fully closed, from the same frozen runtime. Its original is retained as superseded review evidence outside the final screenshot set. The capture helper now waits for the settled state; this does not change application code or relabel a whole repeated suite. Six supplementary recapture assertions passed and are recorded separately from the 1,361 main/HTTP total.

## Responsive footer and behavior

The footer was checked at 1920, 1440, 1280, 1100, 820, 390, 360 and 320px on both root and subdirectory installations. Default height is 77px at the four desktop widths, 127px at 820px, 167px at 390/360px and 183.5px at 320px. These are observed content-driven results, not hardcoded height limits.

All 11 optional navigation/policy links and maximum 240-byte copyright were exercised, including unbroken text. Original links remain present, text stays inside the viewport, and the footer grows only with content and spacing. Its full-width backplate has alpha .75 while text/links retain opacity 1. The fixed HTTPS developer link sits below escaped copyright.

Admin tests save, reload and render copyright, restore automatic fallback, preserve omitted old fields and ignore forged credit fields. The new branding default preserves old records without GET writes; a deliberate custom Publish selects custom, and Use animated 3D logo preserves both IDs. Search, menu/client navigation and the persistent audio node remain functional.

Reduced motion stops logo motion and hides smoke; print and forced colors omit moving smoke. Administration/login/setup have the approved identity but no public stage/smoke layer.

## Integrity, provenance and limits

Current source reports are in design/footer-motion/: source-continuity.json, footer-refresh-verification.json, footer-settings-verification.json, branding-source-verification.json and evidence-integrity.json. Screenshot, crop and video hashes must match the delivery files. All 345 recorded runtime/code/asset hashes must match the final Full ZIP even if README, changelog, documentation and the package manifest are completed after capture.

The final delivery receipt records cumulative retention of all 421 prior payload paths, original image/generated-hero equality, all eight migrations, five pinned recovery/access files, final ZIP/source tree, independent GitHub package checksum and actual workflow states.

Desktop/phone viewport checks do not establish physical-device or Safari acceptance. No A2 deployment, real mailbox delivery, merchant transaction or merge is claimed. Owner review and applicable host/provider acceptance remain separate.
