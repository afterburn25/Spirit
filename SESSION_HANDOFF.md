# Resume here — verified RC2 grouped administration navigation

Repository **afterburn25/Spirit**, **main** authoritative. Current cumulative version **1.0.0-rc.2**. Read live refs, AGENTS.md, PROJECT_STATUS.md, docs/ADMIN_NAVIGATION.md and docs/TEST_RESULTS.md before changing anything. Preserve concurrent changes; never force-push.

## Exact checkpoint

Application **2947013dff9942691cf9c27b128f20cc50da7e77**, tree **47c47421c80675db8ad1479ef4a70a58ed2a8679**, passed full **38654637873**, focused **38654637735**, and three-engine storefront/release/navigation **38654637749**. Tested PR15 merge reference: 71100a594e773c05ffa752beca756f122ecc18cc. The final documentation-only descendant leaves runtime/assets/schema/workflows/tests unchanged; check later exact-main runs directly.

The interrupted prior turn had already committed the menu and its passive-tab fix. This session recovered that actual saved source, verified completed jobs, inspected current rendered evidence, reran local checks and finalized delivery documentation. Do not rebuild from dev12 or treat saved RC1/RC2 work as lost. RC1 application e881a9a7 had passed its own full/storefront/focused runs before RC2.

## Navigation contract

AdminNavigation supplies six server-permission-filtered categories and 34 owner destinations. Overview stays direct; six store/order shortcuts have precise active keys. This is display structure, not authorization. Native details/summary and normal links remain available without JavaScript. Only admin pages load admin-navigation.css/js. Public design and original app.js/manual installer logic remain intact.

Category booleans use a versioned localStorage entry under an installation/account/role-scoped opaque HMAC identifier. Tab-specific sessionStorage holds bounded sidebar scroll and mobile drawer state. No URL, search/filter values, form contents or credentials are stored. Native page refresh preserves the actual URL; never restore an old destination with location/history manipulation. Explicitly closing a current category must not be undone by refresh.

Only actual changed groups are flushed on link/pagehide. A passive old tab must not write stale full preferences over another tab's newer selection. Retain the real two-page regression at every tested width. Open tabs do not continuously synchronize their display; a later explicit change can become the saved preference. New tabs keep their own chosen URL and drawer/scroll. Selecting a phone page closes its drawer but keeps category choices.

Keep the pointer backdrop out of Tab order, keyboard focus inside an open drawer, Escape/close returning to Menu, background/closed-drawer inert behavior and native no-JavaScript access. Storage failure must be reported without disabling navigation. No cross-device persistence, unsaved-form recovery or role bypass.

## Verification and delivery

Local 219 syntax checks, 689 pure assertions and 12 packaging tests passed. New 98 navigation model assertions plus real root/subfolder Chromium/Firefox/WebKit journeys at 1440/820/390/320 cover URL/state persistence, permissions, scroll/drawer, corrupt/unavailable storage, native operation and unchanged database fingerprints. Each engine logs 251 navigation, 212 gallery and 1,491 public-release PASS assertions. Retain all prior full/maintenance/provider/recovery suites and CSP.

Source artifact11936053498 hash6e506fafce63e26ad48afe77f9133c8d9c5f61447742b77f56e7cfdc553f9f44 reconstructs the exact application tree. Browser artifacts11936074135/11936620823/11936805709 were verified by hash; fifteen current root navigation renders were reviewed. The release report records final canonical source and package comparisons. Do not assume container paths survive; re-establish exact files before linking.

## Fixed scope and next steps

A2/cPanel manual one-page installer, no20i, setup code/PRIVATE-SETUP, token or automatic database/user creation. Preserve earlier payload paths, six migrations, five pinned recovery/access files, private configuration/encryption secret/database/media and separate recovery keys. Existing sites upgrade rather than reinstall. Missing ZIP remains a hosting extension requirement, not something this menu changes.

Actual protected A2 operation, receiving mailbox, genuine merchant sandbox, recovery/operating-scope, production artwork/real content/policies, populated real-device quality and owner acceptance remain. No live hosting/payment mode change or accepted final1.0 is implied. Continue from this cumulative RC2 candidate without reintroducing removed installer features, another redesign, native ticketing or fictitious public content.
