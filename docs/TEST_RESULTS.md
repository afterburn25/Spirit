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
- Dashboard: 97 Chromium checks pass at root/subfolder 1440/390/320, including live polling/pause, real dynamic navigation/time flush, CSV and native no-JavaScript settings.
- Admin SQL/HTTP: 107 checks pass at root/subfolder, covering private reports/CSV, validated query imports, stale owner access and City management.
- City uploads: 86 checks pass, including real plain/gzip uploads, exact private bytes, malformed/truncated/oversized preservation, PHP limits, owner/CSRF/session/lock controls and safe removal. Fixed a stale zero-size PHP stat cache before validating a newly written file.
- Cleanup: 47 checks pass, including read-only analytics inventory, expired/current row preservation and exact displayed removal counts. Backup: 77 checks pass, including changes to all four analytics tables after snapshot that neither block nor rewind a valid site-data restore.
- PHP syntax: all 256 PHP/inc files pass. Packaging: 15 tests and the deterministic release gate pass. Generated public bundles match their committed sources, and `git diff --check` passes.
- Actual public capture: 21 screenshots pass across eight public routes at desktop/phone widths, four additional Home tablet widths and the 320px menu. All images/backgrounds decode; no horizontal document overflow. Public captures use unseeded installation content. Exact delivery ZIP captures and remote Actions evidence are being finalized; no pending check is reported as passed.

## Remote Actions

New candidate Actions results are pending publication and observation. Latest 1.0.13 public navigation, storefront navigation and public-asset budget failures were examined and addressed; earlier successful runs do not certify the new head. Preserve the separate PHP 8.2/MariaDB and PHP 8.4/MySQL matrix and existing cross-browser/Apache workflows.

## Acceptance boundaries

These tests do not establish target A2 ownership/ACL/proxy/OPcache behavior, physical-device performance, complete accessibility, actual SMTP/merchant acceptance or owner artwork approval. The same-schema ZIP updater deliberately refuses 007/008; existing sites use the documented code deployment and additive Database updates path. No production install, content publication or live payment occurred. Keep the candidate draft and unmerged until screenshot approval.
