# Fixed concert-stage background and visible smoke — 1.1.0-rc.8

## Selected stage and readable content

The user chose option 1, the blue-lit empty concert stage, as the global public background and requested a transparent main container. A decorative body.public-site::before layer stays fixed to the viewport while content scrolls. Its source image and framing remain unchanged.

The stage shows through page gutters and section gaps. Existing contained hero windows keep their separate artwork. Headings, introductions, forms, captions and other exposed content retain dark navy panels with blue borders and gold accents. The outer shell has no visible background, border, shadow, outline or rounded edge. The decorative stage is generated music artwork, not a claim about a band performance.

The public header and footer backplates extend to both viewport edges. Each has 25% background transparency while logo, controls and text retain opacity 1. The header logo keeps its established left anchor. The footer is now compact and content-driven; see FOOTER.md.

## Visible drifting smoke

The user reported that the rc.7 smoke could not be seen. rc.8 increases its visibility and travel while preserving the stationary stage.

One body.public-site::after layer sits above the stage and behind public content. Six elongated blue-gray radial gradients form soft wisps, with peak color alpha between .32 and .56. The whole layer varies from .65 to .90 opacity. This is still translucent decoration, with no flashing or moving foreground panels.

The stage-smoke-drift animation travels from -7% horizontal / +3% vertical translation and -2° rotation to +7% / -3% and +2°. Each direction takes 18 seconds, alternating smoothly for a 36-second round trip. A native CSS -4-second delay starts a newly loaded page already in motion. A 12% overscan keeps edges outside the viewport.

Only transform and opacity animate; the gradients themselves are not animated filters. The layer is fixed, pointer-events:none and outside layout. It adds no JavaScript, scroll listener, video/image asset or network service. Reduced motion, print and forced colors set it to display:none and animation:none. Admin, login and installer remain outside the public-only selector.

A user-facing motion preview must record ordinary page entry at playback rate 1. Do not seek animation.currentTime, accelerate playback or present endpoint phase comparisons as ordinary motion. Current recordings and pixel/geometry observations are identified in TEST_RESULTS.md and the delivery receipt. Visibility still requires visual review of the actual page, not merely detection of changed pixels.

## Responsive behavior and assets

Desktop uses centered cover framing. At widths up to 820px, 90% center framing favors the stage's right-hand drum kit and guitar. Both use the same image without alteration. The public site remains usable without JavaScript. Print and forced colors omit the stage decoration.

| Item | Value |
|---|---|
| Served image | images/backgrounds/spiritual-gorillaz-stage-background.webp |
| Native dimensions | 1672 × 941px |
| Served bytes | 243,104 |
| Encoding | WebP, quality 0.9 |
| Served SHA-256 | 74c0adcadbec3bd07314d641894a1e5a7b8d3d16153e8edf942bd47eee3d9a67 |
| Source master | design/background-stage/blue-lit-concert-stage-master.png |
| Source SHA-256 | f32efb1672a52a4ccd51af237e5f43506b33688cba85f30a055f3ec71e6001dc |
| Export tool | app/tools/build_stage_background.py |

The original PNG, generation prompt and export receipt remain under design/background-stage/. Encoding is at native dimensions without retouching, sharpening, cropping or upscaling. Viewport cover scaling does not create extra source detail; this is not a 4K asset.

## Build, deployment and evidence

Edit assets/css/site-source.css and rebuild with node app/tools/build_public_assets.mjs. The current measured public CSS/JS total is 129,735 / 130,000 bytes. JavaScript is unchanged. Relative asset URLs work at root and in subdirectories.

The cumulative Full ZIP includes compiled CSS and original artwork; the PHP host needs no Node or image service. Installed rc.1 through rc.7 use normal guarded same-schema Browser upgrades when checks pass. No migration or reinstall is required.

Historical rc.5 background, rc.6 branding and rc.7 smoke results remain under history/. Current results must identify the frozen candidate and verify every runtime hash against the final Full ZIP if final documentation changes after capture. Desktop/phone Chromium checks are not physical-device or Safari acceptance.
