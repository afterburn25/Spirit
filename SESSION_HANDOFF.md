# Resume — 1.1.0-rc.5 stage background and readable content

Repository **afterburn25/Spirit**. Continue from **`work/1.1.0-analytics-and-navigation`**, [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Baseline delivered rc.4 source is `315c45d2968b6b9d1eacc5820b6994aa95cae50c`; live main remains `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`. Preserve earlier artwork, analytics and favicon work. Do not merge before owner approval.

## Current user decision

Use **option 1, the blue-lit concert-stage image**, as a global background that remains fixed while public page content scrolls past it. The user explicitly requested that the main global container containing the inner sections be transparent. This is implemented by a transparent `.site-shell` and one fixed `body.public-site::before` layer.

The user also requested a screen-wide header without moving the logo, plus individual containers for exposed content so text does not clash with the stage. The header backplate extends to both viewport edges with the existing inner layout preserved. Reusable dark navy surfaces group exposed headings, copy, captions and callouts while keeping the gaps transparent.

The artwork picker was not visible to the user. Direct image links/previews were provided and the user selected option 1. Future visual handoffs should include ordinary clickable PNG screenshots rather than relying solely on a choice widget.

## Implementation

- Original master: `design/background-stage/blue-lit-concert-stage-master.png`, native 1672 × 941px, unchanged SHA-256 `f32efb1672a52a4ccd51af237e5f43506b33688cba85f30a055f3ec71e6001dc`.
- Runtime: `images/backgrounds/spiritual-gorillaz-stage-background.webp`, 243,104 bytes, SHA-256 `74c0adcadbec3bd07314d641894a1e5a7b8d3d16153e8edf942bd47eee3d9a67`.
- `tools/build_stage_background.py`: native-size browser WebP export only; no retouching/cropping/upscaling.
- `assets/css/site-source.css` and generated `site.css`: fixed, non-interactive public decoration; transparent shell; full-width header surface; readable content panels; right-stage framing on phones; print/forced-colors omission.
- Small semantic wrappers in four public templates and `PublicSite::releaseCard()` group exposed content. Forms, actions, CSRF fields, content conditions and escaping are preserved.
- Existing contained hero art, JavaScript, favicon links, analytics and admin/installer surfaces are preserved.

The new stage is generated decorative artwork and should not be described as a real band performance. Do not claim 4K source resolution.

## Verification and delivery

Asset/export inspection, all 15 package tests, PHP syntax for all five changed files, bundle reproducibility and the deterministic software release gate passed. Public CSS/JS totals **126,436 / 130,000 bytes**. The final installed-application run passed **1,120 focused checks**, with **48 actual screenshots**: 24 unseeded primary views and 24 synthetic supplemental views. Root and `/band` checks cover desktop/phone layouts, fixed scrolling, readable panels, search/menu, no-JavaScript behavior, print/forced-colors and public-only decoration. No uncaught browser errors were observed.

All six full-width-header comparisons at 1440, 1920 and 390px confirm unchanged logo/navigation/control rectangles within 0.05px, a backplate reaching both screen edges and no horizontal overflow. The source-continuity audit confirms all 80 audited whole files remain byte-identical, including migrations, pinned recovery/access, favicon and JavaScript assets and dedicated analytics code. `PublicSite.php` has only the expected release-card wrapper tags; its rendering/analytics initialization method is unchanged.

Use `tests/capture_stage_background.py` with the existing non-root disposable SQL test environment. It checks root and subdirectory routes, desktop/phone fixed-background pixels and scrolling, transparency, header geometry, menus/search, native no-JavaScript behavior and admin exclusion. Primary screenshots use actual application pages with unseeded public content. Supplemental populated views are explicitly labeled synthetic and exist only in the disposable test database. If docs change after capture, compare every runtime/code/asset hash with the final package and document that provenance.

The companion delivery includes the new immutable cumulative Full ZIP, actual desktop/mobile screenshots and a review board. Packaging uses `tools/package.py` with Pillow 12.3.0 hero generation. Previously delivered rc.4 artifacts remain unchanged. The final receipt records the source commit/tree, ZIP hashes, screenshot provenance, package continuity and observed GitHub state. Do not reinterpret historical or pending Actions results as passes on a new commit.

## Preserved deployment

All database migrations and pinned recovery/access files remain unchanged. Installed rc.1/rc.2/rc.3/rc.4 use normal same-schema Browser upgrades when checks pass. For 1.0.x retain additive schema-update instructions. Preserve private config, encryption identity, original owner/database, media and removed installer. No reinstall, hosting token, setup code, native ticketing or automatic database provisioning.

Target-host/provider acceptance is separate; no production deployment or live payment is authorized by this presentation change.
