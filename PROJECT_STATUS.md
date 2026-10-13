# Project status

Updated 2026-10-13. Version **0.1.0-dev.2**. The 0.1 foundation is now database/HTTP/browser-smoke verified, but is not yet an accepted release or a completed public website.

## Implemented

Organized PHP/MySQL source; guarded browser installer; owner account/authentication and basic administration; account-level private runtime selection; known/explicit public-root checks; canonical-path/symlink protections; real IO probes in private subdirectories; permission enforcement before secret writes; completion lock and safe installer deletion/retry; settings and password management.

Packaging now includes known deployment paths only, excludes test/build scripts and private/runtime data, protects separate setup receipts and refuses overwrites. Git contains executable unit, storage, package, SQL/HTTP and Chromium tests with an automated two-database matrix.

## Verified

Run **38454993289** on commit **aa01c32bb5b9450579880b8d2372e5582a38c161** passed PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4. Original 43 unit and 26 storage checks passed; real root/subfolder installation, owner workflows, cleanup denial/retry and migration recovery passed. Chromium smoke passed at desktop/phone widths on PHP 8.4. The additional 10 package tests passed locally and are included in the next matrix revision; see docs/TEST_RESULTS.md for exact evidence.

## Remaining

Before promoting 0.1: additional DDL-crash boundary review, legacy-runtime migration safety, real Apache/hosting access-rule and TLS/alias validation, and staged package acceptance. Unknown hosting restrictions must fail safely, not claim permissions can be bypassed.

The public site is still the temporary holding page. The approved design binary is not yet committed. Versions 0.2 through 1.0 remain the fixed next milestones; no content/payment/email/backup feature is falsely marked complete. No live deployment, real payments, production email delivery or owner acceptance is claimed.

GitHub writes work. Main plus SESSION_HANDOFF.md and the evidence files are the continuation source of truth.
