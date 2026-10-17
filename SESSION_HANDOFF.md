# Resume — 1.0.1 concept rebuild upgrade

Repository **afterburn25/Spirit**. Active review branch: `work/concept-exact-rebuild`.

## Current state

- Application version: **1.0.1**
- Installed production baseline before this upgrade: **1.0.0**
- PR #27 remains draft until owner visual approval.
- The prior concept ZIP was incorrectly left at version 1.0.0, so the guarded browser upgrader refused it as a same-version replacement. That deployment defect is corrected in 1.0.1.
- Static bundled assets now receive a `?v=1.0.1` query string so the new design cannot be hidden by stale browser caches.

## Fixed decisions

- Chad Lindsey — guitar, keyboards and vocals.
- Ron Green — harmonica.
- Charlie Savell — drums.
- Candidate 3 / Part 3 remains selected but unpublished until genuine provider URL/title/credits are approved.
- Preserve the black/deep-navy, gold and blue crowned-gorilla design.
- Show tickets continue to each venue's configured external seller.
- No native ticketing.
- No synthetic member portraits.
- No fake shows, products, rates, testimonials or recording metadata.

## 1.0.1 visual update

- Native-resolution Home, Music, Shows, Band, Booking and Merch artwork is bundled.
- Real HTML/CSS/PHP titles, controls, forms and content remain separate from artwork.
- Legacy low-resolution moodboard crops are not used as production masters.
- Desktop, tablet, 390 px and 360 px title containment is enforced.
- Existing CMS, media, customer, commerce, booking, backup and configuration data is preserved.

## Deployment

On the existing A2 installation, use **System & maintenance → Browser upgrades** with the cumulative `Spiritual-Gorillaz-1.0.1-Full.zip`. Save the private recovery receipt, install the staged update, then verify and reopen the website. Do not reinstall or replace the database/configuration.

## Operational follow-ups

After deployment, confirm administration reports version 1.0.1 and review the actual public pages. Do not claim unobserved provider/host evidence. If commerce is later enabled, genuine Authorize.net sandbox/provider acceptance is required before activation.

Only publish genuine owner-approved optional content/assets.
