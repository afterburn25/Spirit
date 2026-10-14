# Resume here — manual-only one-page installer

Repository **afterburn25/Spirit**, **main** authoritative. Current target **0.9.0-dev.9**; branch **work/manual-database-installer**. Inspect live refs before writing. Read AGENTS.md, status and TEST_RESULTS.md. Never force-push or overwrite concurrent changes.

## Latest user decisions

The user cannot get a cPanel token and explicitly removed automatic database creation. **A2/cPanel, manual existing database only, no 20i, no hosting API token, no setup code.** Keep all setup fields/checks/corrections/completion on one page. A later pasted cURL/ZIP notice led to splitting optional extension diagnostics; do not hide missing features or reintroduce automatic setup.

## Changes

Remove all three Cpanel runtime classes and the matching five retired test files/workflow calls. The request handler and domain installer reject retired automatic inputs. No provider calls, database/user creation, password resets or privileged grants remain. Preserve an existing unfinished cpanel-provision.php record without executing/deleting/adopting it; owner/A2 support must inspect it before a fresh setup. Normal completed installations and manual pending.php recovery are unchanged.

cURL and ZIP are individually reported by Installer::optionalChecks in setup and owner Installation health. Both are optional for installation; checkout needs cURL and browser ZIP upgrades need ZIP. Enable extensions for the PHP version actually serving this domain through cPanel Select PHP Version or A2 support. No token is needed. Corrected obsolete maintenance-not-available copy in health.

## Verification

Base dev8 main 1cffdccaaab20737b6ddf1f0f62ee306d5c61d51 passed full38563052511. New exact .9 verification is pending. Run 38 manual-installer unit assertions, new SQL/HTTP test (including actual PHP environments with cURL/ZIP disabled), cumulative units/package/browser suites and Apache HTTPS. Browser setup checks remain1440/390/320 and no-JavaScript. Inspect rendered form/completion/health screens. Remove only obsolete cPanel tests, not live payment/upgrade/TLS regressions.

## Delivery and next work

Promote only the verified branch, record exact CI evidence and package cumulative Full ZIP with no setup receipt. Preserve migrations001–006, pinned recovery/access files, original encryption/config/data/uploads and unrelated backup/update recovery keys. Existing sites upgrade; do not create a replacement database or reinstall. Fresh setup is open until completion, so protect unattended staging.

Actual A2 host/manual database and extension acceptance, real mailbox/merchant sandbox, approved master artwork/content, populated-site quality and owner acceptance remain. Do not silently enable production services or label the site1.0. Local runtime lacks required database/image extensions; exact full integration evidence comes from Actions, not local lint.
