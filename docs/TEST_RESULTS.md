# Test evidence

## 0.9.0-dev.1 — staff/access checkpoint awaiting full run

Local syntax, Python compilation, packaging and **38 permission unit checks** pass. New SQL/HTTP and browser tests are committed for execution, not reported as passed yet.

Staff tests cover root/subfolder owner reauthentication/CSRF, hashed/encrypted email-first invitations, assigned-role/email tamper refusal, scanner GET safety, single-use completion, role-specific pages, actual content publication/private media, owner-data denial, suspended/stale session revocation, own password change/reset, cancellation/expiration and obsolete mail suppression, self-owner protection, private audit, real manager inventory/shipment and forbidden refund/reporting/control operations, and additive .8 schema preservation. Browser tests cover actual owner invitation/activation/editor sign-in, scoped navigation/pages and direct forbidden URLs at1440/390 with CSP intact. Fixtures contact no actual provider and seed no production users.

## Verified 0.8 baseline

Application cd3d645bf031e7ab8cf2a7c32b44278e8bc8d89e, tree68ebedd13411f17856e24ae484a3318a856070d2, passed full run38493194973 on PR#3, completed2026-10-14 01:02:51UTC. PHP8.2/MariaDB11.4 job115622964325 and PHP8.4/MySQL8.4 job115622964643 passed. All five configured browser suites passed, including checkout/orders. Artifact11862496582 contains54 synthetic screenshots;14 payment/order/admin renders inspected. Documentation descendant eb995810 changes no runtime/tests.

Executed foundation/storage/path/content/communication/commerce/payment/packaging suites and real SQL/HTTP/concurrency/recovery/loopback TLS. Fixed the invalid AUTO_INCREMENT CHECK fixture and real phone payment-settings overflow without skipping assertions.58 payment units plus actual browser token-only POST tests passed. Test gateway injection is CLI-only, external SDK replacement lives only in Playwright, and live payment networking is disabled. This is not proof of real provider sandbox acceptance or PCI compliance.

## Boundaries

Actual hosting permissions/rules/TLS/aliases, legacy runtime migration, actual payment provider SDK/API/webhook and inbox delivery, full penetration testing/WCAG/field performance, high-resolution artwork/content approval and v1.0 acceptance remain unverified. A source-artifact workflow is not an application test. Detailed earlier evidence remains in Git history.
