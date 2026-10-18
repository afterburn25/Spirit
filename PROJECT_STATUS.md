# Project status — 1.0.10 centered native-art hero stage

The application is **1.0.10**. The verified software foundation is retained, with the hero framing corrected so the page canvas remains full width while the source artwork itself stays centered at native scale.

## Software implementation status

**Implementation scope: complete.**

The public site, owner administration, content/media/music/shows/press, booking/email/newsletter, customer accounts, merchandise/orders/payments, branding, backups, guarded upgrades/recovery, diagnostics and shared-host installer are implemented.

Current visual hardening includes:
- the owner-selected animated Concept 2 identity in the public header, with matching static/reduced-motion fallback;
- larger centered sticky desktop navigation;
- a visible magnifying-glass control that opens the accessible dropdown Search panel;
- dimensional metallic “Music With a Higher Purpose” Home title;
- full-width black/deep-navy hero canvases;
- centered 1672×941 production artwork capped at native width instead of stretched to the viewport;
- complete crowns, faces, headphones, performers and equipment preserved without destructive crop;
- hero copy anchored inside the artwork's left negative space rather than outside it;
- subtle edge feathering into matching atmosphere only when the viewport is wider than the native master;
- the same centered-stage treatment across Home, Music, Shows, Band, Media, Press, Merch and Booking;
- mobile layouts that keep Menu and Search visible and stack the complete artwork beneath the copy;
- CMS-published artwork and branding continuing to override bundled fallbacks.

The complete RC10 GitHub Actions matrix was green on source commit `2a833986f5a39d2f8de99f41bd8640b0642648e8` before final promotion. Version 1.0.10 is a cumulative visual correction on that verified application foundation.

## Launch-safe content behavior

The confirmed roster and factual Band/Press copy are available without requiring invented content. Published CMS records still override fallbacks.

Truthfully empty optional sections are supported:
- Shows may remain empty until a genuine event exists.
- Part 3 may remain unpublished until its real YouTube/Vimeo URL and exact public metadata are confirmed.
- Member portraits may remain unset; factual non-clickable fallback cards are used for unpublished profiles.
- Merchandise may remain closed. When closed, Merch/Bag and merchandise search affordances are hidden; merchant credentials/provider acceptance are feature-activation requirements rather than core-site blockers.
- Longer biographies are optional.

## Release lineage

Version 1.0.10 supersedes the 1.0.5–1.0.9 visual review builds. It keeps the approved full-width atmospheric canvas while replacing the stretched-image experiment with a centered native-art stage and keeping all text inside that stage.

Commerce remains intentionally disabled, so Authorize.net sandbox/provider acceptance is deferred until commerce activation.

## Remaining blockers

No known software implementation blocker. The 1.0.10 branch remains in draft review until the owner approves the centered desktop and mobile hero screenshots.

## Operational follow-ups

- Review the supplied 1.0.10 desktop/mobile screenshots and review board before merging.
- After deployment, confirm sticky navigation and dropdown Search on the real host.
- Review Launch Checks and address any genuine warning.
- Observe scheduled-worker heartbeat freshness during normal operation.
- Exercise the supported recovery path during a suitable maintenance/staging window.
- Complete genuine Authorize.net sandbox/provider acceptance before enabling commerce.
- Publish only genuine owner-approved optional shows, Part 3 metadata, portraits or merchandise.
