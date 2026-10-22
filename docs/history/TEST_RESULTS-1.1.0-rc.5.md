# Verification — 1.1.0-rc.5 stage background and readable content

## Scope and baseline

Baseline: delivered rc.4 source `315c45d2968b6b9d1eacc5820b6994aa95cae50c`. This update adds the selected fixed blue-lit stage, transparent public shell, screen-wide header surface and dark containers for exposed content. Runtime changes are the new WebP, public CSS/source bundle and small semantic wrappers in four public templates and the release-card helper. Existing form actions, CSRF fields, conditions and escaping are preserved. No JavaScript, database, analytics, controller, payment, installer or recovery behavior changes are introduced.

The exact rc.4 verification and delivered receipt are archived in `history/TEST_RESULTS-1.1.0-rc.4.md` and `history/VERIFICATION-1.1.0-rc.4.json`. Historical results are not new rc.5 regression runs.

## Observed current checks

| Check | Result |
|---|---|
|PHP syntax|Passed for all five changed PHP files: the four public templates and `app/PublicSite.php`. Their diff contains only semantic wrapper/class additions.|
|Production bundle|Rebuild and `--check` passed. Loaded public CSS/JS is **126,436 / 130,000 bytes**; generated CSS is 109,999 bytes. All JavaScript is unchanged.|
|Artwork export|Native 1672 × 941px source exported to 243,104-byte WebP at quality 0.9. The source PNG is byte-identical; there is no crop, retouching or upscale.|
|Header geometry|Actual installed-candidate comparison at 1440, 1920 and 390px passed. Logo, navigation, search, booking button, menu toggle and header content rectangles match the pre-extension baseline within 0.05px. The backplate reaches both viewport edges.|
|Focused application browser review|**1,120 checks passed** on actual root and `/band` installations in Chromium 143.0.7499.4. The report includes six scroll/header comparisons and 86 page/panel observations with 110 visible near-opaque surfaces. No uncaught browser errors were observed.|
|Actual screenshots|**48 captures:** 24 unseeded primary views and 24 clearly labeled synthetic supplemental views. Desktop widths are 1440 and 1920px; phone width is 390px. Root visually inspected wide/desktop Home, phone Search and populated release/cart surfaces; the capture reviewer inspected the remaining representative views.|
|Packaging unit tests|All **15 packaging tests passed**.|
|Software release gate|Passed, including two byte-identical cumulative builds, payload manifest verification, required-file and private-file checks. Final package continuity and documentation/runtime equivalence are recorded separately in the delivery receipt.|
|Source continuity|All **80 audited whole files** are byte-identical to rc.4, including all 8 migrations, 5 pinned recovery/access files, 8 favicon assets, 16 JavaScript files, dedicated analytics PHP and retained dependencies/integration files. The expected `PublicSite.php` change contains only release-card wrapper tags; its complete rendering/analytics initialization method is byte-identical.|
|Whitespace|`git diff --check` passed.|

The native source, exact generation prompt, export receipt, source-continuity report and final browser report are retained under `design/background-stage/` in development source. See `STAGE_BACKGROUND.md` for the presentation decisions.

## Browser and screenshot provenance

`tests/capture_stage_background.py` uses genuine installations of a cumulative candidate ZIP at both `/` and `/band`. It records route/viewport coverage, viewport-fixed image geometry and sampled pixels across scroll, shell transparency, header geometry, panel surfaces, text containment, image decoding, menu/search behavior, native no-JavaScript operation and public-only decoration.

The primary page sweep is unseeded. Supplemental release, show, member, page, media, press, product, search and cart views use clearly labeled synthetic fixtures only inside the disposable test installation. Those fixtures are not included in the installation package and are not approved band content. No production website or visitor data is accessed.

All six viewport/install combinations scrolled 600px with the fixed-layer geometry unchanged. At matching screen coordinates, all 96 desktop samples stayed within the documented 6/255 RGB tolerance, and the 1920px samples were exactly unchanged. On phones, 90 of 96 samples were within tolerance; remaining small differences arise from existing content shadows moving over the scene. This is not a claim that every rendered phone pixel stays byte-identical while content scrolls.

The final browser report records the candidate ZIP and every runtime/code/asset hash. Final documentation can be updated after captures; the delivery receipt must compare every recorded runtime hash to the final immutable Full ZIP and identify that provenance accurately. The screenshot review board must use these actual captures, not generated page mockups.

## Publication and acceptance

Published commit/tree, final Full ZIP hashes, package continuity, screenshot provenance and observed GitHub Actions state belong in the companion delivery receipt. Pending workflows must not be labeled passed.

These desktop/phone viewport checks use Chromium and are not Safari or physical-device acceptance. No A2 production deployment, real mailbox delivery or merchant transaction was performed. Owner review and applicable target-host/provider acceptance remain separate; the PR remains draft and unmerged.
