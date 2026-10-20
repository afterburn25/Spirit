# 1.1.0-rc.2 — graphical analytics and navigation review candidate

This cumulative candidate retains the latest 1.0.13 artwork and rc.1 analytics work. It adds a dedicated Analytics menu, prominent graphical reports with consistent colors, a filterable anonymous visitor explorer with page journeys and seven-day snapshots on the owner Overview. Public search sits beside Book the Band and navigation text is larger. Actual local checks and observed remote Actions belong in TEST_RESULTS.md. A prior successful run does not certify this candidate.

Migrations 007 and 008 are unchanged from rc.1. An installed rc.1 uses the guarded same-schema Browser upgrades path when its checks pass. Updating a 1.0.x installation adds analytics tables and the browser updater deliberately refuses that schema transition; use the documented code deployment and additive Database updates flow in ANALYTICS.md. Existing sites retain their owner, configuration, encryption secret, database and media. Do not reinstall or bypass the updater's guard.

Keep this candidate draft and unmerged until the owner approves its actual desktop/mobile screenshots. Deliver the full immutable ZIP, checksum, actual public/admin screenshots and review board. Real A2, mailbox, merchant, recovery and physical-device acceptance remain separate. Preserve the approved design, external ticket sellers and manual one-page installer; no hosting token, setup code, 20i or automatic database provisioning is introduced.

## Historical RC7 checkpoint — not current candidate evidence

The earlier 1.0.0-rc.7 navigation/native-control pass was based on RC6 and changed no PHP business logic, form defaults, installer, schema or recovery code at that checkpoint. Its recorded evidence was 230 PHP/inc syntax checks, 812 pure assertions, eight JavaScript syntax checks and 12 package tests, plus isolated Chromium checks and six Actions workflows for `adcafab9`. Three browser jobs recorded 361 passing checks each. The later RC7 finalization was documentation-only. Retained historical results and NAVIGATION_QUALITY.md provide that older context; none of those counts establishes verification of the analytics candidate.
