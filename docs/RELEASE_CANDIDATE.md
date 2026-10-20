# 1.1.0-rc.1 — audience analytics review candidate

This cumulative candidate retains the latest 1.0.13 artwork work and adds owner analytics, private location-data management, aggregate Search Console imports, consolidated public assets and reliable phone navigation. Actual local checks and observed remote Actions belong in TEST_RESULTS.md. A prior successful run does not certify this candidate.

Migrations 007 and 008 add analytics tables. Existing sites retain their owner, configuration, encryption secret, database and media. The same-schema browser ZIP updater deliberately refuses this transition; use the documented code deployment and additive Database updates flow in ANALYTICS.md. Do not reinstall or bypass the updater's guard.

Keep this candidate draft and unmerged until the owner approves its actual desktop/mobile screenshots. Deliver the full immutable ZIP, checksum, actual public/admin screenshots and review board. Real A2, mailbox, merchant, recovery and physical-device acceptance remain separate. Preserve the approved design, external ticket sellers and manual one-page installer; no hosting token, setup code, 20i or automatic database provisioning is introduced.

## Historical RC7 checkpoint — not current candidate evidence

The earlier 1.0.0-rc.7 navigation/native-control pass was based on RC6 and changed no PHP business logic, form defaults, installer, schema or recovery code at that checkpoint. Its recorded evidence was 230 PHP/inc syntax checks, 812 pure assertions, eight JavaScript syntax checks and 12 package tests, plus isolated Chromium checks and six Actions workflows for `adcafab9`. Three browser jobs recorded 361 passing checks each. The later RC7 finalization was documentation-only. Retained historical results and NAVIGATION_QUALITY.md provide that older context; none of those counts establishes verification of the analytics candidate.
