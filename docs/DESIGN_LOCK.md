# Approved visual design — locked

The user selected the FIRST cinematic Spiritual Gorillaz composite, reuploaded as **Spiritual Gorillaz Website Concept Moodboard.png**, and explicitly said **no more mockups**. Do not substitute the six-concept alternatives or another project.

Original image SHA-256: `4e76c31eadeb7720701c7d8bd94f484824eb5eb9dd4ba04479393914efad033e`. Original dimensions: 1214 × 1295. The original PNG is a conversation attachment, not a tracked repository file.

## Required modification to the concept

The approved composite is the visual-direction reference, **not a pixel-for-pixel boxed layout**. The production site must preserve its black/deep-navy, gold, blue-stage-light and crowned-gorilla identity while applying all of the following owner-requested changes:

- Every primary **hero canvas** spans the available viewport from left edge to right edge.
- The **source artwork itself must not be stretched to the viewport**. Center each production master and cap it at native width.
- On screens wider than the native master, extend both sides with a seamless matching dark/navy atmosphere.
- The complete right-side source artwork remains visible at a zoomed-out scale; do not crop the crown, face, headphones, performers, equipment or other critical subject matter merely to fill a short banner.
- Do not use a blurred duplicate, stretched copy, enlarged low-resolution layer or obvious split panel behind the sharp artwork.
- Keep real HTML headings, body copy and controls inside the artwork's left negative-space region. They must not sit outside the centered image or resemble a separate rectangular box overlapping it.
- A very light outer-edge feather may blend a centered native master into the extension on wider screens; it must not obscure important subject detail.
- Home retains the dimensional `MUSIC WITH A HIGHER PURPOSE` treatment.
- Public navigation remains large, centered and sticky, and Search remains visible and opens its dropdown rather than navigating away.
- On mobile, place the copy first and the complete native artwork beneath it. Do not hide the modification by switching back to a cropped background image.
- Apply the same rule consistently to Home, Music, Shows, Band, Media, Press, Booking and Merch.

A build that resembles the moodboard but ignores these modifications is not accepted.

## Tracked reference and assets

- `design/approved-reference-thumbnail.webp`: a derived 300-pixel-wide thumbnail of the selected composite. It preserves the overall reference, not its small text detail. It does NOT have the original PNG hash.
- `images/gorilla-hero.webp`: isolated crown/gorilla/blue-light artwork crop from the chosen homepage panel, trimmed to avoid interface fragments.
- `images/gorilla-music.webp`: isolated headphone-wearing gorilla artwork crop from the chosen music-page panel.
- `images/spiritual-gorillaz-*-hero.png`: reviewed production-resolution page masters used as sharp native artwork; CSS may extend their surrounding atmosphere but must not fabricate image detail by enlargement or blur.

These assets remain separate artwork under real HTML/CSS, never a flattened full website screenshot with hit targets.

## Visual contract

Black/deep navy, gold branding and controls, blue stage lighting, crowned-gorilla identity, large editorial headings, consistent interior/account/checkout styling. Public implementation is in `assets/css/public.css`, the later concept/fidelity layers, and `views/public`. Admin and installer retain a matching dark-and-gold family with readable forms/tables.

Separate image focal points, real buttons/forms, responsive navigation, no autoplay audio, reduced-motion support and keyboard-visible focus. Concept names, portraits, tracks, shows, prices and products remain unapproved examples. Only owner-published approved content belongs on the actual site.
