# Project status — 1.1.0-rc.3 analytics corrections

The application is **1.1.0-rc.3**, a cumulative review candidate on `work/1.1.0-analytics-and-navigation` in [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Its baseline is the delivered rc.2 source `f6b8e6349e75d3ca465bec60a010f74f8eaf84e4`. Live `main` was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`. Resuming from main alone would omit the intervening artwork and analytics work.

## New in rc.3

- **Exact detail-page drilldowns:** page-performance links now carry recorded release, show, member, product and custom-page paths into Visitor Explorer, including their validated `slug`. The selected path and dates remain attached when inspecting a visit.
- **Canonical filter validation:** the explorer accepts the collector's exact public path format. Arbitrary query fields, fragments, private routes and noncanonical aliases remain rejected. The field explains how to use the recorded identifier.
- **Search attribution consistency:** supported Ecosia referrals are classified as Organic search, matching the collector's existing supplied-search-term recognition. Lookalike hosts remain referrals.
- These corrections work on retained history immediately. There is no migration, traffic rewrite, change to collection rules or extension of the recorded personal information.

## Retained rc.2 work

Analytics has its own owner-only menu with Audience dashboard, Visitor explorer and Geography data. The owner Overview has a seven-day UTC snapshot, six colored metrics, a real daily traffic chart, top sources/pages and an explicitly timestamped live count at page load. The full dashboard includes graphical traffic/channel/device/hourly/engagement/depth/entry/exit reports, private CSV exports, live refresh, separate crawlers, local country lookup, optional City data and aggregate Search Console imports.

The public search control sits directly beside **Book the Band**, with larger centered navigation and responsive tablet/phone spacing. The complete approved artwork, real HTML content, persistent audio, CMS overrides and native forms remain. Public bundles stay within the existing 130,000-byte budget.

## Verification

Local rc.3 verification passed: 57 analytics units, 223 SQL/HTTP checks and 201 Chromium browser checks at root and `/band`, including 14 actual detail-page journeys and native/no-JavaScript coverage. The new test also reproduced the defect in the unchanged delivered rc.2 ZIP. All 15 packaging tests, the deterministic release gate and the public-bundle reproducibility check passed. `docs/TEST_RESULTS.md` records the exact scope. Latest-source GitHub outcomes, the exact delivered-version upgrade and final captures are recorded in the companion verification receipt after publication; no pending result is counted as a pass.

The delivered **rc.2** completed all **10 GitHub workflows  / 23 jobs**, 40 actual screenshots and a successful exact rc.1-to-rc.2 browser upgrade preserving its original state. Its final receipt is retained as `docs/history/VERIFICATION-1.1.0-rc.2.json`; the original source checkpoint is `docs/history/TEST_RESULTS-1.1.0-rc.2.md`. Those results do not certify new rc.3 changes.

## Deployment

Installed **rc.1 or rc.2 → rc.3** uses the guarded same-schema Browser upgrades process when its normal integrity/recovery checks pass. Migrations 007/008, the collector and the pinned recovery layer remain unchanged. Installed **1.0.x → 1.1** still needs the documented cumulative-code deployment and additive Database updates flow. Preserve the original owner, database, configuration/encryption secret, private runtime and uploads. Never reinstall or bypass update guards.

## Remaining blockers

Owner review of the current actual desktop/phone screenshots and target-host acceptance remain separate from automated verification. No A2 deployment, real mailbox delivery, physical-device test or merchant transaction is claimed. Commerce remains dormant until genuine provider acceptance, real catalog/policy content and owner approval. Part 3 remains unpublished until its real URL/title/credits are approved; show tickets use external venue sellers.
