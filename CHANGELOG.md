# Changelog

## 0.6.0-dev.1 — 2026-10-13 — verified booking and communication checkpoint

Recovered previously uncommitted communication code, reviewed it and verified the integrated application in Actions **38479213942**, source **dd0db41248a21b53968b2b26d7540c2944f9fa5a**. Both database environments and all configured Chromium suites passed. This documentation-only update changes no application code, schema, assets or tests. No provider inbox or production acceptance is implied.

Added real public booking submissions and session-private receipts, contact consent, deduplication, owner review/search/pagination/status/assignment/private notes and durable recovery of interrupted notification queue writes.

Added encrypted SMTP configuration and message bodies, TLS-only authenticated delivery, bounded transport operations, retries/backoff/worker locks and explicit uncertain-delivery recovery. A failed database receipt after SMTP acceptance cannot cause an automatic resend. Newsletter has separate opt-in, hashed expiring tokens, scanner-safe POST confirmation/unsubscribe, confirmed-only CSV export and stale-message suppression. No booking-to-marketing auto-enrollment, native ticketing or payment action is added.

Fixed public audio test polling without weakening production CSP and corrected phone roadmap status-label overflow without clipping. Added 27 communication unit checks, root/subfolder/additive-upgrade SQL/HTTP journeys, ten real loopback TLS SMTP scenarios, five post-acceptance database-failure assertions and rendered desktop/mobile communication tests. Inspected 14 actual synthetic browser screenshots. See docs/TEST_RESULTS.md.

## 0.5.0-dev.1 — 2026-10-13 — cumulative development checkpoint

Approved real responsive public pages, structured owner CMS/drafts/preview, protected image/audio/PDF media, band/galleries/press kit, releases/tracks/persistent audio and shows/archive/external venue tickets. Hardened early installer ownership, database-prefix locking, config publication, legacy-runtime detection and additive migrations. Approved-concept crops are lower-resolution reference assets, not final master art. Expanded public browser verification later failed on CSP-incompatible test polling; see TEST_RESULTS.md.

## 0.1.0-dev.2 — 2026-10-13

Account-level private runtime, per-directory IO probes, safe cleanup/denial recovery, protected packages/receipts and real SQL/HTTP/Chromium installer-admin tests. Verified application/package cb1ce92a4af133e5a9c86fcfd7dce281af4ee336 in Actions 38456010840: 43 unit, 26 storage, 10 packaging checks, PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, Chromium at 1440px/390px.

## 0.1.0-dev.1 — 2026-10-13

Initial organized installer/admin foundation, private storage, owner authentication/settings, browser setup authorization, documentation and local unit tests. Not a production release.
