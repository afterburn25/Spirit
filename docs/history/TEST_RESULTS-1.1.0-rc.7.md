# Verification — 1.1.0-rc.7 subtle drifting stage smoke

## Scope and baseline

Baseline is published rc.6 commit `03470a4b6400ebfbd2d16a1d899c899ee07e2d48`, tree `a444a001dddfbcd655a0a91ab81c44301227689a`. The new change adds restrained blue-gray drifting smoke above the stationary public stage and behind page content. Prior approved logo/admin/login, footer, shell, navigation transparency and favicon fixes are retained.

Current runtime changes are limited to CSS and the release version. All PHP, JavaScript, original artwork, analytics, forms/authentication, migrations and pinned recovery/access remain unchanged. Historical rc.6 tests and the delivered receipt are preserved under `history/TEST_RESULTS-1.1.0-rc.6.md` and `history/VERIFICATION-1.1.0-rc.6.json`.

## Current verification

Current verification passed on disposable installations at the domain root and `/band`. Both browser passes used the frozen rc.7 candidate ZIP with SHA-256 `bf5138e6fd7a7504bca2d1d9479665caec8e84101b24221092e829d35431930b`. No production site was accessed.

| Current check | Observed result |
|---|---|
| Retained public/admin/login/installer branding and layout | **1,376 assertions passed**, 31 actual screenshots; no page errors. Covers desktop/phone widths, footer and header geometry, transparent shell, 75% opaque navigation background, static/animated logo selection, controls, root/subdirectory URLs and owner-uploaded branding. |
| Installed background smoke | **529 assertions passed**, 16 actual screenshots; no page errors. Covers 1920, 1440, 390 and 320px viewports at root and `/band`, natural animation progress, stationary stage during scroll, no horizontal overflow, pointer-transparent decoration, Menu/Search/PJAX, and exclusion from admin/login/setup. |
| Accessibility and alternate rendering | Reduced motion, print and forced colors hide the smoke and remove its animation. Returning to ordinary motion restores the running layer. |
| Actual motion recordings | Two **10.000-second H.264 MP4s at 25fps**, 1920×1080 desktop and 390×844 phone. Playback rate is 1, with the 48-second CSS loop unchanged. |
| Public bundle reproduction | `node tools/build_public_assets.mjs --check` passed: **128,396 / 130,000 bytes**, including 1,055 added bytes. JavaScript remains byte-identical. |
| Package unit checks | `python3 tests/package_test.py`: **15 passed**. |
| Local release gate | `python3 tools/release_gate.py` passed, including deterministic repeated packaging, manifest verification and private/development payload exclusions. |
| Source continuity | **347 protected source files byte-identical** to rc.6; only `VERSION`, CSS source and compiled CSS change among the 345 runtime payloads. PHP, JavaScript, original logo/favicon/stage/hero images, migrations and pinned recovery/access files remain unchanged. |

The two browser passes total **1,905 assertions and 47 screenshots**. Three custom-logo screenshots use explicitly labeled synthetic fixtures; the other 44 contain no seeded band content. Admin screenshots use a disposable test owner. Main branding captures use reduced motion; smoke Home captures show naturally running animation. Smoke footer captures retain the controlled 24-second phase used during comparison. The separate phase images are verification samples, not real-time recordings.

The MP4 previews start approximately 12 seconds into the existing 48-second cycle to show its drift, then run at normal speed. They are trimmed from the final ten seconds of actual browser recording without speed changes, overlays or retouching. Smoke remains deliberately restrained; endpoint-phase comparisons detect 78,778 changed desktop background pixels and 11,767 phone pixels above a one-level RGB threshold. These phase samples demonstrate a visible change, not a performance benchmark.

Current reports are `design/background-smoke/main-branding-verification.json`, `background-smoke-verification.json`, `evidence-integrity.json` and `source-continuity.json`. Screenshot/video hashes and all **345 runtime payload hashes** were reverified against the frozen candidate. Final Full ZIP equivalence, cumulative path retention, source publication and observed GitHub workflow state are recorded in the companion delivery receipt. Final documentation may differ from the captured candidate; runtime/code/asset bytes must match. Historical tests and queued/running workflows are not current passes.

## Evidence requirements

The smoke must remain decorative, fixed to the viewport, behind content and outside pointer interaction. Its transforms may drift while the stage background's placement remains unchanged during scroll. Reduced-motion preferences, print and forced colors omit the moving layer. Admin/login/setup remain unaffected.

Deterministic sampling at selected animation phases is for pixel/geometry verification; a user-facing motion preview must identify its normal-speed installed-site origin. Current screenshot manifests record hashes and fixture status. Any final documentation changes after capture require every runtime/code/asset hash to match the final immutable Full ZIP.

## Acceptance limits

Browser checks are desktop/phone viewports in the identified engine, not physical-device or Safari acceptance. No A2 production deployment, real mailbox delivery or merchant transaction is claimed. Owner review and applicable host/provider acceptance remain separate; draft PR 37 stays unmerged.
