# Verification — 1.0.0-rc.2 preparation

Exact RC2 Actions results are pending. The earlier RC1 application e881a9a7 passed full38646174199, storefront38646174182 and focused38646174211; it is the tested baseline, not evidence for changed navigation.

Local verification passed219 PHP/inc syntax checks,689 distinct pure assertions and12 packaging tests. New98 model assertions cover all routes/current keys, six categories, preservation of prior links, role filtering, scope separation and fixed decorative icons. Existing591 pure assertions remain. public_access_unit now supplies explicit CLI request variables so its unchanged URL assertions work independently of the checkout-directory length.

Offline template renders at1440/1024/820/390/320 show no page overflow or JavaScript exceptions; the about-blank browser context cannot use storage, so these are layout checks only. Actual persistence, authorization and database behavior are delegated to the new root/subfolder three-engine navigation journey. It covers exact query/hash refresh, independent groups/scroll/drawer, explicit collapsed active sections, cross-page and new-tab behavior, per-account/role isolation, malformed/oversized/unavailable storage, native no-JavaScript links, keyboard focus and unchanged database fingerprints.

Required full workflow retains both PHP/database environments,13 existing Chromium workflows and actual Apache HTTPS. Dedicated Chromium/Firefox/WebKit workflow retains gallery and release-review tests and adds the navigation journey. No existing failed check may be skipped to obtain success. Actual A2/server settings, real mailbox/genuine merchant, production art/content and final owner acceptance remain outside these automated results.
