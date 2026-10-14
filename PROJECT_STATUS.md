# Project status — 0.9.0-dev.11

Updated 2026-10-14. Verified application **d699fd6e9f42a8fc2d0329fd07778c4a2718f7a8**, tree **df20fbeb8c1d552755b3fd8c4b7bc10e766e1c0c**. Full workflow **38589219303** and focused maintenance **38589219399** passed. The final documentation-only descendant leaves runtime, assets, schema, workflows and tests unchanged. Inspect live main before further work.

## Landed in this cumulative build

- One owner-only **Launch checks** dashboard with 43 observations/review tasks. It distinguishes configured services and published records from actual launch acceptance; six external review gates never become approved automatically.
- Current PHP extension availability, file-upload/whole-request/memory ceilings, upload headroom and private-configuration observations. No phpinfo dump or server-setting modification.
- Published-only content and visible-category merchandise counts; contact, account, sandbox and operating-mode settings; unresolved email/payment counts. Production selected and enabled warns about live-charge risk even when checkout is otherwise unavailable. Viewing/exporting never processes work or contacts a provider.
- Fixed-key private support export excludes hostnames, paths, credentials, logs, personal details and content text. Both admin/launch and the former preview admin/readiness URL use the same active-owner, method and CSRF checks.
- Upload limits are shown on media, backup import and ZIP upgrade pages. A whole request discarded by PHP returns a usable HTTP 413 explanation with no requested action, rather than an unrelated form-expiry message. Ordinary forms still require CSRF and permissions.
- Keyboard export focus no longer overlaps its timestamp. The page retains the approved dark-and-gold administration design and tested narrow-screen reflow.

The interrupted work was not lost: saved dev11 branch2cb333b7 passed its own full/focused runs before this session. It reconciled a saved dev10 branch and a separately delivered local dev10 preview. The previous response's claim that no diagnostics were committed was not an accurate description of all repository branches. This build is their consolidated successor, not a replacement ZIP reusing the dev10 version.

## Verification

211 PHP/inc files passed local syntax checks. All 544 pure assertions (480 retained +64 launch/hosting) passed locally under an unprivileged account; all 12 packaging tests passed. The exact full Actions run tested PHP8.2/MariaDB11.4, PHP8.4/MySQL8.4, all thirteen Chromium workflows and actual Apache HTTPS access rules. New diagnostics/alias/permission/redaction/no-mutation tests cover root/subfolder and real no-cURL/no-ZIP child environments. Oversized media/backup/upgrade requests are tested against real PHP size limits.

The final launch browser journey covers 1440px,390px and320px, real keyboard JSON download, all43 rows, the preview alias, secret omission, whole-request rejection, fresh upload form and keyboard error focus. Current screenshots were inspected; source and delivery hashes are recorded in the release report. See TEST_RESULTS.md for exact jobs/artifact.

## Preserved and still outstanding

Manual database setup on A2/cPanel only; no token, setup code, automatic creation or20i. One-page installer, private writes, locks and self-deletion remain. Prior dev9 and delivered dev10-preview payload paths, six immutable migrations and five pinned recovery/access files remain compatible. Keep original config/encryption secret/database/media and separate recovery keys.

Actual A2 permissions/aliases/PHP settings/deletion and ZIP enablement, receiving mailbox, genuine merchant sandbox, approved high-resolution artwork/content, populated-site accessibility/performance/security and owner acceptance remain. No production service or hosting setting has been changed. Same-instance guarded restoration and same-schema ZIP upgrades retain their documented limits. No accepted v1.0.
