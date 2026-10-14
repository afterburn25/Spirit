# Session handoff

Read AGENTS.md and the live main/status/roadmap before writing. Repository afterburn25/Spirit; main is authoritative. Preserve approved design, shared-host installer, config/secrets/media/data and immutable migrations. No mockup-only replacements, native ticketing, invented public content, 0777 or force-push.

Current work: **0.9.0-dev.1 on work/staff-access**, based on .8 documentation checkpoint eb995810e35f23af28080f7f7b2b65f3021085c2. New tests pending; do not promote until the entire current verification matrix passes. .8 application cd3d645bf031e7ab8cf2a7c32b44278e8bc8d89e passed38493194973 (both databases, all five browser suites);54 synthetic screenshots,14 payment/admin renders inspected. Real provider transactions are not established by synthetic tests.

Staff changes: scoped role map, server-side controller/media gates, owner-confirmed email-first invites and recovery, single-use hashed tokens/encrypted outbox, password/session race checks, revocation and last-owner protection, store fulfillment-only view, owner audit history. Read docs/STAFF.md for exact capability boundaries. Customer identities remain separate. Legacy unknown editor role remains denied; supported role is content_editor.

Next: run/fix staff SQL/HTTP root/subfolder/upgrade and Chromium1440/390, inspect screenshots, record exact evidence and promote only tested code. Then continue actual backup/restore/browser upgrade/retention and remaining content/search/quality work. Never claim those exist because staff management exists. Preserve001–005 and add006.

GitHub tools are working. A transient protocol failure must be followed by a ref read before retrying a write, not a claim of lost permissions. Use the source-artifact workflow for canonical files. Container lacks real SQL/browser extensions; Actions supplies disposable test services. Do not use a different commit's green run as proof. No plaintext setup receipts/secrets/test runtime in Git or deployment ZIP. Final1.0 requires complete scope, actual staging/provider acceptance and user approval.
