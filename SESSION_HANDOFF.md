# Resume — 1.1.0-rc.2 graphical analytics and navigation

Repository **afterburn25/Spirit**. Continue from **`work/1.1.0-analytics-and-navigation`** and [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). The rc.2 work starts from delivered rc.1 source `1bde497daa174ac265096e1adee967dd9bf3f900`. Main was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`; the cumulative 1.0.13 artwork base is `2afbd551d6083d86d26499a9bf82511aa7f7f9c2`. Do not resume from main alone and lose the intervening artwork or analytics work. Do not merge before owner screenshot approval.

## Current scope

Version **1.1.0-rc.2** addresses the owner's corrections: Analytics has its own category, reports have prominent charts and consistent colors, individual anonymous sessions have a visitor explorer, and the main Overview has analytical snapshots. Public search is moved directly beside **Book the Band**, with larger centered navigation text and fitted tablet spacing; local public navigation QA has passed at root and `/band`, including adjacent search and fitted desktop/tablet spacing.

The owner Overview renders seven inclusive UTC days of visits/pageviews, completed-visit bounce, measured active time per visit, live count at page load and separate crawler requests. It includes a real daily chart, no-JavaScript daily disclosure, top referrer/country/page and links into full reports. It says when the snapshot was taken and does not pretend to poll live. Empty, paused and unavailable reporting states are separate, and a report failure leaves operational Overview summaries usable.

The full Analytics dashboard retains date filters, export, live visitor refresh, source/supplied-search/UTM reporting, client/location/crawler details and aggregate Search Console imports. Expanded charts cover channels, devices, hourly activity, engagement, page depth and entry/exit pages. The visitor explorer offers filters, native pagination and bounded retained page journeys with measured time and stored attribution. No report identifies people, invents withheld search terms or stores raw IP/full UA/full referrer URLs. Definitions remain in `docs/ANALYTICS.md`.

| Destination | Route | Active navigation key |
|---|---|---|
|Owner Overview|`admin/`|`dashboard`|
|Audience dashboard|`admin/analytics/`|`analytics`|
|Visitor explorer and session detail|`admin/analytics/visitors.php`|`analytics-visitors`|
|Geography data|`admin/analytics/geography.php`|`analytics-geography`|

The live endpoint remains `admin/analytics/live.php` and the bounded session collector remains `pages/analytics.php`. Public tracking starts only for eligible public renders and observes `spirit:navigated` after dynamic navigation. Staff, private routes, DNT/GPC, prefetch and HEAD requests are excluded. Analytics remains owner-only in navigation and on direct requests; staff Overview does not query or show the owner snapshot.

## Verification checkpoint and next steps

Focused local rc.2 Navigation/Overview checks passed: all 109 navigation unit checks; packaged root and `/band` snapshots at 1440/390/320px; exact fixture metrics; native SVG and daily data without JavaScript; no read-side database mutation; accurate report links; and no document overflow. A further Chromium run passed paused/unavailable reporting, a phone menu journey, all Analytics active identities, owner/staff separation and direct staff 403 responses. Desktop/phone QA screenshots were inspected; final delivery captures must still be produced from the final ZIP. Detailed scope is in `docs/TEST_RESULTS.md`.

Local analytics units/integration/browser checks, the public navigation suite, package tests and deterministic release gate have since passed; see TEST_RESULTS for their exact counts. Nineteen QA-only administration captures were visually reviewed. Latest-head GitHub Actions and final exact-package capture/manifests remain pending at this source checkpoint. Record observed outcomes before delivery; do not copy rc.1 passes forward as rc.2 evidence. The earlier all-ten-workflow baseline is retained in `docs/history/TEST_RESULTS-1.1.0-rc.1.md`.

## Deployment and packaging

Migrations **007 and 008 are unchanged from rc.1**; rc.2 adds no migration and does not alter existing public collector rules.

- **Installed 1.1.0-rc.1 → rc.2:** use the normal guarded **Browser upgrades** flow for the newer cumulative Full ZIP when its same-schema, recovery and file-integrity checks pass. Existing analytics history remains in place.
- **Installed 1.0.x → rc.2:** migrations 007/008 are additive. The same-schema browser updater must reject this transition. Deploy code through the documented hosting-panel path, then apply **Database updates** with the existing owner.

Both paths preserve the installed owner, database, encryption secret, private configuration/runtime and uploads. Do not rerun the fresh installer, strip migrations, bypass manifest checks or change the pinned recovery layer. See `docs/ANALYTICS.md` for the exact version-specific instructions.

Public source is `assets/css/site-source.css` plus source JS. Build with `npm ci --prefix tools/public-assets --ignore-scripts` and `node tools/build_public_assets.mjs`; `--check` verifies committed bundles. Node is a development dependency only. Keep historical public patch paths as inert compatibility files for cumulative manifests. The public request must remain within 130,000 bytes of CSS/JS.

`tools/package.py` builds a cumulative immutable ZIP and uses Pillow 12.3.0 to generate wide WebP backgrounds. It includes licensed offline geography and reader files, but excludes tests, dev dependencies and private state. Deliver every completed version with the full ZIP, checksum, actual public/admin screenshots and review board; never replace rc.1 downloads with different bytes. `tests/capture_review.py` can render an exact supplied ZIP. Administration fixtures are synthetic, clearly labeled in review material, and never shipped as production data.

## Visual and content decisions

Full-width sticky header; larger centered navigation; accessible search directly beside Book the Band. Short centered hero windows capped at 1320px, a continuous artwork field behind real copy and a single soft fade. Phone cards show copy then complete artwork. Preserve original source art and complete focal subjects; resizing/dark extension does not create higher-resolution detail. Preserve animated Concept 2 branding and the Home title. rc.2 changes administration presentation and public header spacing, not the approved artwork.

Chad Lindsey: guitar, keyboards and vocals. Ron Green: harmonica. Charlie Savell: drums. Part 3 stays unpublished until genuine URL/title/credits are approved. External venue ticket links only; no native ticketing. Do not fabricate portraits, performances, products, reviews or audience numbers.

## Remaining acceptance

Review this candidate's final desktop/mobile screenshots before merge. Observe target-host navigation, location/collection and scheduled cleanup after deployment; choosing analytics retention does not create a cron schedule. Commerce stays dormant until genuine Authorize.net merchant/provider acceptance, real catalog/policy content and owner approval. Local and CI checks do not prove A2 deployment, mailbox delivery, a real recovery drill, physical-device performance or a payment. None is claimed by this development checkpoint.
