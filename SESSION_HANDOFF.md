# Session handoff — resume here

Repository **afterburn25/Spirit**; authoritative **main**. Current cumulative development checkpoint: **0.6.0-dev.1**. Preserve AGENTS.md and the selected navy/gold/blue crowned-gorilla design. No new mockups, native ticketing, fake public content, secrets in Git, destructive reinstall or force-push.

## Verified application checkpoint

Application source `dd0db41248a21b53968b2b26d7540c2944f9fa5a`, tree `cb2277ed7cca634c3909328387606c85934e9615`, passed Actions **38479213942** on PR #1 at 2026-10-13 22:41:16 UTC. PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 both passed, including real TLS transport and post-acceptance database-failure tests. Chromium installer, public/player, booking and owner communication journeys passed at 1440px/390px. Fourteen screenshots in artifact 11856157592 were inspected. This documentation-only descendant changes no application code, schema, assets or tests; verify the current main ref/run when resuming.

The earlier booking/email claim had not advanced main beyond 32e7d9ba / 0.5.0-dev.1. Actual saved tree e05075b was recovered on work/booking-email-recovery, reviewed, fixed and tested rather than treated as already landed. The .5 CSP-incompatible playback test is fixed without weakening CSP. The new mobile roadmap overflow is fixed without clipping content.

## What now works

Database-first booking submissions and private receipts; owner inquiry status/search/pagination/assignment/private notes with conflict checks; transactional deduplicated notification creation and recovery of interrupted queue writes; encrypted TLS SMTP settings/message bodies; bounded queue retry/backoff/locking/uncertainty; confirmed newsletter opt-in, scanner-safe POST consent actions, expiring hashed tokens, unsubscribe suppression and confirmed-only CSV export. SMTP-accepted mail whose database receipt fails remains uncertain until explicit duplicate-risk acknowledgement.

## Next actions

1. Inspect live main, VERSION, status and latest Actions. Preserve concurrent changes. Use the verified cumulative .6 checkpoint rather than rebuilding from .1 or .5. Run the same full regression matrix for future changes; do not weaken CSP or skip failures.
2. Implement **0.7 merchandise catalog, variants, inventory, cart and customer accounts** in organized folders. Keep checkout closed until the end-to-end workflow exists. No fictional public products or sales metrics. Preserve the approved design and existing configuration, uploads and content.
3. Implement sandbox-first Authorize.net payments/orders/fulfillment, then complete maintenance, backup/restore and browser ZIP-upgrade tooling. A redirect or button is not evidence of payment. Never store raw card data or commit provider credentials.
4. Finish staff delegation, advanced CMS pagination/revision restoration, event metadata/redirects, accessibility and measured performance. High-resolution production artwork and actual band content still need approval and a proper pass.
5. Deliver cumulative immutable full packages and separate private setup receipts; never upload a plaintext setup receipt to the website or GitHub. Continue beyond a checkpoint, but do not call the entire project 1.0 until full acceptance.

## Environment and deployment boundaries

The current local container lacks pdo_mysql/GD, Chromium and network DNS. Local lint/unit/package checks run, while GitHub Actions provides disposable real database/browser validation. Obtain canonical tracked source through the source-artifact workflow, not an invented sandbox path. Test-only fixtures are CLI-guarded and refuse non-test databases; executable tests and synthetic runtime data never enter the deployment ZIP. The post-acceptance failure test uses a disposable CHECK constraint, not a SUPER grant or relaxed MySQL security setting.

Email uses AUTH LOGIN over verified STARTTLS/implicit TLS. Submission shutdown processing is a small best-effort batch, not a permanent daemon. Optional hosting-panel PHP schedule or owner queue action handles quiet-site retries; see docs/COMMUNICATION.md. No production provider inbox delivery, host acceptance or real payment verification occurred. Existing supported account-private installations use admin/updates.php for additive schema; preserve private config including its encryption secret and all data/uploads. Do not bypass the legacy dev.1 runtime-migration boundary.
