# Project status — 0.9.0-dev.12

**Verified application:** eefab5ab98b27397a5be48ba311bf9b9ffe869fb, tree7efabaa53a80676f04c841706f436c286fa7ba35. On2026-10-14 the full workflow38632645453, focused maintenance38632645480 and three-engine storefront workflow38632644731 all completed successfully. This final documentation-only descendant changes no runtime, assets, migrations, workflows or tests. Read live main before further changes.

## Completed in this pass

- Product thumbnails now select a complete responsive image with its own source candidates, intrinsic dimensions and alternative text, instead of changing only a fallback URL and leaving the old photograph displayed.
- Slow or failed images leave the previous photo visible. A delayed earlier response cannot replace the most recent choice. Loading/selection/failure are announced without losing keyboard focus or entered quantity.
- Portrait main photos remain inside the intended square gallery, and portrait thumbnails no longer overflow their cells or overlap the status text. Complete images are contained, not cropped to disguise the defect.
- Native approved-position photo links work when JavaScript is disabled. Enhanced selection preserves the existing audio node and product canonical metadata and does not add a Back-history entry per thumbnail.
- A new populated storefront matrix covers Chromium, Firefox and WebKit at1440px,390px and320px, with native fallback, failed loads, delayed responses, form/history/audio preservation and unpublished-media withdrawal.

The previously interrupted dev11 work was already saved on main21e9bd6f and its exact full run38591939605 passed. Its consolidated Launch checks dashboard and actionable413 upload feedback are included in this cumulative build, alongside all earlier website/admin/store/maintenance features.

## Verification

575 distinct pure assertions (544 retained +31 gallery) and12 packaging tests passed. Local syntax checking covered213 PHP/inc files, JavaScript and Python. The exact full Actions workflow passed both PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, all13 retained Chromium workflows and actual Apache HTTPS access-rule tests. The dedicated gallery run completed212 browser/integration assertions per engine, and fifteen current renders were inspected. See TEST_RESULTS.md for exact jobs, artifacts, test corrections and limits.

Original configuration/database/media are not modified by photo selection. Six migrations, five pinned recovery/access files and the manual installer bytes remain unchanged. Earlier dev9 and the distinct delivered dev10-preview deployment paths remain compatible. Final package hashes and canonical-source comparisons are recorded with the delivery.

## Fixed installer and hosting scope

A2 Hosting with cPanel only. Manual existing database, no20i, no setup code or PRIVATE-SETUP receipt, no cPanel API token, no automatic database/user creation. One-page checks/database/owner/correction/completion, private writes, independent locks and verified self-deletion remain. PHP cURL/ZIP notices remain separate and truthful; the application has not enabled either on the user's server.

## Still before v1.0

Approved production-resolution artwork and genuine band/store content, broader populated-site accessibility/performance/security and catalog/card/native-control review, actual protected A2 staging, receiving-mailbox and genuine merchant sandbox acceptance, recovery/operating-scope review and final owner approval remain. Linux browser-engine tests are not physical-device/native-Safari or audible-speaker certification. No live deployment, real charge, mailbox delivery or hosting change was performed. This is a tested development checkpoint, not accepted v1.0.
