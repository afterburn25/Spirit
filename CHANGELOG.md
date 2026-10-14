# Changelog

## 0.9.0-dev.8 — verified one-page A2/cPanel installation

Remove the user-rejected package setup-code/claim gate and PRIVATE-SETUP receipt. Checks, URL/database/owner configuration, validation feedback and completion now share one page. Preserve CSRF, private writes, prefix/concurrency/interruption recovery, permanent locks and verified cleanup. Completion offers owner sign-in, not another setup step. Native form works without JavaScript; secrets are not reflected after correction/success. Required image-extension checks align with cumulative application requirements.

Add optional cPanel API database/user creation for **A2 Hosting only; no 20i integration**. Generate a strong DB password, honor reported prefix/limits, grant database-scoped privileges and verify PDO. Panel token is request-only; transport uses validated/pinned TLS destinations, no redirects/proxies and bounded responses/time. Private progress preserves confirmed steps after rejection/migration failures. Uncertain outcomes pause rather than duplicate, adopt, reset passwords or delete unrelated resources. See CPANEL_INSTALLER.md.

Add 56 cPanel units, actual loopback TLS/POST tests and guarded dedicated-SQL boundary scenarios, plus same-page cPanel-mode browser validation/non-reflection at 1440/390/320 px. Expanded installer tests also cover no-JavaScript, cleanup denial/replay and existing-data recovery. Correct a PHP development-server missing-directory expectation without changing production access rules. Packaging emits one cumulative ZIP, excludes legacy keys/stale manifests and passes 12 tests. Full workflow **38560767553** and focused **38560767558** passed for application **b9d32849e9b556dbc2b6d4f2adb7526703b46562**; eleven installer renders inspected. No actual A2 account acceptance is implied.

Corrected a playback accessibility timing regression discovered by the complete matrix: duration/ARIA now updates on native metadata events, and browser tests wait for real observable media state. This final documentation-only descendant changes no runtime, assets, schema or tests.

## 0.9.0-dev.7 — responsive media and keyboard quality

Application 7c5582075b4b47c01e721c2c5e30d4f4a593acbb passed full 38525702729 and focused 38525702713. Both databases, twelve Chromium workflows and actual Apache HTTPS included. Documentation-only descendant changed no tested runtime/assets/schema/tests. See MEDIA_QUALITY_CHECKPOINT.md.

Four fixed responsive candidates, alpha-safe private cache, original downloads, authorization before cache, memory/nonblocking-lock fallback, damaged-cache regeneration and bounded derivative retention. Original media unchanged. Intrinsic image/focal-class fixes, named gallery dialogs, focus/source cleanup, player queue/pressed/seek state, fixed-player clearance/close focus, server-error focus, phone typography and forced colors. Very narrow player uses two rows. Owner roadmap now reflects cumulative implemented modules and real acceptance gates.

44 new pure checks; 442 cumulative pure assertions and 11 packaging tests; root/subfolder alpha/privacy/host failures; 1440/390/320 px browser workflow. Nine renders inspected from preceding 99-image artifact. Generated fixture 640 px delivery 94.8% smaller; no production bandwidth/field-performance claim.

## 0.9.0-dev.6 — published discovery

Application af43d153 passed 38518823266/38518823277; exact-main 4f6005f7 passed 38521278470. Published metadata/sharing/canonical URLs, indexing, public search, sitemap and former-published-slug redirects; navigation retains audio. See DISCOVERY_CHECKPOINT.md.

## 0.9.0-dev.5 — content history and large-library controls

Application 9546b7c passed 38513423793/38513423736; exact-main 11a5038 passed 38514355067. Search/filter/sort/pagination, comparison and append-only private restoration preserve publication/media/later history. See CONTENT_HISTORY_CHECKPOINT.md.

## 0.9.0-dev.1 through dev.4

Staff permissions/invitations/audit; encrypted backups/guarded same-instance restore; same-schema ZIP upgrades/pinned independent recovery; conservative technical retention. Exact results in MAINTENANCE_CHECKPOINT, BACKUP_CHECKPOINT and Git history.

## 0.8.0-dev.1 and earlier

Token-only checkout/orders/fulfillment, catalog/accounts, booking/mail/newsletter, public CMS/music/shows and browser installer remain cumulative. Exact-main .8 eb995810 passed 38493945223. Payment SDK/provider boundaries are synthetic in CI. No accepted v1.0 or live deployment claimed.
