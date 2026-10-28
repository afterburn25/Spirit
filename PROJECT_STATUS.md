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
Its own exact-head results must be observed before marking that verification
complete. Source reproduction is not proof of byte-identical original delivery.

## Remaining blockers

- Verify every GitHub Actions job and the exact-package 1.1.0-to-1.2.0-rc.1
  root, subfolder and interruption/rollback journeys at this candidate head.
- Inspect actual screenshots of Band pages, Help Center and content editing at
  desktop, tablet and mobile widths, including keyboard labels, focus and
  screen-reader interactions. Synthetic CI screenshots are evidence of test
  rendering, not owner or physical-device acceptance.
- Verify zero/draft/published/unpublished/deleted historical Band profile
  upgrade states without resurrecting removed profiles or changing private
  owner/configuration/media/database state.
- Reconcile final version, package integrity and documentation; obtain owner's
  explicit acceptance before promotion of a new final immutable 1.2.0 ZIP.
- Keep PR #39 draft, never merge to main or change live hosting before gates.

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
