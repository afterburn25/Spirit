# Verification — 1.1.0-rc.2

## Current candidate

This candidate starts from the delivered rc.1 source `1bde497daa174ac265096e1adee967dd9bf3f900`. It adds the requested dedicated Analytics navigation, Overview snapshots, graphical reports and visitor exploration. The application remains on schema migrations 007/008; existing migrations and public collector behavior are unchanged.

The rc.1 evidence is retained in `history/TEST_RESULTS-1.1.0-rc.1.md`. All ten workflows on its final documentation checkpoint completed successfully before this work began. Those earlier results are baseline evidence, not verification of the rc.2 changes.

## Current verification

The following local checks passed on rc.2 application/test source using PHP 8.3.6, MariaDB 10.11.14 and disposable installations. Remote rc.2 Actions and final delivery capture evidence are pending at this checkpoint; no pending check is counted as a pass.

| Check | Observed result |
|---|---|
|Analytics admin/chart/import units|**35 checks passed**, including safe empty arrays, exact data values, unique SVG IDs, escaped labels, cohort percentages and native heatmap data.|
|Analytics owner SQL/HTTP integration|**191 checks passed** at root and `/band`: report bucket totals, bounded filters, session/detail scope, journey pagination/chronology, supplied attribution escaping, owner boundaries, query imports and geography controls. Supported search hosts and short lookalikes are distinguished.|
|Rendered analytics browser suite|**141 checks passed** in Chromium at root and `/band`: the traffic chart is within the first 1440×960 screen; actual values, keyboard readouts, color distinctions, channel/hourly/heatmap charts, 390/320px layouts, visitor drilldowns/filters, live source/OS/links and pause/resume, public page-time beacons, CSV, geography and native forms.|
|Final report-section navigation|**4 focused assertions passed** after the final JavaScript adjustment: click, hash change, browser back and direct anchor. Persistent regression assertions were added for CI.|
|Public navigation and controls|**401 checks passed** in Chromium at root and `/band`. Search sits 8–20px beside Book the Band, aligned and centered with logo clearance at 1440/1180/1024/900/821px; dropdown/focus/Escape, mobile menu, persistent audio, native controls and no-JavaScript navigation remain usable with unchanged business data.|
|Public asset build|Committed bundles reproduce exactly: **123,884 / 130,000 bytes** across four loaded CSS/JS files.|
|Packaging|**15 package tests passed**, including immutable version filenames, complete hashes, private/development exclusions and repeatable bytes. The software release gate passed after two independent cumulative builds.|
|Changed-source validation|15 changed/new PHP files passed syntax checks; changed JavaScript passed Node syntax and six changed Python tools compiled at this checkpoint. Final source checks also run in CI.|
|Schema and collector continuity|Migrations 007/008, the public Analytics collector and browser sampling asset, and the pinned recovery engine remain byte-identical to rc.1.|

## Focused Navigation and Overview checks — rc.2

Observed locally on 2026-10-20 against the working rc.2 source. These checks used packaged disposable PHP/database installations and synthetic traffic, with payment networking disabled; they did not access the real A2 website or production audience data. They are focused checks, not a claim that the full rc.2 workflow matrix has completed.

| Check | Observed result |
|---|---|
|`php tests/admin_navigation_unit.php`|**109 checks passed.** Seven owner groups and 37 total destinations, including a separate three-link Analytics group; four communication destinations remain under Bookings & communication. Every destination exists, identities are unique, staff receive no Analytics links, and opaque saved-menu scopes remain isolated.|
|Packaged owner Overview, Chromium, root and `/band`|**Passed.** Empty-state report, explicit seven-day UTC window, uncached response, seeded **67 visits / 131 pageviews / 35.8% bounce**, real daily SVG series, seven-row native daily table, real top referrer/country highlights and working links into Analytics/visitor exploration.|
|Overview responsive geometry|**Passed at 1440, 390 and 320px** in both root and subfolder checks. All six metric cards and both traffic series render without document overflow; the traffic chart is within the first 960px desktop screen.|
|Read-only and no-JavaScript behavior|**Passed.** Reload, resize and daily disclosure preserve the full database fingerprint. With JavaScript disabled, Overview metrics/SVG, native daily disclosure and the visitor-explorer link remain usable.|
|Paused and unavailable reporting, additional Chromium root run|**Passed.** Pausing collection retains the 67 seeded visits and performs no read-side writes. Temporarily renaming an analytics table returns a usable 200 Overview with an explicit unavailable state, no invented zero metric cards and the existing operational summaries. Test state was restored afterward.|
|Phone navigation, additional 390px Chromium root run|**Passed.** The dedicated category, unique current destination, persisted expand/collapse choices, exact URL/query/hash reload, sidebar scroll restoration, focus containment, Escape handling and per-tab behavior remain correct with no database mutation or uncaught page errors.|
|Owner/staff identities and direct report routes, Chromium root|**Passed.** Owner snapshot/menu visibility, absence for content editors/store managers, direct staff 403 responses for dashboard/explorer/geography, exact current keys on all three Analytics destinations, corrupt/unavailable browser-storage fallback and native no-JavaScript navigation.|
|Source validation|**Passed.** PHP syntax for the Overview controller/partial, navigation, geography renderer and shared header; Python compile for the navigation browser test; `git diff --check`.|

The reproducible assertions are in `tests/admin_navigation_unit.php` and `tests/admin_navigation_browser.py`; the existing verification/storefront workflows already invoke those files. Focused runs called the browser test functions directly to isolate the changed behavior before the broader final suites. The additional root run includes the phone chart label/focus improvements and paused/unavailable coverage; the earlier root/subfolder run established path and responsive behavior. A final text-only Overview footer addition retains the DB-IP country attribution and must be present in final delivery captures.

Actual 1440px desktop and 390px phone Overview QA images were captured and visually inspected. Values are synthetic fixture values, not production measurements. The snapshot explicitly labels live activity as **at page load** with a UTC timestamp and a reload instruction; it does not claim continuous polling. Public navigation QA has since passed the 401-check Chromium suite described above. A QA-only package produced 19 actual administration captures, which were visually inspected at desktop and phone sizes. The final delivery must use fresh captures tied to its exact archive; broader latest-head rc.2 CI evidence is still pending at this source checkpoint.

## Acceptance boundaries

Automated workflows use disposable databases and synthetic traffic. Exact-package owner screenshots label synthetic demonstration data and are accompanied by image/package hashes. They do not report real production audience counts.

Actual hosting behavior, owner screenshot approval and external provider acceptance remain separate. This work does not install or activate services on the real A2 website.
