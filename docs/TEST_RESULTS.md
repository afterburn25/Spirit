# Test evidence

## Verified 0.8.0-dev.1

Application **cd3d645bf031e7ab8cf2a7c32b44278e8bc8d89e**, tree **68ebedd13411f17856e24ae484a3318a856070d2**, passed **Website and installer verification** run **38493194973**, PR #3, completed **2026-10-14 01:02:51 UTC**. Tested merge ref **8d6ffa369fd32866221b3b46a86a0bfacba419dd** against9abe656 main. The documentation-only descendant leaves runtime, schema, assets and tests unchanged.

Both jobs passed: **115622964325 PHP8.2/MariaDB11.4**, **115622964643 PHP8.4/MySQL8.4**. Five Chromium suites passed in the PHP8.4 job: installer/admin, public/player, booking/communication, catalog/customer and checkout/order. All configured browser widths are1440px and390px.

Executed PHP syntax,43 foundation units,26 permission-aware storage,5 path,26 content-schema,27 communication,35 commerce,58 payment units and10 package checks, plus the existing SQL/HTTP and loopback SMTP suite. New payment SQL/HTTP covers server totals, duplicate preparation, private receipts, native token-only POST, raw-card-field refusal, real concurrent payment/reservation transactions, decline/expiry, uncertainty, webhook dedupe/conflict/authentication, reporting correlation/type/amount pinning, accepted-payment receipt failure, mail recovery, fulfillment, full void/refund idempotency, terminal-state preservation and additive .7 migration.

The payment boundary is a CLI-only synthetic transport; CI disables actual provider calls. Separate real cURL tests contact loopback TLS with a temporary CA: JSON/BOM parsing, certificate/hostname refusal, redirects, HTTP failures, malformed/oversized output, disconnects and deadlines. Browser tests replace only the external SDK through Playwright and inspect real form requests for absence of PAN/CVV. They do not certify genuine Authorize.net SDK/API/webhook sandbox acceptance, provider inbox delivery or merchant compliance.

Fixed failures rather than skipping them: initial CHECK fault incorrectly referenced AUTO_INCREMENT id and failed before gateway acceptance; it now targets immutable order reference, verifies the expected CHECK violation and retained processing/uncertainty. The next run passed SQL/HTTP but found actual390px payment-setting overflow; controls and endpoint URLs now wrap without hiding overflow. Run38493194973 is fully green.

Screenshot artifact **11862496582** has **54 synthetic renders**. Downloaded and inspected a contact sheet of all14 payment/order/admin screens at both widths. Current site art remains modest-resolution reference crops, not approved high-resolution masters. Local lint/units/package checks are separate from Actions SQL/browser evidence.

## Historical verified checkpoints

.7 application6a9396f571a3c8658886f84f1b64b754cd6c7fe6 passed38483296156; exact-main9abe656b991f04bc34f77667d871be91440bd09c passed38484316285.40 synthetic screenshots. .6 applicationdd0db412 passed38479213942; exact-main6b00769fee969570872c88446110429047dc0499 passed38479822564. Earlier detailed evidence is retained in Git history.

## Boundaries

Not established: real provider transactions/webhook compatibility, production inbox delivery, actual Apache/LiteSpeed/domain aliases/TLS/ACL/open_basedir/proxies, legacy runtime relocation, complete penetration test, WCAG conformance, field Core Web Vitals, final art/content approval or v1.0 acceptance. No test data/scripts/private setup receipts enter deployable ZIPs. A source artifact success is not application verification.
