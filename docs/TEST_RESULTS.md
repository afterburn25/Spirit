# Verification — 1.0.0-rc.2

## Exact application evidence

Application **2947013dff9942691cf9c27b128f20cc50da7e77**; tree **47c47421c80675db8ad1479ef4a70a58ed2a8679**.
Full workflow **38654637873 — completed / success**.
Focused maintenance **38654637735 — completed / success**.
Three-engine storefront/release/navigation **38654637749 — completed / success**.
PR15 merge reference **71100a594e773c05ffa752beca756f122ecc18cc**. Results completed2026-10-14. This final documentation-only descendant changes no runtime, assets, database migrations, workflow or test bytes. A later exact-main run must be checked separately; source artifacts are not application tests.

| Environment | Job | Result |
|---|---|---|
| PHP8.2 / MariaDB11.4 | 116161383860 | PASS |
| PHP8.4 / MySQL8.4 plus13 retained Chromium workflows | 116161383775 | PASS |
| Actual Apache PHP8.4 HTTPS access rules | 116161383571 | PASS |
| Chromium gallery/release/admin navigation | 116161383476 | PASS |
| Firefox gallery/release/admin navigation | 116161383266 | PASS |
| WebKit gallery/release/admin navigation | 116161383474 | PASS |

## Local and cumulative checks

Local verification independently passed **219 PHP/inc syntax checks**, **689 distinct pure assertions** (591 retained plus98 navigation) as an unprivileged user, all JavaScript syntax checks, Python compilation and **12 packaging tests**. Local PHP lacks PDO MySQL/GD/cURL/ZIP; actual SQL/HTTP/browser/Apache execution comes from Actions, not local lint.

The complete workflow retains both SQL engines, real no-cURL/no-ZIP manual setup, application/private-media/consent/payment/maintenance/rollback boundaries and13 established Chromium suites. Those browser suites cover installer/admin, public/player, communication, commerce/accounts, tokenization/orders, staff, backups, upgrades/recovery, retention, content history, discovery, media/keyboard and diagnostics/upload-limit feedback. No existing suite, browser engine, security assertion or CSP rule was removed to obtain success.

The dedicated browser jobs each logged **251 navigation**, **212 gallery** and **1,491 release-review** PASS lines. These repeated engine executions are not multiplied into the distinct pure-assertion count. The release review still covers native public navigation/music/photos/booking, published policies, root/subfolder forms and checkout review/cancellation without real charges.

## Navigation coverage

Six categories,34 owner destinations and precise active keys are checked at domain root/subfolder across1440/820/390/320-pixel viewports. Tests verify first-visit expansion, independent keyboard disclosures, Collapse all/Expand all, explicit closed active groups, exact current path/query/hash on refresh, independent sidebar scroll, phone drawer open/closed reload, closing on navigation, Escape/focus/Tab boundaries and current section context.

Real multiple-page tests check independent new-tab URLs/drawer state and ensure a passive older tab cannot overwrite a newer explicit choice. Separate owner/content-editor/store-manager accounts test browser preference scope isolation and server-filtered destinations. Direct forbidden payment URLs still receive403. Corrupt, oversized and unauthorized preference fields cannot redirect the page or expose forbidden links. Browser storage property access is deliberately made unavailable to check graceful failure. Native no-JavaScript disclosure links remain available. Full database fingerprints are unchanged by navigation/preferences.

The prior turn's last fix restricts group persistence to actual changes instead of unconditional pagehide/link writes, and removes the pointer backdrop from keyboard order. Both changes are in the exact tested application above. Existing public_access_unit URL assertions use fixed CLI request variables to avoid checkout-path-dependent test behavior; production path handling was not relaxed.

## Visual evidence and logs

Three final artifacts were independently verified by SHA-256:

| Artifact | Engine | Archive SHA-256 |
|---|---|---|
|11936074135|Chromium|1beb96095cb9da2510fbaf72d05ba31237d5bb985fb7b7efd51aefa9fe519984|
|11936620823|Firefox|9e1fb022f2be7c9920c4de030ec08b0ceedd71834da6e50b697674b16eff6dec|
|11936805709|WebKit|7d2685f92238fa5a8f9a2cb88ae79aa43028d26e81d256efc28415ac8f2c6e62|

Thirty current administration-navigation screenshots are retained (ten per engine). Fifteen root renders were reviewed across the four widths and native no-JavaScript case. They are actual endpoint captures using synthetic accounts, not new mockups or real customer content. The full-page phone captures show a fixed drawer at the original viewport height, not a drawer intended to stretch through the whole document. Other cumulative screenshots were not all manually reviewed again.

The navigation logs end in success with251 assertions per engine. The Chromium gallery log also contains a Playwright request-cleanup `Target closed` warning around an intentionally intercepted response; the gallery assertions and job completed successfully. Do not describe that log as wholly warning-free or confuse it with an application-page exception. The navigation and release workflows retain their explicit page-error checks.

## Source and package integrity

Source artifact **11936053498** has SHA-256 **6e506fafce63e26ad48afe77f9133c8d9c5f61447742b77f56e7cfdc553f9f44**. Its350 tracked files reconstruct Git tree47c47421c80675db8ad1479ef4a70a58ed2a8679 exactly. Final documentation and package payload are compared with canonical source in the delivery report.

Preserve every supported prior deployment path, six immutable migrations, five pinned access/recovery files and original manual installer behavior. Admin template/active-key changes do not modify private configuration, business data or endpoint permissions. The package excludes executable tests/build tools, runtime data, setup receipts and private recovery locations. Existing versioned artifacts are never overwritten with different bytes.

## Remaining acceptance

No actual A2 setting, PHP extension, live website, production-payment mode or real-provider operation was changed. Actual A2 permissions/aliases/ZIP/cURL/OPcache/proxy behavior, receiving mailbox, genuine merchant sandbox/settlement/reversals, recovery drill, approved master artwork/content/policies, complete populated-device/accessibility/performance/security and owner acceptance remain. Same-instance restore and same-schema upgrades retain documented limits. RC2 is not accepted final1.0.

Historical RC1 and dev12 evidence remains in Git at e881a9a7 and5e39af30 respectively. The interrupted navigation work was saved and tested; this session finalized its review and delivery rather than inventing a new implementation.
