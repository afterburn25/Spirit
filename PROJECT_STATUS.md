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

All **11/11 GitHub PR workflows passed** at exact source head `6196f25b1dee5ca0e7dc98b9c70212c17300aa40`, including the new member alt text, HTTPS member links, Band hero and SEO roster correction. The pinned 1.1.0-source-to-1.2.0-rc.1 package upgrades and independent recovery passed **737 assertions on each** PHP/database matrix. Synthetic screenshots and package integrity remain machine evidence, not human device/accessibility acceptance.

The following code-only correction addresses another roster consistency gap: the Press story still named the original lineup after a member was published or removed, and an editor who published a headline-only Page could inadvertently blank the approved Home, Band or Press introductory body. The renderer now uses the existing blank-aware Page helper and the legacy Press names only while the old lineup fallback is legitimate. Browser and unit assertions were added. **This new commit is not verified by the previous head's green CI.** Help guidance is updated; no schema or host state changes.

## Remaining blockers

- Verify all 11 GitHub Actions workflows and both exact-package upgrade/recovery matrices on the new source head. Never attribute previous passing jobs to this change.
- Inspect the new Band/Press/Home and editor browser artifacts, confirm no regressions on desktop/mobile and test keyboard/focus behavior on actual devices and with assistive technology.
- Preserve separate provenance for the source-reproduced final 1.1.0 baseline; it is not asserted byte-identical to the historical customer-delivered ZIP.
- Before final 1.2.0, reconcile VERSION, release docs, immutable archive SHA-256 and owner acceptance. Keep PR #39 draft; no merge, tag or production deployment without explicit approval.

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
