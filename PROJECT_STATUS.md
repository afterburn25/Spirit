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

All 11/11 PR workflows passed at `4ae17b0d284a8d2fa60250ba9c6eba0b38e51bda`, covering the Press roster and blank Home/Band/Press introduction fixes. Both PHP/database matrices passed 737 actual ZIP upgrade/recovery assertions each. The downloaded `1.2.0-rc.1` archive SHA-256 is `4d02c4cc6d265a0a1250b94000b2dca87e0a58389c0ad7176000a4d88a5662e8`; published checksums, 451 manifest hashes, archive integrity and no-nested-ZIP checks match. The baseline final 1.1.0 ZIP was reproduced from pinned source, not asserted byte-identical to a customer's delivered ZIP.

**New branch-only responsive correction awaiting exact-head CI:** The real synthetic 390px staff screenshot showed `ADMINISTRATION` splitting mid-word when the contextual Help shortcut and user chip competed for header space. CSS now gives the breadcrumb a full mobile row, keeps Help/account separate with a 44px Help target and adds 320/390px browser assertions. An isolated Chromium CSS reproduction reduced the 390px label from a 25.2px two-line element to 12.6px on one line. This is not yet a passing full-site test. The Help guide and roadmap were updated; no production or schema change.

## Remaining blockers

- Re-run **all 11** GitHub workflows and both PHP/database upgrade/recovery matrices on the new header-fix commit. Earlier passing results belong to `4ae17b0`.
- Review 320/390px updated administration screenshots and conduct owner physical-device, keyboard focus and screen-reader acceptance.
- Keep the source-reproduced final 1.1.0 baseline distinct from the historical customer ZIP; reconcile final 1.2.0 VERSION, package digest and release documents.
- Keep PR #39 draft and unmerged; no production deployment or release without explicit owner approval.

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
