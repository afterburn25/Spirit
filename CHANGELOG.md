# Changelog

## 0.1.0-dev.2 — 2026-10-13 — tested development foundation, not accepted release

Added the real MySQL/MariaDB HTTP integration matrix and Chromium installer/admin smoke. Run 38454993289 on aa01c32bb5b9450579880b8d2372e5582a38c161 passed both database legs and desktop/mobile Chromium checks. Corrected a fixture that assumed only /admin/index.php instead of accepting the application's valid /admin/ redirect.

Hardened runtime placement at account level, including nested-public_html and configured public-root checks, canonical paths and symlink rejection. Added real per-child IO checks, defensive access rules, verified restrictive permissions before secret writes and truthful unreadable/undeletable installer handling. Added 26 storage regressions alongside the existing 43 isolated unit checks.

Hardened deployment packaging to include known application paths only and exclude test/build tools, local/runtime data, setup receipts, symlinks and output directories. Private receipt creation and versioned outputs are exclusive; unsafe output/version values are refused. Added 10 passing local package regressions and included them in the next full CI run; that revision's result is recorded separately in TEST_RESULTS.md.

Real-host acceptance, early-DDL interruption boundaries, legacy-runtime migration and final packaging/approval remain pending. Public redesign and later business modules are not implemented in this version. No live deployment or final release claimed.

## 0.1.0-dev.1 — 2026-10-13 — unaccepted development draft

Added organized installer/admin application source, private runtime and safe cleanup routines, package-specific setup authorization, owner authentication/settings source, shared UI, database schema, packaging script and unit tests. Established complete scope, design lock, roadmap, handoff, installation constraints, security notes and test evidence in Git.

Local verification at that point: 23 PHP files linted; 43 non-root unit checks passed. MySQL/browser/host integration, package acceptance and full public design were unverified or pending at that commit. Later verification is recorded in dev.2 above.
