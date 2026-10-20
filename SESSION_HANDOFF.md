# Resume — 1.1.0-rc.3 analytics drilldown corrections

Repository **afterburn25/Spirit**. Continue from **`work/1.1.0-analytics-and-navigation`**, [draft PR 37](https://github.com/afterburn25/Spirit/pull/37), rather than main alone. The baseline is delivered rc.2 commit `f6b8e6349e75d3ca465bec60a010f74f8eaf84e4`; main was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`. The latest 1.0.13 artwork and complete rc.1/rc.2 analytics remain. Do not merge before owner screenshot approval.

## Current correction

The continuation audit found that the collector already retains canonical `?slug=` identifiers for detail/custom pages, while the rc.2 visitor filter rejected every question mark. The dashboard therefore generated a valid recorded path that its explorer refused. rc.3 validates the filter by comparing it to `Analytics::normalisePath()` instead of banning all queries. It accepts only the exact canonical stored value, preserving the single validated slug while rejecting unrelated queries, private paths, aliases and fragments.

The report's existing search-domain classifier also omitted Ecosia although the collector recognized its supplied search term. rc.3 adds the explicit supported host suffix and tests genuine and lookalike domains. It does not modify the collector or introduce new tracking.

## Source and test ownership

Runtime changes are confined to `app/AnalyticsVisitors.php` and the visitor-filter help text in `views/admin/analytics-visitors.php`. Tests cover all five detail route families, a second distinct release slug, root/subfolder behavior, actual report links, retained selected visit/date scope, no-JavaScript operation and malformed/private filters. A separate test-only `detail-paths-fixture` keeps the original demonstration totals stable. Never ship or use these fixtures as production observations.

The exact-package analytics capture helper retains its 19 existing images and adds desktop/phone detail-page-filter journeys, for 21 administration captures. The public helper retains 21 captures. Both manifests must reference the same final Full ZIP by SHA-256. The review-board renderer validates its source manifests and images; all admin pictures are synthetic and labeled.

## Verification checkpoint

See `docs/TEST_RESULTS.md` for current observed rc.3 checks. Local validation, the published source, remote workflow results, exact rc.2-to-rc.3 upgrade and final capture evidence must stay distinguishable. Do not label earlier rc.2 passes as rc.3 verification.

The archived rc.2 final receipt (`docs/history/VERIFICATION-1.1.0-rc.2.json`) records all 10 workflows / 23 jobs passing, its exact ZIP hash,40 screenshots and the 43-check rc.1-to-rc.2 upgrade. The source-checkpoint report is retained separately because its final CI/captures occurred after that immutable commit.

## Retained behavior and upgrade path

The owner Overview is an at-load seven-day UTC snapshot. The Analytics dashboard alone refreshes live activity. Anonymous visit/session definitions, classic completed-visit bounce, measured foreground time, unknown locations/search terms, aggregate Search Console periods and separate crawler identities remain as documented in `ANALYTICS.md`. Staff have no Analytics access; report reads do not change the database.

Installed rc.1/rc.2 use the normal same-schema Browser upgrades process for rc.3. Existing 1.0.x installations still need cumulative-code deployment plus additive Database updates 007/008; the browser updater intentionally rejects that schema change. Preserve configuration, encryption secret, database/owner, private runtime, media and previously removed installer. Do not alter migrations, collector/sampling code or pinned recovery/access files.

Build with `tools/package.py`, which uses Pillow 12.3.0 for deterministic wide WebP backgrounds. Public bundles reproduce with `node tools/build_public_assets.mjs --check` and remain within 130,000 bytes. Deliver every completed version as a new immutable Full ZIP plus checksum, actual screenshots and review board. Never overwrite prior downloads.

## Remaining external acceptance

Keep the PR draft until the owner approves the current screenshots. Actual A2/cPanel behavior, optional scheduling and merchant/provider acceptance are not established by local tests. Commerce stays closed until separately accepted; Part 3 remains unpublished without approved provider details. Preserve the approved public artwork and confirmed band roles. No native ticketing, hosting token, setup code or automatic database provisioning.
