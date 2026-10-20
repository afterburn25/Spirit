# Fixed concert-stage background — 1.1.0-rc.5

## Selected artwork and behavior

The user chose option 1, the blue-lit empty concert stage, and explicitly requested a transparent main container. The public site's outer `.site-shell` now has a transparent background. A single decorative `body.public-site::before` layer stays fixed to the viewport as the document scrolls.

The stage can show through page gutters and spaces between sections. Existing contained hero windows keep their separate artwork, and readable cards, forms, the header, footer and persistent player retain their own surfaces. The decorative stage is generated music artwork, not a claim about an actual band performance.

The layer uses `position: fixed; inset: 0`, isolated stacking behind the page, and `pointer-events: none`. It requires no scroll listener or animation and does not intercept search, menu, form, link or keyboard interaction. Administration, staff access and installation layouts do not receive the public-only layer.

## Full-width header and protected content

The header's background and lower border extend to both viewport edges through a non-interactive backplate. Its existing content container, grid, logo size and padding remain in place. This gives the user a screen-wide navigation bar without moving the logo. Search, Book the Band, the phone menu and sticky behavior retain their controls and geometry.

Previously exposed content receives separate dark navy panels with subtle blue borders and gold accents. Existing semantic wrappers provide most of the grouping; small markup wrappers group page introductions and release captions where required. The treatment covers loose section headings, search/account/checkout introductions, detail-page copy, media captions and public content areas. Already-contained hero copy, cards, forms and notices retain their surfaces. The transparent outer shell and open gaps continue to expose the stage.

## Responsive and accessible presentation

Desktop framing uses centered `cover`. At widths up to 820px, `90% center` framing favors the stage's right-hand drum kit and guitar. This is CSS viewport framing of the same artwork; the source file is unchanged.

The normal website remains usable without JavaScript. Print and forced-colors modes omit the decorative background. No new animation or automatic audio is added. The retained near-opaque content surfaces provide readability over the scene.

## Asset and source

| Item | Value |
|---|---|
| Served image | `images/backgrounds/spiritual-gorillaz-stage-background.webp` |
| Native dimensions | 1672 × 941px |
| Served bytes | 243,104 |
| Encoding | WebP, quality 0.9 |
| Served SHA-256 | `74c0adcadbec3bd07314d641894a1e5a7b8d3d16153e8edf942bd47eee3d9a67` |
| Source master | `design/background-stage/blue-lit-concert-stage-master.png` |
| Source SHA-256 | `f32efb1672a52a4ccd51af237e5f43506b33688cba85f30a055f3ec71e6001dc` |
| Export tool | `tools/build_stage_background.py` |

The original generated PNG, exact generation prompt and export receipt are retained under `design/background-stage/`. The browser export changes the encoding at the original dimensions: no retouching, sharpening, cropping or upscaling. Displays can scale the image to cover their viewport; this does not create extra source detail. Do not describe the asset as 4K.

The CSS source is `assets/css/site-source.css`. Rebuild its committed production bundle using `node tools/build_public_assets.mjs`; the measured public CSS/JS total is **126,436 / 130,000 bytes**. Existing JavaScript and public header markup are unchanged. The relative image URL works for root and subdirectory installations.

## Deployment and verification

The cumulative Full ZIP includes the ready-to-use WebP and rebuilt CSS. The shared PHP host needs no image conversion or Node build. Installed rc.1/rc.2/rc.3/rc.4 use the normal guarded same-schema Browser upgrades path when its integrity and recovery checks pass. No migration or reinstall is needed for this presentation update.

`tests/capture_stage_background.py` passed 1,120 checks on actual disposable installations at root and `/band`, with 48 genuine application screenshots. Its report records fixed image geometry, sampled pixels at matching viewport positions before/after scroll, transparent shell, full-width header geometry, readable panels, route coverage, interaction checks and screenshot provenance. All six header comparisons retain the baseline logo/control rectangles at 1440, 1920 and 390px. Primary screenshots are unseeded; supplemental populated views are clearly marked synthetic. See `TEST_RESULTS.md` and the companion delivery receipt for the results and runtime equivalence. Desktop/phone Chromium checks are not physical-device or Safari acceptance.

