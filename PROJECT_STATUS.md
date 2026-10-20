# Project status — 1.1.0-rc.2 graphical analytics

The application is **1.1.0-rc.2**, a cumulative review candidate on `work/1.1.0-analytics-and-navigation` in [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). It starts from delivered rc.1 source `1bde497daa174ac265096e1adee967dd9bf3f900` and retains the complete latest 1.0.13 artwork and application work. `main` was re-inspected at `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`; resuming from main alone would omit the intervening candidate changes.

## Implemented in this candidate

- A dedicated owner-only **Analytics** navigation category contains Audience dashboard, Visitor explorer and Geography data. Bookings & communication contains only its four communication destinations. Staff do not receive Analytics navigation, Overview metrics or direct report access.
- The owner **Overview** shows a compact seven-day UTC snapshot: visits, pageviews, completed-visit bounce rate, measured active time per visit, live activity at page load and separate crawler requests. A real daily visits/pageviews chart, native daily data table, top referrer/country/page and report links sit above the existing operational summaries. The snapshot has an explicit timestamp; it does not claim continuous live refresh. Empty, paused and unavailable states remain distinct.
- The Analytics dashboard has a compact report header, prominent native SVG charts and consistent metric colors. New channel, device, hourly activity, engagement, page-depth and entry/exit views accompany the existing source, search, campaign, geography, browser, operating-system and crawler reports.
- A filterable anonymous visitor explorer and bounded session detail view expose retained page journeys, acquisition/campaign data, client families, approximate locations and measured active time. These records describe browser sessions, not identified people.
- Existing inclusive date filters, private CSV export, live visitor refresh, optional City MMDB management and aggregate Google Search Console Queries CSV import remain. Withheld search phrases are not invented or linked to individual visits. Crawler requests remain separate from human traffic; verified Google/Bing labels require matching fresh official network ranges.
- Public search is placed immediately beside **Book the Band**, with larger centered desktop navigation and fitted tablet spacing. The existing phone controls, single navigation controller, persistent audio and native no-JavaScript behavior are preserved. Local Chromium checks passed the adjacent button, centering, logo clearance, focus and dropdown fit at 1440/1180/1024/900/821px, plus the existing phone journeys.
- The analytics schema remains **007/008**, with no new migration or change to the public collector rules. Configuration, owner accounts, media, content and existing analytics history remain in place.

## Current visual state

The administration design keeps the deep navy and gold branding and adds consistent gold, blue, coral, purple and teal metric accents. The Overview traffic chart is visible in the first desktop screen and the snapshot fits 390px phones and the 320px stress width in focused local checks.

Public artwork remains the existing composition: full-width sticky header, Concept 2 branding, 1320px maximum contained hero width, shortened hero height and single soft readability fade. Copy is real HTML, with complete artwork beneath it on phones. Home, Music, Shows, Band, Media, Press, Booking and Merch retain their section compositions. Wide WebP backgrounds are generated from existing PNG artwork; resizing/extension does not add native image detail. CMS-published artwork and branding continue to override fallbacks.

## Verification

Focused rc.2 local checks have passed for the dedicated navigation and owner Overview: 109 navigation unit checks; packaged root and `/band` snapshots at 1440/390/320px; exact synthetic totals; charts and disclosure data without JavaScript; no database writes while reading; and explicit at-load live wording. Additional Chromium checks passed paused collection, an unavailable analytics table, phone menu persistence/focus, each Analytics route's active identity, owner/staff isolation and direct staff access rejection. See `docs/TEST_RESULTS.md` for the scope and limits of each observed run.

Local analytics integration (191 checks), rendered dashboard/visitor journeys (141 browser checks), final report navigation, all 35 chart/import units, the 401-check public navigation suite, 15 packaging tests and the deterministic release gate have also passed. Actual QA-only desktop/phone report captures were inspected. Latest-head GitHub Actions and final exact-package desktop/mobile captures are pending at this source checkpoint. The rc.1 workflow results are retained as historical baseline evidence in `docs/history/TEST_RESULTS-1.1.0-rc.1.md`; they do not certify rc.2. Final screenshot manifests must identify the delivered ZIP and images by SHA-256 and label synthetic administration demonstration data.

## Deployment

An installed **rc.1 → rc.2** update uses the guarded same-schema **Browser upgrades** process when its normal integrity and recovery checks pass. An installed **1.0.x → rc.2** update adds migrations 007/008: the browser updater deliberately refuses that schema transition, so use the documented code deployment and additive **Database updates** flow. Preserve the installed database, owner, encryption secret, private configuration/runtime and uploads; never rerun the fresh installer or bypass update checks.

## Remaining blockers

Owner approval of this candidate's actual desktop/mobile screenshots remains required before merging. Real A2/cPanel deployment, host-specific behavior and final acceptance are not established by local/CI tests. Genuine Authorize.net sandbox/provider acceptance, real catalog/policy content and explicit owner approval remain prerequisites for commerce activation.

## Operational follow-ups

- Observe the final rc.2 GitHub workflows, then review the immutable cumulative ZIP, checksum, actual build screenshots and review board.
- Use the upgrade path matching the installed version; verify Analytics from an owner account and public visits from a separate browser session after staging deployment.
- Configure the host's scheduled cleanup if desired; selecting a retention duration alone does not create that schedule.
- Keep Part 3 unpublished until its real provider URL/title/credits are approved. Shows use external venue ticket sellers; unpublished shows, products or portraits stay absent.
