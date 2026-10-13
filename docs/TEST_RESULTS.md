# Test evidence

## 0.7.0-dev.1 — complete workflow passed

Application source **6a9396f571a3c8658886f84f1b64b754cd6c7fe6**, tree **88dfa6fcdd316f9ef258f2c17bff74540bb32685**. Actions **38483296156**, PR #2, tested merge ref **c986189f3b02f5b44a9e08ab6ca5ca0a7800c940**. Both jobs passed: **115590780970** PHP8.4/MySQL8.4 and **115590781333** PHP8.2/MariaDB11.4. The PHP8.4 job additionally passed all four Chromium suites at1440px/390px. This documentation-only update changes no application code/assets/schema/tests.

Executed existing43 foundation,26 storage,5 path,26 content-schema,27 communication units and10 packaging tests, plus **35 commerce unit checks**. Existing67 installer/account assertions,7 database-ownership checks, content/media/music/shows, booking/newsletter, real loopback TLS SMTP and accepted-mail receipt-failure regressions remained passing.

New commerce SQL/HTTP tests ran root and subfolder installations: published product vs private edits; image authorization; exact price calculation and submitted-total tampering; stock deduplication/stale revisions/nonnegative limits; bag ownership, duplicate Add to bag, price/stock changes and unpublished-item removal; owner-only catalog controls; email-first account activation without an attacker-preselected password; token hashing/single-use/expiry/scanner-safe GET; login and separate staff/customer identities; address ownership/stale edits; password/session revocation; owner suspension/restoration; and additive .6 upgrade preservation.

Actual Chromium journeys rendered product/collection/cart/account/register/reset and owner product/category/variant/settings/customer screens. They submitted Add to bag and checked the real server subtotal, logged in a verified customer, checked overflow and uncaught JavaScript errors at both widths. Production CSP remained enforced; no live provider/payment was used.

Screenshot artifact **11857557964** contains **40 synthetic renders**, including26 commerce renders and14 prior communication/public renders. Catalog/account/admin contact sheets and selected full-size forms were reviewed. These are actual application renders, not concept mockups. Source artifact11858347801 was extracted and compared with the local draft; differences are documentation wording plus a non-functional muted-label typo. All other runtime/test/assets bytes matched. Local lint,35 new units, existing units, packaging and non-root43/26 checks passed separately; database/browser tests ran only in Actions.

## Historical .6 baseline

Application dd0db41248a21b53968b2b26d7540c2944f9fa5a passed38479213942; exact-main docs descendant6b00769fee969570872c88446110429047dc0499 passed38479822564. Ten loopback TLS scenarios cover STARTTLS/implicit TLS, trust/hostname refusal, authentication errors, disconnects, DATA rejection, lost acknowledgement/QUIT and time bounds. Five enforced-CHECK-constraint tests verify no automatic resend after SMTP acceptance but failed database receipt, without privileged grants. Prior CSP polling and mobile roadmap overflow were fixed rather than skipped. Detailed historical evidence remains in .6 Git history.

## Boundaries

Not established: production provider inbox delivery, real Authorize.net sandbox/production transactions, actual host Apache/LiteSpeed rules/aliases/TLS/ACL/open_basedir/proxies, legacy-runtime relocation, complete penetration testing, WCAG conformance, field Core Web Vitals, final high-resolution artwork or v1.0 owner acceptance. Customer addresses are US-only and not postal-validated. Checkout/orders remain unfinished. Synthetic tests and plaintext setup receipts never enter the public package. A source-artifact run is not application verification.
