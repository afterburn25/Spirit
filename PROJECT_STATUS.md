# Project status

Updated 2026-10-13. Development identifier: **0.1.0-dev.2**. Full milestone 0.1.0 is not yet promoted.

## Implemented
Existing installer/owner-admin foundation plus account-level private runtime selection, known/explicit public-root checks, canonical-path/symlink refusal, private subdirectory IO probes, defensive deny rules, verified permissions before secret writes, and truthful unreadable/undeletable installer handling. Original 43 unit checks are isolated from real account runtime data; 26 new storage checks are added.

GitHub Actions now runs real MySQL/MariaDB installation and HTTP account tests. Chromium smoke tests at 1440px and 390px are wired for actual installer/admin pages. They are not mockups or visual acceptance of the full public design.

## Evidence
Baseline CI syntax/original 43 checks passed. Baseline HTTP reached real database install and physical folder deletion, then stopped on a fixture URL expectation. That expectation has been corrected. All 26 new storage assertions passed locally as non-root. Full updated CI and browser outcomes are pending; see docs/TEST_RESULTS.md.

## Still pending / blocked
Shared-host and Apache/LiteSpeed acceptance, provider-specific proxy/TLS and account alias validation, package-output secrecy review, earlier DDL crash recovery, old-runtime migration verification, full release packaging and acceptance. The public page remains the temporary holding page. Approved design implementation and all features from 0.2.0 through 1.0.0 remain planned. No live deployment, payments, email delivery, visual sign-off, accepted release or binary moodboard commit is claimed.

GitHub writes work. Continue from current main and update the handoff and evidence before ending each session.
