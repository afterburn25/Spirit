# Session handoff — resume here

Repository **afterburn25/Spirit**; authoritative **main**. Current work: **0.6.0-dev.1** on `work/booking-email-recovery`. Preserve AGENTS.md and the selected navy/gold/blue crowned-gorilla design. No new mockups, native ticketing, fake public content, secrets in Git, destructive reinstall or force-push.

## Recovery and implementation

The last claimed booking/email writes had not advanced main beyond 32e7d9ba / 0.5.0-dev.1. Recovered the actual saved e05075b tree into cc5a7be on a separate branch. Added notification-recovery state, queue deduplication/uncertainty controls, stale-subscription-message cancellation, TLS transport bounds/header folding and comprehensive new tests. VERSION is now 0.6.0-dev.1; this is not a final or production release.

## Next actions

1. Inspect live branch/main and the exact newest verification run. Fix regressions; do not skip tests, weaken CSP or claim another commit's green checks. Merge/fast-forward only the tested recovery head and preserve concurrent changes.
2. Run all SQL/HTTP suites on PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, real loopback SMTP certificate/failure tests, Chromium installer/public/audio and communication journeys at 1440px/390px. Inspect the synthetic screenshot artifact.
3. Record verified commit/run/jobs and remaining provider/host boundaries in TEST_RESULTS and status. Deliver an immutable cumulative full ZIP and separate private setup receipt after verification; do not publish the setup receipt in GitHub artifacts.
4. Continue into 0.7 merchandise/catalog/variants/stock/cart/customer accounts, then sandbox-first Authorize.net and orders, then full maintenance/backup/restore/ZIP-upgrade hardening. Do not claim money was taken or mail reached an inbox without evidence.
5. Finish staff delegation, advanced CMS pagination/revision restoration, event metadata/redirects, accessibility and measured performance. High-resolution production artwork and actual band content still require a proper pass/approval.

## Environment and safety

Local Git clone has no DNS access and local PHP lacks pdo_mysql/GD. Retrieve tracked source via the source-artifact workflow, not an invented sandbox filename. New source artifacts run on main and the recovery branch. GitHub Actions provides disposable real database/browser validation. Test-only communication fixtures are CLI-guarded and refuse non-test databases; no executable tests enter the deployable ZIP.

Email is AUTH LOGIN over verified STARTTLS/implicit TLS. Queue processing after submission is a small best-effort shutdown batch, not a persistent daemon. Optional hosting-panel PHP schedule or owner queue action handles quiet-site retries; see docs/COMMUNICATION.md. No real production credentials, email delivery or payment verification occurred. Existing installations use admin/updates.php for additive schema; preserve private config including its encryption secret and all data/uploads.
