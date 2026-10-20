# Project status — 1.1.0-rc.1 audience analytics

The application is **1.1.0-rc.1**, a cumulative review candidate on `work/1.1.0-analytics-and-navigation` in [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). It preserves the complete latest 1.0.13 work and adds the requested owner analytics module. `main` was inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`; candidate work starts from `2afbd551d6083d86d26499a9bf82511aa7f7f9c2`.

## Implemented in this candidate

- Owner-only charts, inclusive date filters and CSV export for visits, pageviews, referral domains, supplied search terms, UTM campaigns, browser/OS/device families and approximate locations.
- Single-page bounce rate for visits completed after 30 minutes of inactivity; visible-page time with measured coverage; anonymous live visitors with 15-second dashboard refresh and 60-second activity expiry.
- Server-side crawler reporting, including Google, Bing and Yahoo, separate from human totals. Verified Google/Bing labels require a match against fresh bundled official network ranges.
- Attributed offline country data. Optional owner upload/removal of a private, validated City MMDB supplies available region/city labels. No visitor IP is sent to a location API or stored in analytics rows.
- Validated Google Search Console Queries CSV imports, with visibly separate dates and aggregate clicks/impressions/CTR/position. Withheld search phrases are never invented or connected to individual visits.
- Additive migrations 007 and 008. Existing installation credentials, owner, content and media remain in place. The same-schema browser ZIP updater intentionally refuses this cross-schema transition; use the documented code deployment and Database updates flow.
- One public navigation controller, reproducible committed CSS/JS bundles, native no-JavaScript navigation, retained audio across page changes and the established 130,000-byte public-asset budget.
- Bounded, contained CMS section artwork; fallback-only layout rules no longer crop owner-uploaded section images.
- Correct Home foreground proportions, wide-background visibility and the full-height readability fade. Tablet copy and controls fit their existing hero without clipping or overlap.

## Current visual state

The existing full-width sticky header, visible search, Concept 2 branding, 1320px maximum contained hero width, shortened hero height and single soft readability fade remain. Copy is real HTML, with complete artwork beneath it on phones. Home, Music, Shows, Band, Media, Press, Booking and Merch share the existing composition. Wide WebP backgrounds are generated from the existing PNG artwork; resizing/extension does not add native image detail. CMS-published artwork and branding continue to override fallbacks.

## Verification

The new collection, privacy, session/timing, reporting and additive migration SQL/HTTP suite has passed locally at root and subfolder. Dashboard browser checks pass at 1440, 390 and 320px, including real navigation and live refresh. Remaining final runs, package integrity, screen inspection and remote Actions results are recorded in `docs/TEST_RESULTS.md`; no result is inferred from a workflow file or previous version.

## Remaining blockers

Owner approval of this candidate's actual desktop/mobile screenshots remains required before merging. Real A2/cPanel deployment, host-specific behavior and final acceptance are not established by local/CI tests. Genuine Authorize.net sandbox/provider acceptance, real catalog/policy content and explicit owner approval remain prerequisites for commerce activation.

## Operational follow-ups

- Review the delivered cumulative ZIP, checksum, actual build screenshots and review board.
- Apply the additive update on the existing staging installation without reinstalling; verify Analytics from an owner account and public visits from a separate browser session.
- Configure the host's scheduled cleanup if desired; selecting a retention duration alone does not create that schedule.
- Keep Part 3 unpublished until its real provider URL/title/credits are approved. Shows use external venue ticket sellers; unpublished shows, products or portraits stay absent.
