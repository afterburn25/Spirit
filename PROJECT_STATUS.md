# Project status — Spiritual Gorillaz 1.2.0-rc.1 (unreleased)

## Current development scope

The authoritative final production source remains 1.1.0 on main.
Draft PR #39 contains the 1.2.0 page-content editor, portrait-and-biography
Band manager, one-time legacy-member import, media previews and a searchable
role-aware Help Center. This candidate is not deployed or merged.

## Verified source baseline and new evidence boundary

At commit ae31a7a97bb9fbf9cde9599bb10bc52ba3eaa274 (October 28, 2026 UTC),
all eleven PR workflows completed successfully, including PHP 8.2/MariaDB 11.4
and PHP 8.4/MySQL 8.4 website/installer checks, Help, package, grouped-layout
recovery, browser navigation and storefront checks. That head still packaged
itself as 1.1.0; those passing checks do **not** establish a successful
1.1.0-to-1.2.0 upgrade or candidate release acceptance.

Versioned review candidate 1.2.0-rc.1 adds a pinned final-1.1.0 source
reproduction baseline to the actual installer ZIP upgrade/recovery matrix.
At exact head 45a072c095cbbfc97eeaaca437a09de259645c15, all eleven PR
workflows succeeded on October 28, 2026 UTC. Both PHP 8.2/MariaDB 11.4
and PHP 8.4/MySQL 8.4 grouped-layout matrices passed 737 assertions each,
including the real ZIP upgrade, historical member fixtures and independent
recovery. Source reproduction does not prove byte identity with an earlier
delivered 1.1.0 archive. Tests added after that head need their own CI result.

## Current verified evidence — October 28, 2026 UTC

All **11 of 11 PR workflows passed** at source head `41d2dcf1e08d9408d4f720e827ba943175a3200e` (tested through PR merge ref `3140ac5c0f036bc9c9949edbbb730823c8aaf5cf`). The exact-package 1.1.0-to-1.2.0-rc.1 upgrade and independent recovery matrices passed **737 assertions on each** of PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4. Review package integrity and SHA-256 verification passed. Synthetic Chromium screenshot inspection covered Help topics and search at 1440/820/390/320px and Band/editor screens; no obvious clipping remained after the phone search control fix. This is not owner, assistive-technology or physical-device acceptance.

A subsequent source change adds editable per-member portrait descriptions and safe optional HTTPS website links, and removes stale default Band hero/SEO lineup names after the saved roster replaces the historical fallback. That change is **not verified by the above runs**; the new exact-head tests must finish before claiming it passed. Help Center member guidance is updated for the new workflow. No deployment or merge is authorized.

## Remaining blockers

- Reverify **all 11 GitHub workflows on the new feature-branch commit** covering member portrait descriptions, HTTPS links and the corrected historical roster text. Old-green source SHA must not be represented as current acceptance.
- Retain exact-package root/subfolder/interrupt/recovery and historical member-state evidence on both PHP/database matrices; re-run on the new head. Baseline 1.1.0 is reproduced from pinned source and is not claimed byte-identical to a previously delivered customer ZIP.
- The prior head's synthetic screenshots were reviewed; capture/review updated Band/Help/editor screenshots and conduct owner physical-device, keyboard focus and screen-reader acceptance. CI cannot provide human approval.
- Before final 1.2.0, reconcile final VERSION, status/handoff/changelog and immutable package SHA-256 with passing final-head CI and owner acceptance.
- Keep PR #39 draft; never merge to main or modify live hosting until the release is expressly accepted.

---

## Historical status — final 1.1.0

## Release scope

Final 1.1.0 consolidates the completed analytics/navigation development and
approved branding, background, directory and responsive-layout work through
PR 37. Main remains the authoritative release source.

The final promotion retains rc.12 application code byte for byte. It updates
VERSION and release/operating documentation and adds development-only exact
package upgrade/recovery verification. The ten deployment folders, public URLs,
eight migrations, private-storage identity and recovery pins stay intact.

Short pages fill the viewport with the compact footer at the bottom; long pages
keep it after their content. Music filter wrapping, newsletter spacing and
measured player clearance are retained. Public CSS/JavaScript remains 129,916
bytes against the unchanged 130,000-byte budget.

## Verification

All eleven GitHub workflows succeeded on published rc.12 source
46744207a0916ae2256d84fbaa312c9c4fc67768, including the populated storefront,
installer, editing, navigation, analytics, branding and grouped-layout checks.

Current local evidence, archive identities and the final verification procedure
are in docs/TEST_RESULTS.md. The companion verification receipt records the
frozen final ZIP and publication checks. Final-source Actions must succeed
before publication; rc.12 evidence alone does not establish final-commit CI.

## Historical 1.1.0 blockers

None for the completed 1.1.0 release; the open 1.2.0 candidate blockers are listed above.

## Operational follow-up and evidence boundaries

The owner's continuation authorizes completing this software release and
consolidating PR 37 into main. It does not claim a live-host installation,
physical-device review, provider test or payment activation.

Retain the owner-confirmed installation, administration, booking, newsletter,
account, mail-schedule configuration and unpaid-cancellation results in
docs/acceptance/CONSOLIDATED-FINAL-PASS.md. Do not restart those accepted checks.
Commerce remains intentionally disabled; merchant acceptance belongs to later
activation. Optional content may remain unpublished.

Use the documented update path and check actual host permissions, current
mail/cron execution and physical devices when deploying. No live host or
customer data was changed by this release session. Historical records remain
under docs/history.
