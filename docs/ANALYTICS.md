# Visitor analytics — 1.1.0-rc.3

## Open the dashboard

Sign in with an active owner account. **Analytics** has its own administration menu group containing **Dashboard**, **Visitor explorer** and **Geography**. It is separate from **Bookings & communication**. Content editors and store staff cannot view analytics, export it or change its settings. Charts and the full report are rendered by PHP and remain usable without JavaScript. Live refresh and interactive chart tooltips are browser enhancements.

Choose the last 7, 30 or 90 days, or an inclusive custom range of at most 366 days. Dates and displayed times use UTC. Export the selected report as CSV. Collection, geography and crawler reporting can each be paused in Analytics settings. The default retention policy is 90 days; the owner may choose 30, 90, 180 or 365 days.

## Overview snapshots and graphical reports

The main administration **Overview** shows a compact seven-day snapshot for the owner: visits, pageviews, completed-visit bounce rate, measured active time, live visits and separate crawler hits. Its traffic chart, top referring source, country and page use the same seven-day UTC window. The displayed live number is a snapshot at page load, with its timestamp; open the Analytics dashboard for the refreshing live panel. Pausing collection preserves historical reports. If analytics is unavailable, Overview still opens and shows an explicit unavailable message.

The Analytics dashboard uses consistent colors: gold for visits, blue for pageviews, coral for bounce, purple for measured active time and teal for live activity. The large daily traffic chart is near the top. Channel and device composition, ranked referral/browser/OS/location bars, hourly activity, the weekday/hour grid, entry and exit pages, session depth and measured engagement make the supporting reports easier to compare. Legends, labels and report tables retain the actual numeric values; meaning does not rely on color alone. Empty reports remain empty instead of displaying demonstration numbers.

The channel breakdown derives categories from retained attribution. Recognized paid or email campaign media take precedence, followed by supported social/search referrals, other tagged campaigns, other external referrals and Direct / unknown. This is a reporting classification, not proof of the visitor's intention. Hourly visit counts are distinct within each hour; a session active in several hours appears in each relevant hour. The weekday/hour grid counts pageviews. Session depth uses the complete session's recorded page count; engagement bands use measured activity on pages viewed in the selected period. Unmeasured sessions have their own band.

Entry pages are the first retained pages of sessions qualifying for the selected dates. Exit pages are the last retained pages of completed sessions in that cohort. Those retained first/last pages can lie outside the selected dates when a session crosses midnight. These are session-level journey reports, while the existing page report is scoped to pageviews within the selected dates.

## Explore a visitor session

Open **Analytics → Visitor explorer** to search retained anonymous sessions. Combine date, traffic channel, status, country, device, browser, operating system, referring domain, region, city and visited-page filters. Region/city and page filters match the recorded values. A page filter accepts the exact canonical public path recorded by the collector, including its single validated `?slug=` identifier for a release, show, member, product or custom page. Arbitrary query fields, fragments and private paths remain rejected. Click a page in the dashboard's page-performance report to carry its exact path and selected dates into the explorer. Results show 25 sessions per page, ordered by most recent activity. A selected-date cohort includes sessions with a counted page during that range.

Choose **Inspect visit** to see the session's arrival and last activity, supplied referral/search/campaign labels, client families, available approximate location, first and last retained page, classic bounce status and timing coverage. The chronological journey shows each retained public pageview, its observed timing and whether it falls outside the selected dates. Long journeys are paginated in groups of 100 events. Details keep the active filters and date scope; a missing, expired or unrelated session does not bypass them.

These identifiers describe anonymous sessions. The explorer does not reveal a person's name, raw IP, full user-agent string, account history, checkout details or form contents. Missing phrases stay **Not supplied**, missing location stays **Unknown**, and missing duration stays **Unmeasured**. Live, recent, completed and single-page-bounce labels follow the same boundaries as the dashboard. The visitor list is read-only and updates when loaded; it is not a second continuously polling live feed.

## What the numbers mean

| Measurement | Definition and practical boundary |
|---|---|
| Visits | Distinct anonymous browser sessions with a counted public pageview in the selected range. Thirty minutes of inactivity starts a new visit. This is not a count of uniquely identified people or cross-device users. |
| Pageviews | Successful server-side eligible public page renders. A normal page counts even without JavaScript. Preview, account, cart, checkout, receipt, internal search, administrative, prefetch and HEAD requests are excluded. |
| Bounce rate | Completed visits containing exactly one counted pageview, divided by completed visits. Visits become completed after 30 minutes of inactivity. Active visits are excluded; no completed visits displays an unavailable rate. This uses the classic single-page definition. |
| Active time | Foreground time observed through session-bound browser samples, bounded by elapsed server time. Background tabs do not accrue time. Multiple tabs cannot inflate a visit's elapsed foreground total. JavaScript-disabled or blocked samples have no measured duration. A long single-page visit can still count as a classic bounce. |
| Live visitors | Visits with a visible page sample within the last 60 seconds. The owner dashboard refreshes every 15 seconds while visible. This is an approximate recent-activity view and cannot identify people. |
| Referrers | The supplied external referrer domain. Missing referrers are shown as Direct / unknown. The full referring URL is not saved. |
| Search terms | A supplied, sanitized term from recognized search-engine referrer query fields. Withheld terms remain unavailable; the website does not infer or fabricate them. |
| Campaigns | Sanitized `utm_source`, `utm_medium` and `utm_campaign` labels supplied in the landing link. Other arbitrary query parameters are discarded. |
| Browser, OS and device | Coarse families parsed from the request user agent. These are client-reported descriptions, not verified hardware identification or standardized Client Hints headers. Full user-agent strings are not saved. |
| Country, region and city | Approximate network geography from local databases. Country data is included; region/city require an owner-provided compatible City database and a matching record. Missing locations stay unknown. |
| Spiders/crawlers | Separate server-observed crawler hits, including Google, Bing and Yahoo. Google/Bing labels can be verified against the bundled dated official range snapshots. Other or unmatched claims remain reported by user agent. Crawler traffic is excluded from human visits, bounce rate and live visitors. |

Daily visits need not sum to the overall distinct-visit total: a visit can view pages on both sides of midnight. Time belongs to the pageview's reporting date, and a visit's acquisition fields reflect its first counted page. Rank tables are bounded; exports label their report sections. Unknown data is not zero evidence of traffic.

## Search Console query reports

The **Search Console queries** section accepts an English Queries CSV exported from the verified website's Google Search Console **Performance → Search results** report. Enter the same period used for that export. Expected columns are `Top queries, Clicks, Impressions, CTR, Position`. Limits are 2 MiB and 5,000 rows.

The entire file is validated before replacing the existing query snapshot in one transaction. Duplicate or malformed rows are refused. Clicks, impressions, click-through rate and average position describe aggregate search performance. Imported query rows cannot be matched to individual visitors. Their dates remain visibly separate from the dashboard visitor-date filter. Google can omit privacy-sensitive queries and export only leading rows, so imported-row totals may differ from the complete Search Console chart.

This build supports an explicit CSV import; it does not claim an automatic Search Console connection or continuous synchronization. The imported snapshot stays until the owner replaces or removes it. The temporary-traffic retention policy does not silently erase this owner-imported report.

Official background: [Google Search Console Performance report](https://support.google.com/webmasters/answer/7576553?hl=en).

## Geography and crawler data

Country reports use the included October 2026 DB-IP Lite Country MMDB. Attribution appears in reports and exports. Lookup occurs locally and the visitor IP is not sent to a geolocation API or retained in analytics rows. No browser location permission is requested.

For available state/region/city labels, open **Geography data** from Analytics and upload a legitimately licensed City MMDB or its gzip container. The upload is validated and placed at a fixed private location. A failed replacement keeps the previous usable file. Country lookup continues when optional City data is missing or unusable. The normal hosting upload limit may be below the module's maximum and is shown separately. Removing optional city data returns to the bundled country lookup for future visits; it does not relabel historical rows.

Google and Bing verification uses local official IP-range snapshots, not live DNS. Snapshot retrieval and publisher dates are recorded separately in the bundled metadata and geography documentation; the dashboard shows each observation's verification label. A snapshot older than 90 days after retrieval stops verifying new hits; user-agent reporting continues. Rebuild a new version after an intentional reviewed data refresh, not by replacing already-delivered archive bytes. See [ANALYTICS_GEO.md](ANALYTICS_GEO.md) for provenance, licensing and update instructions.

## Data and access boundaries

Analytics stores an installation-bound anonymous visit hash, allowlisted page paths, coarse acquisition/client/location labels, activity measurements and aggregate crawler counts. It does not store raw IPs, full user agents, arbitrary URL query data or entered form contents. DNT and GPC requests are excluded, as are signed-in staff public previews. The public collector accepts only a bounded JSON payload from the exact configured origin with a token bound to the current session and page. It cannot return the owner report.

Paused or unavailable analytics does not stop the public website. Missing analytics tables yield an owner-facing database-update notice and no public tracking token. Retention runs through the existing owner-reviewed cleanup or explicitly enabled scheduled maintenance, with bounded batches and reported counts. Selecting a retention duration alone does not create a host schedule.

Snapshots include analytics tables for completeness, but existing business/content restores do not rewind analytics. Ongoing traffic and analytics cleanup are excluded from the protected-business-state digest so they do not block a legitimate restore. The optional private City file is maintained separately; keep its original licensed download if it needs restoration.

## Fresh installation

The cumulative Full ZIP includes all runtime code, bundled data, public assets and both new migrations. The existing single-page installer creates the analytics tables along with the rest of the site. The A2/cPanel manual database configuration, permanent installation lock, separate owner sign-in and safe installer cleanup remain in place.

## Update an installed 1.0.x website

Migrations `007.sql` and `008.sql` add four analytics tables. Existing migrations are unchanged. The current **Browser upgrades** feature intentionally accepts only unchanged schemas and will refuse this cross-schema release before replacing application files. Do not strip the migrations, rename the version, change checksums or disable that protection to force acceptance.

1. Use an isolated staging copy and save verified backups of its database, configuration/encryption secret and media. Keep a copy of the previously deployed code and any independent recovery receipt.
2. Deploy the cumulative code through the hosting panel's reviewed update process, preserving the original account-private runtime, configuration, uploads and installed owner. A completed website does not run the fresh installer again; do not recreate a previously removed installer folder on an installed deployment.
3. Sign in to the existing owner account and open **System & maintenance → Database updates**. Apply the included additive migrations. A repeated application verifies the ledger without recreating the owner or deleting existing content.
4. Open Analytics, visit public pages in a separate ordinary browser session and verify the new observations. Check the selected retention settings, country attribution, live data and any optional City/query imports. Review the public desktop/mobile screenshots and run the applicable acceptance checks before cutover.

There is no automatic rollback of the added SQL schema. Existing same-schema recovery checkpoints are not a universal cross-schema rollback. Follow the reviewed staging backup/restore plan if this deployment must be reversed. The implementation does not contact or modify the real A2 site merely by building this archive.

## Update an installed 1.1.0-rc.1 or 1.1.0-rc.2 website

This candidate corrects read-only reports and retains the graphical presentation introduced in rc.2. Migrations `007.sql` and `008.sql` are unchanged and no new migration is added. Follow the existing guarded **Browser upgrades** process for the newer cumulative Full ZIP when its same-schema, recovery and file-integrity checks pass. Preserve the original owner, configuration/encryption secret, database, private runtime and uploads. Existing analytics history remains available, including previously recorded detail-page slugs; do not run the fresh installer again.

## Verification

Reproducible suites are `analytics_unit.php`, `analytics_geo_unit.php`, `analytics_admin_unit.php`, `analytics_integration.py`, `analytics_admin_integration.py`, `analytics_geo_integration.py` and `analytics_browser.py` under `app/tests/`. They use only disposable test databases and synthetic visitors/data. GitHub Actions is configured for PHP/MySQL and PHP/MariaDB variants. Exact observed results belong in [TEST_RESULTS.md](TEST_RESULTS.md), not inferred from the presence of a workflow.
