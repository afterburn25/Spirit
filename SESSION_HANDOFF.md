# Resume — 1.1.0-rc.7 subtle drifting stage smoke

Repository **afterburn25/Spirit**. Continue from **`work/1.1.0-analytics-and-navigation`**, [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Baseline published rc.6 is `03470a4b6400ebfbd2d16a1d899c899ee07e2d48`, tree `a444a001dddfbcd655a0a91ab81c44301227689a`; inspected live main is `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`. Do not merge before owner approval.

## Current user request

Add a subtle drifting-smoke animation over the selected concert-stage background. The stage remains fixed; a separate translucent blue-gray CSS layer moves slowly behind the public content. Preserve readable panels, stationary page geometry, logo/control opacity, and the current navbar's 25% background transparency. No flashing, scroll-driven movement, new video/image download or JavaScript is needed.

Omit moving smoke for reduced motion, print and forced colors, and keep it outside admin/login/setup. It must not receive pointer input or create overflow. Actual motion previews must come from the installed site; do not generate page mockups or present accelerated test phases as natural real-time playback.

## Retained approved presentation

Keep the exact transparent sculpted-gold crowned-gorilla horizontal logo from the approved kit across public header/footer, admin sidebar and staff login/setup. The navbar/staff animation is the approved steady shimmer with a static reduced-motion fallback. Owner-published media remains the public override; never rewrite settings to force the default.

The header logo keeps its left anchor. The footer surface reaches both screen edges. The transparent main shell has no border/shadow/outline/rounded edge. Individual dark content panels and contained page heroes remain. The complete crowned-head favicon uses full proportional framing and fresh `images/icons/crowned-gorilla-full-fit/` paths; do not crop the crown or claim a larger browser icon slot.

Do not add root `favicon.ico`: the pinned recovery root-file allowlist excludes it. Preserve all prior paths and frozen recovery files.

## Implementation and verification

Current runtime changes should be limited to public CSS/source bundle plus the version. PHP, JavaScript, uploaded data/settings, analytics, migrations, recovery and original logo/favicon/stage/hero artwork remain unchanged from rc.6. The host needs no frontend build or image service.

Current observed results belong in `docs/TEST_RESULTS.md`, `design/background-smoke/` and the delivery receipt. Preserve rc.6 results under `docs/history/`; do not relabel them as current tests. Actual root/subdirectory, desktop/phone, reduced-motion/print/forced-colors and interactive navigation checks must cover the new smoke. Current screenshots must include the existing public/footer/admin/login fixes; synthetic fixtures stay explicitly labeled and never enter the installation package.

Current results: **1,376 retained-presentation assertions plus 529 smoke assertions passed**, with 47 screenshots (3 explicitly synthetic) and two actual ten-second, 25fps, normal-speed MP4 previews. Both passes use candidate SHA-256 `bf5138e6fd7a7504bca2d1d9479665caec8e84101b24221092e829d35431930b` and record the same 345 runtime hashes. Main captures use reduced motion; smoke Home captures run naturally; controlled phase images and footer captures are identified separately. The fixture database/user, server and credentials were removed after checks. Bundle reproduction passed at 128,396 / 130,000 bytes; 15 package unit checks and local release gate passed. The source audit confirms 347 protected files unchanged.

Final documentation may follow captures only when every recorded runtime hash is compared with the final Full ZIP. State that runtime equivalence rather than claiming an exact final-archive capture. Motion preview provenance must distinguish normal-speed recording from deterministic animation-phase checks.

## Release continuity

The immutable rc.6 Full ZIP is 26,433,784 bytes, SHA-256 `1e84e6ee7b947b9fda852fee6ddbbfd4102178cb78cf1eb63bd0b1c37105cd84`; its published source is the baseline above. Preserve that archive and its screenshot delivery. Deliver rc.7 as a new cumulative version and record source/tree, hashes and actual Actions state. Queued/running workflows are not passes.

Installed rc.1 through rc.6 use guarded same-schema Browser upgrades when checks pass. Keep the original owner/database, configuration/encryption identity, media, private runtime and removed installer. Existing 1.0.x additive schema-update instructions remain. No reinstall, hosting token, setup code, native ticketing or automatic database provisioning.

Target-host/provider acceptance remains separate; no production deployment or live payment is authorized by this presentation change.
