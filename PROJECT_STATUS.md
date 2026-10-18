# Project status — 1.0.9 full-span hero and Search correction

The application is **1.0.9**. The verified software foundation is retained, with the remaining desktop hero-span issue corrected and the Search control explicitly restored in the sticky header.

## Software implementation status

**Implementation scope: complete.**

The public site, owner administration, content/media/music/shows/press, booking/email/newsletter, customer accounts, merchandise/orders/payments, branding, backups, guarded upgrades/recovery, diagnostics and shared-host installer are implemented.

Current visual hardening includes:
- the owner-selected transparent animated Concept 2 identity in the public header, with matching static/reduced-motion fallback;
- larger centered sticky desktop navigation;
- a visible magnifying-glass control that opens the accessible dropdown Search panel;
- dimensional metallic “Music With a Higher Purpose” Home title;
- true 16:9 viewport-wide desktop hero geometry for Music and coordinated interior pages;
- complete native-resolution subject artwork preserved at a zoomed-out scale rather than enlarged or destructively cropped;
- dark atmospheric left-side space for real HTML headings and controls;
- mobile layouts that keep Menu and Search visible and stack the complete artwork beneath the copy;
- CMS-published artwork and branding continuing to override bundled fallbacks.

The complete RC10 GitHub Actions matrix was green on source commit `2a833986f5a39d2f8de99f41bd8640b0642648e8` before final promotion. Version 1.0.9 is a cumulative visual correction on that verified application foundation.

## Launch-safe content behavior

The confirmed roster and factual Band/Press copy are available without requiring invented content. Published CMS records still override fallbacks.

Truthfully empty optional sections are supported:
- Shows may remain empty until a genuine event exists.
- Part 3 may remain unpublished until its real YouTube/Vimeo URL and exact public metadata are confirmed.
- Member portraits may remain unset; factual non-clickable fallback cards are used for unpublished profiles.
- Merchandise may remain closed. When closed, Merch/Bag and merchandise search affordances are hidden; merchant credentials/provider acceptance are feature-activation requirements rather than core-site blockers.
- Longer biographies are optional.

## Release lineage

Version 1.0.9 supersedes the 1.0.5–1.0.8 visual review builds. It preserves all verified CMS, booking, player, commerce, installer, upgrade, backup and recovery behavior while correcting the unintended desktop Music strip and guaranteeing that Search remains visible on desktop and mobile.

Commerce remains intentionally disabled, so Authorize.net sandbox/provider acceptance is deferred until commerce activation.

## Remaining blockers

None in the software implementation. The 1.0.9 branch remains in draft review until the owner approves the rendered full-span Music hero and Search states.

## Operational follow-ups

- Review the supplied 1.0.9 desktop, mobile and Search-dropdown screenshots before merging.
- After deployment, confirm sticky navigation and dropdown Search on the real host.
- Review Launch Checks and address any genuine warning.
- Observe scheduled-worker heartbeat freshness during normal operation.
- Exercise the supported recovery path during a suitable maintenance/staging window.
- Complete genuine Authorize.net sandbox/provider acceptance before enabling commerce.
- Publish only genuine owner-approved optional shows, Part 3 metadata, portraits or merchandise.
