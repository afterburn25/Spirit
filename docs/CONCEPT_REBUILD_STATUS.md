# Spiritual Gorillaz Concept Rebuild Status

The approved Spiritual Gorillaz concept moodboard remains the literal visual source of truth.

## Integrated and under review

- Native-resolution Adobe Home hero artwork: 2288 × 1824 PNG
- Native-resolution Adobe Music hero artwork: 1808 × 1280 PNG
- Real HTML/CSS/PHP headings, buttons, navigation, music controls, forms and content remain separate from artwork
- Responsive Home and Music layouts use the new production assets
- Existing CMS artwork overrides remain preserved
- Legacy low-resolution concept crops have been removed from public fallbacks
- Shows, Band, Booking and Merch now use sharp native artwork treatments while their dedicated masters are reconstructed
- Actual installed-site screenshots are rendered at desktop and mobile sizes for every review package
- Home title now has a dedicated safe-width column at desktop, tablet and phone sizes
- Automated visual tests reject any build where one of the three Home title lines leaves its container

## Remaining dedicated artwork reconstruction

- Shows hero master
- Band hero atmosphere without fabricated member portraits
- Booking road-case / stage master
- Merch stage master

## Release rule

Do not merge this branch to `main` until the rendered desktop and mobile screenshots are visually reviewed against the approved moodboard. Every review build must include a full installation ZIP and actual rendered screenshots.
