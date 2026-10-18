# Resume — 1.0.9 full-span hero and Search review

Repository **afterburn25/Spirit**. Version 1.0.9 retains the verified final software line and corrects the remaining Music/full-span hero geometry plus the missing Search control.

## Current state

- Application version: **1.0.9**
- Working branch: `work/1.0.9-full-span-search`
- Draft pull request: **#32**
- RC10 verification baseline: `2a833986f5a39d2f8de99f41bd8640b0642648e8`
- Do not merge into `main` until the owner approves the 1.0.9 screenshots.
- Do not return to boxed, narrow, short-banner or separately contained interior hero artwork.

## Locked visual decisions

- Preserve the black/deep-navy, blue stage-light and metallic-gold identity.
- The complete header strip remains sticky while scrolling.
- Desktop navigation stays larger and truly centered.
- The magnifying glass is always visible and opens a dropdown Search panel; it does not immediately navigate away.
- “Music With a Higher Purpose” retains its dimensional metallic treatment.
- Production desktop Music and coordinated heroes use a true 16:9 viewport-wide canvas so the native artwork reaches both edges.
- The complete right-side source artwork remains zoomed out and visible; do not crop the crown, face, headphones, performers or equipment.
- Never enlarge a low-resolution crop to fake detail.
- Dark atmospheric space on the left remains available for real HTML copy and controls.
- Mobile keeps both Menu and Search inside the header viewport, places copy first, and stacks the complete native artwork beneath it.
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

The owner expects every completed build to include:
- cumulative full installation ZIP;
- SHA-256 checksum;
- desktop and mobile screenshots;
- Search-open screenshot when Search behavior changes;
- no merge to `main` before approval.

Do not claim unobserved provider or host evidence. After deployment, observe Launch Checks, worker heartbeat freshness, recovery behavior and production performance as operational follow-up. Genuine Authorize.net sandbox/provider acceptance is required before commerce activation.
