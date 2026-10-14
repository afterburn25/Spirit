# Verification — 0.9.0-dev.9

## Exact application evidence

Application source: **4736764ef681103c7d1b02b3851861a43c9201e5**.
Application tree: **7bd4574ce745e4bdee6419b0033afdbde48c7f3c**.
Full workflow: **38567553630 — completed / success**.
Focused maintenance: **38567553677 — completed / success**.
Date: **2026-10-14**. PR #12 merge reference: **5cb17502e9c272f68e3467144a4a245887b90257**.
The final documentation-only descendant changes no runtime, assets, schema, workflows or tests. A source-artifact success is not an application test result.

| Environment | Job | Result |
|---|---|---|
| PHP8.2 / MariaDB11.4 | 115864285085 | PASS |
| PHP8.4 / MySQL8.4, including all twelve Chromium workflows | 115864285952 | PASS |
| Actual Apache PHP8.4 HTTPS access rules | 115864285705 | PASS |

## Unit, packaging and manual setup

480 cumulative pure assertions and12 packaging tests passed. The38 new manual-installer assertions cover all four cURL/ZIP availability combinations, independent names/results, nonblocking required flags, precise feature messages, retired-field rejection without secret reflection and absence of callable Cpanel classes. The test-only namespace shim is not loaded by application requests and never deployed.

New SQL/HTTP tests use the normal guarded limited test account, not a database administrator. They actually copy the test PHP configuration while omitting only cURL/ZIP extensions, then verify both absent and PDO MySQL/GD still present. With this real configuration, domain-root and subfolder installations finish on the same page, create exactly one owner, physically delete the installer and allow owner sign-in. Optional notices are truthful both before and after installation; payment-settings and upgrade-administration pages do not crash. Those features still require their own extensions before use.

Retired automatic submissions cannot reflect tokens or create state. A synthetic earlier private provisioning record that would throw if executed is left byte-identical and blocks setup for manual review, proving it was not executed or adopted. Removing that test-owned synthetic record permits the ordinary manual path. No real hosting resources, tokens or users are created.

The cumulative97 installer/account/recovery assertions still cover CSRF, no claim/setup code, concurrent setup, correction without password reflection, root/subfolder completion, real self-deletion, retained410 lock on deletion denial, cleanup retry, missing installer replay, unchanged config/lock hashes, prefix conflicts and interrupted migrations without duplicate owners. Existing feature SQL/HTTP/TLS/concurrency/authorization/recovery suites remain.

## Browser and visual checks

All twelve configured Chromium suites passed: installer/admin, public/player, communication, commerce/accounts, tokenization/orders, staff, backup/restore, ZIP upgrades/independent recovery, retention, content history, discovery and responsive media/keyboard behavior. Production CSP remains enforced; provider responses in payment tests remain synthetic.

The installer is tested at1440px,390px,320px and390px without JavaScript. There is exactly one form with native required manual fields and no mode/token/setup-code controls. Tests submit a password mismatch, verify correction/focus/non-secret preservation, complete on the same URL, confirm actual directory deletion and then sign in. Owner health exposes the separate extension diagnostics after setup removal.

Artifact **11896183628** contains109 synthetic screenshots. Ten installer form/completion/health screenshots were inspected at the tested widths, including the JavaScript-free route. These are actual rendered implementation checks, not alternative design mockups. Real host artwork/content acceptance remains separate.

## Canonical source and upgrade compatibility

Canonical tracked source was retrieved through artifact11895148584 and compared with local runtime/assets/migrations/workflows/tests; differences were documentation only. Local PHP lint passed for201 PHP/inc files; all480 pure assertions, Python compilation, JavaScript syntax and12 packaging tests also passed locally. Actual SQL/browser/Apache evidence comes from Actions, not the local environment.

Compared the candidate cumulative ZIP with the previously delivered dev8 manifest: no prior payload paths omitted, all six migration files and five pinned recovery/access files byte-identical, and every manifest hash valid. The three retired Cpanel file paths are declaration/comment-only compatibility files with no class definitions or API logic. This lets the old updater replace retired code without weakening its cumulative-file rule. It is not a new hosting API or an assertion of actual A2 upgrade success. Final package hashes/canonical comparisons are recorded in the delivered report.

## Corrected failure and boundaries

Initial run38566390271 stopped because the recovery test expected the old phrase 'provisioning failed', while the installer now explains manual database/table failures. Corrected the expected message and additionally asserted the correction form remains and completion markup is absent. Pending state, same-database recovery, original data and single-owner checks were retained. No workflow was made green by skipping a failure.

Historical dev8 evidence is preserved in [TEST_RESULTS-dev8.md](history/TEST_RESULTS-dev8.md). Its56 cPanel assertions and provider-boundary tests were retired with that deleted feature; the new38 tests bring the cumulative pure total from498 to480 without removing unrelated security/payment/upgrade coverage.

Not established: actual A2 domain PHP settings/ownership/ACL/open_basedir/aliases/OPcache/proxies; receiving-mailbox delivery; genuine merchant SDK/API/webhook/settlement acceptance; approved master artwork/content; complete populated-site accessibility/performance/security review; or owner acceptance of v1.0. No server extension has been enabled on the user's hosting account. Same-instance restore and same-schema ZIP upgrades retain their stated limits.
