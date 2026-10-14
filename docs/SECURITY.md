# Security controls and remaining acceptance

Cumulative 0.9.0-dev.8; full configured verification passed in run **38560767553**. No security certification, complete penetration audit or production deployment is claimed. TEST_RESULTS.md distinguishes exact Actions results, local checks and unverified provider/host behavior.

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

## Code-free setup and cPanel authorization

Fresh setup is intentionally open until completed. CSRF/database access are not hosting-owner authentication. Install promptly or restrict unattended staging with cPanel directory protection; do not reintroduce a package code. All permanent locks, prefix ownership and self-deletion checks remain.

A2/cPanel automatic provisioning uses a request-only token, TLS on port 2083, public IPv4 DNS pinning, no redirects/proxies, bounded time/responses and fixed Mysql operations. No token persists in sessions/config/logs. Generated DB credentials are private with mode 0600 for partial recovery and then application config. No root/WHM/delete/reset/global-grant APIs. Unknown create/grant outcomes pause for inspection rather than duplicate or adopt resources. A token may have broad account powers; revoke it after use. See CPANEL_INSTALLER.md. CI transport tests use loopback and API-boundary tests use a guarded disposable SQL service, not a real A2 account.

## Verification boundaries

The configured cumulative suites exercise both database engines, browser journeys, loopback TLS transport failures, permission/concurrency/recovery and actual Apache HTTPS access rules. They do not prove arbitrary provider aliases/ACL/open_basedir/OPcache/proxy behavior, real cPanel account/inbox/merchant results, field performance, full accessibility or penetration review, legacy relocation, master-art/content approval or v1.0 acceptance. Never trust forwarded headers indiscriminately, weaken safeguards to pass a test, or label a source-artifact run as application verification. Exceptions/logs must not expose private payloads, tokens, credentials or raw driver diagnostics.
