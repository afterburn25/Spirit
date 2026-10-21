# Spiritual Gorillaz — instructions for every implementation session

Repository: **afterburn25/Spirit**. `main` is the authoritative source of truth. This is the Spiritual Gorillaz PHP/MySQL band website, not any other user project.

## Read before changing code
1. README.md
2. PROJECT_STATUS.md
3. SESSION_HANDOFF.md
4. docs/ROADMAP.md
5. docs/DESIGN_LOCK.md
6. docs/INSTALLATION.md and docs/SECURITY.md
7. Latest test results and CHANGELOG.md

Some of these files are being established in the initial implementation. Do not infer that a planned milestone has been completed.

## Fixed user decisions
- Build the approved first Spiritual Gorillaz moodboard: crowned-gorilla identity, black/deep-navy backgrounds, gold branding/buttons, blue stage lighting, consistent Home, Music, Shows, Band, Booking, Merch and mobile layouts.
- **No more mockups or alternative redesigns.** Build real HTML/CSS, real forms and working modules. Never put a whole mockup image behind invisible click targets.
- Full password-protected owner administration is required. Content editors and store staff must not inherit owner privileges.
- Every show's ticket button opens the venue's configured external ticket seller. Do NOT implement native ticketing, ticket issuance, scanning, or admission management.
- Merchandise is separate: Authorize.net, sandbox-first, no raw card storage, never claim a payment test passed without evidence.
- **User decision 2026-10-14:** No setup code, package claim gate or PRIVATE-SETUP receipt. Use one browser installer page for hosting checks, config/database/owner settings, validation errors and completion. Keep CSRF, private write probes, locks, safe self-deletion and separate owner sign-in. Do not reintroduce a setup code in a later session. Fresh setup is open until completion: document immediate installation or hosting-panel directory protection. Backup/update recovery keys are unrelated and must stay protected.
- Shared PHP/MySQL hosting; browser installation with no required SSH/root or host-side Node build. Keep code organized in app, includes, admin, install, config, storage, database, images/assets, uploads, docs and tests rather than dumping scripts in the root.
- Installer must test actual create/write/read/rename/delete operations and create protected writable runtime locations. It must attempt safe removal of its own installer directory after success, and remain permanently locked even if the host prevents physical deletion. Never claim that PHP can bypass host ownership/ACL restrictions; never require chmod 0777.
- Preserve configuration, uploaded media and existing data during supported upgrades. Never silently reinstall or drop tables.
- Treat concept member identities, portraits, releases, track names, show dates, venues, merchandise and prices as unapproved examples. Do not publish fake sales, testimonials, metrics or performances.
- Version path: 0.1.0 foundation; 0.2.0 visual implementation; 0.3.0 content/media/press; 0.4.0 music; 0.5.0 shows; 0.6.0 booking/email; 0.7.0 catalog/accounts; 0.8.0 payments/orders; 0.9.0 integration/hardening; 1.0.0-rc.N acceptance; 1.0.0 only after complete verified scope and explicit acceptance.

## Session discipline
- Inspect live `main` before writes. Preserve unrelated changes. Never force push.
- Commit implemented code, reproducible tests, deployment instructions and honest status together.
- Update PROJECT_STATUS.md, SESSION_HANDOFF.md, CHANGELOG.md and the roadmap progress table before ending a session.
- Distinguish implemented, locally tested, CI tested, untested, blocked and pending. A page, button or table is not proof of a working workflow.
- Do not tag a final 1.0.0 or claim production readiness while payment, hosting, content or acceptance checks remain.
- Deliver cumulative full installation packages. Never overwrite an already delivered version with different contents.
- Never commit real passwords, production configuration, API keys, subscriber/customer data, sessions, backups or logs.

## Confirmed hosting and installer update (2026-10-14, latest decision)
- **A2 Hosting with cPanel only. No 20i. Manual database configuration only.** The user cannot obtain a cPanel token and explicitly removed automatic config/database/user creation. Do not restore hosting API clients, automatic-mode selectors or token fields.
- No setup code or PRIVATE-SETUP receipt. Keep checks, manual database and owner fields, corrections and completion on one page, with native forms usable without JavaScript.
- cURL and ZIP are optional for installation and must have separate truthful notices. cURL is needed for payment-server connections; ZIP for browser upgrades. Missing extensions cannot be silently treated as enabled. Hosting changes belong in cPanel or with A2 support, not a token workflow.
- Preserve private state from earlier unfinished attempts for manual review without executing, adopting, deleting or resetting it. Completed installations retain their original configuration, config/database/user and media; no reinstall or replacement DB.
- The three retired Cpanel file paths are inert compatibility files, not API classes. Keep them while older supported update manifests require every previous path; do not weaken cumulative-update checks or restore their old logic.
- Read docs/CPANEL_INSTALLER.md for manual setup and extension instructions. Actual A2 host/mailbox/merchant acceptance remains separate from automated tests.

## Consolidated diagnostics continuity (2026-10-14)
- Dev11 supersedes the differing dev10 preview and branch. Use one Launch checks implementation at admin/launch; admin/readiness must retain the same active-owner/method/CSRF boundary. Keep inert preview paths required by existing update manifests.
- Support exports use a strict fixed-field allowlist, not raw settings, paths, logs or private data. Observations cannot enable services, process queues or certify external acceptance.
- Oversized upload feedback must reject with no mutation while preserving normal CSRF and permission checks. Never solve a missing PHP ZIP notice by hiding it or pretending a host module was installed.

## Latest directory decision — 2026-10-21
Root folders are admin, app, assets, config, docs, images, includes, install, storage and uploads. Keep images in root. Public handlers are app/http, views app/views, tasks app/tasks, tests app/tests, tools app/tools, database config/database, design docs/design. Do not restore the old root clutter. Preserve public URLs through the explicit dispatcher. Cross-layout updates need the documented transition; never bypass recovery pins. rc.9 was not recovered; rc.10 is a new version from verified rc.8.
