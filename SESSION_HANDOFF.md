# Active development handoff — Spiritual Gorillaz 1.2.0 (unreleased)

**Repository:** `afterburn25/Spirit`. **Authoritative release:** `main` at 1.1.0. **In-progress branch:** `work/1.2.0-content-band-layout`. **PR:** #39 (draft). **Issue:** #38.

The owner requested continued development of complete page image/text editing, vertical image-left/biography-right Band profiles, admin add/edit/soft-delete of members, and a commercial-quality Help Center updated with every new feature. Work is committed to the development branch, NOT merged into main and NOT deployed. Never represent queued workflow jobs as passing.

**Implemented in branch:** CMS-based published member row layout, responsive portrait/bio stylesheet, admin member removal with CSRF, revision and staff-permission checks; page headline/eyebrow/CTA fields with legacy design fallback; compatible public display across Home, Music, Shows, Band, Media, Booking, Press and Merchandise catalog; dedicated searchable Help Center, permission-aligned navigation, complete admin route coverage, contextual shortcuts, responsive UI, troubleshooting and admin media image previews. Existing content/publications remain governed by draft/publish controls.

**Tests/checks committed:** `app/tests/help_catalog_check.php`, `app/tests/page_controls_check.php`, `.github/workflows/help-center-quality.yml`; focused syntax, endpoint documentation, optional CTA safety, Band layout assertions, JS syntax. These must actually execute and pass. Existing GitHub Actions jobs may remain queued because of runner limits. Do not trigger repeated commits merely to add queued jobs.

**October 27 CI triage:** Commit `e8800f6` fixed the PHP unit test's old seven-category expectations; its exact-head checks passed 8/11 workflows but failed Website/installer on `section_pages_integration.py` Media heading escaping, storefront browsers on stale seven-group/eight-group counts, and one PHP 8.2 Chromium no-JavaScript draft submission click stability timeout. The following test-only adjustments correct expected escaped/default headings, eight owner categories and 38 authorized navigation links (including Overview), staff Help permissions, and native keyboard activation for no-JS submission. Verify the **new exact head**; prior runs are not release acceptance. No HelpCatalog feature change is needed.

**Legacy band transition:** `app/MemberOnboarding.php` preserves the previous confirmed Chad/Ron/Charlie fallback while only unpublished drafts exist, never resurrecting a previously published/unpublished/deleted member. `admin/content/import-lineup.php` provides a one-time explicit CSRF-protected, transactional import into published editable CMS records with empty biographies and no invented portraits. Test this against existing 1.1 data and empty/partial member databases before merge.\n\n**Release blockers:** check current CI on exact head, fix failures, verify visible browser screenshots and mobile/keyboard behavior, perform upgrade/recovery tests from 1.1.0 preserving database/private state/media, reconcile VERSION/docs/acceptance, complete owner-approved admin UX and merge only on green tested head.

**Documentation policy:** when a menu route/feature changes, update `app/HelpCatalog.php` in the same PR. Navigation permissions are the source of truth via `AdminNavigation::documentedDestinations()`. CI fails if nav destinations lack guides. PR template includes Help Center review.

---

# Resume — final 1.1.0

Repository: afterburn25/Spirit. Main is authoritative. PR 37 consolidates
work/1.1.0-analytics-and-navigation. Inspect live branch/PR state and exact
Actions before changing code; do not repeat completed release work or infer
deployment from a merge.

The owner asked to continue after the plan to verify exact upgrades, finish
1.1.0, merge PR 37 and clean superseded pull requests. This supersedes the older
rc.12 instruction to keep PR 37 draft. It does not authorize a live-host
reinstall or payment activation.

## Source and package continuity

Published rc.12 source: 46744207a0916ae2256d84fbaa312c9c4fc67768. All eleven
workflows succeeded. Final deployment changes are VERSION/docs only;
application PHP, CSS, JavaScript, artwork, eight migrations and the grouped
recovery foundation remain byte-identical to rc.12.

Original rc.11 ZIP SHA-256:
4abbc7d59dc6e1cd003950eecca628d73fa8fb3ecffb1466b73dd1147107168c.
Original rc.12 ZIP SHA-256:
f65a5db2e5b226c54409d909b390f174dcc6e0c2e3dc1408f0b0a06e26a0e8ec.
The rc.10 baseline was reproduced from published source
5703ca41a13a6ff01a3b7807287abdeb72c151a5 using its original packager and pinned
Pillow. It is not represented as recovered original delivery bytes.

Use app/tests/exact_package_upgrade.py with explicit --baseline, --target and
--output arguments in a non-root disposable SQL environment. It verifies real
owner upload/receipt/apply/confirmation and independent recovery while retaining
configuration, owner/database state and media. Read docs/TEST_RESULTS.md and
the companion receipt for observed runs and the final ZIP hash.

## Fixed constraints

Keep admin, app, assets, config, docs, images, includes, install, storage and
uploads at root. Do not restore old root clutter. Keep cumulative manifest
paths, original private state, public URLs and removed-installer state.
Read docs/LAYOUT_TRANSITION.md before changing a pre-grouped installation.
Never bypass recovery pins, replace its database or rerun fresh setup.

Retain the approved animated 3D gorilla, fixed stage/smoke, compact 75%-opaque
footer, editable copyright and fixed linked Cajun Web Pro credit. Short-page
footer flow and measured player clearance are completed work, not redesign
tasks. Public CSS/JS has only 84 bytes of budget headroom.

Accepted installation/mail/account checkpoints remain accepted. Commerce is
disabled; optional content and merchant activation are separate. No live-host,
new provider or physical-device result is claimed. Never overwrite delivered
versions, force-push, expose recovery receipts or commit private data.
