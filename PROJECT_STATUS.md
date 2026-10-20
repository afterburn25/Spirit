# Project status — 1.1.0-rc.6 approved identity and seamless surfaces

The application is **1.1.0-rc.6**, a cumulative review candidate on `work/1.1.0-analytics-and-navigation` in [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Its baseline is delivered rc.5 commit `6c5248e1cd6033ce0b24bd9db97621431908fa4e`. Live `main` was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`, and the live PR head matched the local baseline before writes.

## New in rc.6

The user reported that the approved new logo was missing from the public site, admin and admin login, and that the favicon still appeared small. The user also requested a full-width footer and removal of the visible outer transparent-container edge.

- Install the actual approved transparent sculpted-gold crowned gorilla and horizontal wordmark. The earlier kit-only delivery had not changed the active bundled default.
- Use it in the public header/footer, administration sidebar, staff login, installer and default Branding previews. Header/staff artwork uses the approved steady shimmer with a native static reduced-motion fallback; the footer is static.
- Keep the existing owner-uploaded public branding workflow and settings intact. Login/setup rendering has no branding database dependency.
- Fill available space in the header's existing logo track for readability, while preserving its left anchor and responsive controls.
- Give the public navigation background 25% transparency (75% opacity), keeping logo/text/controls fully opaque.
- Extend the footer surface to both viewport edges with its existing content alignment.
- Remove the transparent outer shell's border, shadow, outline and rounded edge. Individual dark content panels, the fixed stage and contained page heroes remain.
- Maximize proportional framing of the same complete transparent crowned-gorilla favicon and change active asset filenames. The earlier set already nearly filled the canvas; this is a modest framing correction plus fresh cache identity, not a larger browser icon slot.

## Retained application

The dedicated Analytics section, graphical reports, exact detail-page Visitor Explorer links, Overview snapshots and all earlier functionality remain. All migrations, analytics logic, JavaScript, installed configuration, accounts, media and pinned recovery/access files remain unchanged. Admin form actions, CSRF fields, conditions and escaping retain their existing behavior.

Historical rc.5 tests and its delivered receipt are archived under `docs/history/`; they are not current rc.6 test results.

## Verification

The approved logo assets are byte-identical to the selected kit originals. The branding unit suite passed 38 checks and the admin navigation suite passed 109 checks. Public bundle reproduction passed at **127,341 / 130,000 bytes**. The actual installed-browser run passed **1,374 checks** at root and `/band`, covering 11 public widths per installation and 31 screenshots. A focused admin paint recheck passed **180 additional checks** with 24 supplemental captures. All 15 package tests, 50 favicon/export checks and the deterministic software release gate passed. All 285 protected whole files remain byte-identical to rc.5. `docs/TEST_RESULTS.md` and the delivery receipt record precise scope, screenshot recapture provenance and final package/runtime equivalence. Pending Actions workflows are not passes.

## Deployment

Installed **rc.1 through rc.5 → rc.6** uses guarded same-schema Browser upgrades when its normal checks pass. Installed **1.0.x → 1.1** retains cumulative code deployment plus additive Database updates. Preserve the original owner, database, configuration/encryption secret, private runtime and uploads. Never reinstall or bypass update guards.

## Remaining blockers

Owner review of actual pages and target-host acceptance remain separate from automated verification. No live A2 deployment, physical-device or Safari acceptance, mailbox delivery or merchant transaction is claimed. The PR remains draft and unmerged. Commerce remains dormant until provider acceptance, real catalog/policies and owner approval. Show tickets continue to use external venue sellers.
