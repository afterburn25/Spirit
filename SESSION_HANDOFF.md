# Resume — 1.1.0-rc.6 approved identity and seamless surfaces

Repository **afterburn25/Spirit**. Continue from **`work/1.1.0-analytics-and-navigation`**, [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Baseline delivered rc.5 source is `6c5248e1cd6033ce0b24bd9db97621431908fa4e`; inspected live main is `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`. Preserve earlier artwork, analytics, fixed background and complete cumulative package. Do not merge before owner approval.

## Current user decisions

The approved horizontal sculpted-gold crowned gorilla and two-line SPIRITUAL GORILLAZ logo must replace the old active bundled identity everywhere: public header/footer, admin sidebar and admin login. The earlier approved kit had not been integrated into rc.5. Use the exact transparent assets, including the stationary six-second navbar shimmer and static reduced-motion fallback; do not substitute the older Concept 2 mark.

Keep the header's existing logo left anchor while allowing the horizontal art to use its available track. The navigation background must be 25% transparent (75% opaque) while its logo, links and controls remain fully opaque. The footer surface must reach both screen edges. The transparent main shell must have no border, shadow, outline or rounded edge. Individual navy readability panels and the fixed blue-lit stage remain.

Use the same complete transparent crowned-gorilla head for the favicon, with maximum proportional framing and fresh active filenames. The earlier 98% fit already occupied nearly the full height, so the framing improvement is modest. Never claim a larger browser-controlled tab slot, distort the face or remove its crown.

## Implementation

- `Branding::bundledMark()` is a database-independent shared helper for approved static/animated assets; public `Branding::mark()` still prefers owner-published media.
- `images/branding/spiritual-gorillaz-sculpted-gold.png` and `spiritual-gorillaz-sculpted-gold-nav.webp` are byte-identical to the selected kit originals. All active URLs are versioned and installation-prefix aware.
- Public header/footer and enhanced-navigation branding templates already use the shared helper. Staff/sidebar/login/setup templates now use it, with the scoped `assets/css/admin-branding.css`.
- Public CSS expands the default horizontal mark within the available track, extends the footer surface and removes the shell edge; custom-image sizing and contained heroes retain their rules.
- Prior logo/icon paths remain for cumulative compatibility. The pinned recovery root-file allowlist does not permit a new root `favicon.ico`; do not add one or edit recovery code merely for this presentation change. Use shared links to new assets under `images/icons`.
- Owner forms/authentication, migrations, analytics, JavaScript, stage master/runtime asset and page hero sources remain unchanged.

## Verification and delivery

The public bundle is 127,341 / 130,000 bytes. All 15 package tests, 38 branding and 109 admin-navigation unit checks, 50 favicon checks and the software release gate passed. All 285 protected whole files are unchanged from rc.5. Current observed results belong in `docs/TEST_RESULTS.md`, `design/branding-refresh/`, `design/favicon/` and the companion delivery receipt. Historical rc.5 outcomes are archived under `docs/history/` and must not be labeled rc.6 passes.

The focused installed-browser review passed 1,374 checks on actual root and `/band` installations, covering public desktop/phone header/footer, admin sidebar, login/installer, reduced motion, cache paths, custom publication/withdrawal and enhanced navigation. Eleven public widths per installation passed. The main set contains 31 screenshots; a separate 180-check/24-capture admin paint recheck supplied one corrected phone-sidebar screenshot with explicit same-candidate provenance. No runtime change was needed for that capture race. Screenshots must be actual application pages. Clearly label any synthetic fixtures used only in disposable installations; never publish those fixtures as band content.

The cumulative Full ZIP is immutable. Final documentation may be updated after captures only when every runtime/code/asset hash is compared with the final ZIP and that provenance is stated. Preserve previously delivered archives. Record the published commit/tree, ZIP hashes, package continuity, screenshot provenance and actually observed Actions state; pending workflows are not passes.

## Preserved deployment

All database migrations and pinned recovery/access files remain unchanged. Installed rc.1 through rc.5 use normal same-schema Browser upgrades when checks pass. For 1.0.x retain additive schema-update instructions. Preserve private configuration/encryption identity, original owner/database, media and removed installer. No reinstall, hosting token, setup code, native ticketing or automatic database provisioning.

Target-host/provider acceptance is separate; no production deployment or live payment is authorized by this presentation change.
