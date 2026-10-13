# Session handoff — resume here

Repository **afterburn25/Spirit**, authoritative branch **main**, development version **0.1.0-dev.2**. No accepted release and no live deployment. Preserve the user-approved design and feature roadmap.

## What landed this session

Added actual GitHub Actions SQL/HTTP integration (PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4), fixed a test assumption about /admin/ versus /admin/index.php, hardened private storage for nested addon-domain layouts, added 26 storage regressions, and added real Chromium installer/admin smoke at 1440px and 390px.

Run **38454993289** for commit **aa01c32bb5b9450579880b8d2372e5582a38c161** completed successfully in both matrix jobs. Full root/subfolder installs, owner auth/settings/password/session workflows, physical installer removal, permission-denied locking and cleanup retry, and interrupted-migration recovery are now verified in isolated environments. Do NOT repeat the old claim that no MySQL tests ran.

The last package revision adds strict deployment-path selection, receipt/runtime/test-tool exclusions, exclusive private receipts, version/output validation and 10 passing local regression tests. Its full matrix result must be read from Actions after the commit; docs/TEST_RESULTS.md records commit-specific evidence.

## Next actions

1. Read AGENTS, README, PROJECT_STATUS, ROADMAP, DESIGN_LOCK, INSTALLATION, STORAGE, SECURITY and TEST_RESULTS on live main. Preserve concurrent changes and never force push.
2. Confirm the package revision's matrix result; fix actual failures rather than loosening security expectations. Record the verified run/commit and update these docs.
3. Close remaining foundation edge cases: interruption between sentinel CREATE and ownership-row INSERT; restart/config publication boundaries; detecting/migrating a dev.1 legacy runtime rather than ignoring old state. Never remove locks or overwrite an existing prefix to make tests pass.
4. Verify Apache/LiteSpeed access rules and staging-host extraction/ownership/open_basedir/TLS/alias behavior. Existing automated HTTP tests use PHP's development server; they do not prove production server rules. Do not request production secrets in chat.
5. Commit the approved moodboard reference asset, then implement v0.2.0 as real responsive components matching it. Do not redesign or pass the holding page off as the approved site. Continue the remaining cumulative roadmap after the foundation checks.
6. Produce and verify cumulative full install packages when ready. Keep setup receipts private. No live replacement or final 1.0 tag without acceptance. Automated upgrades and backup/restore are not implemented yet.

## Environment and boundaries

The local container had PHP but no pdo_mysql/database and external package retrieval failed; actual SQL/browser tests succeeded in GitHub Actions instead. GitHub read/write access works. Do not ask the user to reconnect or grant more permissions without a new actual permission error.

Private storage is account-level and checked against known/configured public roots. It cannot enumerate every arbitrary host alias. Atypical hosts may need server-level SPIRIT_ACCOUNT_HOME/PUBLIC_ROOTS. Legacy dev.1 migration is documented but not automated/tested. User insists on safe browser setup, actual writes and attempted installer self-deletion; never promise an ACL/ownership bypass.

Fixed scope: full admin, shared-host PHP/MySQL, organized folders, external venue ticket sellers only, separate sandbox-first Authorize.net merchandise, no invented band content and no more mockups. Update status, handoff, changelog, roadmap and evidence with every session.
