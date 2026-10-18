# Resume — 1.0.10 centered hero-stage review

Repository **afterburn25/Spirit**. Version 1.0.10 retains the verified final software line and corrects the hero composition so the full-width canvas and centered native artwork are treated as separate layers.

## Current state

- Application version: **1.0.10**
- Working branch: `work/1.0.10-centered-hero-stage`
- Base branch for this work: `work/1.0.9-full-span-search`
- RC10 verification baseline: `2a833986f5a39d2f8de99f41bd8640b0642648e8`
- Do not merge into `main` until the owner approves the 1.0.10 screenshots.

## Locked visual decisions

- Preserve the black/deep-navy, blue stage-light and metallic-gold identity.
- The complete header strip remains sticky while scrolling.
- Desktop navigation stays larger and truly centered.
- The magnifying glass is always visible and opens a dropdown Search panel.
- “Music With a Higher Purpose” retains its dimensional metallic treatment.
- The HERO CANVAS spans the viewport, but the SOURCE ARTWORK does not stretch to the viewport.
- Center each 1672×941 master and cap it at native width.
- On displays wider than the native master, extend both sides with matching atmosphere and softly feather only the outer artwork edges.
- Keep the complete right-side subject visible; do not crop crowns, faces, headphones, performers or equipment.
- Anchor headings, body copy and buttons inside the artwork's left negative-space area. They must not sit outside the image or resemble a separate overlapping box.
- Apply this consistently to Home, Music, Shows, Band, Media, Press, Merch and Booking.
- Mobile keeps Menu and Search visible, places copy first and stacks the complete native artwork beneath it.
- Real headings, buttons, forms and navigation remain HTML/CSS, not baked into artwork.

## Fixed content decisions

- Chad Lindsey — guitar, keyboards and vocals.
- Ron Green — harmonica.
- Charlie Savell — drums.
- Candidate 3 / Part 3 remains selected but unpublished until genuine provider URL/title/credits are approved.
- Show tickets continue to each venue's configured external seller.
- No native ticketing.
- No synthetic member portraits.
- No fake shows, products, rates, testimonials or recording metadata.

## Preserved runtime behavior

- Owner CMS/media/branding overrides remain functional.
- Animated Concept 2 logo remains in the header with static/reduced-motion fallback.
- Closed commerce hides global Merch/Bag links and merchandise Search affordances.
- Customer accounts remain reachable while commerce is dormant.
- Persistent audio, booking, newsletter, checkout, backups, upgrade and recovery behavior remain intact.
- Deterministic release packaging and the software release gate remain mandatory.

## Review deliverables

Every completed build must include:
- cumulative full installation ZIP;
- SHA-256 checksum;
- desktop and mobile screenshots for every major page;
- review board;
- Search-open screenshot when Search behavior changes;
- no merge to `main` before owner approval.

Do not claim unobserved provider or host evidence. After deployment, observe Launch Checks, worker heartbeat freshness, recovery behavior and production performance as operational follow-up. Genuine Authorize.net sandbox/provider acceptance is required before commerce activation.
