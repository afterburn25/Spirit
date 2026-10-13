# Test evidence

## 0.6.0-dev.1 — full application workflow passed

Verified application source: **dd0db41248a21b53968b2b26d7540c2944f9fa5a**.
Application tree: **cb2277ed7cca634c3909328387606c85934e9615**.
Workflow: **Website and installer verification**, Actions run **38479213942**, PR #1.
Result: **completed / success**, 2026-10-13 **22:41:16 UTC**.
Tested PR merge ref: **c932d98f5ad59a3dca7ae3516412969fbcc73e45**, based on main 32e7d9ba68d4416e73b11acafb7617223408a097.
This documentation-only update changes no application code, schema, assets or tests. A later exact-main run can be checked directly in Actions; do not confuse a source-artifact run with verification.

| Environment | Job | Result |
|---|---|---|
| PHP 8.2 / MariaDB 11.4 | 115577390948 | PASS |
| PHP 8.4 / MySQL 8.4 | 115577390730 | PASS |
| Chromium installer/admin, public/audio and communication journeys, 1440px and 390px | Included in PHP 8.4 job | PASS |

## Checks executed

Both database jobs executed PHP syntax checking, 43 foundation unit checks, 26 permission-aware storage checks, 5 foundation-path checks, 26 content-schema checks, **27 communication unit checks** and 10 packaging tests. SQL/HTTP integration included 67 installer/account assertions, 7 database ownership checks, content/media publication and access tests, plus the new communication journeys below.

Communication SQL/HTTP tests run at the domain root and in a subfolder: booking CSRF/honeypot/contact consent; real saved inquiry/private receipt; duplicate POST and message deduplication; escaped organizer content; private owner notes/assignment/status and stale-edit rejection; anonymous/editor denial; queue failure without inquiry loss; durable notification reconciliation; retry backoff/max attempts; concurrent-worker rejection; uncertain/abandoned delivery and explicit duplicate-risk acknowledgement; encrypted settings and blank-password preservation; newsletter opt-in/cooldown/hashed tokens; scanner-safe confirmation/unsubscribe GET versus POST; expired token denial; confirmed-only exports and suppression; CLI-worker HTTP denial. An older-schema update test preserves the owner and website settings and verifies idempotence.

**Ten real loopback TLS SMTP scenarios** cover accepted STARTTLS and implicit TLS delivery, untrusted certificate, hostname mismatch, authentication refusal, pre-DATA disconnect, explicit DATA rejection, lost final acknowledgement, lost QUIT after acceptance and a stalled greeting deadline. Tests inspect actual received MIME/body bytes. They trust a disposable local CA; production certificate verification is never disabled.

**Five post-acceptance database-failure assertions** use an enforced CHECK constraint on a disposable outbox to fail the sent-receipt update after simulated SMTP acceptance. The message becomes uncertain, does not automatically resend and requires duplicate-risk acknowledgement. No global SUPER privilege or weakened binary-log safeguard is used.

Chromium performs real inquiry submission, owner sign-in and note persistence; verifies private owner booking/email/queue/subscriber pages; checks no horizontal page overflow at both widths; and verifies no password value reflection or uncaught JavaScript errors. Public tests verify 10 routes at each width, decoded images, no autoplay, actual audio time progression, the same playing audio node across navigation, pause/close controls, mobile navigation and gallery Escape handling, with CSP enforced. The installer/admin smoke suite includes the corrected phone roadmap layout.

## Visual and local verification

Browser artifact **11856157592** (`Spirit-browser-c932d98f5ad59a3dca7ae3516412969fbcc73e45`) contains **14 synthetic screenshots**: desktop/mobile public home and booking, owner inquiry list/detail, email settings, delivery queue and subscribers. These were inspected; they are renders of the tested implementation, not proposed mockups. Current reference-crop artwork is still not final master art.

Local PHP 8.4 lint, non-root foundation/storage, path/schema/communication unit and packaging checks passed separately. SQL, TLS integration and Chromium results above come from Actions; the local container did not execute those environments. Test media/data, temporary CA keys and executable test scripts are not deployed. Plaintext package setup receipts stay outside the deployment ZIP and Git.

## Corrected failures and historical baselines

Foundation cb1ce92a4af133e5a9c86fcfd7dce281af4ee336 passed Actions 38456010840. The .5 checkpoint 32e7d9ba failed run 38466556133 because public_browser.py used CSP-incompatible string polling; production CSP was preserved and the test now polls actual media state through its automation context.

During .6 verification, corrected an invalid runner context in workflow job-level environment, a test login-button label that did not match Enter backstage, a fault-injection trigger that improperly depended on MySQL SUPER under binary logging, and a genuine phone roadmap overflow caused by longer status labels. No failing test was skipped or turned into a unconditional pass.

## Boundaries

Not established: production provider inbox delivery; DNS/SPF/DKIM/DMARC setup; actual Apache/LiteSpeed rules/domain aliases; live-host TLS/ACL/open_basedir/proxy behavior; legacy runtime relocation; live payments; complete penetration testing; WCAG conformance; field Core Web Vitals; high-resolution art approval; or owner acceptance of v1.0. Green regression tests establish the exercised workflows, not completion of the unfinished commerce/maintenance roadmap.
