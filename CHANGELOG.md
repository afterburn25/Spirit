# Changelog

## 0.7.0-dev.1 — merchandise, inventory and customer accounts

Additive 004 schema; owner-managed categories/products/private drafts, approved public snapshots/media, SKU pricing and auditable idempotent stock adjustments; responsive catalog/detail/homepage collection; browser-owned persistent bag with integer-cent server prices, replay protection and stale availability handling. Email-first customer activation, expiring single-use reset, session revocation, owner suspension and revision-safe customer-bound US address book. Staff and customer identities remain separate. Checkout deliberately closed; no live payments/orders or fake inventory. Added 35 unit checks and SQL/HTTP/browser regressions; exact new execution pending. See docs/COMMERCE.md.

## 0.6.0-dev.1 — 2026-10-13 — verified booking and communication checkpoint

Recovered previously uncommitted communication code, reviewed it and verified the integrated application in Actions 38479213942, source dd0db41248a21b53968b2b26d7540c2944f9fa5a. Exact-main documentation descendant 6b00769fee969570872c88446110429047dc0499 also passed run 38479822564. Both database environments and all configured Chromium suites passed. No provider inbox or production acceptance is implied.

Real booking submissions/private receipts, separate contact consent, deduplication, owner search/pagination/status/assignment/private notes and durable interrupted-notification recovery. Encrypted SMTP settings/bodies, verified TLS, bounded transport, retry/backoff/worker locks and uncertain-delivery recovery. Failed database receipts after SMTP acceptance cannot cause automatic resend. Newsletter DOI, hashed expiring links, scanner-safe POST confirmation/unsubscribe, confirmed-only CSV and stale-message suppression. No automatic marketing enrollment or native ticketing.

Fixed CSP-compatible real audio polling and phone roadmap overflow. Added 27 communication units, root/subfolder/additive-update HTTP journeys, ten real loopback TLS scenarios, five post-acceptance DB-failure assertions and responsive communication tests. Fourteen synthetic browser screenshots inspected in the baseline session; see TEST_RESULTS.md.

## 0.5.0-dev.1 — 2026-10-13

Responsive public pages, owner CMS/drafts/previews, protected image/audio/PDF media, band/gallery/press, releases/tracks/persistent audio and shows/archive/external venue tickets. Hardened installer ownership, database-prefix locks, config publication, legacy-runtime detection and additive schema. Reference crops remain lower-resolution, not final master art. Public test initially failed on CSP-incompatible polling, corrected in .6.

## 0.1.0-dev.2 — 2026-10-13

Account-private runtime, actual IO probes, cleanup denial/recovery, protected packaging and SQL/HTTP/Chromium installer-admin tests. Baseline cb1ce92a4af133e5a9c86fcfd7dce281af4ee336 passed run 38456010840: 43 units, 26 storage checks, 10 packaging checks, PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, Chromium1440/390.

## 0.1.0-dev.1 — 2026-10-13

Initial organized installer/admin foundation, private storage, owner authentication/settings, setup authorization, documentation and local unit tests. Not production-ready.
