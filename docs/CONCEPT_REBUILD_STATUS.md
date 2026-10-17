# Spiritual Gorillaz Concept Rebuild Status

The approved Spiritual Gorillaz concept moodboard remains the literal visual source of truth.

## Integrated and under review

- Native-resolution Adobe Home hero artwork: 2288 × 1824 PNG
- Native-resolution Adobe Music hero artwork: 1808 × 1280 PNG
- Native-resolution Shows hero artwork: 2688 × 1536 PNG
- Native-resolution Band hero artwork: 2048 × 1170 PNG
- Native-resolution Booking artwork: 2688 × 1536 PNG
- Native-resolution Merch artwork: 2688 × 1536 PNG
- Real HTML/CSS/PHP headings, buttons, navigation, music controls, forms and content remain separate from artwork
- Existing CMS artwork overrides remain preserved
- Legacy low-resolution concept crops have been removed from public fallbacks
- Actual installed-site screenshots are rendered at desktop and mobile sizes for every review package
- Home title has a dedicated safe-width column at desktop, tablet and phone sizes
- Phone title sizing keeps `MUSIC`, `WITH A HIGHER`, and `PURPOSE` as three deliberate lines
- Automated visual tests reject title overflow on every reviewed public page
- Dedicated page crops, stacking, lighting, and mobile artwork visibility have been tuned from actual rendered screenshots
- Upgrade integration now runs with an isolated 64 MiB PHP upload fixture so the production-size artwork package is tested rather than rejected by PHP's 2 MiB development default

## Remaining visual work

- Final owner review of this dedicated-artwork checkpoint
- Real member portraits and product photography when supplied by the owner

## Release rule

Do not merge this branch to `main` until the rendered desktop and mobile screenshots are visually reviewed against the approved moodboard. Every review build must include a full installation ZIP and actual rendered screenshots.
