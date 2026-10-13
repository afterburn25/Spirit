# Session handoff — resume here

Repository: afterburn25/Spirit. Authoritative branch: main. Development version remains 0.1.0-dev.1 while integration tests are being added; no accepted release or live deployment.

## Current work
The existing installer/admin source has now been given an automated GitHub Actions test matrix: PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4. New real-database HTTP fixtures build a package and exercise root/subfolder installation, claim/CSRF/permission denial, installer cleanup, login, settings, password/session changes and interrupted-migration recovery. Execution is pending until Actions reports results. Do not equate source tests with passing tests.

The local execution environment lacks pdo_mysql and a database daemon, and its package/network retrieval attempt failed. GitHub connector reads/writes work. Do NOT ask the user to reconnect GitHub or grant broader access unless an actual new permission error occurs.

## Next steps
1. Read AGENTS.md, README.md, PROJECT_STATUS.md, docs/ROADMAP.md, docs/DESIGN_LOCK.md, docs/INSTALLATION.md, docs/SECURITY.md and docs/TEST_RESULTS.md on current main.
2. Inspect the newly triggered Actions run, read failed job logs, correct application/fixture issues, rerun and record exact evidence. Never force push.
3. Fix runtime selection: a sibling of one document root can be public under another domain. Prefer validated account-home placement and fail closed on ambiguous hosts. Test multiple-domain layouts, explicit host configuration, symlinks and denied permissions.
4. Verify config publication, completion and retry boundaries, including interruption after database DDL; maintain protection even when physical deletion is blocked.
5. Add rendered-browser and Apache/hosting-rule acceptance; source HTTP tests are not equivalent. Build and inspect a cumulative staging package, keep its claim receipt private, and never replace the live site without deployment authorization.
6. Commit the approved image reference, then implement the chosen public design at v0.2.0. The holding page is not the delivered redesign.

## Fixed boundaries
Full owner admin; organized PHP/MySQL shared hosting; browser installer; actual IO probes; attempted safe folder deletion with independent permanent lock; external venue ticket links only; sandbox-first merchandise payments; no made-up band content; no more mockups. All later milestones remain pending.

Every implementation session updates status, handoff, changelog, roadmap and test evidence. GitHub main is the source of truth.
