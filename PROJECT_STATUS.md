# Project status — 0.9.0-dev.8 implementation

Target: **A2 Hosting with cPanel only; no 20i**. Work is on `work/single-page-installer`, PR11. Base main1df65ce90124adda5ddb3fd5936be9f006dcee9a / dev7. Exact new full verification is pending. Presence of the code is not a real A2 account acceptance result.

## Implemented

No package setup code, access hash, claim stage or PRIVATE-SETUP receipt. Hosting/write checks, URL, database/owner fields, corrections and completion occupy one browser page. CSRF, safe private writes, permanent locking, prefix/concurrency protection and actual installer cleanup remain. JavaScript provides conditional database fields and busy/focus feedback; the native form works without it. Password/token fields are never reflected after correction.

The database selector offers existing credentials or automatic A2/cPanel setup. Automatic mode reads account naming restrictions and existing resource inventories, generates a unique database/user and strong random password, assigns only necessary database-scoped privileges, and verifies the real PDO connection before application migrations. Panel token is request-only, not stored in sessions/config/recovery/logs. HTTPS2083, public IPv4 DNS pinning, peer/hostname checks, no redirects/proxies and bounded time/response sizes are enforced.

A private0600 progress record precedes resource mutation. Confirmed resources are reused after an explicit rejection or later migration failure. Unknown create/grant results pause for inspection; no blind duplicates, adoption of existing resources, user-password resets or delete APIs. Successful installation removes transient provisioning state after publishing locked private configuration. Read docs/CPANEL_INSTALLER.md for supported connections and uncertainty boundaries.

## Verification

Local new cPanel input/contract tests:56 passed. New PHP/Python syntax and12 packaging tests passed. Added real loopback TLS transport tests, and a CLI-only cPanel boundary simulator that creates actual dedicated databases/users in disposable MySQL/MariaDB. The test-only simulator uses a step-scoped local SQL administrator; application code never receives that credential. CI blocks external cPanel networking. New browser checks cover cPanel-mode switching, same-page validation and non-reflection at1440/390/320px plus a no-JavaScript existing-database installation.

The earlier dev8 run38553237171 failed after correctly completing and deleting the installer because a missing-directory404 assertion did not match PHP development-server fallback. The updated regression tests the deleted PHP entry, absence of the setup form, and inability to recreate owners. Production server access rules are unchanged. Exact integrated CI and screenshot inspection remain pending.

## Preserved and remaining

All cumulative public/CMS/history/discovery/media/player/external-ticket shows, bookings/email/newsletter, merchandise/customers/checkout/orders, staff roles, encrypted backups/guarded restore, same-schema ZIP updates/pinned recovery and retention remain. Migrations001–006 and pinned recovery/access files are unchanged.

Fresh code-free installation is open until completed; install promptly or protect unattended staging with cPanel Directory Privacy. This is not a removal of backup/update recovery keys or the original encryption secret. Actual A2 API/host, merchant sandbox, receiving mailbox, master artwork/approved content, populated-site quality and owner acceptance remain before1.0. Current development ZIP must not be described as production accepted.
