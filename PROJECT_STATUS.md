# Project status — 1.0.12 contained hero correction

The application is **1.0.12**. The verified software foundation is retained, and the public hero geometry now matches the centered content containers used by the rest of the page.

## Software implementation status

**Implementation scope: complete.**

The public site, owner administration, content/media/music/shows/press, booking/email/newsletter, customer accounts, merchandise/orders/payments, branding, backups, guarded upgrades/recovery, diagnostics and shared-host installer are implemented.

## Current visual correction

- The navigation strip remains full width and sticky.
- Hero sections are no longer full screen.
- Desktop heroes use the existing 1320-pixel page-container maximum width and centered margins.
- Interior heroes are shortened to approximately 430–500 pixels depending on viewport width; Home remains slightly taller while staying contained.
- Copy and artwork occupy separate columns with no overlap.
- Music, Shows, Band, Media, Press, Merch and Home share the same contained treatment.
- Booking is constrained to the same centered container.
- Mobile uses a contained card, copy first and complete artwork beneath it.
- Search remains visible and opens its dropdown.
- Animated Concept 2 branding and the dimensional Home title remain intact.
- CMS-published artwork and branding continue to override bundled fallbacks.

The complete RC10 GitHub Actions matrix was green on source commit `2a833986f5a39d2f8de99f41bd8640b0642648e8` before final promotion. Version 1.0.12 is a cumulative visual correction on that verified application foundation.

## Launch-safe content behavior

The confirmed roster and factual Band/Press copy are available without requiring invented content. Published CMS records still override fallbacks.

Truthfully empty optional sections are supported:
- Shows may remain empty until a genuine event exists.
- Part 3 may remain unpublished until its real YouTube/Vimeo URL and exact public metadata are confirmed.
- Member portraits may remain unset; factual non-clickable fallback cards are used for unpublished profiles.
- Merchandise may remain closed. When closed, Merch/Bag and merchandise search affordances are hidden; merchant credentials/provider acceptance are feature-activation requirements rather than core-site blockers.
- Longer biographies are optional.

## Release lineage

Version 1.0.12 supersedes the 1.0.5–1.0.11 visual review builds. It preserves the approved cinematic identity while replacing the full-screen hero experiments with centered, shorter containers that align with the rest of each page.

Commerce remains intentionally disabled, so Authorize.net sandbox/provider acceptance is deferred until commerce activation.

## Remaining blockers

No known software implementation blocker. The 1.0.12 branch remains in draft review until the owner approves the desktop and mobile screenshots.

## Operational follow-ups

- Review the supplied 1.0.12 desktop/mobile screenshots and review board before merging.
- After deployment, confirm sticky navigation and dropdown Search on the real host.
- Review Launch Checks and address any genuine warning.
- Observe scheduled-worker heartbeat freshness during normal operation.
- Exercise the supported recovery path during a suitable maintenance/staging window.
- Complete genuine Authorize.net sandbox/provider acceptance before enabling commerce.
- Publish only genuine owner-approved optional shows, Part 3 metadata, portraits or merchandise.
