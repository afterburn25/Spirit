# Test evidence

## Historical baselines

Foundation code cb1ce92a4af133e5a9c86fcfd7dce281af4ee336 passed Actions 38456010840 (PHP8.2/MariaDB11.4, PHP8.4/MySQL8.4 and Chromium installer/admin). Main 32e7d9ba / 0.5.0-dev.1 failed run 38466556133 in public_browser.py: the test's wait_for_function string evaluation was blocked by the strict script CSP. Earlier SQL/HTTP and installer/admin steps passed; this was not a completely green public-browser acceptance.

## 0.6.0-dev.1 — execution pending

Recovered and reviewed communication code plus new fixes/tests are on the recovery branch. The full new run must pass before recording verified evidence or advancing main.

Included checks: existing 43 unit + 26 storage + 5 foundation-path + 26 content-schema + 10 packaging checks; communication encryption/input/header/CSV tests; real database/HTTP root/subfolder install, content/media, inquiries, duplicate submission, owner permissions/private notes, notification reconciliation, queue backoff/locks/uncertainty, opt-in/scanner-safe confirmation/unsubscribe/export, and old-schema additive migration preservation. Real TLS SMTP fixtures exercise STARTTLS, implicit TLS, certificate/hostname rejection, authentication errors, pre-DATA disconnect, definite DATA rejection, lost acceptance acknowledgement, lost QUIT and deadline. Chromium tests exercise real audio progression, persistent navigation, public routes, inquiry submission and owner communication screens at 1440px/390px with CSP enforced.

Synthetic screenshot artifacts contain test-only content. Test media/data are not seeded into deployment ZIPs. TLS tests use a disposable local CA, never disable verification, and contact only loopback. No production secrets are present.

## Boundaries

Not established by these tests: provider inbox delivery, DNS/SPF/DKIM/DMARC setup, actual Apache/LiteSpeed rules and aliases, live host TLS/ACL/open_basedir/proxy behavior, legacy runtime migration, live payments, WCAG conformance, field Core Web Vitals, high-resolution art approval or owner acceptance of v1.0. A source artifact success is not application verification.
