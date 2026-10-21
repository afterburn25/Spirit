# Security controls and remaining acceptance

Cumulative 1.1.0-rc.5; see TEST_RESULTS.md for the exact new verification status. No security certification, complete penetration audit or production deployment is claimed. TEST_RESULTS.md distinguishes exact Actions results, local checks and unverified provider/host behavior.

## Installation, sessions and storage

User-requested code-free one-page installation; native HTTPS (loopback HTTP exception only in PHP's test server); strict sessions, HttpOnly/SameSite/Secure cookies as applicable; hashed passwords, revocable session versions, CSRF mutations, prepared SQL and request limits. Private runtime validates canonical/known public roots, probes real IO and checks permissions before secrets. No web-controlled storage target, 0777 or public secret fallback. Prefix/operation ownership and independent completion markers prevent destructive reinstall. Physical installer removal is verified separately from permanent logical locking.

Packages exclude runtime/customer data, plaintext recovery receipts, test fixtures, local pointers, credentials, symlinks and build output. No package setup credential is generated and there is no default owner password. Never commit real private data to this public repository.

## Content and identity

Published snapshots and media visibility are separate from drafts and history. Typed validation rejects malformed structures; comparison escapes saved text. Restore reloads database history, checks current locked staff role/session/revision and source-media integrity, then writes a new private revision without exposing it publicly. Staff roles are deny-by-default; content editors/store managers cannot inherit owner credentials, backups or financial controls. Invitations/recovery require the intended email holder, expiring single-use tokens and owner-authorized administration. Customer identities and addresses are separately bound and cannot create staff sessions.

Booking consent is not marketing consent. Newsletter confirmation/unsubscribe require explicit protected POST actions. Secrets/message bodies use authenticated encryption with the original private installation secret. Mail with uncertain acceptance or a failed post-acceptance database receipt is not blindly resent.

## Payments

Custom Accept.js checkout sends card details directly to the official provider and submits only opaque token fields to application code. Unexpected fields are rejected and tokens are not stored/logged. Payable-page CSP admits only the environment's official SDK/API hosts, without unsafe-eval/inline. Merchant PCI obligations and actual provider compatibility remain acceptance requirements.

Durable operations precede charge/reversal calls. Repeated/concurrent requests do not create another operation. Unknown acceptance cannot silently free stock or retry a payment. Reservations/release are transactional and idempotent. Signed notifications are authenticated and deduplicated, then reconciled through merchant/environment/invoice/amount/type-aware reporting; a browser redirect or webhook receipt alone is not proof of current payment. Private receipts/history require ownership or scoped capability. Financial credentials/reversals require owner reauthentication. See PAYMENTS.md for operating limits and genuine sandbox acceptance.

## Maintenance and recovery

Encrypted, authenticated, bounded snapshots do not execute uploaded SQL/PHP. Same-instance guarded restore preserves sensitive operational histories; content restores cannot rewind external transactions or consent. Exclusive maintenance locks coordinate application requests; external panel tools do not participate. Browser ZIP validation rejects unsafe entries, incompatible/pinned files, hash/version/schema mismatches and oversized archives. Originals and a durable journal precede replacement; a separate new-application request verifies before reopening. Pinned recovery restores only recorded code, not arbitrary paths. ZIP updates remain same-schema; backup restore is not a universal cross-host disaster recovery system.

Conservative cleanup is bounded, opt-in where scheduled, and preserves active credentials/tokens, business histories, payment/notification idempotency and recovery archives. It cannot be invoked by an unauthenticated web request.

## Code-free manual setup

Fresh setup is intentionally open until completion. CSRF/database access are not proof of hosting ownership. Install promptly or protect unattended staging; do not restore the removed setup-code gate. Private writes, prefix ownership, safe deletion and permanent installation locking remain.

Automatic hosting provisioning is removed. No cPanel API class, token form, privileged SQL administrator or config/database/user creation path remains. Retired automatic input is rejected without reflecting credentials. A prior unfinished cpanel-provision.php record is detected by existence only: never execute, adopt, delete or reset it. Keep it for manual owner/host review. Already installed configurations remain locked and untouched.

The three former Cpanel file paths contain only an inert declaration and comments, not class definitions or API logic. Their presence preserves dev8's cumulative-update requirements while replacing the retired functionality; this is not a supported automatic setup path.

Separate optional cURL and ZIP notices do not grant capabilities or disable payment/ZIP validation. The installation can finish without either; the relevant feature must still pass its own configuration/runtime checks. Neither a token nor a modified public php.ini can be promised to bypass the provider's extension controls.

## Passive diagnostics and rejected uploads

Launch checks and its preview alias require an active owner. Unsupported methods/actions are refused; export is POST/CSRF-protected, private/no-store/no-referrer. The report uses fixed keys and allowed states, not raw snapshot serialization. Query failure reports unavailable data and does not leak driver text or fabricate empty work queues. Reading/exporting does not decrypt credentials, contact providers, process work or alter business records. Configuration and report counts cannot approve a launch.

Authenticated media, backup and update handlers detect a POST exceeding PHP's whole-request limit before interpreting the discarded body, then return413 with no requested mutation. This is a rejection path, not a CSRF bypass: ordinary requests still require valid CSRF and the relevant privilege/reauthentication. Private config/database fingerprints and absence of staged operations are regression-tested. Upstream host rejection before PHP executes is outside this response path.

## Verification boundaries

The configured cumulative suites exercise both database engines, browser journeys, loopback TLS transport failures, permission/concurrency/recovery and actual Apache HTTPS access rules. They do not prove arbitrary provider aliases/ACL/open_basedir/OPcache/proxy behavior, real hosting/inbox/merchant results, field performance, full accessibility or penetration review, legacy relocation, master-art/content approval or v1.0 acceptance. Never trust forwarded headers indiscriminately, weaken safeguards to pass a test, or label a source-artifact run as application verification. Exceptions/logs must not expose private payloads, tokens, credentials or raw driver diagnostics.

## Owner analytics

Analytics reports, exports, settings, imported query snapshots and location-data management require an active owner. Mutations require CSRF; current role/session is rechecked under a database row lock before settings/query changes or City-file mutation. City uploads use a fixed private path, bounded streaming/decompression, file-type/metadata validation, atomic replacement and staged-file cleanup; a rejected upload preserves the prior file.

The Overview snapshot and visitor explorer use the same owner boundary. The explorer is read-only, private/no-store, rejects cross-site and unsupported-method requests, validates bounded filter values and binds SQL parameters. Session details retain the selected date/filter cohort. Results and journeys are paginated; stored attribution is escaped. The UI exposes an anonymous shortened session label, not the internal visit hash, raw IP, account identity or private-route contents. Unavailable reporting leaves the owner Overview usable without inventing zero-valued metrics.

Only eligible public GET renders receive an analytics page token. Activity accepts a bounded JSON payload from the matching origin, bound to the current session/page, with cumulative replay checks and a shared elapsed-time limit across tabs. Raw IP addresses are used transiently for local country/crawler lookup and are not persisted in analytics. Arbitrary URL/query fields, full referral URLs, full user agents and form contents are not collected. Staff/private routes, DNT/GPC and prefetch/HEAD requests are excluded. Disabling analytics stops page and activity collection; unavailable analytics must not break public rendering.

Search Console imports contain aggregate queries, not visitor identities. Their dates are independent of visitor filters and the entire validated snapshot replaces atomically. CSV exports neutralize formula-leading strings. Retention is bounded and applies only to traffic/crawler tables through explicit owner or enabled scheduled maintenance; imported query snapshots are removed only by owner replacement/removal. Analytics tables are included in encrypted backups but excluded from protected-business-state digests and business/content restoration, so visitor traffic does not block or rewind a site restore.
