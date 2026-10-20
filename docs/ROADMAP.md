# Roadmap — 1.1.0-rc.3 analytics corrections

The original 1.0 feature foundation and graphical rc.2 analytics are complete. This continuation fixes two defects in existing reports: detail/custom-page drilldowns lost their validated slug filter, and Ecosia search referrals were misclassified. It does not add another product module or reset existing analytics.

| Milestone | Current status | Remaining work |
|---|---|---|
|Core website and administration|**Complete.** Public site, CMS, media/music/shows/press, booking/mail, accounts and dormant commerce are implemented.|Correct concrete defects found by verification or acceptance.|
|Approved public presentation|**Implemented.** Existing crowned-gorilla artwork, real HTML content, CMS overrides, responsive heroes, larger menu and search beside Book the Band are retained.|Owner may publish genuine content through the existing editors.|
|A2 installation, updates and recovery|**Implemented and extensively tested.** One-page manual installer, encrypted backups, guarded same-schema updates and recovery are present.|Observe real host/recovery behavior as required; no reinstall for updates.|
|Email and scheduled processing|**Implemented.** Mail/payment worker heartbeats and owner-opt-in bounded cleanup are available.|Observe actual host schedules when needed. A setting does not create a cron job.|
|Commerce and Authorize.net|**Software complete; activation-gated.**|Genuine provider acceptance, real catalog/policies and explicit owner approval before activation.|
|Graphical audience analytics and Overview|**Implemented in rc.2.** Dedicated menu, six colored metrics, charts, anonymous visitor journeys, live/bounce/time, source/device/location/crawler reports and aggregate search imports.|Preserve data definitions and owner-only access.|
|Exact page drilldowns and search attribution — rc.3|**Implemented and locally verified: 57 units, 223 SQL/HTTP checks and 201 browser checks.** Canonical detail paths remain scoped through report links and visit inspection; Ecosia joins the existing supported-search classification.|Observe current GitHub checks and the exact rc.2-to-rc.3 upgrade evidence.|
|Release integrity and browser quality|**rc.2 baseline verified: 10 workflows / 23 jobs and 40 actual screenshots.** rc.3 requires its own observed evidence.|Publish current source, observe applicable Actions, retain exact-package captures and immutable Full ZIP/checksums.|
|Owner and target-host acceptance|**Current candidate review required.** Earlier 1.0 release does not certify later candidate changes.|Review current screenshots and applicable real-host observations before final promotion.|

## Analytics upgrade paths

Migrations 007/008 are unchanged. **rc.1/rc.2 → rc.3** uses normal guarded same-schema Browser upgrades when all checks pass. **1.0.x → 1.1** requires reviewed code deployment plus additive Database updates; the browser updater deliberately rejects that cross-schema transition. Preserve the existing owner, configuration/encryption secret, database, private runtime and uploads. Do not reinstall or bypass safeguards. See `ANALYTICS.md`.

## Evidence and launch profile

Current observed verification belongs in `TEST_RESULTS.md`. The exact rc.2 final outcomes are archived in `history/VERIFICATION-1.1.0-rc.2.json`; they remain baseline evidence. Final rc.3 workflow/capture outcomes observed after its immutable source commit belong in the companion review receipt and PR.

A content-first launch does not require fabricated shows, portraits, Part 3 or merchandise. Commerce may remain closed; payment/provider acceptance is required when activating it. Final content guidance remains in `design/final-launch-content/FINAL-CONTENT-MAP.md` and `PRODUCTION-ASSET-SPECS.md`; acceptance scope remains in `acceptance/CONSOLIDATED-FINAL-PASS.md`.
