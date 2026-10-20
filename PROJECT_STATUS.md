# Project status — 1.1.0-rc.4 transparent gorilla favicon

The application is **1.1.0-rc.4**, a cumulative review candidate on `work/1.1.0-analytics-and-navigation` in [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Its baseline is delivered rc.3 commit `c3584d348067d035ec14a739c9783b5880b95321`. Live `main` was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`, and the live PR head matched the local baseline before writes. Continue from this branch to retain all prior artwork and analytics work.

## New in rc.4

- Use the user-selected sculpted gold crowned-gorilla head as the transparent favicon, without the wordmark.
- Fit the complete visible head to 98% of a square viewport. The actual 16px and 32px raster exports occupy the full vertical pixel extent after antialiasing, with transparent corners and preserved proportions.
- Provide 16/32/48/64px PNGs and ICO frames, a 180px touch icon, and 192/512px PNGs. Retain the old SVG URL with the new artwork.
- Share versioned favicon links across public, administration and account headers, including subdirectory installations. Native browser tab dimensions remain browser-controlled.
- Keep the source image, exact edit prompt, reproducible browser export script and actual icon-size comparison evidence in the repository.

## Retained application

The rc.3 exact detail-page visitor drilldowns, Ecosia classification, dedicated Analytics section, graphical dashboard, Overview snapshots, live/bounce/time reports, geography, aggregate search imports and visitor journeys remain unchanged. Public navigation and hero composition retain their existing behavior. This release does not integrate the separately prepared animated navbar-logo kit or alter any database migration.

The complete delivered rc.3 evidence is archived in `docs/history/VERIFICATION-1.1.0-rc.3.json`: all 10 workflows/23 jobs,42 actual application screenshots and the 52-check exact rc.2-to-rc.3 upgrade. These are historical baseline results, not new favicon verification.

## Verification

Current focused checks passed: PHP syntax for both headers and the shared partial; all 8 links at root and `/band`;16 real static-file HTTP requests with correct MIME types and exact file bytes; all PNG dimensions, ICO frames and transparent pixels; actual16/32/48/64px Chromium compositions on light and dark backgrounds. The source master is unchanged by the rendering/export pipeline. This is a focused asset/header fixture, not a new installed-site or database test.

All 15 packaging tests and the deterministic software release gate passed. The source master, all 8 migrations, all 5 pinned recovery/access files and existing public collection/bundles retain their verified bytes. Final source-publication and package-comparison outcomes are recorded in the delivery receipt. Pending remote checks are not counted as passes.

## Deployment

Installed **rc.1/rc.2/rc.3 → rc.4** uses the guarded same-schema Browser upgrades process when its normal integrity/recovery checks pass. Migrations007/008 and the pinned recovery layer remain unchanged. Installed **1.0.x →1.1** still needs the documented cumulative-code deployment and additive Database updates flow. Preserve the original owner, database, configuration/encryption secret, private runtime and uploads. Never reinstall or bypass update guards.

## Remaining blockers

Owner review of the current favicon and target-host acceptance remain separate from automated verification. No A2 deployment, physical browser/device acceptance, mailbox delivery or merchant transaction is claimed. The PR remains draft and unmerged. Commerce remains dormant until genuine provider acceptance, real catalog/policy content and owner approval. Part 3 remains unpublished pending approved details; show tickets use external venue sellers.
