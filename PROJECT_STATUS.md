# Project status

Updated 2026-10-13. Development checkpoint **0.6.0-dev.1**. Recovery/verification branch: `work/booking-email-recovery`. Main was verified at `32e7d9ba68d4416e73b11acafb7617223408a097` / 0.5.0-dev.1 before this work. The prior answer's booking/email checkpoint had not reached main; saved tree e05075b was recovered for review, not treated as a release.

## Implemented in this checkpoint

- Public booking form, separate contact consent, session-bound private receipt, nonce deduplication, validation/rate limiting/honeypot and database-first saving.
- Owner booking list/search/status filters/pagination, assignment to active owners, private notes and optimistic edit conflict protection.
- Durable notification reconciliation after interrupted queue writes; idempotent encrypted outbox; TLS-only SMTP settings and owner test/queue controls.
- Bounded transport deadline, verified certificates/hostnames, definite-failure retries/backoff, abandoned-worker uncertainty and explicit duplicate-risk acknowledgement.
- Separate newsletter opt-in, hashed expiring confirmation tokens, explicit POST confirmation/unsubscribe, confirmed-only formula-safe CSV export and stale-message suppression.
- Corrected browser audio polling without adding unsafe-eval or bypassing the site's Content Security Policy.
- Reproducible communication unit, SQL/HTTP root/subfolder/upgrade, real loopback TLS SMTP and desktop/mobile browser tests.

## Preserved cumulative functionality

Organized shared-host browser installer, private runtime/ownership locks, safe cleanup, owner auth, additive schema ledger, selected public design, structured drafts/published snapshots/previews, media authorization/upload/ranges, band/press/gallery, releases/local player and external-ticket shows. Nothing is reset or replaced with mockup-only pages.

## Verification

The new checkpoint is awaiting its exact CI results. See docs/TEST_RESULTS.md. Previous successful tests do not establish success for recovered or changed code. No production mail, payment, hosting, artwork approval or complete security/accessibility acceptance is claimed.

## Remaining

Merchandise/catalog/accounts/cart and Authorize.net; delegated staff management; complete backup/restore and browser ZIP upgrades; advanced CMS pagination/revision restoration; final metadata/redirects/event structured data; measured performance and full accessibility/security acceptance. Current artwork consists of modest-resolution approved-reference crops, not finished master art.

Email supports password-based AUTH LOGIN over STARTTLS or implicit TLS, not every provider authentication method. Newsletter campaigns are not implemented. Configured credentials are not verified inbox delivery. Preserve the private installation secret with database backups. Actual host aliases/TLS/ACL/open_basedir and legacy-runtime migration need staging verification. No final v1.0 acceptance has occurred.
