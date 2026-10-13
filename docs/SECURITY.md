# Security status and boundaries

This is a development draft, not a security-certified deployment.

Implemented source controls: random package claim code with only a hash in the ZIP; HTTPS enforcement with local development exception restricted to PHP cli-server; secure/HttpOnly/SameSite session cookies, strict sessions, idle timeout and session rotation; CSRF checks on POST actions; password hashing, validation and session versioning; prepared SQL; login attempt buckets; owner-only administration; escaped public text; CSP and private-page cache controls; private runtime and atomic config writes; installer lock and allowlisted cleanup.

Never commit real config, setup receipts, API keys, passwords, customer/subscriber records, uploaded private media, sessions, logs or backups. The repository is public. Deny application/internal folders at the web server; do not rely on the development router for production security.

Known review items: current runtime placement does not prove privacy relative to other domain roots on the hosting account; provider reverse proxies need explicitly trusted configuration rather than accepting arbitrary forwarded headers; database installation/recovery and authentication still need integration tests; staff roles, uploads, email tokens, payment notifications, backups/restoration and safe upgrades need their own implementation/tests. Do not imply they are complete.

Installer cleanup must never accept a visitor-supplied deletion path. Only the fixed application's known install files may be removed. Physical removal and the logical installation lock are independent. Error reports must not expose credentials. Payment integration must not store raw card data and must not trust a return URL as evidence of payment.
