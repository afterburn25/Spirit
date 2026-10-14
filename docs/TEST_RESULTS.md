# Test evidence

## Current 0.9.0-dev.8 — integrated verification pending

Branch work/single-page-installer includes code-free one-page setup and optional A2/cPanel provisioning. Local cPanel input/contract tests:56 passed. New PHP/Python syntax and12 packaging tests passed locally. Full current database/browser/Apache results are not yet verified.

New coverage: actual loopback cURL TLS/token/form transport with certificate, redirect, authentication, malformed/oversized response and redaction checks; guarded test cPanel boundary creating real dedicated databases/users in disposable MySQL/MariaDB; fresh, explicit rejection, lost acknowledgement and migration-retry scenarios at root/subfolder paths. Application code never receives the test-only SQL admin credential and cannot choose test transport from an HTTP input. External cPanel networking is disabled in CI. No real A2 API call is claimed.

The baseline dev8 run38553237171 failed at an over-specific missing-directory404 assertion after successful installation/deletion. The test now verifies the physically missing PHP entry is404, directory fallback cannot reopen the form, and POST cannot duplicate owners. The actual Apache HTTPS job passed separately; this correction changes no server access rules. Subsequent full results must be recorded for the exact new code before promotion.

## Historical 0.9.0-dev.7 baseline

Application **7c5582075b4b47c01e721c2c5e30d4f4a593acbb** passed full workflow **38525702729** and focused maintenance **38525702713**. The documentation-only descendant changed no tested runtime/assets/schema/tests. MEDIA_QUALITY_CHECKPOINT.md records source/artifact and render evidence. This historical run is not evidence for changed dev8 code.

The dev7 matrix executed **442 cumulative pure assertions**, including **44 media-quality checks**, **11 packaging tests**, SQL/HTTP root/subfolder workflows on PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, **twelve Chromium workflows**, and actual Apache PHP8.4 HTTPS access-rule checks. Chromium runs in the PHP8.4 job, intentionally not duplicated in the PHP8.2 job.

Media SQL/HTTP tests exercise actual resized bytes and alpha, preserved originals, authorization before cache/304, bounded widths, representation-specific ETag/HEAD/If-Range, original downloads, busy generation locks, damaged cache regeneration, host-denied writes, symlink refusal, original disappearance, retention and low-memory fallback. Existing payment/mail/backup/update/security/consent tests remain in the matrix.

The media browser workflow covers1440px/390px/320px: real responsive candidate selection and lower bytes, intrinsic aspect ratio, first-party asset budget, reduced motion/reflow, keyboard gallery open/close/focus, true audio progression and queue boundaries, pressed/seek state, narrow-player bounds, focus above fixed player, close-focus return, server validation feedback and ordinary admin Escape. Targeted evidence is not full WCAG conformance.

## Historical fixture and visual evidence

The generated1800×1200 RGBA fixture is not band artwork. Its re-encoded original measured412,565 bytes and640px copy21,306 bytes:94.8% fewer for that fixture. Desktop selected960px measuring64,820 bytes. The fixture page stayed below130,000 bytes of first-party CSS/JavaScript. These are not field LCP/INP/CLS or a claim about actual band photographs.

Application238fa431193a86fb00e867f96a445ecafc19f46b passed38524917377/38524917358. Nine new renders were inspected from99-PNG artifact11875780837. Only subsequent application change was owner-roadmap wording, separately included in the full run. Source artifact11875622065 was digest-checked and runtime/assets/schema/tests matched local files. Local pure/package/lint and isolated Chromium component experiments also ran; local PHP did not run real database/GD transports.

## Historical corrected failures

A textured fixture initially exceeded default uploads; compressible texture preserved dimensions/alpha while fitting the unchanged limit. Intrinsic HTML dimensions/CSS width exposed image stretching, corrected with automatic height and class-token hints. A320px player title overlapped controls, corrected with constrained flex and two rows. Native Escape returns focus before a queued close event; tests wait for actual effects without CSP bypass, skipped assertions or fabricated passes.

## Earlier verified checkpoints

Dev6 af43d153 passed38518823266/38518823277; exact-main4f6005f7 passed38521278470. Dev5 exact-main11a5038 passed38514355067. Earlier exact evidence remains in DISCOVERY_CHECKPOINT, CONTENT_HISTORY_CHECKPOINT, MAINTENANCE_CHECKPOINT, BACKUP_CHECKPOINT and Git history.

## Explicit limits

No genuine merchant transaction, A2/cPanel account acceptance, receiving-mailbox result, arbitrary host aliases/ACL/open_basedir/OPcache/proxy acceptance, complete penetration/accessibility conformance, field Core Web Vitals, production artwork/content approval, automatic cross-schema upgrade/cross-host disaster restoration or acceptedv1.0 is established by synthetic tests. A source-artifact success is not application verification. Follow LAUNCH_CHECKLIST before cutover.
