# Test evidence

## 0.9.0-dev.8 — complete configured workflow passed

Application **b9d32849e9b556dbc2b6d4f2adb7526703b46562** passed full workflow **38560767553** and focused maintenance **38560767558**. Both database environments, all twelve Chromium workflows and actual Apache HTTPS checks passed. This final documentation-only descendant changes no runtime, assets, database migrations or tests.

The cumulative matrix executed **498 pure assertions** (442 retained plus 56 cPanel input/contract checks), **12 packaging tests**, SQL/HTTP integration on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4, all twelve Chromium workflows on PHP 8.4, and actual Apache PHP 8.4 HTTPS access-rule checks. Browser steps are intentionally not duplicated on PHP 8.2.

Successful full-run jobs: PHP 8.4/MySQL 8.4 **115840967275**, PHP 8.2/MariaDB 11.4 **115840967766**, actual Apache HTTPS **115840967560**.

### Installer and cPanel evidence

One-page browser installation passed at 1440 px, 390 px, 320 px and without JavaScript: first-page checks/database/owner form, no setup code, native validation and secret non-reflection, cPanel mode selection, same-page completion, actual folder deletion, separate owner sign-in. SQL/HTTP checks additionally cover denied cleanup with permanent lock/retry, removed-path GET/POST replay, preserved config/completion-lock hashes, existing prefix rejection and interrupted migration recovery.

Nine actual loopback cURL TLS scenarios test direct/wrapped success, explicit API rejection, unauthorized response, refused redirect, malformed/oversized body, untrusted certificate and hostname mismatch. They inspect actual Authorization header and POST bytes, without logging credentials. Eight root/subfolder API-boundary scenarios create real dedicated SQL databases/users in disposable services: success, definite user rejection, lost database acknowledgement and migration interruption. Generated credentials connect through PDO; confirmed resources are reused; uncertain outcomes pause. The test-only SQL administrator is scoped to that simulator step. Application code does not receive it and external cPanel/payment networking is disabled.

Eleven installer screenshots were inspected from artifact **11891257992** (105 total renders) at source **a0f0f380**. Subsequent runtime changes affect only public playback time/ARIA, not installer code/layout. The archive digest was checked: `520a6e1c493f0eb932d0007e44e8ad8797b8de8ba9d2e25c2a0822403d3d6ec2`. Final application source artifact **11892855093** from run **38560760087** was checked against digest `c9e0bd7647210f2b80c5c797349eee62e13317e1a55d9a2f5e5a7bd12df20435`; all 315 tracked files matched the implementation apart from the deliberately prepared documentation edits. The 6 migrations and 5 pinned recovery/access files matched dev7 exactly. Final release packaging independently validates its complete manifest and excludes tests, private runtime and setup credentials.

### Corrected failures, not skipped tests

Initial one-page testing exposed a server-specific deleted-path 404 assumption. PHP 8.4 development-server parent-index fallback is accepted only when it is the real public homepage; no setup form or completion may return. Both removed URLs are tested with GET/POST and the database, credentials and locks must remain unchanged. Retained installer 410 and actual Apache access assertions remain strict.

The loopback TLS fixture initially closed without complete HTTP framing; adding actual Content-Length fixed the fixture without relaxing transport error/TLS checks. Run **38558812967** passed the installer, cPanel, database and earlier browser suites but failed a timing-sensitive media seek-value check. Playback can advance before the next timeupdate. The corrected runtime paints clock/ARIA on actual metadata/duration/time/empty events; tests wait for observable states and verify the real eight-second recording. No currentTime/duration spoof, CSP bypass or skipped failure was used.

### Local versus real provider

Local PHP/JavaScript/Python syntax, 498 pure assertions and 12 packaging tests passed separately. PHP syntax covered 203 PHP/INC files. Real SQL/cURL/GD/ZIP/browser/Apache evidence comes from Actions, not the local container. This establishes the exercised code paths, not compatibility of an untested real A2 account, delivered inbox mail or a genuine merchant transaction. Continue LAUNCH_CHECKLIST.md before launch.

## Historical 0.9.0-dev.7 baseline

Application **7c5582075b4b47c01e721c2c5e30d4f4a593acbb** passed full workflow **38525702729** and focused maintenance **38525702713**. The documentation-only descendant changed no tested runtime/assets/schema/tests. MEDIA_QUALITY_CHECKPOINT.md records source/artifact and render evidence. This historical run is not evidence for changed dev8 code.

The dev7 matrix executed **442 cumulative pure assertions**, including **44 media-quality checks**, **11 packaging tests**, SQL/HTTP root/subfolder workflows on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4, **twelve Chromium workflows**, and actual Apache PHP 8.4 HTTPS access-rule checks. Chromium runs in the PHP 8.4 job, intentionally not duplicated in the PHP 8.2 job.

Media SQL/HTTP tests exercise actual resized bytes and alpha, preserved originals, authorization before cache/304, bounded widths, representation-specific ETag/HEAD/If-Range, original downloads, busy generation locks, damaged cache regeneration, host-denied writes, symlink refusal, original disappearance, retention and low-memory fallback. Existing payment/mail/backup/update/security/consent tests remain in the matrix.

The media browser workflow covers 1440/390/320 px: real responsive candidate selection and lower bytes, intrinsic aspect ratio, first-party asset budget, reduced motion/reflow, keyboard gallery open/close/focus, true audio progression and queue boundaries, pressed/seek state, narrow-player bounds, focus above fixed player, close-focus return, server validation feedback and ordinary admin Escape. Targeted evidence is not full WCAG conformance.

## Historical fixture and visual evidence

The generated 1800×1200 RGBA fixture is not band artwork. Its re-encoded original measured 412,565 bytes and 640 px copy 21,306 bytes: 94.8% fewer for that fixture. Desktop selected 960 px measuring 64,820 bytes. The fixture page stayed below 130,000 bytes of first-party CSS/JavaScript. These are not field LCP/INP/CLS or a claim about actual band photographs.

Application 238fa431193a86fb00e867f96a445ecafc19f46b passed 38524917377/38524917358. Nine new renders were inspected from 99-PNG artifact 11875780837. Only subsequent application change was owner-roadmap wording, separately included in the full run. Source artifact 11875622065 was digest-checked and runtime/assets/schema/tests matched local files. Local pure/package/lint and isolated Chromium component experiments also ran; local PHP did not run real database/GD transports.

## Historical corrected failures

A textured fixture initially exceeded default uploads; compressible texture preserved dimensions/alpha while fitting the unchanged limit. Intrinsic HTML dimensions/CSS width exposed image stretching, corrected with automatic height and class-token hints. A 320 px player title overlapped controls, corrected with constrained flex and two rows. Native Escape returns focus before a queued close event; tests wait for actual effects without CSP bypass, skipped assertions or fabricated passes.

## Earlier verified checkpoints

Dev6 af43d153 passed 38518823266/38518823277; exact-main 4f6005f7 passed 38521278470. Dev5 exact-main 11a5038 passed 38514355067. Earlier exact evidence remains in DISCOVERY_CHECKPOINT, CONTENT_HISTORY_CHECKPOINT, MAINTENANCE_CHECKPOINT, BACKUP_CHECKPOINT and Git history.

## Explicit limits

No genuine merchant transaction, A2/cPanel account acceptance, receiving-mailbox result, arbitrary host aliases/ACL/open_basedir/OPcache/proxy acceptance, complete penetration/accessibility conformance, field Core Web Vitals, production artwork/content approval, automatic cross-schema upgrade/cross-host disaster restoration or accepted v1.0 is established by synthetic tests. A source-artifact success is not application verification. Follow LAUNCH_CHECKLIST before cutover.
