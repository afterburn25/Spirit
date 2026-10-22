# Final 1.1.0 verification

## Release scope

Final 1.1.0 promotes the production runtime from published rc.12 source
46744207a0916ae2256d84fbaa312c9c4fc67768. Application PHP, public CSS/JavaScript,
approved artwork, all eight SQL migrations, routing and recovery pins are
unchanged. Deployment changes are VERSION and release/operating documentation.
Development source adds app/tests/exact_package_upgrade.py and an explicit CI
invocation against pinned published baselines.

All previous cumulative payload paths remain. The ten deployment directories,
original installation identity, removed installer and disabled-commerce scope
are preserved. No live host or real customer/provider data is used.

## Observed rc.12 CI

On 2026-10-22 UTC, all eleven workflows for the exact rc.12 head above were
observed completed successfully:

| Workflow | GitHub Actions run |
|---|---:|
| Populated storefront browser verification | 39661313013 |
| Website and installer verification | 39661313086 |
| Visitor analytics verification | 39661313065 |
| Grouped layout transition verification | 39661313143 |
| Public navigation and native controls | 39661312854 |
| Focused maintenance diagnostics | 39661313218 |
| Review package artifact | 39661313353 |
| Software release gate | 39661312927 |
| Authoring safety verification | 39661312958 |
| Site logo and footer verification | 39661312940 |
| Candidate installation package | 39661303086 |

These are rc.12 results, not a claim about another commit. Final-source CI must
be inspected separately before publication. PR 37 and the companion release
receipt identify those final checks; post-merge main may have a different
workflow set.

## Exact baseline-to-rc.12 upgrade and recovery — observed 2026-10-22 UTC

Environment: non-root PHP 8.3.6, MariaDB 10.11.14 and Python 3.12.14 with dedicated
disposable database prefixes. PHP's normal extension configuration is retained;
only isolated test upload/post limits are raised to accept the original ZIPs.
Verification GETs use the site's supported DNT behavior so observer requests do
not create analytics rows. Production settings and guards are not changed.

| Baseline | Original/source identity | ZIP SHA-256 |
|---|---|---|
| rc.10 | Reproduced from published commit 5703ca41a13a6ff01a3b7807287abdeb72c151a5 | dddd811599691dff31f4adb30426d0aa156227d65133bbe811f5acf11399b4f5 |
| rc.11 | Original delivered archive | 4abbc7d59dc6e1cd003950eecca628d73fa8fb3ecffb1466b73dd1147107168c |
| rc.12 target | Original delivered archive | f65a5db2e5b226c54409d909b390f174dcc6e0c2e3dc1408f0b0a06e26a0e8ec |

The original rc.10 delivery's checksum was not recovered. Its baseline uses the
exact published source, original packager and Pillow 12.3.0; identity with its
originally delivered ZIP is not asserted.

**429 assertions passed across six scenarios:** each baseline performed a root
and /band installation, real owner upload, receipt download, apply, separate
confirmation and independent recovery, plus a separate interrupted-copy
recovery. Each baseline passed 76 root checks, 76 subfolder checks and 56
interruption checks; thirteen further checks validated archive integrity and
unchanged input hashes.

The checks verified exact installed payload hashes, encrypted pre-update backup,
public 503 maintenance until confirmation, original owner sign-in, unchanged
private configuration/lock/runtime identity and stored/served media bytes.
Digests of all 32 stable database tables include complete users/password hashes,
settings, published copy, private drafts, migration ledger and analytics.
Expected audit and request/login counters are excluded, not business records.
Code recovery preserved content added after the upgrade, removed target-only
files, left the installer physically absent and refused replay of a consumed
recovery receipt. Synthetic test content is confined to disposable sites.

## Retained footer and media verification

The original rc.12 evidence is preserved under
history/rc12-source-checkpoint/docs-TEST_RESULTS.md. It records:

- 53 installed before/after geometry checks, with no uncaught browser errors.
- Footer bottoms matching short viewports at 1100, 1200 and 800 pixels, while
  footer heights, long-page geometry, links and 75%-opaque appearance remain.
- The original 32px Home booking/newsletter gap.
- Real playback at 320px: 152px player and 168px body clearance, including the
  existing 16px allowance; focused footer links remain visible.
- 112 installed media/keyboard assertions and 197 isolated real-template Music
  filter assertions. The latter is a component check, not an installation test.
- Reproducible public assets totaling 129,916 / 130,000 bytes, fifteen package
  unit tests and the deterministic software release gate.

Those historical observations are retained accurately rather than relabeled
as fresh tests of the final archive.

## Final archive publication procedure and receipt

Freeze all deployment files before building the immutable
Spiritual-Gorillaz-1.1.0-Full.zip. The package's own hash cannot be embedded in its
payload without changing that hash; record it in the separate release receipt.

Before publication:

1. Run the package unit tests, deterministic release gate and public asset check.
2. Compare final and rc.12 manifests: no previous path omitted; all non-VERSION,
   non-documentation runtime hashes unchanged; original migrations/pins retained.
3. Run the exact-package harness with rc.10, original rc.11 and original rc.12
   baselines against that frozen final ZIP, preserving each input unchanged.
4. Capture real unseeded public pages and short-page Search geometry from the
   same ZIP using Chromium 143.0.7499.4. Include package/screenshot hashes in the
   capture manifests. Static reduced-motion captures do not certify animation.
5. Observe successful Actions for the final PR head, merge that reviewed head
   into main, and record the merge/tree identity. Check applicable main runs
   separately. Never infer a deployment from a GitHub merge.

The companion Screenshots-and-Verification ZIP contains these final execution
receipts and actual captures. The final source workflow reproduces pinned
rc.10/rc.12 baseline packages; those CI builds are labeled source reproductions,
while local rc.11/rc.12 tests use the original delivered bytes.

## Existing acceptance and limits

The consolidated 1.0 owner-confirmed installation/mail/account results remain
accepted; see acceptance/CONSOLIDATED-FINAL-PASS.md and acceptance/FINAL-1.1.0.md. They are
not newly inspected provider records. Commerce stays disabled and optional
content may remain unpublished. Merchant acceptance belongs to activation.

Tests do not establish actual A2 permissions, physical-device behavior, recent
cron execution, live delivery, merchant processing, field performance or a
complete accessibility/security audit. No live deployment is performed.
rc.8-or-earlier updaters must still reject the grouped foundation; use the
documented same-root transition rather than weakening recovery checks.
