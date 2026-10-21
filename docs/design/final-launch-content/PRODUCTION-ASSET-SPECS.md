# Production asset specifications — final launch

These specifications convert the remaining visual blockers into exact deliverables for the existing RC8 layouts. They do **not** authorize another redesign, synthetic member portraits or reuse of rejected full-page mockups.

## 1. Home hero artwork

The current public CSS renders the hero artwork as a large cover image occupying about 64% of the desktop hero, with a desktop hero height of roughly 700–770 px and aggressive mobile cropping. The existing fallback `images/gorilla-hero.webp` is only a small concept asset and is not a production master.

### Required composition

- crowned gorilla subject on the **right half/right third**;
- dark navy/black environment with restrained blue stage/rim lighting;
- gold crown/detail accents;
- **no embedded headline, buttons, navigation or page mockup**;
- preserve dark negative space on the **left** for the existing HTML headline/buttons;
- avoid important subject detail at the extreme top, bottom or far-right edge because mobile uses a tighter crop;
- cinematic photographic/illustrative finish consistent with the approved reference, not cartoon styling.

### Delivery

Preferred master:
- 2000 × 1400 px or larger;
- WebP or high-quality JPEG;
- sRGB;
- no transparency required.

Minimum practical master:
- 1600 × 1100 px.

Recommended public derivative:
- WebP, visually lossless/high quality, ideally under ~500 KB after optimization if it can be achieved without visible degradation.

Suggested filename:
- `spiritual-gorillaz-hero-master.webp`

The CMS page artwork/focal control should be used instead of replacing the built-in fallback file in place. Preserve the previous image until the reviewed master is selected and previewed.

## 2. Brand/logo master

The site already supports owner-managed branding. Supply a clean production master rather than baking branding into hero artwork.

Preferred:
- SVG with transparent background for the primary logo/wordmark;
- if only raster is available, transparent PNG/WebP at least 2000 px wide;
- retain enough clear space around crown/gorilla/wordmark details for header use;
- provide a version readable on very dark navy/black backgrounds;
- do not include a full webpage background.

Suggested filenames:
- `spiritual-gorillaz-logo.svg`
- `spiritual-gorillaz-logo-2000.png` (fallback/preview if needed)

The existing Branding workflow should preview the saved draft before publish.

## 3. Member portraits

Only use files explicitly labelled by identity. Do not infer identity from appearance.

For each of:
- Chad Lindsey;
- Ron Green;
- Charlie Savell.

Preferred portrait:
- 4:5 vertical;
- 1600 × 2000 px or larger;
- minimum 1000 × 1250 px;
- subject centered with comfortable headroom;
- neutral/dark/cinematic background preferred;
- no text overlay, watermark or social-media UI;
- WebP or JPEG, sRGB.

Suggested filenames:
- `chad-lindsey-portrait.webp`
- `ron-green-portrait.webp`
- `charlie-savell-portrait.webp`

The current member cards use a 4:5 crop. A labelled source image may be cropped in the Media workflow, but the original must remain identifiable.

## 4. Part 3 poster and playback

The selected Part 3 poster already exists as an unretouched 1920 × 1080 full frame and is suitable for a 16:9 video card. Do not regenerate it merely for resolution.

Still missing:
- actual approved HTTPS YouTube or Vimeo URL;
- exact public title;
- recording-specific performer/venue/date credits only when known.

Working private metadata remains:
- Title: `Performance — Part 3`
- Slug: `performance-part-3`
- Description: `Selected performance recording — Part 3.`

Do not map ANMP0002/0006/0011 titles to Part 3 without evidence.

## 5. Shows

No artwork or placeholder event is required when no genuine show exists. For each real launch show, collect:
- title/event name;
- venue;
- local start date/time and timezone;
- city/state;
- public/invitation-only status if known;
- venue's existing external ticket URL if tickets exist;
- optional approved event image.

Never fabricate an event to make the layout look populated.

## 6. Merchandise

If merchandise is not genuinely ready, keep the store closed. A launch product should have:
- approved product name;
- real product photograph;
- SKU/variant labels;
- real price;
- real available stock;
- size/color options where applicable;
- care/description;
- reviewed shipping/returns/tax operating policy.

Product images should be at least 1400 × 1400 px where possible, cleanly framed, without marketplace UI or watermarks.

## 7. Final visual acceptance

After assets are placed:
- preview Home hero at desktop, tablet and ~390 px mobile width;
- verify hero subject remains visible and does not collide with HTML headline/buttons;
- verify logo readability in header/mobile menu;
- verify all three portraits are correctly labelled and crop well at 4:5;
- verify Part 3 poster at 16:9;
- confirm no rejected mockup or placeholder/demo artwork is published.

No software release is required merely to upload these assets through the existing CMS/Branding/Media workflows.
