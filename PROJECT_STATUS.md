# Project status — RC9 software complete / final acceptance

The application remains **1.0.0-rc.9**. The planned application scope is implemented. Optional features remain frozen. Current work is no longer feature development; it is final verification, real-world acceptance and owner approval.

## Software implementation status

**Implementation scope: complete.**

The public site, owner administration, content/media/music/shows/press, booking/email/newsletter, customer accounts, merchandise/orders/payments, branding, backups, guarded upgrades/recovery, diagnostics and shared-host installer are implemented.

Recent finalization work also landed:
- approved launch copy as safe runtime fallbacks for Home, Music, Shows, Band, Media, Booking, Press and Merch;
- confirmed three-member Band fallbacks for Chad Lindsey, Ron Green and Charlie Savell, including partial-profile publishing without duplicates or fake portraits;
- scalable bundled crown/wordmark SVG fallback while preserving owner-managed logo publication;
- contained fallback hero presentation so the small concept image is not stretched/cropped as a fake production master;
- responsive hardening at 360 px, 390 px and desktop widths;
- cross-browser 360 px checkout/review/cancellation coverage in Chromium, Firefox and WebKit;
- browser/server billing-address validation alignment;
- closed-store navigation that hides Merch/Bag when commerce is intentionally dormant;
- launch diagnostics that distinguish dormant commerce from activation blockers;
- private successful-run heartbeats for the real mail and payment CLI workers;
- deterministic software release gate that validates source/package integrity and refuses final 1.0.0 while true blockers remain.

The latest GitHub Actions matrix must still finish on the current head before final release status can be recorded as green.

## Launch-safe content behavior

The confirmed roster and factual Band/Press copy are available without requiring invented content. Published CMS records still override fallbacks.

Truthfully empty optional sections are supported:
- Shows may remain empty until a genuine event exists.
- Part 3 may remain unpublished until its real YouTube/Vimeo URL and exact public metadata are confirmed.
- Member portraits may remain unset; factual non-clickable fallback cards are used for unpublished profiles.
- Merchandise may remain closed. When closed, Merch/Bag are not advertised globally and merchant credentials/provider acceptance are treated as feature-activation requirements rather than core-site blockers.
- Longer biographies are optional.

The prepared Band & Booking Sheet PDF exists at `design/launch-review-03/Spiritual-Gorillaz-Band-and-Booking-Sheet.pdf`; it is prepared content, not automatically published.

## Visual baseline

A scalable bundled fallback logo now exists at `images/spiritual-gorillaz-logo.svg` and is used only when no owner-published logo is active. The owner branding workflow still overrides it normally.

The built-in gorilla hero remains a small concept asset, but the runtime now presents it as contained identity artwork instead of upscaling it as a full-bleed photograph. A higher-resolution owner-approved hero can still replace it through the existing page-artwork workflow without a software release.

## Remaining blockers

These are acceptance/deployment blockers, not missing application features:

1. Latest CI/workflow matrix must pass on the final RC9 source head.
2. Explicit owner review/approval of the final site and chosen launch content/visual baseline.
3. Actual A2 staging/production observations that source tests cannot fabricate, including real-host performance and any required recovery drill/worker heartbeat evidence.
4. Genuine merchant sandbox acceptance **only if the storefront/payment feature will be enabled at launch**. If commerce stays closed, this is deferred until activation.
5. Any optional real content the owner chooses to launch with (shows, Part 3 provider metadata, portraits, merchandise) must be genuine and approved before publication.

No paid provider result, deployment, CMS publication or owner approval is inferred from repository work.
