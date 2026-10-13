# Project status

Updated 2026-10-13. Cumulative development checkpoint **0.6.0-dev.1**. Application source `dd0db41248a21b53968b2b26d7540c2944f9fa5a` passed the complete verification workflow, run **38479213942**, on the recovery branch before promotion to main. The following documentation-only update leaves application code, schema, assets and tests unchanged. Always inspect the live main ref before resuming.

## Implemented and regression-tested

- Public booking form, separate contact consent, session-bound private receipt, nonce deduplication, validation/rate limiting/honeypot and database-first saving.
- Owner booking list/search/status filters/pagination, assignment to active owners, private notes and optimistic edit conflict protection.
- Durable notification reconciliation after interrupted queue writes; idempotent encrypted outbox; TLS-only SMTP settings and owner test/queue controls.
- Bounded transport deadline, verified certificates/hostnames, definite-failure retries/backoff, abandoned-worker uncertainty and explicit duplicate-risk acknowledgement. A database receipt failure after SMTP acceptance also stays uncertain and cannot automatically resend.
- Separate newsletter opt-in, hashed expiring confirmation tokens, explicit POST confirmation/unsubscribe, confirmed-only formula-safe CSV export and stale-message suppression.
- Corrected real-audio browser checks without unsafe-eval or CSP bypass; playback survives internal navigation on desktop and mobile.
- Phone roadmap labels now reflow instead of overflowing. Public and communication screenshots were inspected after the successful run.

## Preserved cumulative functionality

Organized shared-host browser installer, private runtime/ownership locks, safe cleanup, owner auth, additive schema ledger, selected public design, structured drafts/published snapshots/previews, media authorization/upload/ranges, band/press/gallery, releases/local player and external-ticket shows. Nothing is reset or replaced with mockup-only pages.

## Verification evidence

Run 38479213942 finished successfully on 2026-10-13 at 22:41:16 UTC. PHP 8.2/MariaDB 11.4 job 115577390948 and PHP 8.4/MySQL 8.4 job 115577390730 both passed. Chromium ran installer/admin, public playback and booking/communication journeys at 1440px and 390px. Browser artifact 11856157592 contains 14 synthetic screenshots. See docs/TEST_RESULTS.md for the suite breakdown and corrected failures.

No production mail, payment, hosting, artwork approval or complete security/accessibility acceptance is claimed. Real SMTP transport was tested against disposable loopback TLS servers, not a live provider inbox.

## Remaining and next milestone

**0.7 merchandise/catalog, variants, stock, cart and customer accounts** is next. Then Authorize.net sandbox payments/orders, delegated staff management, complete backup/restore and browser ZIP upgrades, advanced CMS pagination/revision restoration, final metadata/redirects/event structured data, measured performance and full accessibility/security acceptance.

Current artwork consists of modest-resolution approved-reference crops, not finished master art. Email supports password-based AUTH LOGIN over STARTTLS or implicit TLS, not every provider authentication method. Newsletter campaigns are not implemented. Preserve the private installation secret with database backups. Actual host aliases/TLS/ACL/open_basedir and legacy-runtime migration need staging verification. No final v1.0 acceptance has occurred.
