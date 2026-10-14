# Staging and launch acceptance — manual installer

Current installer scope: A2/cPanel, manual database configuration, no API token, no setup code. This checklist is not a declaration of production readiness. Read PROJECT_STATUS.md and TEST_RESULTS.md for exact verified code and known limits. Record environment, tester, date, result and evidence for every item. Never put passwords, payment keys, recovery keys or customer details in Git.

## 1. Approved band content and artwork

- Approve logo and production-resolution assets against the locked design; reference crops are not finished high-resolution masters.
- Approve genuine band story, names, roles and photographs. No generated people or concept identities presented as actual members.
- Approve releases, tracks, recordings, credits, videos, photographs and press downloads, with usage permission.
- Verify actual show dates, named timezone, venue, admission and external ticket destination. No invented performances.
- Approve merchandise photos/descriptions/variants/prices/stock/fulfillment. No synthetic public products, sales or testimonials.
- Approve contact/sender addresses, privacy information, store terms, shipping/returns and operating policies.

## 2. Actual A2/cPanel staging

No 20i integration. Use an isolated staging domain/subfolder before replacing the live site. Confirm setup is one page with no package code and a same-page completion result. Install promptly or restrict unattended setup through cPanel directory protection.

Prepare a dedicated database and user through cPanel Database Wizard or Manage My Databases, assign the user to that database, and copy the full prefixed names and database-user password into the manual form. Verify PHP cURL and PHP ZIP separately for payment transport and browser upgrades. Read CPANEL_INSTALLER.md. No token, automatic resource creation or host API acceptance is required.

- Verify private runtime lies outside every public mapping; review extensions, HTTPS, ACL/ownership/access rules, upload limits and open_basedir. Confirm the PHP version serving this domain, not just the account default.
- Install and verify owner, protected config, tables and independent locks. Verify actual installer removal separately from a retained locked directory.
- Verify owner/editor/store-manager/customer access boundaries and revocation; staff cannot inherit owner financial/system controls.
- Test approved image/audio/PDF upload, private previews, publication/unpublishing, derivative authorization and original downloads.
- Test populated desktop/phone navigation/search/redirects/forms/gallery/player, keyboard, zoom, reduced motion and focus.
- Create/verify/import an encrypted backup with its separate offline key; exercise supported guarded restore, same-schema browser upgrade and independent rollback on staging. Do not extrapolate to cross-host disaster restoration or unattended cross-schema rollback.

## 3. Receiving-mailbox and scheduled work

- Configure actual supported SMTP/sender and verify the test in provider logs and receiving mailbox.
- Submit staging booking and verify saved inquiry, owner notification and acknowledgement; it must not reserve a date or enroll marketing.
- Complete real newsletter confirmation/unsubscribe and customer/staff activation/recovery through the mailbox; test expired/used tokens.
- Verify actual sandbox order receipts/fulfillment messages. SMTP acknowledgement alone is not inbox evidence.
- Configure documented panel mail/payment scheduled commands and observe quiet-site executions. Cleanup schedule is owner-opt-in, not automatically created.

## 4. Genuine merchant sandbox

Keep live enablement off until owner approval. Synthetic tests do not satisfy these checks.

- Confirm matching actual sandbox SDK/API credentials and Accept.js tokenization with deployed HTTPS/CSP.
- Test approved/declined/held transactions, duplicate submission, interruption/reporting recovery, totals and stock holds/release/consumption.
- Receive genuine signed notifications; verify raw signature plus merchant/reference/amount/type against authenticated provider reporting.
- Verify settlement timing, full void/refund workflow, order privacy/history, inventory adjustments and receipts. Review documented partial-refund/shipment/discount/international limits before accepting operating scope.
- Independently verify shipping/tax/terms/destinations and compliance/security/fulfillment responsibilities; configured rates are not an automatic jurisdiction-aware tax service.
- No live test charge without separate explicit authorization.

## 5. Release candidate, approval and cutover

Resolve blockers, retain a verified independent backup/recovery materials, record final commit/package hashes and rerun the complete regression matrix. Review populated pages against the approved design. Measure real-host performance with approved assets; fixture budgets are not field Core Web Vitals. Preserve/map actual old-site URLs deliberately.

Only then prepare the candidate, record owner acceptance and plan cutover. A green run, version number, checkout button or lack of reported defects does not establish accepted v1.0.
