# Roadmap — 1.2.0-rc.1 candidate (unreleased)

## Current 1.2.0 milestone and release criteria

The 1.2.0-rc.1 development branch implements owner/staff editing of public-page text and media with safe designed fallbacks, Band profiles in portrait-left/bio-right desktop rows and mobile stacks, add/edit/draft/publish/sort/soft-remove, one-time confirmed legacy lineup import, media previews, and a searchable permission-controlled Admin Help Center. Main remains the authoritative final 1.1.0 production source; PR #39 stays draft.

**Verified October 28, 2026:** All **11 of 11 GitHub workflows passed** at commit `41d2dcf1e08d9408d4f720e827ba943175a3200e`. Both PHP 8.2/MariaDB and PHP 8.4/MySQL passed **737 exact-package upgrade/recovery assertions each**, including historical zero, draft, published, unpublished and soft-deleted member records. Package archive integrity, digest and no-nested-ZIP checks passed. Synthetic Chromium Help Center screenshots at 1440, 820, 390 and 320px were reviewed; the prior stretched phone search field was fixed, and sampled Band/editor layouts showed no obvious clipping. This is not human accessibility, physical-device or original-delivery ZIP verification.

**New corrective work, awaiting its own CI:** Address issue #38's remaining member portrait-description and optional HTTPS-link fields, render them safely on Band rows/member detail, and stop hardcoded former member names from appearing in default Band hero and metadata after owner changes the roster. Preserve the original historical fallback intro on untouched/draft-only installations, and preserve explicit owner-edited Page text. Update the Help Center member workflow and focused validation/browser regression tests. No database migration is required; the new member fields are stored in the existing content JSON.

**Open gates:** All 11 checks and both upgrade matrices must pass again on the new source head; inspect new artifacts; obtain human screen-reader, keyboard/focus and physical-device UI acceptance; finalize release VERSION, immutable archive digest and docs; and receive explicit owner authorization before any merge, tag or production deployment. Avoid redundant CI reruns, duplicated ZIPs or uncontrolled changes.

## Historical final 1.1.0 roadmap

| Area | Final software status | Operational follow-up |
|---|---|---|
| Grouped directories | Ten deployment roots, root images and original URLs retained | Same-root transition for pre-grouped installations |
| Short-page footer | Viewport-filling layout with content-sized footer; long-page flow retained | Physical-device review during deployment |
| Player clearance | Measured height restored; installed 320px keyboard regression passed | Preserve the measured inset in future styles |
| Music filters | rc.11 wrapping retained | Existing responsive CI stays active |
| Data and recovery | Unchanged schema and recovery boundary; exact-package tests | Preserve backups/receipt and verify host permissions |
| Approved design | Animated 3D logo, stage/smoke, transparent surfaces and compact credit retained | Review actual package screenshots; no redesign |
| Analytics | Graphs, Visitor Explorer and Overview reports complete | Retain owner access and measurement definitions |
| CMS, media, music, shows, booking and accounts | Functionality and accepted checkpoints retained | Add only owner-approved optional content |
| Commerce | Optional and disabled; venue ticket links remain external | Merchant/catalog/policy acceptance before activation |
| Final release | 1.1.0 promoted from rc.12 runtime through PR 37 | Publication does not update the live host |

## Evidence and continuation

TEST_RESULTS.md separates historical checks, exact package tests and current
release verification. acceptance/FINAL-1.1.0.md records the accepted scope.
The companion receipt identifies the delivered ZIP and exact CI/publication
results. Do not turn owner-confirmed installation or communication checks into
new blockers. No mandatory feature remains in this 1.1 scope.

## Existing installations

Read LAYOUT_TRANSITION.md. Grouped rc.10/rc.11/rc.12 use guarded same-layout
updates. An rc.8-or-earlier updater must refuse the changed foundation; the
unavailable earlier rc.9 needs comparison with its actual package. Do not
reinstall, replace the database, relocate private runtime or delete unknown
custom files to force the transition.

## Planned follow-up — 1.2.0 content administration

Track implementation and acceptance in [issue #38](https://github.com/afterburn25/Spirit/issues/38). Add owner-editable page/section text and imagery throughout the public site using the existing CMS/media permissions and draft/publish infrastructure. Provide a dedicated admin Band Members manager for add/edit/delete, image/biography editing, publishing and persistent ordering. Public `/band/` members must be vertically stacked, with portrait left and name/role/bio right on desktop and image-first stacking on mobile. Preserve the approved 1.1.0 design, legacy content, installations, media and upgrade/recovery guarantees. This is planned work, not shipped with 1.1.0.

## Admin Help Center — 1.2.0 in progress

- Add **Help & guidance → Help center** with owner/staff-accessible topic navigation and short task-oriented instructions. Implemented on development branch, not yet released.
- Help content must describe the actual routes and workflows, distinguish unpublished and optional features, and remain consistent with permissions.
- **Release rule:** Every feature addition, rename, workflow change, or admin deletion requires updating the corresponding HelpCatalog guide, reviewing links/permissions and checking responsive and keyboard usability in the same PR. Help review is mandatory before merge.
- Implemented on the development branch: categorized searchable articles, contextual Help shortcuts, troubleshooting, permission-filtered content, navigation coverage checks, and responsive cards. No new PHP database migration; existing CMS JSON records gain optional page fields.\n- Still required before 1.2.0 release: exact-head Actions, PHP syntax and focused tests, desktop/mobile screenshots, accessibility checks, same-root upgrade/recovery proof, owner signoff and a version bump. Screenshots or live-host behavior must not be invented.\n- The new image picker previews selected files in the editor without auto-publishing them. The public Band page renders published CMS records in stacked portrait-and-bio rows. Confirmed legacy name/role placeholders remain only while no member has ever been published; deletion or unpublishing does not resurrect them.

### 1.2.0 CI regression triage — October 27, 2026

Earlier exact-head checks at `e8800f6` exposed outdated browser-test expectations after the eighth (Help) navigation group was added, and two preview-test heading mismatches (HTML-escaped `&amp;` on Media, unbroken Booking title). Tests now assert eight owner groups/38 total links and Help visibility for permitted staff, and match the actual safe rendered headings. The one intermittent PHP 8.2 Chromium no-JavaScript save-click stability failure is exercised via native keyboard activation (Enter) without bypassing form validation. These are test corrections; the HelpCatalog workflow and UI behavior remain unchanged. Do not call the release green until the **new exact-head** GitHub Actions jobs, section-page integration, browser screenshots, safe 1.1.0 upgrade/recovery and owner review have all completed.

**Further CI finding (October 27):** `PublicSite::render()` uses dedicated `app/views/public/booking.php`, not the legacy Booking branch in `sections.php`. That active template had ignored editable eyebrow, headline, button and optional details heading. The development fix uses the existing Page record with escaped text, HTTPS-only external links, the unchanged inquiry form/disabled draft form and the original line-broken default title. Real preview/publication tests and Booking Help guidance were added. The browser history assertion now matches the extended Page schema's 19 comparison fields; no-JavaScript form navigation and analytics cohort waits are synchronized. All require new exact-head CI verification before a version bump, upgrade/recovery review and owner acceptance.

**Exact-head CI follow-up (October 27):** At commit `716eeb1`, ten of eleven workflows passed, including live browser navigation, storefront across Chromium/Firefox/WebKit, Help Center, analytics, authoring, package and release gates. Website/installer failed in both PHP/DB configurations on a stale HTML attribute-order assertion: the image picker now includes `data-media-src` between the selected image's `value` and `selected` attributes. The preservation regression still checks the selected old image after 1001 newer uploads; it now accepts valid option attributes in between. Do not extrapolate earlier green results to a newly committed head; rerun exact-head CI and complete upgrade/recovery and owner acceptance.
