# Session handoff — resume here

Repository **afterburn25/Spirit**, authoritative branch **main**, version **0.1.0-dev.2**. No accepted release and no live deployment. Preserve the user-approved design and roadmap.

## Verified checkpoint

The application/package commit **cb1ce92a4af133e5a9c86fcfd7dce281af4ee336** passed **Actions run 38456010840**. Jobs 115498795114 (PHP8.2/MariaDB11.4) and 115498794410 (PHP8.4/MySQL8.4) succeeded. Both ran syntax, 43 unit, 26 storage and 10 package tests plus real SQL/HTTP workflows. The PHP8.4 leg also passed actual Chromium setup/admin smoke at 1440px and 390px. The following documentation-only checkpoint does not alter code or tests.

Real root/subfolder installs, owner auth/settings/password/session workflows, physical installer deletion, denied-deletion lock and cleanup retry, prefix preservation and interrupted-migration recovery are verified in disposable environments. Do NOT repeat the old claim that no database or browser tests ran.

## This session's implementation

Added the SQL/HTTP matrix and real browser tests. Fixed the test's /admin/ versus /admin/index.php redirect assumption. Hardened runtime placement at account level rather than beside a nested addon document root, checked known/explicit public roots and canonical paths, probed each private child, enforced private permissions before writes and handled unreadable/undeletable installer folders safely.

Hardened packaging to include deployment paths only and exclude private/runtime data, setup receipts, symlinks, test/build scripts and output folders. Receipts and output files are exclusively created; unsafe versions/output locations are rejected. The revised package passed the full matrix, not only its isolated tests.

## Next implementation steps

1. Read live AGENTS, README, PROJECT_STATUS, ROADMAP, DESIGN_LOCK, INSTALLATION, STORAGE, SECURITY and TEST_RESULTS. Inspect current main and preserve concurrent changes; never force push.
2. Close foundation edge cases: interruption between sentinel-table CREATE and ownership-row INSERT, config/restart publication boundaries, and detecting/migrating legacy dev.1 runtime rather than ignoring it. Do not remove locks or overwrite an existing database prefix to make setup pass.
3. Verify a graceful setup diagnostic when account-home discovery fails. Bootstrap currently consults Paths::installed while starting the session, before preflight rendering; do not let an atypical provider produce only a generic error without actionable safe diagnostics.
4. Verify Apache/LiteSpeed access rules and actual staging-host extraction/ownership/open_basedir/TLS/domain-alias behavior. Automated tests use PHP's development server and do not prove production rules. Do not request production secrets in chat.
5. Commit the approved moodboard reference asset, then implement v0.2.0 as real responsive components matching it. No more mockups/redesigns. Do not pass the holding page off as the finished site. Continue the remaining cumulative roadmap.
6. Deliver tested cumulative full installation packages when ready, with setup receipts kept private. No live replacement/final 1.0 tag without acceptance. Automated upgrades and backup/restore remain later work.

## Environment and user boundaries

Local container lacked pdo_mysql/database and external package retrieval failed; actual integration succeeded in GitHub Actions. GitHub reads/writes work. Do not ask the user to reconnect or broaden permissions absent a new real permission failure.

Private runtime is account-level, checked against known/configured public roots; PHP cannot discover every arbitrary alias. Atypical hosts may need server SPIRIT_ACCOUNT_HOME/SPIRIT_PUBLIC_ROOTS. Legacy-runtime migration is documented, not automated or verified. The installer must test real writes and attempt self-deletion, but must never claim to bypass host ownership or ACLs.

Fixed scope: complete admin, shared-host PHP/MySQL, organized folders, external venue ticket sellers only, separate sandbox-first Authorize.net merchandise, no invented band content, no more mockups. Update status, handoff, changelog, roadmap and evidence each session.
