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
