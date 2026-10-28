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

## Verified application-code baseline — October 28, 2026 UTC

Commit `c7f2d613252f8feb56a8a5339733005ec9f29d62` completed all **11 of 11** exact-source PR Actions workflows
successfully. The PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 actual-package
1.1.0-to-1.2.0-rc.1 upgrade and independent recovery matrices each passed
**737 assertions**. The source-reproduced final 1.1.0 baseline is not
asserted byte-identical to the historical customer-delivered 1.1.0 ZIP.

The candidate review ZIP at that source head has SHA-256
`4357afa2243557bfc1be98d665f69b890ed483f69338f1892ecffd683b535312`.
Its checksum file, **451 of 451** package manifest hashes, archive integrity
and absence of nested ZIPs were inspected. This CI candidate is not an
immutable, approved final release artifact.

Installed Chromium 320px and 390px regressions now pass for the corrected
mobile administration heading, contextual Help touch target and separated
account controls. Synthetic mobile Help and editor screenshots were inspected.
This is not physical-device, assistive-technology or owner acceptance. These
results belong to the cited application-code commit; any newer branch head
requires its own workflow verification before release.

## Remaining blockers

- Confirm all required GitHub checks on the exact branch head after any later
  documentation or code changes; do not assume the verified application-code
  baseline transfers to a new commit.
- Complete owner review on actual devices, keyboard/focus and screen-reader
  acceptance; synthetic Chromium evidence is not a substitute.
- Reconcile final 1.2.0 VERSION, release documents, original-delivery
  provenance where available and an immutable final-package digest.
- Obtain explicit owner approval before merging, tagging, distributing or
  deploying. Keep PR #39 draft and production/main unchanged.

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
