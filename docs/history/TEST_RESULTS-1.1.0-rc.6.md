# Verification — 1.1.0-rc.6 approved identity and seamless surfaces

## Scope and baseline

Baseline: delivered rc.5 source `6c5248e1cd6033ce0b24bd9db97621431908fa4e`. This update installs the actual approved logo throughout public/admin/login/setup, maximizes proportional favicon framing with fresh active URLs, extends the footer across the viewport, removes the outer shell edge and makes the navigation background 25% transparent. It preserves the fixed stage, contained heroes, individual readability panels, owner-uploaded public branding and existing data/workflows.

Historical rc.5 tests and its delivered receipt are archived in `history/TEST_RESULTS-1.1.0-rc.5.md` and `history/VERIFICATION-1.1.0-rc.5.json`. Those historical outcomes are not new rc.6 regression runs.

## Observed current checks

| Check | Result |
|---|---|
| Branding unit suite | 38 checks passed, including unchanged draft/publication guards, exact new asset paths and native static fallback. |
| Administration navigation suite | 109 checks passed, preserving grouped destinations, separate Analytics and role boundaries. |
| Packaging unit suite | All 15 tests passed, including deterministic payloads, private-state exclusion and immutable-version refusal. |
| Favicon source/export/compatibility | 50 checks passed. Independent Chromium export reproduced all native PNG/ICO and compatibility bytes; original source, crown, proportions, transparency and pinned recovery remain unchanged. |
| Approved logo identity | Both deployed files are byte-identical to the approved static PNG and navbar WebP kit originals. |
| Production bundle | Rebuild and `--check` passed after the final transparency change: **127,341 / 130,000 bytes**, including 110,904-byte compiled CSS. All JavaScript is unchanged. |
| Installed-browser review | **1,374 checks passed** in Chromium 143.0.7499.4 on actual root and `/band` installations, with 31 screenshots. All 11 public widths per installation passed, from 320 through 1920px. No uncaught browser errors. |
| Admin paint recheck | **180 additional checks passed**, with 24 captures at 1440, 390 and 320px across root and `/band`. Painted-pixel verification confirms the sidebar logo is visible; a single premature main capture was replaced by its recorded same-candidate recapture. |
| PHP syntax | All six changed PHP files passed, comprising five runtime files plus the branding unit test. |
| Source continuity | All **285 protected whole files** are byte-identical to rc.5. All 8 migrations, 5 pinned recovery/access files, 17 access-rule files, 16 runtime JavaScript files, analytics, original stage/hero artwork and unrelated application/controllers are preserved. Only the bundled-output method changes within `Branding.php`; the other 11 methods are identical. |
| Software release gate | Passed, including two byte-identical cumulative builds, exact payload manifests and private-file exclusions. Final ZIP/source/runtime comparisons are recorded separately in the delivery receipt. |
| Whitespace | `git diff --check` passed. |

The main browser report includes 22 public geometry observations, 114 logo checks, four complete favicon-link response/decode sets, and six custom-logo workflow combinations. Widths are 320, 360, 390, 820, 821, 880, 1000, 1100, 1280, 1440 and 1920px. Navigation background alpha is exactly 0.75 while logo/controls remain at opacity 1. Header/footer backplates reach both edges without overflow; shell background, borders, shadow, outline and radius are clear. The prior logo left anchors at 1440, 1920 and 390px are retained.

Actual private preview, publication, a portrait replacement, withdrawal, native no-JavaScript pages and persistent audio during enhanced navigation passed. Reduced-motion pages select the static original, and changing the preference updates the selected image after browser decoding.

A screenshot review caught one phone admin drawer image before its logo painted. A same-candidate focused recheck confirmed approximately 6,070–6,081 gold pixels in all six tested drawer views. The capture tool now waits for decoded frames and verifies painted pixels. The original incomplete screenshot is not delivered as a passing view; the replacement and its source manifest are explicitly recorded. No runtime code was changed to resolve this capture race.

## Favicon size evidence

The unchanged head is fitted at 100% rather than 98% of its visible height, a 2.04% scale change. At native 16px, its visible 14 × 16 and solid-core 12 × 14 bounds are unchanged after pixel rounding; alpha-weighted area rises from 50.153% to 52.292%. At 32px, its visible bounds remain 28 × 32, while the solid core grows from 25 × 29 to 26 × 30 and weighted area rises from 50.468% to 52.518%. This is a modest improvement. A website cannot enlarge the browser's own icon controls.

`design/favicon/maximum-fit-rc6/` records the original/master hashes, exact export geometry, all size measurements and asset checks. Its review board shows native-size image samples plus clearly labeled nearest-neighbor pixel enlargements; it is not fabricated browser chrome. Shared links use new `images/icons/crowned-gorilla-full-fit/` paths with the release version. Old cumulative paths remain available. No root payload is added because the pinned recovery allowlist excludes it.

## Browser and screenshot provenance

`tests/capture_branding_refresh.py` exercises actual disposable root and `/band` installations of the cumulative candidate. The main set contains 28 captures without seeded band content and 3 explicitly synthetic custom-logo views; administrative captures use a disposable owner. The 24 supplemental admin views are recorded separately. A separate synthetic phase tests owner-uploaded branding, preview/publication/withdrawal and persistent audio during enhanced navigation. Synthetic data is not included in the installation package or published as band content.

The capture manifest records each screenshot hash, route, viewport, scroll position, motion preference observations, icon responses and runtime/code/asset hashes. Any documentation updates after capture require exact comparison of every recorded runtime hash to the final immutable Full ZIP; the delivery receipt states that provenance explicitly.

## Publication and acceptance

Published commit/tree, final Full ZIP hashes, cumulative package continuity, screenshot provenance and actually observed GitHub Actions state belong in the companion delivery receipt. Pending workflows are not passes.

These checks use Chromium desktop/phone viewports; they are not physical-device or Safari acceptance. No A2 production deployment, real mailbox delivery or merchant transaction is claimed. Owner review and applicable host/provider acceptance remain separate; draft PR 37 stays unmerged.
