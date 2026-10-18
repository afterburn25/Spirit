# Resume — 1.0.12 contained hero review

Repository **afterburn25/Spirit**. Continue from `work/1.0.12-contained-short-heroes`.

## Current state

- Application version: **1.0.12**
- Working branch: `work/1.0.12-contained-short-heroes`
- Base branch for this work: `work/1.0.11-hero-safe-zone`
- RC10 verification baseline: `2a833986f5a39d2f8de99f41bd8640b0642648e8`
- Do not merge into `main` until the owner approves the 1.0.12 screenshots.

## Locked visual decisions

- Preserve the black/deep-navy, blue stage-light and metallic-gold identity.
- The complete header strip remains full width and sticky while scrolling.
- Desktop navigation stays larger and truly centered.
- The magnifying glass remains visible and opens the dropdown Search panel.
- “Music With a Higher Purpose” retains its dimensional metallic treatment.
- The **header** may be full width; the **hero sections must not be full screen**.
- Home and interior heroes use the same centered `1320px` maximum width as `.section`.
- Interior heroes are shortened to a compact editorial height rather than filling most of the viewport.
- Copy stays in a dedicated left column and artwork stays in a dedicated right column; do not shift the whole composition sideways or place copy over image focal areas.
- Use only a narrow seam fade between columns, not a broad overlay or separate text panel.
- Apply this consistently to Home, Music, Shows, Band, Media, Press and Merch.
- Booking is constrained to the same centered page container.
- Mobile keeps Menu and Search visible, places copy first and stacks the complete artwork beneath it.
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
