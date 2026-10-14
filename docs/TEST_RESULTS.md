# Verification — 0.9.0-dev.12

## Exact application evidence

Application **eefab5ab98b27397a5be48ba311bf9b9ffe869fb**; tree **7efabaa53a80676f04c841706f436c286fa7ba35**.
Date: **2026-10-14**.
Full workflow **38632645453 — completed / success**.
Focused maintenance **38632645480 — completed / success**.
Populated storefront workflow **38632644731 — completed / success**.
PR #14 tested merge reference **a8cc68830a9ff90081daabf19c31234e272f37d6**.

The final documentation-only descendant leaves application runtime, assets, database migrations, workflows and tests unchanged. An additional exact-main rerun can be checked directly in Actions; never confuse a source-artifact success with application verification.

| Environment | Job | Result |
|---|---|---|
| PHP8.2 / MariaDB11.4, cumulative SQL/HTTP | 116088363059 | PASS |
| PHP8.4 / MySQL8.4, cumulative SQL/HTTP and13 Chromium suites | 116088363845 | PASS |
| Actual Apache PHP8.4 HTTPS access rules | 116088363838 | PASS |
| Populated storefront / Chromium | 116087862457 | PASS |
| Populated storefront / Firefox | 116087862866 | PASS |
| Populated storefront / WebKit | 116087862994 | PASS |

## Executed coverage

**575 distinct pure assertions**:544 retained assertions in the cumulative suite plus31 gallery assertions in the dedicated matrix. Running the same31 checks in three engines does not create93 distinct checks. **12 packaging tests** passed. Separate local validation checked213 PHP/inc files, all575 pure assertions as an unprivileged user, five JavaScript syntax files and Python compilation. The local environment does not have the required SQL/GD/cURL/ZIP extensions; real SQL/HTTP/browser/Apache evidence comes from Actions.

All13 retained Chromium workflows passed: installer/admin, public/player, communication, commerce/accounts, tokenization/orders, staff, backup/restore, ZIP upgrade/recovery, retention, content history, discovery, responsive-media/keyboard and launch-diagnostics/upload feedback. Both database environments retain actual no-cURL/no-ZIP manual setup tests. Production CSP and original publication/authorization/payment/recovery checks were not weakened.

The added storefront workflow uses PHP8.4/MariaDB11.4 and actual owner forms to create synthetic merchandise with three different landscape, portrait and small photographs. Each engine runs1440px,390px and320px, plus390px without page JavaScript. Each produced212 PASS lines from browser/integration assertions. Tests verify actual selected currentSrc and every responsive candidate, source dimensions and alternative text, loaded image state, exactly one selection, square main geometry and thumbnail containment, focus, form quantity, canonical URL and history length.

Real user-initiated audio continues from a release through merchandise navigation, retaining the same audio node. Failed-photo requests keep the previous image and URL, a retry succeeds, and an actually intercepted earlier response cannot replace a later photograph when released. Native photo links cannot select unrelated media or revive an unpublished product. Reading and switching photos preserves catalog, inventory, account and audit snapshots. Unpublishing withdraws otherwise-private originals and resized copies.

## Visual evidence

Fifteen final storefront screenshots were inspected: five per engine, covering desktop, both narrow widths, native portrait selection and failed-load fallback. Files are actual endpoint renders, not new design mockups. Synthetic patterned images and fixture products are not band inventory and never enter deployment databases. A fixed player appears at its viewport position in a full-page capture; that is not a claim that its live CSS position is mid-page.

| Artifact | Engine | Archive SHA-256 |
|---|---|---|
| 11927155848 | Chromium | 9c23d6d0f6081ee3371348cae846adef54b7031c2da1ff23d8ce6d1dbcf5c7fe |
| 11927515633 | Firefox | 2654b75e115d9aa26bbc2b3cf55abb76cf05bfcc9e59a9a7540e4324684f241c |
| 11927395726 | WebKit | 4455fed5d744192d6653820bccb9d6e5a051598ec420c403ad5675935d6b1d1b |

All three downloaded archive hashes were independently verified and their successful final log lines read. The full workflow's other screenshots were not all subjected to another manual visual review in this pass.

## Corrected defects and fixture assumptions

An in-memory reproduction using the actual old and new gallery scripts confirmed the stale-srcset defect: the old script changed fallback src to photo2 while actual currentSrc and alternative text remained photo1. The fixed script selected photo2. A separate actual-CSS reproduction measured a portrait thumbnail overflowing its78px anchor and a390px-wide main container expanding to584px high; the bounded-grid fix restored containment and a390px square. Final real-browser geometry assertions cover the same behavior.

Early tests incorrectly demanded a warm thumbnail reload a smaller image rather than reuse cached decoded data; the measurement now uses an unselected cold thumbnail. Delayed-response testing uses a fresh context instead of assuming a warm image must refetch. Firefox receives a disposable CI audio output sink; actual playback state is observed, not fabricated. With page JavaScript disabled, image readiness is polled synchronously with a failure bound rather than awaiting an indefinitely stalled document Promise. A deliberately held fallback request cannot reach network-idle before release, so that test waits for DOM readiness and explicitly verifies a real intercepted response. All image, privacy, failure and last-selection assertions remain.

Canonical comparison also caught and restored an unrelated address-card margin omitted during a stylesheet copy. Visual inspection found the portrait-cell defects even after earlier behavior assertions passed. No browser was removed and no failing assertion was replaced with an unconditional success.

## Source and delivery integrity

Canonical main21e9bd6f/dev11 was recovered from its source artifact and exact-main run38591939605 was verified before work. Successive Git tree reconstructions matched the connector's returned tree IDs, including final application7efabaa5. Only product-gallery runtime paths, the dedicated browser workflow/tests and documentation were changed. All manual installer files, six migrations and five pinned recovery/access files remain byte-identical to the baseline. Previous dev9 and distinct delivered dev10-preview payload paths are preserved.

The final cumulative Full ZIP and manifest are independently checked against canonical source. Executable tests/build scripts, private runtime data and setup receipts are excluded. Do not overwrite an already delivered version with different bytes. The delivered report records final commit/tree/package hashes and whether the subsequent exact-main rerun was finished at handoff.

## Boundaries and history

Not established: actual A2 account PHP ZIP/cURL settings, aliases/ACL/open_basedir/OPcache/proxies; real receiving-mailbox or merchant SDK/API/webhook/settlement/reversal acceptance; audible-speaker or physical-device/native-Safari coverage; approved master artwork/content; complete populated-site accessibility/performance/security; or final owner approval. No hosting setting or production payment mode was changed. Same-instance guarded restoration and same-schema browser upgrades retain their documented limits.

Previous complete evidence is retained in history/TEST_RESULTS-dev11.md and earlier Git checkpoints. This focused gallery pass is not the finished v1.0 launch.
