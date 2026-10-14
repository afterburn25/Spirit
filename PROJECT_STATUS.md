# Project status — 1.0.0-rc.2 preparation

Current work adds the user's requested polished collapsible administration categories and remembered navigation state. The new exact verification is pending. Previous RC1 application e881a9a7f5f57fb6639a8d22a032a969c26837a9 passed full38646174199, storefront38646174182 and focused38646174211; these results do not establish RC2 success. Main remains5e39af30 until tested promotion.

## Implemented for this candidate

Six permission-filtered categories organize all28 existing sidebar destinations plus six existing store/order shortcuts. Overview stays direct. Native details/summary, icons, expanded/collapsed chevrons, current-link and current-category highlighting, independent sidebar scrolling and a page-context header retain the navy/gold visual system.

First visit opens all permitted categories. The user's choices, including a deliberately closed current category, survive page changes and refresh using browser-local preferences scoped to installation/account/role. Sidebar scroll and mobile-drawer state are tab-specific. The code never stores URLs, filters, credentials or form data and never changes browser location or history. Refresh retains the actual page, query and hash. Selecting a phone destination closes the drawer to reveal the new page while preserving category choices.

Phone focus handling includes Escape, Tab boundaries, close/backdrop controls and inert closed/background content. Native no-JavaScript details/links remain available. Corrupt or unavailable browser storage cannot disable navigation. Server endpoint permissions are unchanged; staff receive only permitted links and nonempty categories.

The RC1 native public menu/photo/audio, portrait catalog and price fixes, published-policy footer, broader release review and all earlier website/admin/store/maintenance modules remain cumulative. Manual A2 one-page installation, no token/setup code/automatic database/20i, six migrations and five pinned recovery/access files remain.

## Verification

Local219 PHP/inc syntax checks,689 distinct pure assertions (591 retained +98 navigation) and12 packaging tests passed. New real root/subfolder Chromium/Firefox/WebKit tests cover four widths, exact URL/state persistence, sidebar scroll, phone drawer/focus, new-tab separation, account/role isolation, malformed/unavailable storage, no-JavaScript navigation and unchanged database fingerprints. Existing full/focused/gallery/public/payment/recovery suites remain required. Local offline renders do not establish persistence or SQL acceptance. Actual new Actions results must be recorded before promotion.

## Remaining final gates

Actual A2 PHP extensions/private-root/ACL/cleanup/recovery, real receiving mailbox and merchant sandbox, approved production artwork/genuine content/policies, populated real-device quality and owner acceptance remain. No production or hosting settings were changed. Final1.0 is not released.
