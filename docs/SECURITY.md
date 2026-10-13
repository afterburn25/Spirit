# Security controls and verification boundaries

Development version 0.1.0-dev.2; no security certification or production deployment is claimed.

## Implemented controls

Package-specific high-entropy claim code with only its hash deployed; HTTPS enforcement with loopback HTTP exception limited to PHP's development server; strict sessions with HttpOnly/SameSite cookies and secure cookies under HTTPS; CSRF-protected mutations; password hashing and session revocation; prepared SQL; login attempt limits; owner-only administration; escaped text, CSP and no-store admin responses.

Private storage uses a validated account-level base, known/explicit public-root checks and canonical paths rather than trusting the current document root's parent. Filesystem probes cover all runtime children; directory/file modes are checked and secrets are written only after private permissions apply. Unsafe symlinks, unreadable installer directories and unexpected cleanup contents are refused.

Installer operation locks, prefix ownership and independent installation markers prevent concurrent/destructive reinstall. Physical deletion is separate from logical locking. No visitor controls a deletion target.

Deployment packaging includes known application paths only. Runtime files, customer data, setup receipts, test scripts, build tools, local configs, symlinks and output directories are excluded. The separate receipt is created exclusively with mode 0600; no default owner password exists. Never place secrets anywhere in this public repository even if packaging would omit that path.

## Verified versus remaining

CI run 38454993289 passed security-related unit/storage checks and real SQL/HTTP workflows on the two supported test combinations. Chromium smoke covered the real installer/admin at desktop/mobile widths. A successful smoke test does not constitute penetration testing or a complete accessibility/security audit.

Remaining deployment reviews: Apache/LiteSpeed rule enforcement, all provider domain aliases, open_basedir/ownership restrictions, production TLS and explicitly trusted reverse proxies, early-DDL crash windows, and migration of existing dev.1 runtime. Never blindly trust X-Forwarded-Proto or accept an arbitrary public storage fallback.

Later modules still need their own controls and tests: staff management, uploads/media delivery, email tokens, commerce, authenticated payment notifications, backup/restore and upgrades. Never mark those implemented merely because this foundation has authentication. No raw card storage; a payment-return URL will not be evidence of payment.

Errors must not expose credentials, private data or exception driver messages. Test databases are guarded by name/prefix and disposable; credentials in the CI workflow are deliberately test-only and must never be reused for production.
