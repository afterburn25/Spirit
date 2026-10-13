# Project status

Updated 2026-10-13. Development version **0.1.0-dev.2**. Latest tested application/package commit **cb1ce92a4af133e5a9c86fcfd7dce281af4ee336**; both jobs in Actions run **38456010840** succeeded. The documentation checkpoint that follows changes no executable code.

## Implemented and verified

Organized PHP/MySQL source, guarded browser installer, initial owner and basic administration, account-level private runtime selection, known/explicit public-root checks, path/symlink protection, actual IO probes in private subdirectories, restrictive permissions before secret writes, independent completion lock, safe installer removal and owner cleanup retry, settings and password/session management.

Packaging includes known deployment paths only, excludes test/build scripts and private/runtime data, protects the separate setup receipt and refuses overwrites. Git retains executable tests and the automated matrix.

**43 unit + 26 storage + 10 package tests passed**, plus real SQL/HTTP root/subfolder/recovery workflows on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4. Chromium smoke passed on PHP 8.4 at 1440px and 390px. See docs/TEST_RESULTS.md for exact scope, run and job evidence.

## Not complete yet

The 0.1 milestone still needs remaining restart/early-DDL edge-case review, legacy-runtime migration safety, graceful diagnostics for unusual host layouts, Apache/actual-host TLS/alias/ownership validation, and final staged package acceptance. Unknown provider restrictions must fail safely, not pretend they can be bypassed.

The public site is still a temporary holding page. The approved design image binary has not yet been committed. Versions 0.2 through 1.0 remain the fixed next milestones; no CMS/music/events/booking/store/payment/backup feature is falsely marked complete. No live deployment, real transaction, production email delivery, final release or owner acceptance is claimed.

GitHub read/write works. Main, AGENTS.md, SESSION_HANDOFF.md, design lock, roadmap and evidence are the continuation source of truth.
