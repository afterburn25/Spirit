# Test evidence

## Current 0.9.0-dev.7

Application **7c5582075b4b47c01e721c2c5e30d4f4a593acbb** passed full workflow **38525702729** and focused maintenance **38525702713**. This documentation-only descendant changes no tested runtime, assets, schema or tests. MEDIA_QUALITY_CHECKPOINT.md records the source/artifact and render evidence.

The full matrix executes **442 cumulative pure assertions**, including **44 new media-quality checks**, **11 packaging tests**, existing and new real SQL/HTTP root/subfolder workflows on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4, **twelve Chromium workflows**, and actual Apache PHP 8.4 HTTPS access-rule checks. Chromium runs in the PHP 8.4 job; it is intentionally not duplicated in the PHP 8.2 job.

New SQL/HTTP tests exercise actual resized bytes and alpha, preserved originals, authorization before cache/304, bounded widths, representation-specific ETag/HEAD/If-Range, original downloads, a busy generation lock, damaged cache regeneration, host-denied cache writes, symlink refusal, original disappearance, retention and low-memory fallback. Existing payment/mail/backup/update/security/consent tests remain in the matrix.

The new browser workflow covers 1440px, 390px and 320px: real responsive candidate selection and lower image bytes, intrinsic aspect ratio, first-party asset budget, reduced motion/reflow, keyboard gallery open/close/focus, true audio progression and queue boundaries, pressed/seek state, narrow-player bounds, focus above the fixed player, close-focus return, server validation feedback and ordinary admin Escape behavior. This targeted evidence is not full WCAG conformance.

## Measured fixture evidence and visual review

The generated 1800×1200 RGBA fixture is not band artwork. Its uploaded/re-encoded original measured **412,565 bytes** and its 640px copy **21,306 bytes**: **94.8% fewer bytes** for that fixture. The desktop browser selected a 960px copy measuring **64,820 bytes** in the media pass. The fixture page remained below its 130,000-byte first-party CSS/JavaScript budget. These are synthetic-fixture measurements, not field LCP/INP/CLS or a claim about the real band's photographs.

The preceding application **238fa431193a86fb00e867f96a445ecafc19f46b** passed full **38524917377** and focused **38524917358**. Nine new renders were inspected from its **99-PNG artifact 11875780837**. The only subsequent application change is owner-roadmap wording, separately included in the current full run. Canonical source artifact **11875622065** was digest-checked and its runtime/assets/schema/tests matched local files. Local pure/package/lint checks and isolated native Chromium component experiments were also run; local PHP did not run the real database/GD transport suites.

## Corrected failures

The original textured fixture exceeded common PHP upload defaults; a compressible four-pixel texture retained dimensions/alpha while fitting the existing limit. Intrinsic HTML dimensions plus CSS width exposed image stretching, corrected with low-specificity automatic height and class-token layout hints. The 320px player title could overlap controls, corrected with a constrained flex child and two-row layout. Native Escape returns focus before the queued dialog-close event; assertions now wait for real handler effects rather than racing them. No CSP bypass, skipped assertion or fabricated pass was introduced.

## Previous verified checkpoints

Dev6 af43d153 passed 38518823266/38518823277, with exact-main 4f6005f7 passing 38521278470. Dev5 exact-main 11a5038 passed 38514355067. Earlier exact evidence remains in DISCOVERY_CHECKPOINT.md, CONTENT_HISTORY_CHECKPOINT.md, MAINTENANCE_CHECKPOINT.md, BACKUP_CHECKPOINT.md and Git history.

## Explicit limits

No genuine merchant transaction, actual inbox result, arbitrary shared-host aliases/ACL/open_basedir/OPcache/proxy acceptance, full penetration/accessibility conformance, field Core Web Vitals, approved production artwork/content, automatic cross-schema upgrade/cross-host disaster restoration or accepted v1.0 is established. A source-artifact success is not an application test. Follow LAUNCH_CHECKLIST.md before live cutover.
