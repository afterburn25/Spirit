# Resume — 1.0.13 contained continuous-image hero review

Repository **afterburn25/Spirit**. Continue from `work/1.0.13-contained-overlay-heroes`.

## Current state

- Application version: **1.0.13**
- Working branch: `work/1.0.13-contained-overlay-heroes`
- Base branch for this work: `work/1.0.12-contained-short-heroes`
- RC10 verified application baseline: `2a833986f5a39d2f8de99f41bd8640b0642648e8`
- Do not merge into `main` until the owner approves the 1.0.13 screenshots.

## Locked visual decisions

- Header/navigation remains full width, sticky, larger and centered.
- Search remains visible and opens the dropdown panel.
- Hero windows are centered, shortened and capped at the same 1320px maximum width as the sections below.
- The artwork fills the complete contained hero window, including behind the real HTML copy.
- Do not split the hero into a text box and a separate image box.
- Do not stretch or blur low-resolution source imagery.
- Use deterministic wide production masters with dark atmosphere on the left and the complete focal subject on the right.
- Use only one soft readability fade over the same continuous image field.
- Apply this to Home, Music, Shows, Band, Media, Press, Booking and Merch.
- Home retains the dimensional `MUSIC WITH A HIGHER PURPOSE` title.
- Mobile remains a contained card with copy first and complete artwork beneath it.

## Fixed content decisions

- Chad Lindsey — guitar, keyboards and vocals.
- Ron Green — harmonica.
- Charlie Savell — drums.
- Candidate 3 / Part 3 remains unpublished until genuine provider URL/title/credits are approved.
- Show tickets use external venue sellers; no native ticketing.
- No synthetic member portraits or fabricated shows/products/testimonials.

## Build contract

- `tools/generate_extended_heroes.py` deterministically creates the packaged WebP masters from the reviewed PNG source art.
- `tools/package.py` runs the generator before collecting the cumulative package.
- Pillow 12.3.0 is installed in package/release CI.
- Every completed delivery includes the cumulative ZIP, checksum, page screenshots and review board.
- Do not merge before owner approval.
