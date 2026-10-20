# Fixed concert-stage background and drifting smoke — 1.1.0-rc.7

## Selected artwork and behavior

The user chose option 1, the blue-lit empty concert stage, and explicitly requested a transparent main container. The public site's outer `.site-shell` now has a transparent background. A single decorative `body.public-site::before` layer stays fixed to the viewport as the document scrolls.

The stage can show through page gutters and spaces between sections. Existing contained hero windows keep their separate artwork, and readable cards, forms, the header, footer and persistent player retain their own surfaces. The decorative stage is generated music artwork, not a claim about an actual band performance.

The layer uses `position: fixed; inset: 0`, isolated stacking behind the page, and `pointer-events: none`. It requires no scroll listener or animation and does not intercept search, menu, form, link or keyboard interaction. Administration, staff access and installation layouts do not receive the public-only layer.

## Subtle drifting smoke

The requested rc.7 continuation adds one separate `body.public-site::after` layer above the fixed stage and behind the public content. Six soft elongated radial gradients form two broad blue-gray wisps. The source image and its framing remain unchanged; only the stage layer's stacking position moves from -1 to -2 so smoke can occupy -1.

`stage-smoke-drift` runs a smooth 48-second loop. It moves from a -2% horizontal / +1.5% vertical translation and -1° rotation to the opposite pose at halfway, then returns continuously. Layer opacity moves from 0.65 to 0.9 and back; the gradient colors themselves use only 0.05–0.16 alpha, keeping the smoke translucent. The gradients do not repaint their shape through animated filters; only transform and opacity animate. A bounded 6% overscan prevents the layer edge from appearing during drift.

The layer is fixed, `pointer-events: none`, and outside page layout. It adds no JavaScript, scroll listener, image/video asset or network dependency. The stage image remains stationary while content scrolls; the decorative smoke drifts independently behind readable panels and controls. Reduced-motion preferences, print and forced-colors modes set the smoke to `display: none` and `animation: none`. Admin, login and setup remain outside the public-only selector.

The compiled CSS increase is 1,055 bytes. Current installed-browser phase/pixel checks and actual normal-speed desktop/phone recordings are identified in `TEST_RESULTS.md` and the delivery receipt. Deterministic phase samples are verification aids and must not be presented as naturally elapsed animation footage.

## Full-width header and protected content

The header's background and lower border extend to both viewport edges through a non-interactive backplate. Its existing content container, grid and left logo anchor remain in place. In rc.6 the approved horizontal logo fills available first-track space for legibility. Search, Book the Band, the phone menu and sticky behavior retain their controls. The navigation backplate is 25% transparent (75% opaque), with fully opaque logo, links and controls. The footer now uses a similar viewport-wide surface, preserving its inner alignment. The outer transparent shell has no border, shadow, outline or rounded edge; a transparent layout spacer preserves the former border width without drawing a line.

Previously exposed content receives separate dark navy panels with subtle blue borders and gold accents. Existing semantic wrappers provide most of the grouping; small markup wrappers group page introductions and release captions where required. The treatment covers loose section headings, search/account/checkout introductions, detail-page copy, media captions and public content areas. Already-contained hero copy, cards, forms and notices retain their surfaces. The transparent outer shell and open gaps continue to expose the stage.

## Responsive and accessible presentation

Desktop framing uses centered `cover`. At widths up to 820px, `90% center` framing favors the stage's right-hand drum kit and guitar. This is CSS viewport framing of the same artwork; the source file is unchanged.

The normal website remains usable without JavaScript. Print and forced-colors modes omit the decorative background. The stage itself does not animate. The separate smoke layer and approved navbar shimmer both honor reduced-motion preferences; automatic audio is not introduced. The retained near-opaque content surfaces provide readability over the scene.

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

The CSS source is `assets/css/site-source.css`. Rebuild its committed production bundle using `node tools/build_public_assets.mjs`; the measured public CSS/JS total is **128,396 / 130,000 bytes**. Existing JavaScript and public header markup are unchanged. The relative image URL works for root and subdirectory installations.

## Deployment and verification

The cumulative Full ZIP includes the ready-to-use WebP and rebuilt CSS. The shared PHP host needs no image conversion or Node build. Installed rc.1 through rc.6 use the normal guarded same-schema Browser upgrades path when its integrity and recovery checks pass. No migration or reinstall is needed for this presentation update.

In rc.5, `tests/capture_stage_background.py` passed 1,120 checks on actual disposable installations at root and `/band`, with 48 genuine application screenshots. Its report records fixed image geometry, sampled pixels at matching viewport positions before/after scroll, transparent shell, full-width header geometry, readable panels, route coverage, interaction checks and screenshot provenance. All six header comparisons retain the baseline logo/control rectangles at 1440, 1920 and 390px. Primary screenshots are unseeded; supplemental populated views are clearly marked synthetic. Those historical results are preserved under `history/TEST_RESULTS-1.1.0-rc.5.md`. Historical rc.6 captures verify the approved logo, footer and borderless shell. Current rc.7 checks cover smoke plus the retained presentation; see `TEST_RESULTS.md` and the companion delivery receipt for current results and runtime equivalence. Desktop/phone Chromium checks are not physical-device or Safari acceptance.

