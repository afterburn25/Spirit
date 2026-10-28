# Roadmap — 1.2.0-rc.1 candidate (unreleased)

## Current 1.2.0 milestone and release criteria

The 1.2 development branch implements site-wide CMS section text/media controls
with designed fallbacks, responsive image-left/bio-right Band rows, permitted
profile additions/edits/sort/soft removal and explicit one-time legacy import,
media picker previews and a searchable permission-aware Admin Help Center.
The existing 1.1.0 production source remains on main; PR #39 stays draft.

**CI checkpoint, October 28, 2026 UTC:** all 11 workflows succeeded on
ae31a7a97bb9fbf9cde9599bb10bc52ba3eaa274, including both PHP/database
web/installer and grouped-layout matrices. That commit's package still claimed
1.1.0. The 1.2.0-rc.1 candidate includes a distinct final-1.1.0 source
reproduction baseline in grouped-layout CI, exercising real
root/subfolder/interrupted upgrades with independent rollback. The newly
versioned candidate's results must be checked separately.

Legacy member-state test coverage was added to the exact-package upgrade suite:
1.1.0 baseline zero-member/import, private-only draft and mixed
published/unpublished/soft-deleted histories. Each starts from real owner
content forms (with the deleted state modeled only in isolated test SQL),
checks the new Band public listing and Help-linked import flow after upgrade,
and preserves data through independent recovery. This is **new, unverified
source** until CI succeeds at the resulting commit; no host or owner acceptance
is asserted. Help Center task instructions require no change because this adds
verification only, not a user-facing workflow.

Remaining acceptance: exact-head CI on rc.1, visual review of true desktop,
tablet and mobile screenshots of the Band, Help Center and content editor;
keyboard/labels/focus and screen-reader review; explicit legacy member
zero/private/published/unpublished/deleted state coverage; confirmation of
owner/configuration/database/media preservation and recovery; final release
metadata/package hash reconciliation and explicit owner acceptance.
Do not conflate CI fixtures with physical-device/live-host validation or
merge/deploy the candidate.

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
