# Test evidence

## 0.8.0-dev.1 — new full execution pending

Local PHP syntax checks, **58 payment unit checks**, JavaScript syntax, Python compilation and **10 packaging tests** passed. The new database, concurrent payment/reservation, HTTP, cURL TLS and Chromium tests await their exact Actions run. No new source is certified by a previous version's green result.

Payment tests exercise exact server totals and tax configuration; duplicate preparation; private guest/customer order access; token-only submission and unexpected-card-field refusal; real concurrent database transactions for once-only charging and last-unit reservation; definite decline/expiration release; uncertainty and held-payment retention; signed native-HTTP webhook deduplication/conflicts; reporting correlation/amount/type validation; accepted-payment database receipt failure; mail receipt recovery; separate fulfillment; full void/refund idempotency; terminal-state preservation; additive .7 migration. The gateway boundary is synthetic and CLI-only. CI sets SPIRIT_DISABLE_PAYMENT_NETWORK=1.

Separate actual cURL tests contact loopback TLS servers with a temporary CA. They exercise successful JSON/BOM parsing, certificate/hostname refusal before HTTP POST, redirects, HTTP failures, malformed/oversized responses, disconnection and deadline. Browser tests render real checkout/order/admin pages, replace the external SDK only through Playwright, and inspect an actual token-only form submission. Backend acceptance is separately simulated. These tests do **not** establish real Authorize.net SDK/API/webhook sandbox acceptance, merchant PCI compliance, mailbox delivery or host behavior.

## Verified .7 baseline

Application **6a9396f571a3c8658886f84f1b64b754cd6c7fe6**, tree **88dfa6fcdd316f9ef258f2c17bff74540bb32685**, passed Actions **38483296156** on PR #2, merge ref c986189f3b02f5b44a9e08ab6ca5ca0a7800c940. Jobs115590780970 PHP8.4/MySQL8.4 plus four Chromium suites and115590781333 PHP8.2/MariaDB11.4 passed. Exact-main docs descendant **9abe656b991f04bc34f77667d871be91440bd09c** passed **38484316285** on 2026-10-13 at23:55:04 UTC.

Executed43 foundation,26 storage,5 path,26 content-schema,27 communication,35 commerce units and10 packaging tests. Existing67 installer/account assertions,7 database-ownership checks and all content/media/music/shows, booking/newsletter, TLS and accepted-mail receipt-failure tests remained passing. Root/subfolder and additive-upgrade commerce tests covered draft/public separation, media, server pricing, stock/replay/conflicts, bag isolation, email-first activation, reset/expiry, address ownership, session revocation and owner suspension. Chromium exercised real catalog/bag/account and owner screens at1440px/390px. Screenshot artifact11857557964 contained40 synthetic renders; contact sheets and selected full-size forms were reviewed. Historical detailed evidence remains in the .7 Git history.

## Historical .6 baseline

Applicationdd0db41248a21b53968b2b26d7540c2944f9fa5a passed38479213942; exact-main docs descendant6b00769fee969570872c88446110429047dc0499 passed38479822564. Ten loopback TLS SMTP scenarios and five database receipt-failure assertions passed. Prior CSP polling, wrong test login label, privileged fault-injection trigger and genuine phone roadmap overflow were fixed rather than skipped. Historical detailed evidence remains in .6 Git.

## Boundaries

Not established: actual provider transactions or signed webhook compatibility; production inbox delivery; host Apache/LiteSpeed rules/aliases/TLS/ACL/open_basedir/proxies; legacy-runtime relocation; complete penetration testing; WCAG conformance; field Core Web Vitals; final high-resolution artwork; or v1.0 acceptance. Synthetic data, scripts and plaintext setup receipts never enter the public installation package. A source-artifact run is not application verification.
