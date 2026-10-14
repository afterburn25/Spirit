# Security controls and verification boundaries

Development0.8.0-dev.1. No certification, complete penetration audit or production deployment is claimed. TEST_RESULTS.md distinguishes local tests, Actions and unverified provider/host acceptance.

## Foundation

Package-specific random setup code with hash-only deployment; HTTPS with loopback HTTP exception only on PHP development server; strict sessions, HttpOnly/SameSite and Secure cookies under HTTPS; CSRF mutations; hashed passwords/session revocation; prepared SQL; rate limits; role-gated owner administration; escaped text, self-only script CSP and uncached private pages.

Account-private storage validates known/declared public roots and canonical paths. Actual probes cover runtime children. Permissions are verified before secrets. Symlinks, ambiguous destinations, unreadable installer directories and unknown cleanup content fail closed. Installer operation locks, prefix ownership and independent completion markers prevent destructive reinstall. Physical deletion is separate from permanent setup locking.

Deployment packaging allows known paths only and excludes runtime/customer data, plaintext receipts, scripts/tests/build tools, local configs, symlinks and output folders. The private receipt is exclusively created0600. No default owner password. Never place real secrets in this public repository regardless of packaging exclusions.

## Content, communication and customer identity

Private drafts/media are authorized independently from public snapshots. Uploads are validated and private until published. Settings/message bodies are authenticated-encrypted using the private installation secret. Newsletter tokens are hashed/expiring and consent changes use explicit POST. Booking consent never opts into marketing. Mail accepted by SMTP but lacking a persisted receipt remains uncertain, not automatically resent.

Customer identities are separate from staff users. Email-first activation prevents attacker-preselected passwords; single-use reset/activation links expire and revoke. Password changes and suspension revoke sessions. Address mutations bind customer and revision. Private browser bags use random keyed-hash identities; all prices/availability are server computed. Test fixtures cannot become HTTP behavior.

## Checkout and payment boundary

Sensitive card inputs have no native form names; intended application payment submission contains opaque token fields only. Unexpected fields are rejected before mutation and tokens are not stored/logged. Scoped payable-page CSP admits only the corresponding official SDK/API domains, without unsafe-eval/inline. A custom Accept.js form retains merchant PCI obligations.

Durable processing precedes irreversible calls; repeated/concurrent requests cannot create another charge operation. Unknown acceptance, lost database receipt or interrupted worker cannot silently free stock or retry payment. Atomic reservations and idempotent release preserve stock. Reporting matches merchant/environment/invoice/amount/type and correlation. Browser return parameters do not prove payment.

Webhooks verify unmodified bytes with the configured key before durable insertion and deduplication. Signed receipt alone is not current payment evidence; reporting follows separately. Receivers are machine requests without browser sessions, but still require HTTPS and signatures. Private order data is encrypted and accessed only by authenticated ownership, originating session or private capability. Private pages are no-store/no-referrer/noindex. Credential/reversal changes require owner reauthentication.

API endpoints are fixed HTTPS, bounded and non-redirecting with certificate/hostname checks. CLI-only synthetic gateway and loopback-TLS seams cannot be chosen through web requests. Actual provider signatures/SDK/API behavior and merchant compliance remain staging acceptance, not established by synthetic fixtures. Read PAYMENTS.md.

## Remaining review

Actual Apache/LiteSpeed rules and arbitrary aliases; provider TLS/ACL/open_basedir/trusted-proxy configuration; early-DDL crash windows; legacy dev.1 runtime migration; actual mailbox/provider transactions; complete backup/restore/ZIP upgrade safety; delegated staff; retention; advanced application security/accessibility/performance. Never blindly trust forwarded headers, public storage fallback, a source-artifact success or another commit's test results.

Exceptions must not expose credentials, private order data, raw gateway messages or driver diagnostics. CI credentials are deliberately disposable and never reusable for production. Runtime test data and temporary CA keys stay outside deployment archives.
