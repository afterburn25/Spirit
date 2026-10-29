# Roadmap — 1.2.0-rc.1 candidate (unreleased)

## Current 1.2.0 milestone and release criteria

The 1.2.0-rc.1 development branch implements owner/staff editing of public-page text and media with safe designed fallbacks, Band profiles in portrait-left/bio-right desktop rows and mobile stacks, add/edit/draft/publish/sort/soft-remove, one-time confirmed legacy lineup import, media previews, and a searchable permission-controlled Admin Help Center. Main remains the authoritative final 1.1.0 production source; PR #39 stays draft.

**Verified application-code head — October 28, 2026:** At
`c7f2d613252f8feb56a8a5339733005ec9f29d62`, **all 11/11 PR workflows passed**, including the 320/390px
mobile admin-header and contextual Help browser regressions. Each PHP
8.2/MariaDB and PHP 8.4/MySQL exact-package upgrade/recovery matrix passed
**737 assertions**. The candidate ZIP SHA-256
`4357afa2243557bfc1be98d665f69b890ed483f69338f1892ecffd683b535312`
matched its published checksum and all 451 package-manifest hashes; archive
integrity and no-nested-ZIP checks passed. These are synthetic CI results,
not real-device, screen-reader or owner acceptance. The 1.1.0 upgrade baseline
was rebuilt from pinned source, not proven byte-identical to an old delivery.

**Mobile header correction verified:** The 390px staff screenshot previously
showed the administration title split mid-word by Help/account controls.
The corrected <=600px layout reserves a title row, keeps Help/account below,
and retains a 44px Help target; installed Chromium at 320px and 390px
passed root/subfolder tests. Existing mobile Help instructions describe the
shortcut. No database migration, public-design redesign or host change.

**Open gates:** Recheck exact-head Actions after any subsequent commit;
conduct owner physical-device, keyboard focus, screen-reader and visual
acceptance; reconcile final 1.2.0 version, immutable artifact/package digest
and release documents; and obtain explicit approval. Do not merge, tag,
distribute, deploy or manually dispatch redundant jobs.

## October 29, 2026 — exact-head layout-transition regression (verification pending)

The documentation-only head `f39acc6bfac0c469239ccece5cf0b1871914f5f0`
passed 10/11 PR workflows but failed the PHP 8.4/MySQL 8.4
grouped-layout matrix. After copying the grouped ZIP and immediately
pruning legacy views, the running synthetic PHP server requested the
previous `/views/header.php` path and returned 503 on `/admin/`.
The PHP 8.2/MariaDB matrix passed. This is evidence of a stale-worker
compatibility window, not proof that the new package omits its view files.

The staged test sequence is being corrected to retain the old manifest-owned
views during file replacement, refresh the disposable PHP worker while
preserving sessions/private state, verify the new owner dashboard, and only
then delete checksum-verified obsolete files. The upgrade Help Center and
operator transition document now warn against early legacy-view removal.
**Do not mark this issue resolved until the new exact-head matrix passes.**
No live-host change is part of this work.

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

## October 29 — deeper Help Center and contextual navigation (feature branch only)

Help Center articles now include detailed task procedures for every documented administration destination, covering publishing, band profiles, media, music, analytics, bookings, communication, commerce, security, website settings and recovery. The shared admin header passes its active screen key to the Help Center; the Help Center resolves that key through the allowlisted administration navigation and displays only matching guides permitted to the signed-in staff member. The sidebar still supports browsing all topics and search. No database migration or live-site change is required. Verify the help catalog validation, desktop/mobile links and full exact-head PR checks before acceptance. This remains an unreleased draft feature; physical-device, accessibility and owner sign-off gates remain open.

### Browser regression follow-up — expanded Help Center

The populated storefront browser matrix exposed an outdated assertion that demanded exactly five Band member articles despite the newly added detailed profile guide. The fixture now checks that the original five tasks remain covered and that the new complete-profile article is visible. This is a documentation/test expectation correction, not a production-site or commerce change. Exact-head browser CI must still pass before marking this regression resolved.

### Expanded contextual help navigation (feature branch)

Added an in-topic jump list with stable article anchors, a return-to-screen action for contextual admin help, and eight focused guides for website identity/settings, draft-to-publish behavior, homepage layout checks, member ordering, hero image sizing and safe bulk editing. The Help Center remains permission-filtered and uses only allowlisted admin destinations. Exact-head browser and Help Center checks are required after this source change. Do not treat these authoring improvements as owner acceptance or release authorization.

### Contextual Help permission hardening

The Help Center's `screen` query is now resolved through the requesting staff account's navigation permission before rendering screen-specific guides or the return-to-screen link. Previously a hand-written URL could display a return link to a known but unauthorized destination, even though the target endpoint still denied access. Owner, content editor, store manager, inactive-account and unknown-screen cases are covered by the Help Catalog check. This prevents misleading or unauthorized contextual navigation; it does not change any endpoint authorization or grant extra access. Exact-head CI must pass before release acceptance.
