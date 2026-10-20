# Resume — 1.1.0-rc.1 audience analytics and navigation

Repository **afterburn25/Spirit**. Continue from **`work/1.1.0-analytics-and-navigation`**. Main was inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`; the latest cumulative 1.0.13 base is `2afbd551d6083d86d26499a9bf82511aa7f7f9c2`. Do not resume from main alone and lose the intervening artwork work. Do not merge before owner screenshot approval.

The cumulative review is [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). First hosted checks confirmed the analytics database workflows, public navigation across all three engines, packaging and Apache rules, then exposed stale menu counts and two test observation-boundary issues. Their targeted corrections retain complete access, read-only and timing assertions. Consult the current TEST_RESULTS.md and PR checks before claiming the updated head passed.

## Current scope

Version **1.1.0-rc.1** adds owner analytics: visits/pageviews, completed-visit bounce, visible-page time, live visitors, referrals/supplied search phrases/UTMs, client families, offline country/optional City data, separate crawlers, charts/date ranges/CSV and optional aggregate Search Console imports. Public navigation conflicts and the old asset budget failure are corrected; uploaded CMS section images are contained. See `docs/ANALYTICS.md` for definitions and `docs/TEST_RESULTS.md` for observed evidence.

Analytics entry is `admin/analytics/`; owner City data is `admin/analytics/geography.php`; live report is `admin/analytics/live.php`; the bounded session-bound collector is `pages/analytics.php`. Public tracking starts only for eligible real public renders and observes the `spirit:navigated` event after dynamic navigation. Staff, private routes, DNT/GPC, prefetch and HEAD requests are excluded. Reports do not identify people or retain raw IP/full UA/full referrer URLs.

## Deployment and packaging

Migrations **007 and 008 are additive**. The current Browser upgrades feature accepts unchanged schemas and must reject this transition. Preserve the installed owner, database, encryption secret, private configuration/runtime and uploads. Deploy code through the reviewed hosting-panel path, then apply **Database updates** with the existing owner. Do not rerun the fresh installer, bypass manifest checks or change the pinned recovery layer.

Public source is `assets/css/site-source.css` plus source JS. Build with `npm ci --prefix tools/public-assets --ignore-scripts` and `node tools/build_public_assets.mjs`; `--check` verifies committed bundles. Node is a development dependency only. Keep historical public patch paths as inert compatibility files for cumulative manifests. The public request must remain within 130,000 bytes of CSS/JS.

`tools/package.py` builds a cumulative immutable ZIP and uses Pillow 12.3.0 to generate the wide WebP backgrounds. It includes licensed offline geography and reader files, but excludes tests, dev dependencies and private state. Deliver every completed version with the full ZIP, checksum, actual public/admin screenshots and review board. `tests/capture_review.py` can render an exact supplied ZIP; admin screenshot fixtures are synthetic and never shipped as production data.

## Visual and content decisions

Full-width sticky header; larger centered navigation; visible accessible search. Short centered hero windows capped at 1320px, a continuous artwork field behind real copy and a single soft fade. Phone cards show copy then complete artwork. Preserve original source art and complete focal subjects; resizing/dark extension does not create higher-resolution detail. Preserve animated Concept 2 branding and the Home title.

Chad Lindsey: guitar, keyboards and vocals. Ron Green: harmonica. Charlie Savell: drums. Part 3 stays unpublished until genuine URL/title/credits are approved. External venue ticket links only; no native ticketing. Do not fabricate portraits, performances, products, reviews or audience numbers.

## Remaining acceptance

Review this candidate's screenshots before merge. Observe target-host navigation, location/collection and scheduled cleanup after deployment. Commerce stays dormant until genuine merchant/catalog/policy acceptance. No real A2 deployment or payment was performed during these development checks.
