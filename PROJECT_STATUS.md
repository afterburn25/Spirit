# Project status

Updated: 2026-10-13. Development identifier: **0.1.0-dev.1**.

## Implemented in source
- Organized PHP application, shared views, browser installer, administration and static asset folders.
- Package-specific installer claim code; only its hash goes into the deployment ZIP, never the private receipt.
- HTTPS enforcement; HTTP exception limited to loopback on PHP's development server, not generic production loopback/proxy traffic.
- Actual private-storage create/write/read/rename/delete probes; atomic configuration publication and restricted permissions.
- Installer operation lock, prefix collision checks and same-install recovery metadata. Completion configuration/marker precede deletion.
- Allowlisted installer-file removal, symlink/unknown-file refusal, truthful blocked-deletion reporting, owner-only cleanup retry.
- MySQL schema/provisioning source, owner login and logout, session invalidation, login throttling, password changes, basic settings and activity records.
- Responsive dark/navy-and-gold installer/admin foundation and an explicitly temporary public holding page. This is NOT the approved full public design implementation.

## Locally tested
- PHP 8.4.24 CLI; 23 PHP source files passed syntax checks.
- 43 unit checks passed as an unprivileged account, including actual denied write/deletion tests.
- Latest security check restricts the development HTTP exception so production loopback does not bypass HTTPS.
- Exact command and coverage in docs/TEST_RESULTS.md.

## Not yet verified / blocked
- This execution environment has PDO but no pdo_mysql driver. No MySQL/MariaDB integration test was executed.
- No fresh browser/database install, authenticated owner journey, interrupted-install integration test, hosting-panel extraction test, or live shared-host deletion was completed.
- No desktop/mobile rendered-browser acceptance, performance measurement or formal accessibility audit.
- Package generation code exists; deployment ZIP acceptance remains pending.
- Approved moodboard is identified in docs/DESIGN_LOCK.md; its binary has NOT yet been committed to Git.
- No CI run or deployment is claimed.

## Still pending
Complete and verify 0.1.0 before promoting it. Versions 0.2.0 through 1.0.0 remain planned: public design, content/media/press, music, shows, booking/email, commerce/accounts, payments, backup/upgrade tooling and acceptance.

GitHub writes are working. Missing implementation is not a read-only repository problem. Never mark a milestone complete merely because its source or interface exists.
