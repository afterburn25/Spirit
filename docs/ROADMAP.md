# Cumulative roadmap — 1.0.0-rc.2

RC2 includes the requested grouped, remembered administration navigation and all RC1 public-access improvements. Application2947013d passed full38654637873, focused38654637735 and three-engine storefront/release/navigation38654637749. Exact evidence is in TEST_RESULTS.md. Version numbers are not completion percentages.

| Target | Scope | Current state |
|---|---|---|
|0.1|One-page manual A2 installer, private runtime, owner, deletion/lock|Implemented and regression-tested. No token/setup code/automatic database creation. Actual A2 acceptance remains.|
|0.2|Approved responsive design|Implemented, including native mobile navigation and catalog/image fixes. Approved production master artwork/content remains.|
|0.3|CMS, band/media/press, search/history|Implemented; native photo access, published-policy footer and private revision restoration included.|
|0.4|Releases/tracks/persistent audio|Implemented; native no-JavaScript audio fallback without autoplay included.|
|0.5|Shows and external venue ticket links|Implemented; no native ticket sales or scanning.|
|0.6|Booking/email/newsletter|Implemented and tested. Actual receiving-mailbox acceptance remains.|
|0.7|Catalog/variants/stock/cart/customers|Implemented and tested; initial addresses remain US-only.|
|0.8|Token-only payment/orders/fulfillment|Application and synthetic provider boundaries tested. Genuine merchant sandbox acceptance remains.|
|0.9|Staff/backups/upgrades/retention/discovery/quality|Implemented with guarded same-instance restore and same-schema upgrade limits. Broader real-site quality acceptance remains.|
|1.0.0-rc.1|Public native access, policy links, catalog polish, wider release review|Included cumulatively; exact RC1 application e881a9a7 and current RC2 tests passed.|
|1.0.0-rc.2|Polished grouped administration and remembered menu/page state|Implemented and verified in three engines at root/subfolder paths; six categories,34 owner destinations, independent category/scroll/drawer state and unchanged current URLs.|
|1.0.0|Complete explicitly accepted release|Not released. Actual host/provider/recovery/content/policy/owner evidence remains.|

All earlier payload paths, six migrations, five pinned recovery/access files, original private configuration/encryption secret/database/media and offline recovery keys must remain protected. Existing sites upgrade rather than reinstall. Candidate packages are cumulative and immutable. Read ADMIN_NAVIGATION.md, RELEASE_CANDIDATE.md and LAUNCH_CHECKLIST.md for operating details and remaining acceptance.
