# Spiritual Gorillaz Concept Rebuild Status

The approved Spiritual Gorillaz concept moodboard remains the literal visual source of truth.

## Integrated and under review

- Native-resolution Adobe Home hero artwork: 2288 × 1824 PNG
- Native-resolution Adobe Music hero artwork: 1808 × 1280 PNG
- Real HTML/CSS/PHP headings, buttons, navigation, music controls, forms and content remain separate from artwork
- Responsive Home and Music layouts use the new production assets
- Existing CMS artwork overrides remain preserved
- Legacy low-resolution concept crops have been removed from public fallbacks
- Dedicated native-resolution Shows, Band, Booking and Merch artwork masters are integrated
- Actual installed-site screenshots are rendered at desktop and mobile sizes for every review package
- Home title now has a dedicated safe-width column at desktop, tablet and phone sizes
- Phone title sizing was reduced so `MUSIC`, `WITH A HIGHER`, and `PURPOSE` remain three deliberate lines
- Automated visual tests reject any build where a Home title line wraps unexpectedly or leaves its container

## Remaining visual work

- Final crop and contrast tuning after screenshot review
- Real member portraits and product photography when supplied by the owner

## Release rule

Do not merge this branch to `main` until the rendered desktop and mobile screenshots are visually reviewed against the approved moodboard. Every review build must include a full installation ZIP and actual rendered screenshots.
