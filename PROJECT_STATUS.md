# Project status — 1.0.0-rc.4

## Completed in this continuation

Content-history browser verification was hardened after RC3 exact-main workflow 38672702303 failed at pagination. The previous parent retry was not a proven fix. RC4 waits for the expected URL and exact scoped results, retaining the 25/6 record and eleven-field requirements. It adds three mandatory next/previous cycles, reload/empty-search checks and two actual private restores per journey, with unchanged published JSON/timestamp, anonymous page and old/new media permissions.

Chromium, Firefox and WebKit each passed the new journey at 1440/390/320px in root and subfolder installations, with JavaScript enabled and disabled: 12 contexts and 443 reported passing checks per engine. All retained gallery/release/navigation/section suites also passed. Seventy-two synthetic history screenshots are in the inspected evidence archives; selected desktop/mobile/no-JavaScript screens were visually reviewed.

## Verified source

Commit `5373be7884b3b9e6d10dd9d051d0ffdf9dd4e021`, tree `5ffb3a8b2ff1b70c3894ad38c276a2c30e94dab1` passed full 38675061084, focused 38675061064 and three-engine 38675061080. Full verification includes PHP 8.2/MariaDB 11.4, PHP 8.4/MySQL 8.4, all thirteen retained Chromium workflow steps and Apache HTTPS. Local 223 PHP/inc syntax checks, 747 pure assertions, six JavaScript syntax checks and twelve packaging tests passed. Local PHP lacks PDO MySQL/GD/cURL/ZIP; no local SQL success is claimed.

The final documentation commit leaves tested runtime, tests and workflows unchanged. Subsequent exact-main runs are additional evidence and must be read live rather than inferred from a version number. Details, archive hashes and a nonfatal WebKit gallery runner warning are recorded in docs/TEST_RESULTS.md.

## Unchanged and outstanding

This is test hardening, not a new user-facing site feature. RC3 page/artwork tooling, approved design, remembered administration navigation and manual one-page A2 installer remain. All earlier deployment paths, six migrations, five pinned access/recovery files and runtime code are preserved. No 20i/token/setup code/automatic database creation, fake production content, hosting alteration or real provider operation.

Production master artwork, genuine content/policies, actual A2/mailbox/merchant/recovery acceptance, physical-device quality and final owner approval remain. Existing installations upgrade with backups; do not reinstall or replace their database/private encryption secret. Same-schema code upgrades and same-instance guarded data recovery retain their documented limits. Final 1.0 is not released.
