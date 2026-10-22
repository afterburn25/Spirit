# 1.1.0-rc.3 — analytics drilldown review candidate

This cumulative candidate starts from delivered rc.2 (`f6b8e6349e75d3ca465bec60a010f74f8eaf84e4`). It fixes page-performance links for canonical release, show, member, product and custom-page paths containing the validated slug. Visitor Explorer retains the exact path/date scope when inspecting a visit, while rejecting private routes and unrelated query fields. It also classifies supported Ecosia referrals consistently with the existing search-term collector.

All rc.2 graphical analytics, Overview snapshots, visitor journeys, dedicated navigation and public search/menu refinements remain. This is a read-only reporting correction, not a new collection scheme or data migration. Existing traffic becomes usable without being rewritten.

Migrations 007/008 and the recovery layer are unchanged. Installed rc.1/rc.2 sites use guarded same-schema Browser upgrades when their normal checks pass. A 1.0.x installation still needs reviewed cumulative code deployment and additive Database updates as documented in `ANALYTICS.md`; do not reinstall or bypass the cross-schema guard.

`TEST_RESULTS.md` records observed local checks. The archived rc.2 final receipt records its 10 workflows / 23 jobs and 40 captures; those are baseline evidence. rc.3's published-source Actions, exact delivered-version upgrade and final captures belong in its final companion verification receipt and PR.

Keep the candidate draft until owner review of the actual desktop/phone screenshots. Deliver a new immutable Full ZIP, checksums, screenshots and review board. Real hosting, mailbox, merchant, recovery and physical-device acceptance remain separate. Preserve approved artwork, external venue ticket sellers and the manual one-page installer. No setup code, hosting token, 20i or automatic database provisioning.
