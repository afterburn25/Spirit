# Verification — 0.9.0-dev.11

## Exact application evidence

Application source **d699fd6e9f42a8fc2d0329fd07778c4a2718f7a8**; tree **df20fbeb8c1d552755b3fd8c4b7bc10e766e1c0c**.
Full workflow **38589219303: completed / success**, 2026-10-14.
Focused maintenance **38589219399: completed / success**.
PR #13 tested merge reference **7c2f10ce46eef7b5470d63bef6bfcf133e5088e6**.
The final documentation-only descendant changes no runtime, assets, database migrations, workflows or tests. Check subsequent exact-main runs directly; a source-artifact result is not application verification.

| Environment | Job | Result |
|---|---|---|
| PHP 8.2 / MariaDB 11.4 | 115939297865 | PASS |
| PHP 8.4 / MySQL 8.4, including thirteen Chromium workflows | 115939297301 | PASS |
| Actual Apache PHP 8.4 HTTPS access rules | 115939297356 | PASS |

## Tests executed

**544 cumulative pure assertions** (480 retained and 64 launch/hosting assertions), **12 packaging tests**, both SQL/HTTP environments and all configured browser/access suites passed. Local verification separately checked 211 PHP/inc files, all 544 pure assertions under an unprivileged account, Python compilation, JavaScript syntax and the packaging suite. The local container lacks the database/image extensions needed for end-to-end integration; those results come from Actions, not local lint.

Launch and hosting coverage includes PHP quantity parsing, malformed/unlimited/overflow cases, oversized Content-Length detection, strict report projection and suppression of arbitrary private fields. Real SQL/HTTP cases cover domain-root and subfolder installations, the canonical and preview URLs, actual no-cURL/no-ZIP PHP environments, partial schema and unavailable-table behavior. Owner, content-editor, store-manager, anonymous, suspended and revoked-access paths are exercised. Viewing and exporting preserve full database-table fingerprints. Private drafts and products under hidden categories do not inflate published counts. Missing outbox data becomes an isolated unavailable observation rather than leaking an exception or failing the whole screen.

The report verifies all 43 fixed check IDs and the distinction between configuration, unresolved work and six external review gates. Selected and enabled production payments warn even when other checkout prerequisites are absent. Report values exclude credentials, hostnames, paths, database names, logs, email addresses and content text. Unsupported methods/actions and invalid CSRF are rejected; no provider request or queue action is performed by diagnostics.

Upload tests use actual deliberately small PHP limits, not a mocked successful upload. Media, backup-import and ZIP-staging requests over the whole-request ceiling receive HTTP 413 without executing the requested action or modifying business data. Normal-sized forms retain CSRF and role/reauthentication requirements. Existing installation, payments, mail, staff, maintenance, backup, restoration and recovery regressions remain in the full workflow.

## Browser and visual evidence

Thirteen Chromium workflows passed: installer/admin, public/player, communication, commerce/accounts, tokenization/orders, staff, backup/restore, ZIP upgrades/independent recovery, retention, content history, discovery, responsive media/keyboard and launch diagnostics/upload feedback. CSP remains enforced. Payment-provider results remain synthetic, not real merchant transactions.

The launch workflow runs at **1440px, 390px and 320px**. It verifies all 43 rows, active navigation, the former preview URL, no horizontal overflow, a real keyboard-triggered JSON download, private-value omission, operating-mode warning and report states. It then submits a genuinely oversized multipart upload, receives 413, focuses the explanatory error and leaves a fresh usable file field. The export button's keyboard focus ring has at least 12 pixels before the timestamp, measured in the actual browser.

Final artifact **11906393696**, `Spirit-browser-7c2f10ce46eef7b5470d63bef6bfcf133e5088e6`, contains **115 synthetic screenshots**. Its archive SHA-256 is `184d12865b08f95c3d077da84d5c091a36b80b2e8dc1a432b961f1f3457394d7` and was independently checked. The six current launch/upload renders were inspected, including the corrected support-card focus spacing on desktop and both phone widths. These are actual endpoint renders, not new design mockups. Other suites' screenshots are retained as evidence; this is not a claim that every screenshot received a new visual review.

## Recovery and source integrity

Live branch discovery recovered the saved dev10 diagnostics application f5e165cd and consolidated dev11 source 2cb333b7. Their successful runs were not known in the earlier reply describing only the separate local dev10 preview. The recovered 2cb333b7 passed full38581606678/focused38581606460. This session fixed a visible support-card focus overlap and required the new d699fd6e run above. No failed test was skipped or weakened.

The recovered canonical source artifact 11903570900 was hashed and reconstructed to Git tree bc5fa28e7dcb25a1286ce089b8744165870ab7d5. The three changed template/CSS/browser-test paths reconstructed the same df20fbeb tree returned by GitHub. Final documentation-only source and deployment checks are recorded in the delivered release report.

Cumulative package checks preserve all deployment paths from both delivered dev9 and the distinct local dev10 preview, six immutable migration files, five pinned access/recovery files, and the manual one-page installer bytes. Preview helper/template paths and retired Cpanel paths remain inert compatibility files, not callable old features. Package manifests are independently verified; executable test/build scripts and private setup receipts are excluded. No existing delivered ZIP is overwritten with different bytes.

## Remaining acceptance boundaries

Not established by these tests: actual A2 account/domain PHP settings, ZIP enablement, aliases/ACL/open_basedir/OPcache/proxy behavior; real receiving-mailbox delivery; genuine merchant SDK/API/webhook/settlement/reversal acceptance; approved master artwork/content; complete populated-site accessibility/performance/security review; or final owner approval. Diagnostics do not certify backups or a real recovery drill. Same-instance guarded restoration and same-schema ZIP upgrades retain their documented limits. No hosting setting, production payment mode or live website was changed.

Historical dev9 evidence is preserved in history/TEST_RESULTS-dev9.md and earlier checkpoints remain in Git history. The separate delivered dev10 preview's 561 local assertions are not being added to this consolidated build's 544 executed pure assertions.
