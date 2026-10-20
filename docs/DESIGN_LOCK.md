# Approved visual design — locked

The user selected the FIRST cinematic Spiritual Gorillaz composite, reuploaded as **Spiritual Gorillaz Website Concept Moodboard.png**, and explicitly said **no more mockups**. Do not substitute the six-concept alternatives or another project.

Original image SHA-256: `4e76c31eadeb7720701c7d8bd94f484824eb5eb9dd4ba04479393914efad033e`. Original dimensions: 1214 × 1295. The original PNG is a conversation attachment, not a tracked repository file.

## Required production modification — current and authoritative

The approved composite is the visual-direction reference, not a pixel-for-pixel screenshot. The production site must preserve its black/deep-navy, metallic-gold, blue-stage-light and crowned-gorilla identity while following these rules:

- The navigation/header strip remains full width and sticky.
- Hero windows are not full-screen. They are centered, shortened and capped at the same `1320px` maximum width as the sections below.
- The complete hero window is **one continuous artwork field**, including underneath the real HTML heading, body copy and buttons.
- Do not split the window into a left text box and right image box. Do not introduce a hard seam or a second panel.
- Production artwork must provide genuine dark atmospheric space on the left and preserve the complete focal subject on the right.
- Use one soft readability fade over the same image field; it must not resemble a rectangular overlay.
- Do not stretch a low-resolution source, blur a duplicate, or enlarge a crop to fabricate detail.
- Preserve crowns, faces, headphones, performers, equipment and audience focal points.
- Apply the same contained continuous-image treatment to Home, Music, Shows, Band, Media, Press, Booking and Merch.
- Home retains the dimensional `MUSIC WITH A HIGHER PURPOSE` treatment.
- Public navigation remains large and centered; Search remains visible and opens its dropdown rather than navigating away.
- On mobile, use a contained card with copy first and the complete artwork beneath it.

The earlier full-screen and split-column interpretations are superseded by this section.

## Tracked reference and assets

- `design/approved-reference-thumbnail.webp`: a derived 300-pixel-wide thumbnail of the selected composite.
- `images/spiritual-gorillaz-*-hero.png`: existing source artwork; preserve its actual available detail.
- `tools/generate_extended_heroes.py`: deterministic build tool that resizes and extends the source artwork with dark atmosphere and a feathered join.
- Packaged `images/extended/spiritual-gorillaz-*-hero-wide.webp`: generated wide background compositions used by the contained continuous-image heroes.

Resizing and extending these assets does not create new native image detail or restore detail already outside a source image. The source Home image itself truncates the top of its crown; CSS should avoid adding further crop.

Artwork remains separate from real HTML/CSS; never flatten the website into a screenshot with fake hit targets.

## Visual contract

Black/deep navy, gold branding and controls, blue stage lighting, crowned-gorilla identity, large editorial headings, consistent interior/account/checkout styling. Admin and installer retain a matching dark-and-gold family with readable forms and tables.

Separate image focal points, real buttons/forms, responsive navigation, no autoplay audio, reduced-motion support and keyboard-visible focus. Concept names, portraits, tracks, shows, prices and products remain unapproved examples. Only owner-published approved content belongs on the actual site.
