# Verification — 1.1.0-rc.4 favicon

## Scope and baseline

Baseline: delivered rc.3 source `c3584d348067d035ec14a739c9783b5880b95321`. The only runtime markup change adds a shared favicon link set to the two existing headers. New static icon assets replace the old crown-and-SG identity. There are no database, analytics, navigation-controller, payment, installer or recovery behavior changes.

The exact rc.3 final results are archived in `history/VERIFICATION-1.1.0-rc.3.json`. They do not count as new rc.4 test results.

## Observed current checks

| Check | Result |
|---|---|
|PHP syntax|Passed for `views/favicon.php`, `views/header.php` and `views/public/header.php`.|
|Shared link rendering|All8 links passed at both `/` and `/band`, with matching dimensions and `?v=1.1.0-rc.4`.|
|Static HTTP assets|All16 actual requests through a temporary PHP server returned200, appropriate PNG/ICO MIME types and bytes matching the source files.|
|PNG/ICO formats|Every PNG has its declared square dimensions and true alpha; ICO contains16/32/48/64px frames.|
|Apparent size|Visible crown-to-chin silhouette fits98% of the square;16px and 32px raster exports span their full vertical pixel extent after antialiasing, with transparent corners.|
|Visual inspection|Actual Chromium 143.0.7499.4 compositions at 16/32/48/64px on light and dark backgrounds passed; no rectangular matte or colored edge fringe.|
|Source preservation|Transparent1254px master hash remains `3f3232cd3c314b3f227c1c598c8bf75f01c6b04b779cb6ece1bc7f51334f66bc`.|
|Packaging/release gate|**15 packaging tests passed.** The software release gate passed, including two byte-identical cumulative builds and independent manifest checks.|
|Source continuity|All 8 migrations, all 5 pinned recovery/access files, public collection and public bundles remain byte-identical to delivered rc.3 source. Final ZIP-to-ZIP continuity belongs in the delivery receipt.|

Detailed icon and HTTP evidence is retained in `design/favicon/` in development source. The HTTP check is a focused partial/static-file fixture; it is not a fresh installed application, database regression run or real browser-chrome capture. The comparison image labels this distinction.

## Publication and acceptance

Published commit, immutable Full ZIP hash and observed Actions state belong in the final companion delivery receipt. Do not label a pending workflow as passed. This small asset/header change does not require repeating unrelated analytics or merchant scenarios locally.

No A2 deployment, physical-device acceptance, real mailbox delivery or merchant transaction was performed. Owner review and host acceptance remain separate.
