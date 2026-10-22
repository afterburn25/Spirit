# Selected blue-lit concert-stage background

The user selected **option 1** for a global background that remains stationary while public-page content scrolls over it. This is newly generated decorative music artwork, not a photograph of a real Spiritual Gorillaz performance.

## Source and export

- Master: `blue-lit-concert-stage-master.png`, unchanged native **1672 × 941 px** PNG.
- Source SHA-256: `f32efb1672a52a4ccd51af237e5f43506b33688cba85f30a055f3ec71e6001dc`.
- Deployed asset: `images/backgrounds/spiritual-gorillaz-stage-background.webp`.
- Export: **243,104 bytes**, same native dimensions, WebP quality 0.9.
- Output SHA-256: `74c0adcadbec3bd07314d641894a1e5a7b8d3d16153e8edf942bd47eee3d9a67`.
- Built-in image generation was used for the original art. The native-size browser export changes the encoding only; it does not retouch, crop, sharpen or upscale the source. The requested generation size was not the returned size; do not describe this artwork as 2560px or 4K.

Reproduce the committed WebP with Playwright, Pillow (inspection only), and Chromium 143.0.7499.4:

```sh
python3 tools/build_stage_background.py --browser /path/to/chromium
```

The shared PHP host serves the committed WebP directly and needs no build tools. The source master and export tool are development assets excluded from the installation ZIP.

## Exact generation prompt

```text
[image_picker_background_preview]
Use case: photorealistic-natural.
Asset type: artwork-only preview for a fixed global background on the Spiritual Gorillaz band website.
Primary request: create a polished cinematic empty concert stage background, in a wide 16:9 landscape composition, native 2560 by 1440 detail if supported.
Scene: a real-looking music venue stage with tasteful blue beams shining through light atmospheric haze, dark guitar amplifiers near the sides, a classic drum kit toward the back, a microphone stand and electric guitar on a stand near opposite outer edges. No people and no audience.
Composition: build depth across the full frame, with the most recognizable equipment and crisp details toward the lower and outer edges. Leave the middle 55 percent quieter and darker, with continuous atmosphere rather than an empty rectangular box, so website content could later scroll over it. Equipment must remain recognizable when viewed as artwork.
Lighting and palette: deep black and midnight navy, rich cobalt blue stage light, restrained warm gold highlights on metal and cymbals. Premium live-music photography, detailed materials, natural perspective, sharp equipment silhouettes, controlled contrast, subtle haze.
Constraints: standalone background artwork only. No website, no page layout, no text, no lettering, no logos, no people, no watermark, no border, no simulated browser. Avoid a flat black blank image and avoid overexposed light beams.
```

## Presentation

The selected stage appears behind the existing real HTML site, not as a flattened page mockup. Existing page-specific contained heroes keep their own artwork. The fixed layer is decorative and cannot intercept input. Phone positioning favors the right-hand drum kit and stage equipment. See `docs/STAGE_BACKGROUND.md` and the current verification checkpoint for actual page results.

