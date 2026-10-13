# Test evidence

## 0.7.0-dev.1 — new execution pending

Local PHP lint, 35 commerce unit checks, existing path/content/communication units and packaging checks passed. SQL/HTTP root/subfolder/additive-upgrade and Chromium suites are added but require their exact new Actions run. The local environment lacks pdo_mysql/GD and browser/database execution is not claimed. Do not treat prior .6 evidence as verification of changed source.

Commerce tests exercise approved public product snapshots vs private edits; exact image authorization; price parsing/server recomputation; inventory adjustments, dedupe and stale revisions; private bag ownership, form replay, price spoofing, stock changes, unpublishing and removable unavailable lines; email-first account activation, no attacker-preselected password, hashed/token expiry/single-use/scanner-safe completion; independent staff/customer identities; address ownership and stale edits; password/session revocation; owner suspension/restoration; and .6 migration preservation. Rendered tests cover actual customer bag/account and owner catalog interfaces at 1440px/390px. No real provider or payment is used.

## Historical .6 baseline — passed

Application dd0db41248a21b53968b2b26d7540c2944f9fa5a, tree cb2277ed7cca634c3909328387606c85934e9615, passed run 38479213942 at 2026-10-13 22:41:16 UTC. Tested PR merge ref c932d98f5ad59a3dca7ae3516412969fbcc73e45. Jobs: PHP8.2/MariaDB11.4 115577390948 and PHP8.4/MySQL8.4 115577390730. Exact-main docs descendant 6b00769fee969570872c88446110429047dc0499 passed run 38479822564 at 22:46:49 UTC.

Executed 43 foundation units, 26 storage, 5 foundation-path, 26 content-schema, 27 communication unit and 10 packaging tests. SQL/HTTP included 67 installer/account assertions, 7 ownership checks, content/media publication/access and root/subfolder communication/upgrade workflows. Ten real loopback TLS SMTP scenarios covered STARTTLS/implicit TLS, certificate/hostname rejection, authentication errors, pre-DATA loss, explicit DATA rejection, lost acceptance reply, lost QUIT and deadline. Five database-fault assertions verified no automatic resend after accepted SMTP but failed receipt persistence, using an enforced disposable CHECK constraint without global privileges.

Chromium installer/admin, public pages/actual audio advancement and persistence, booking and owner communication flows passed at 1440px/390px. Artifact11856157592 contained14 synthetic screenshots inspected in the .6 session. No new .7 screenshots have yet been inspected. Earlier .5 CSP polling, wrong login-test label, MySQL privileged trigger and genuine mobile roadmap overflow were corrected rather than skipped. Historical detailed evidence remains in the .6 Git history.

## Boundaries

Not established by automated tests: actual host Apache/LiteSpeed rules/aliases, provider inbox delivery or DNS mail setup, production TLS/ACL/open_basedir/proxies, legacy-runtime relocation, live payments, complete penetration testing, WCAG conformance, field Core Web Vitals, final high-resolution artwork or v1.0 owner acceptance. Source-artifact success is not application verification. Synthetic data/scripts and plaintext setup receipts never enter a public deployment package.
