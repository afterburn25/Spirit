# Verification — 1.1.0-rc.3

## Current source and exact baseline

This candidate starts from delivered rc.2 commit `f6b8e6349e75d3ca465bec60a010f74f8eaf84e4`. It fixes canonical detail-page visitor filters and the existing Ecosia search-source classification. The collector, browser sampling asset, migrations 007/008 and pinned recovery layer are unchanged. Retained traffic needs no data conversion.

The rc.2 source checkpoint is archived in `history/TEST_RESULTS-1.1.0-rc.2.md`. Its later final receipt, `history/VERIFICATION-1.1.0-rc.2.json`, records all 10 workflows / 23 jobs passing, the exact delivered archive, 40 screenshots and the 43-check rc.1-to-rc.2 upgrade. Those results are historical baseline evidence and do not certify this candidate.

## Current local verification

Observed locally using PHP 8.3.6, MariaDB 10.11.14 and Chromium in disposable installations. Remote rc.3 Actions and final delivered-package captures occur after this source checkpoint and are recorded separately below. No pending remote result is counted as a pass.

| Check | Observed result |
|---|---|
|Analytics admin/chart/import units|**57 passed.** Canonical static/detail/custom-page identifiers, subfolder normalization, supplied Ecosia terms, forbidden path forms and the retained chart/import contracts.|
|Analytics owner SQL/HTTP integration|**223 passed** at root and `/band`. Each of the five detail families and a second release slug select only their own session and native 200 journey; invalid/private forms reject 400. Ecosia and its www host classify as organic, while lookalikes remain referrals.|
|Delivered rc.2 negative witness|**Passed.** The new real-link browser regression reproduced the old delivered ZIP's actual rejection of a canonical slug path, without changing that archive or its runtime.|
|Rendered analytics browser suite|**201 passed** at root and `/band`, including 14 actual dashboard→explorer→visit journeys. Exact path, dates and selected session remain correct for all detail families, same-route/different-slug controls and no-JavaScript custom pages. Retained charts, live controls, responsive layouts and native forms also pass.|
|Packaging|**15 tests passed**, including immutable version paths, manifest coverage, private/development exclusions and repeatable bytes. The deterministic software release gate also passed with two independent cumulative builds.|
|Public bundles|Reproduce exactly at **123,884 / 130,000 bytes** across 4 files; public code is unchanged.|
|Source validation|Changed PHP/Python syntax and `git diff --check` passed.|
|Continuity|Migrations 007/008, public collector/sampling and all pinned recovery/access files are byte-identical to delivered rc.2.|

The initial SQL/browser setup attempts encountered a stopped disposable database. After the same local server was restarted, the complete runs above passed. No production database was accessed. The two runtime changes are limited to visitor filter validation, the supported-search host constant and explanatory visitor-field text.

## Evidence produced after source publication

GitHub builds and tests the published candidate. The final companion `FINAL-VERIFICATION.json` in its delivered screenshot/review archive records the observed workflow/job outcomes, source commit, exact Full ZIP hash, capture manifests and exact delivered-version upgrade result. The PR links those results. A prior source-checkpoint statement that CI/captures were pending is not a final release verdict; the companion receipt identifies the same immutable source and archive.

The final expected capture set contains 21 unseeded public screenshots and 21 synthetic administration screenshots, including the desktop/phone retained detail-page filter. Counts and outcomes must be checked against the completed capture manifests before delivery.

## Acceptance boundaries

Tests use disposable databases and synthetic data. Administrative review images are labeled synthetic demonstration data and are not measurements of the real audience. No real A2 deployment, mailbox delivery, merchant transaction or physical-device acceptance is established by the automated results. Current owner screenshot review and applicable host/provider acceptance remain separate.
