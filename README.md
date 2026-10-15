# Spiritual Gorillaz — 1.0.0-rc.4

Cumulative PHP/MySQL website and owner administration for shared A2/cPanel hosting. `main` is authoritative. This is a staging release candidate, not an accepted final 1.0 or a live deployment.

## This continuation

RC4 hardens content-history browser verification after the exact RC3 main workflow failed its pagination check. Tests now wait for the correct destination and assert the exact records in the Content results region. The original 25-row first page, six remaining records and eleven comparison fields remain mandatory. Three next/previous round trips, reload, empty search and private restoration are checked with unchanged public snapshots and media permissions.

The same suite runs in Chromium, Firefox and WebKit, at 1440/390/320px, root/subfolder, with and without JavaScript. Each engine completed 443 reported passing history checks. Full workflow 38675061084, focused 38675061064 and three-engine 38675061080 passed for application/test commit `5373be7884b3b9e6d10dd9d051d0ffdf9dd4e021`, tree `5ffb3a8b2ff1b70c3894ad38c276a2c30e94dab1`. Final documentation-only changes do not alter those tested bytes. See docs/TEST_RESULTS.md for evidence and limitations.

**No customer-facing feature, production artwork, runtime PHP/CSS/JavaScript, installer, menu, database migration or recovery kernel changes in this pass.** VERSION advances to identify an immutable cumulative candidate. The prior RC3 archive is not overwritten.

## Retained website

The approved black/deep-navy, gold and blue design; eight managed section introductions/artwork with actual-layout saved-draft previews; pages, band, media and press; published music with persistent playback/native fallback; shows with external venue ticket links; booking/newsletter/email queue; merchandise, customer accounts and sandbox-first Authorize.net orders; scoped staff access; protected backup, same-schema browser upgrade/recovery, maintenance and diagnostics. The grouped administration menu keeps browser-local preferences. Real production content must be supplied and explicitly published, never invented from examples.

## Install or upgrade

Read docs/INSTALLATION.md, docs/CPANEL_INSTALLER.md and docs/UPGRADES.md. Fresh installation uses one manual A2/cPanel browser form with existing database and owner details, no setup code, API token, 20i or automatic database creation. cURL and ZIP have separate optional-installation notices. PHP cannot override hosting ownership/ACL restrictions. Completed setup remains permanently locked even when physical installer cleanup is denied.

Existing installations upgrade rather than reinstall or create a replacement database. Preserve original private configuration, encryption secret, database, uploaded media and offline backup/update recovery receipts. Back up first and use protected staging. No real hosting setting, mailbox or payment service was changed here.

## Remaining acceptance

Approved master artwork, genuine band/store content and policies; actual A2 private storage/extensions/cleanup/recovery; receiving-mailbox delivery; genuine merchant sandbox; populated physical-device/accessibility/performance review and owner approval. Automated tests do not replace these checks. See PROJECT_STATUS.md, SESSION_HANDOFF.md, docs/ROADMAP.md and docs/LAUNCH_CHECKLIST.md.
