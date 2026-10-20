# Project status — 1.1.0-rc.5 stage background and readable content

The application is **1.1.0-rc.5**, a cumulative review candidate on `work/1.1.0-analytics-and-navigation` in [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Its baseline is delivered rc.4 commit `315c45d2968b6b9d1eacc5820b6994aa95cae50c`. Live `main` was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`, and the live PR head matched the local baseline before writes.

## New in rc.5

The user selected **option 1, the blue-lit concert stage**, for a global background that stays stationary while content scrolls. The follow-up decisions are a transparent main outer container, a screen-wide header that preserves the logo position, and separate dark containers around exposed content.

- One fixed public-only decorative layer displays the chosen stage behind the page.
- The main `.site-shell` is transparent so the artwork appears through side gutters and section gaps.
- The header background and lower border extend to both screen edges while its existing logo and controls keep their positions.
- Exposed headings, introductions, detail copy and captions receive matching dark navy panels with blue borders and gold accents. The stage stays visible between groups.
- Existing contained page heroes, cards, forms, navigation, search and player retain their behavior.
- Phone framing favors the drum kit and guitar on the right; print and forced-colors modes omit the decoration.
- The source master is preserved at native 1672 × 941px and exported to a 243,104-byte WebP. The artwork is generated decoration, not a photograph of an actual performance.
- Runtime changes are public CSS and its compiled bundle, the new background asset and small semantic markup wrappers in public templates and the release-card helper. No JavaScript or controller logic changes are introduced.

## Retained application

All rc.4 crowned-gorilla favicon assets and shared links remain unchanged. The rc.3 detail-page visitor drilldowns, Ecosia classification, dedicated Analytics section, graphical reports, Overview snapshots and earlier functionality remain. The separately delivered animated navbar-logo kit is not newly integrated by this change.

Migrations, analytics collector, controller logic, installed configuration, accounts, media and pinned recovery layer are unchanged. Public markup changes preserve existing forms, actions, CSRF fields, conditions and escaping. Historical rc.4 source verification and its delivered receipt are archived under `docs/history/`.

## Verification

The native-size asset export, unchanged-source hash check, all 15 packaging tests and deterministic software release gate passed. The public CSS/JS bundle is reproducible and totals **126,436 / 130,000 bytes**. All five changed PHP files pass syntax checks.

The final installed-application browser pass completed **1,120 checks and 48 actual screenshots** at root and `/band`, including 1440px and 1920px desktop and 390px phone viewports. All six header comparisons confirm unchanged logo/navigation/control rectangles and a background reaching both screen edges. Panel readability/containment, fixed scrolling, search/menu, no-JavaScript behavior, print/forced-colors and public-only decoration passed with no uncaught browser errors. Of the captures, 24 are unseeded and 24 use clearly labeled synthetic fixtures only in disposable installations.

The source-continuity audit confirms all 80 protected files are byte-identical to rc.4. Observed results and screenshot provenance are recorded in `docs/TEST_RESULTS.md` and `design/background-stage/`. The companion delivery receipt records the published source, immutable Full ZIP, final runtime/package equivalence and observed Actions state. Pending workflows are not passes.

## Deployment

Installed **rc.1/rc.2/rc.3/rc.4 → rc.5** uses the guarded same-schema Browser upgrades process when its normal checks pass. Installed **1.0.x → 1.1** retains the documented cumulative-code deployment and additive Database updates flow. Preserve the original owner, database, configuration/encryption secret, private runtime and uploads. Never reinstall or bypass update guards.

## Remaining blockers

Owner review of actual pages and target-host acceptance remain separate from automated verification. No live A2 deployment, physical-device or Safari acceptance, mailbox delivery or merchant transaction is claimed. The PR remains draft and unmerged. Commerce remains dormant until provider acceptance, real catalog/policies and owner approval. Show tickets continue to use external venue sellers.
