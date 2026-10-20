# Verification — 1.1.0-rc.1

## Current candidate

The candidate starts from latest 1.0.13 commit `2afbd551d6083d86d26499a9bf82511aa7f7f9c2`. Changes include new analytics/schema/reporting, offline data/City imports, public asset consolidation, mobile navigation and bounded CMS section artwork. Prior RC7 evidence is retained in `history/TEST_RESULTS-before-analytics.md`; it is not evidence that new candidate workflows passed.

## Observed local verification

Runtime: PHP 8.3.6, MariaDB 10.11.14, Python with Pillow 12.3.0, Playwright 1.57.0 and Chromium 143.0.7499.4. Tests use disposable databases, isolated packaged applications and synthetic data. No real customer, visitor, mailbox or merchant data is involved.

- Collection units: 67 passing checks. Geography/crawler units: 75 passing checks. Admin/parser units: 26 passing checks, including strict grouped-number parsing.
- Analytics SQL/HTTP: root and subfolder complete; session-bound tokens, cross-tab elapsed-time cap, replay bounds, visibility/live expiry, missing-time coverage, attribution/privacy exclusions, crawler separation, retention and fail-open public rendering pass. Existing-schema to 007/008 additive updates and repeated application pass while retaining the owner.
- Public navigation: root/subfolder 820/390/320 and 320x568 active-player checks pass, including pointer/keyboard/focus/resize/native navigation and preserved audio node.
- Media quality: 1440/390/320 checks pass, including the existing 130,000-byte public asset budget. Final public CSS/JS is 122,397 bytes across four files. Home artwork geometry additionally passes at 1180/1024/900/821px: native foreground aspect ratio, decoded wide background, full-height soft fade, and no copy clipping or button/caption overlap.
- Section artwork: actual root/subfolder public/editor/private-preview layouts pass at 1440/390/320, including complete portrait artwork in bounded frames.
- Dashboard: 99 Chromium checks pass at root/subfolder 1440/390/320, including live polling/pause, real dynamic navigation/time flush, CSV and native no-JavaScript settings. The visitor is explicitly foregrounded and the departing beacon's successful response is awaited before checking stored time.
- Admin SQL/HTTP: 107 checks pass at root/subfolder, covering private reports/CSV, validated query imports, stale owner access and City management.
- City uploads: 86 checks pass, including real plain/gzip uploads, exact private bytes, malformed/truncated/oversized preservation, PHP limits, owner/CSRF/session/lock controls and safe removal. Fixed a stale zero-size PHP stat cache before validating a newly written file.
- Cleanup: 47 checks pass, including read-only analytics inventory, expired/current row preservation and exact displayed removal counts. Backup: 77 checks pass, including changes to all four analytics tables after snapshot that neither block nor rewind a valid site-data restore.
- Administration navigation: 103 unit checks and the full root/subfolder Chromium journey pass, including all 35 owner destinations and explicit owner-only analytics access. Branding: 109 SQL/HTTP checks pass; the complete-table read-only preview assertion is measured before the separate public-page observation.
- PHP syntax: all 256 PHP/inc files pass. Packaging: 15 tests and the deterministic release gate pass. Generated public bundles match their committed sources, and `git diff --check` passes.
- Actual public capture: 21 screenshots pass across eight public routes at desktop/phone widths, four additional Home tablet widths and the 320px menu. All images/backgrounds decode; no horizontal document overflow. Public captures use unseeded installation content. The exact delivery ZIP's public and analytics capture manifests are supplied in its companion screenshot archive, with per-image hashes and explicit synthetic-data provenance for owner reports.

## Remote Actions

**All ten workflows and all 23 jobs passed** on application and test source `917997185a7512189353a4008ffbc1cd3b4f9951` in [PR 37](https://github.com/afterburn25/Spirit/pull/37). This documentation checkpoint records that observed result; it does not substitute previous-version evidence for a new code change.

| Workflow | Jobs | Observed result |
|---|---:|---|
|[Website and installer verification](https://github.com/afterburn25/Spirit/actions/runs/39373347004)|3|Passed|
|[Visitor analytics verification](https://github.com/afterburn25/Spirit/actions/runs/39373347230)|2|Passed|
|[Public navigation and native controls](https://github.com/afterburn25/Spirit/actions/runs/39373347254)|3|Passed|
|[Populated storefront browser verification](https://github.com/afterburn25/Spirit/actions/runs/39373347064)|3|Passed|
|[Site logo workflow verification](https://github.com/afterburn25/Spirit/actions/runs/39373347116)|4|Passed|
|[Authoring safety verification](https://github.com/afterburn25/Spirit/actions/runs/39373347097)|4|Passed|
|[Focused maintenance diagnostics](https://github.com/afterburn25/Spirit/actions/runs/39373347156)|1|Passed|
|[Software release gate](https://github.com/afterburn25/Spirit/actions/runs/39373347148)|1|Passed|
|[Review package artifact](https://github.com/afterburn25/Spirit/actions/runs/39373347108)|1|Passed|
|[Candidate installation package](https://github.com/afterburn25/Spirit/actions/runs/39373341248)|1|Passed|

The complete PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 unit, SQL/HTTP and configured browser journeys passed, including actual Apache HTTPS access rules. Separate navigation, storefront, logo and authoring matrices passed in Chromium, Firefox and WebKit where configured. Payment and email scenarios use guarded fixtures; these results are not external-provider acceptance.

The first round on `a9d5fca302f2dd689efc0fae7eb96f2668399f7b` exposed three test-boundary issues: the old administration-menu destination count, an unsynchronized asynchronous timing assertion, and a branding preview fingerprint that also included an unrelated public visit. The verified follow-up corrected those test observations while retaining complete access, read-only and timing guarantees. No failed first-round result is counted as a pass.

## Acceptance boundaries

These tests do not establish target A2 ownership/ACL/proxy/OPcache behavior, physical-device performance, complete accessibility, actual SMTP/merchant acceptance or owner artwork approval. The same-schema ZIP updater deliberately refuses 007/008; existing sites use the documented code deployment and additive Database updates path. No production install, content publication or live payment occurred. Keep the candidate draft and unmerged until screenshot approval.
