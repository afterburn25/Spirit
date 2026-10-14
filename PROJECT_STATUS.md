# Project status — 0.9.0-dev.10 work checkpoint

Based on verified manual-only main bfb061766e0e1ebf7b2d97c58b61c4f8b2de6575. New application verification is pending on work/launch-diagnostics. No accepted v1.0.

## Added

Owner-only Launch checks combines current PHP/extensions/upload limits, installation safeguards, database ledger, service configuration and published-section counts. It performs no hosting/provider calls, changes no settings, and does not turn local configuration into payment/mailbox/launch acceptance. A POST/CSRF download contains only explicitly allowed software/extension/limit/check-state fields, not hostnames, paths, credentials, logs, database names, customer details or content. Failed subchecks are unavailable, never green.

Media upload, backup import and browser ZIP upgrade screens now show the actual domain PHP file/body limits. Requests discarded by PHP for exceeding post_max_size return413 with a usable page and specific feedback instead of an expired-form error. Normal submissions still require their existing authorization, CSRF and owner confirmations. No limits, file validation or recovery policies were weakened.

Preserved: A2/cPanel manual database only, one-page code-free setup, no API token/automatic creation/20i; private configuration and uploads; inert retired Cpanel compatibility paths; six immutable migrations and five pinned recovery/access files. All existing website/admin/commerce/maintenance workflows remain.

## Next verification and actual acceptance

Execute cumulative units/package, both SQL environments, thirteen Chromium workflows and Apache HTTPS; inspect diagnostic/error screenshots and verify cumulative upgrade compatibility. Current provider/host testing, master artwork/content, broader populated-site quality and final owner approval remain open. Local configuration checks do not certify those tasks.
