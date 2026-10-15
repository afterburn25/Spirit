# Project status — 1.0.0-rc.2

**Verified application:** 2947013dff9942691cf9c27b128f20cc50da7e77, tree 47c47421c80675db8ad1479ef4a70a58ed2a8679. Full workflow **38654637873**, focused maintenance **38654637735** and three-engine storefront/release/navigation **38654637749** completed successfully on 2026-10-14. This final documentation-only descendant changes no runtime, assets, schema, workflow or test bytes. Inspect live main before further writes.

## Completed user request

Six polished collapsible administration categories contain 34 owner destinations. Overview remains direct. Every earlier destination is retained, with six direct shortcuts to existing store/order settings. Server-filtered staff menus expose only permitted destinations and omit empty groups; no endpoint permissions changed.

First visit opens all permitted categories. Independent open/closed choices survive page changes and refresh, including an intentionally closed current category. Current-link and category highlighting, decorative icons, counts, chevrons and the contextual page header retain the navy/gold design. Expand all and Collapse all do not navigate or submit a form.

Category choices are browser-local and scoped to installation/account/role. Sidebar scrolling and mobile drawer state are tab-specific. Refresh retains the actual path, query and hash rather than redirecting to a remembered URL or Overview. Phone link selection closes the drawer to expose the destination, without resetting categories. A passive older tab cannot overwrite a newer choice merely by navigating or refreshing.

Phone keyboard behavior includes native disclosure controls, Tab boundaries, Escape/close return focus, pointer backdrop and inert background/closed-drawer content. The backdrop is not a keyboard stop. Native no-JavaScript navigation remains available; storage restrictions report a limitation rather than breaking the menu. Browser storage does not save form values, credentials or page URLs. Cross-device synchronization and unsaved-form persistence are not claimed.

## Verification

219 PHP/inc syntax checks, 689 distinct pure assertions (591 retained plus 98 navigation) and 12 packaging tests passed locally. Exact Actions verification passed PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4, all 13 retained Chromium workflows and actual Apache HTTPS access rules.

The dedicated Chromium/Firefox/WebKit jobs each completed 251 navigation assertions in addition to 212 gallery and 1,491 release-review assertions. Navigation covers domain-root/subfolder installations at 1440, 820, 390 and 320 pixels, exact URL refresh, groups/scroll/drawer, native controls, new-tab behavior, passive-tab conflict prevention, account/role isolation, malformed/unavailable storage and unchanged database fingerprints. These are executed assertions, not counts multiplied into a unique test total.

Fifteen current root navigation renders were reviewed across all three engines, including four widths and native navigation. Thirty navigation screenshots are retained for root and subfolder evidence. Other retained workflow screenshots were not all newly reviewed in this pass. See TEST_RESULTS.md for jobs, artifacts and limitations.

## Cumulative scope and remaining gates

RC1 public native access, policy navigation, catalog refinements and all prior website/admin/store/maintenance functionality remain. A2/cPanel manual one-page installation stays: no 20i, setup code, cPanel token or automatic database creation. The application has not enabled PHP ZIP or changed hosting settings. Preserve six migrations, five pinned recovery/access files, original private configuration/encryption secret/data/uploads and separate recovery keys.

Final 1.0 still requires actual A2/mailbox/genuine merchant sandbox/recovery acceptance, approved production artwork and real content/policies, populated real-device quality review and owner approval. Existing sites upgrade, never reinstall or replace their databases. Same-instance restore and same-schema upgrade limitations remain.
