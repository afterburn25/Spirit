# Changelog

## 0.6.0-dev.1 — 2026-10-13 — booking and communication checkpoint

Recovered previously uncommitted communication code without treating it as a landed release. Added real public booking submissions and session-private receipts, contact consent, deduplication, owner review/search/pagination/status/assignment/private notes and durable recovery of interrupted notification queue writes.

Added encrypted SMTP configuration and message bodies, TLS-only authenticated delivery, bounded transport operations, retries/backoff/worker locks and explicit uncertain-delivery recovery. Newsletter has separate opt-in, hashed expiring tokens, scanner-safe POST confirmation/unsubscribe, confirmed-only CSV export and stale-message suppression. No booking-to-marketing auto-enrollment, native ticketing or payment action is added.

Fixed public audio test polling without weakening production CSP. Added communication unit, SQL/HTTP/root/subfolder/additive-upgrade, real loopback TLS SMTP and rendered desktop/mobile communication tests. Verification of this checkpoint is pending the new Actions run. No provider inbox or production acceptance is implied.

## 0.5.0-dev.1 — 2026-10-13 — cumulative development checkpoint

Approved real responsive public pages, structured owner CMS/drafts/preview, protected image/audio/PDF media, band/galleries/press kit, releases/tracks/persistent audio and shows/archive/external venue tickets. Hardened early installer ownership, database-prefix locking, config publication, legacy-runtime detection and additive migrations. Approved-concept crops are lower-resolution reference assets, not final master art. Expanded public browser verification later failed on CSP-incompatible test polling; see TEST_RESULTS.md.

## 0.1.0-dev.2 — 2026-10-13

Account-level private runtime, per-directory IO probes, safe cleanup/denial recovery, protected packages/receipts and real SQL/HTTP/Chromium installer-admin tests. Verified application/package cb1ce92a4af133e5a9c86fcfd7dce281af4ee336 in Actions 38456010840: 43 unit, 26 storage, 10 packaging checks, PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, Chromium at 1440px/390px.

## 0.1.0-dev.1 — 2026-10-13

Initial organized installer/admin foundation, private storage, owner authentication/settings, browser setup authorization, documentation and local unit tests. Not a production release.
