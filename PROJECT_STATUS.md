# Project status — 1.1.0-rc.7 subtle drifting stage smoke

The application is **1.1.0-rc.7**, a cumulative review candidate on `work/1.1.0-analytics-and-navigation` in [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Its baseline is published rc.6 commit `03470a4b6400ebfbd2d16a1d899c899ee07e2d48`, tree `a444a001dddfbcd655a0a91ab81c44301227689a`. Live main was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`; the live draft head matched rc.6 before new writes.

## New in rc.7

The user asked for drifting smoke over the global background. A restrained blue-gray CSS layer moves slowly above the fixed stage image and behind the public content. The selected stage itself remains stationary. The decoration does not receive pointer events or alter layout, and it requires no additional image/video download, JavaScript or scroll handler.

Reduced-motion preferences, print and forced-colors displays omit moving smoke. Admin, staff login and setup keep their existing separate backgrounds. Motion preview and actual current-package screenshot provenance are recorded with the delivery.

## Retained rc.6 and earlier work

The actual approved transparent sculpted-gold crowned gorilla and horizontal wordmark remain in public header/footer, admin sidebar, login/setup and Branding previews. Header/staff shimmer retains the native static fallback; owner-uploaded public branding and private publication workflows remain intact.

Navigation background stays 25% transparent (75% opaque), with fully opaque logo/text/controls. The header logo keeps its left anchor, the footer surface reaches both screen edges and the transparent outer shell has no border, shadow, outline or rounded edge. Individual dark panels, contained heroes and maximum-fit crowned-head favicon remain.

The dedicated owner Analytics section, graphical reports, Visitor Explorer, Overview snapshots and earlier workflows remain. Database migrations, analytics, JavaScript, PHP controllers/auth/forms, installed configuration/media and pinned recovery/access files are unchanged by rc.7.

## Verification

The CSS bundle rebuild and reproduction passed at **128,396 / 130,000 bytes**, adding 1,055 bytes for the smoke. Current installed-browser passes completed **1,905 assertions** (1,376 retained presentation and 529 smoke), with **47 actual screenshots and two normal-speed ten-second MP4 previews**. Root/subdirectory, desktop/phone, natural drift, fixed stage, controls, reduced motion, print and forced colors passed. Three custom-logo screenshots are explicitly synthetic; admin captures use a disposable owner.

All 15 package unit checks and the local release gate passed. The source audit confirms **347 protected files unchanged** from rc.6. `docs/TEST_RESULTS.md` records current evidence; the delivery receipt records final cumulative package continuity, equivalence of all 345 captured runtime hashes, published source and actual workflow state. Historical rc.6 results and its complete delivery receipt are archived under `docs/history/`; they are not automatically new rc.7 passes. The published rc.6 package and screenshots remain immutable.

## Deployment

Installed **rc.1 through rc.6 → rc.7** uses guarded same-schema Browser upgrades when its normal checks pass. Installed **1.0.x → 1.1** retains cumulative code deployment plus additive Database updates. Preserve the original owner, database, configuration/encryption secret, private runtime and uploads. Never reinstall or bypass update guards.

## Remaining blockers

Owner review of actual pages and target-host acceptance remain separate from automated verification. No live A2 deployment, physical-device or Safari acceptance, mailbox delivery or merchant transaction is claimed. The PR remains draft and unmerged. Commerce remains dormant until provider acceptance, real catalog/policies and owner approval. Show tickets continue to use external venue sellers.
