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

## Selected global background — 2026-10-20

The user selected **option 1: the blue-lit concert stage** as a global public-site background. It remains fixed relative to the viewport while page content scrolls. Expose it through the page shell, gutters and section gaps while preserving the existing contained continuous-image heroes, readable content surfaces, sticky navigation and controls. Use a decorative, non-interactive layer; no scroll-driven animation is needed. Phone framing should retain recognizable stage equipment. Omit decoration for print and forced-colors displays.

The source master is `design/background-stage/blue-lit-concert-stage-master.png` (native 1672 × 941px); the served asset is `images/backgrounds/spiritual-gorillaz-stage-background.webp`. This selected background supersedes the former solid full-height page-shell backdrop. It does not replace the separate page-specific hero artwork. It is generated decorative music artwork, not evidence of a real band performance.

The user's follow-up decisions are part of the same presentation: keep the main outer container transparent; extend the header's background and lower border to both viewport edges while keeping the logo in exactly its existing position; place previously exposed content into its own dark navy containers. Group related headings and descriptions together, use existing semantic wrappers where possible, and avoid adding extra panels inside already-contained heroes, cards or forms. Keep the stage visible in the spacing between those surfaces.

## Approved identity and outer surfaces — 2026-10-20 follow-up

The latest approved logo supersedes the earlier Concept 2 square crown-and-shield identity. Use the actual transparent sculpted-gold crowned gorilla at the left of the horizontal two-line SPIRITUAL GORILLAZ wordmark. The user explicitly selected option 3, asked for greater three-dimensional depth and the gorilla head, then approved transparency and animation. The kit assets are now integrated in rc.6; do not substitute the previous logo or a text/crown imitation.

Use the same bundled identity in public header/footer, admin sidebar and admin login/setup. The navbar animation is a stationary six-second metallic shimmer; honor reduced-motion preferences with the static original. Preserve the owner-managed public override. The header logo may fill its available first track for legibility while its left anchor and the navigation layout stay in place.

The navigation background must be 25% transparent (75% opaque); do not reduce opacity on its logo, links or controls. The footer surface must reach both viewport edges with its inner content alignment preserved. The transparent global shell must have no visible border, shadow, outline or rounded edge. Keep the individual readable dark panels; their surfaces are separate from the invisible outer shell. Retain the fixed blue stage and existing contained page heroes.

The favicon uses the same complete crowned gorilla head, genuine transparency and maximum proportional framing. Preserve the crown, face and chin; do not stretch the head or claim a larger browser-controlled icon slot. Active icon URLs must avoid stale previous filenames while compatibility paths stay available.

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
