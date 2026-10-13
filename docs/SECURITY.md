# Security controls and verification boundaries

Development version 0.6.0-dev.1; no security certification or production deployment is claimed.

## Implemented controls

Package-specific high-entropy claim code with only its hash deployed; HTTPS enforcement with loopback HTTP exception limited to PHP's development server; strict sessions with HttpOnly/SameSite cookies and secure cookies under HTTPS; CSRF-protected mutations; password hashing and session revocation; prepared SQL; login attempt limits; owner-only administration; escaped text, CSP and no-store admin responses.

Private storage uses a validated account-level base, known/explicit public-root checks and canonical paths rather than trusting the current document root's parent. Filesystem probes cover all runtime children; directory/file modes are checked and secrets are written only after private permissions apply. Unsafe symlinks, unreadable installer directories and unexpected cleanup contents are refused.

Installer operation locks, prefix ownership and independent installation markers prevent concurrent/destructive reinstall. Physical deletion is separate from logical locking. No visitor controls a deletion target.

Deployment packaging includes known application paths only. Runtime files, customer data, setup receipts, test scripts, build tools, local configs, symlinks and output directories are excluded. The separate receipt is created exclusively with mode 0600; no default owner password exists. Never place secrets anywhere in this public repository even if packaging would omit that path.

## Verified versus remaining

See TEST_RESULTS.md for exact commit/run evidence. Expanded tests cover SQL/HTTP forms, private content/media, subscription consent, encryption, TLS transport failures and desktop/mobile workflows. A successful smoke test does not constitute penetration testing or a complete accessibility/security audit.

Remaining deployment reviews: Apache/LiteSpeed rule enforcement, all provider domain aliases, open_basedir/ownership restrictions, production TLS and explicitly trusted reverse proxies, early-DDL crash windows, and migration of existing dev.1 runtime. Never blindly trust X-Forwarded-Proto or accept an arbitrary public storage fallback.

Content/media authorization, validated uploads, encrypted communication settings/bodies, hashed expiring subscription tokens, CSRF-protected consent changes and durable inquiry/email recovery are implemented. Mail accepted by SMTP but not recorded successfully in the database is uncertain and cannot be automatically resent.

Later modules still need their own controls and tests: delegated staff management, commerce, authenticated payment notifications, full backup/restore and ZIP upgrades. Never mark those implemented merely because this foundation has authentication. No raw card storage; a payment-return URL will not be evidence of payment.

Errors must not expose credentials, private data or exception driver messages. Test databases are guarded by name/prefix and disposable; credentials in the CI workflow are deliberately test-only and must never be reused for production.
