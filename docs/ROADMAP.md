# Roadmap — final 1.1.0

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
- Implemented on the development branch: categorized searchable articles, contextual Help shortcuts, troubleshooting, permission-filtered content, navigation coverage checks, and responsive cards. No new PHP database migration; existing CMS JSON records gain optional page fields.\n- Still required before 1.2.0 release: exact-head Actions, PHP syntax and focused tests, desktop/mobile screenshots, accessibility checks, same-root upgrade/recovery proof, owner signoff and a version bump. Screenshots or live-host behavior must not be invented.\n- The new image picker previews selected files in the editor without auto-publishing them. The public Band page renders only published CMS records in stacked portrait-and-bio rows; confirmed fallback member cards have been removed.
