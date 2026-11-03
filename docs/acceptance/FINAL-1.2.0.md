# Spiritual Gorillaz 1.2.0 — owner acceptance record (PENDING)

This checklist is for the unreleased `1.2.0-rc.1` candidate on PR #39. This is **not** an assertion that manual checks have passed and is **not** authorization to merge, tag, distribute, or deploy. Record tester, date, device/browser/screen reader, evidence link, pass/fail, and any issue for every item. Leave unchecked items as **not performed**.

## Candidate identity and automated baseline
- PR: https://github.com/afterburn25/Spirit/pull/39
- Candidate source head last checked: `59d4b2a89afb3483a907b4eb922644222e5dc25e`
- Candidate version: `1.2.0-rc.1`; final `1.2.0` has **not** been built or approved.
- Last verified exact-head automation: 11/11 workflows passed; PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4 exact-package upgrade/recovery tests passed 737 assertions each.
- CI review ZIP SHA-256 (not an approved release): `ab75bb58b3f00da3f1298ae576c96b015a1511a0114fd82589b5de0abd16385e`.
- Re-evaluate everything above against the **new exact source head** if commits are added. Do not infer that earlier runs certify later commits.

## Human acceptance — record evidence; do not pre-check
| Area | Required manual verification | Result | Tester, device and evidence |
|---|---|---|---|
| Public design | Home, Music, Shows, Band, Media, Press, Booking, Merch; approved logo/smoke, player and compact footer; desktop, tablet, narrow phone; no horizontal overflow | NOT PERFORMED | |
| Band pages | Multiple portrait-left/bio-right rows on desktop, image-first mobile; correct published lineup/order, long text, missing portrait, safe external links | NOT PERFORMED | |
| Band admin | Create/edit, portrait alt text, save draft, preview, publish/unpublish, reorder via keyboard, soft-delete confirmation; no resurfacing of deleted members | NOT PERFORMED | |
| Page editor | Change headline, body and media on representative pages; draft-vs-published, reload persistence, blank override fallback and no-JavaScript save where supported | NOT PERFORMED | |
| Help Center | Owner/staff role filtering, search and guide links, contextual shortcuts, mobile 320/390px controls, keyboard access and focus visibility | NOT PERFORMED | |
| Screen reader | NVDA + Firefox or Chrome on Windows, or VoiceOver + Safari on Apple device; headings, landmarks, input labels, validation feedback, image alternatives, status announcements | NOT PERFORMED | |
| Keyboard-only | Tab order, visible focus, Enter/Space activation, dialogs, sorting, media picker, save/publish and escape paths without a mouse | NOT PERFORMED | |
| Owner visual review | Approved identity and representative screenshots accepted by owner, or specific issues filed | NOT PERFORMED | |

Use a disposable staging installation and nonproduction accounts/test data only. Do not touch the live website, payment settings or production credentials.

## Before requesting merge approval
1. Resolve any acceptance defects on `work/1.2.0-content-band-layout`, keeping Help Center and roadmap current with changed workflows.
2. Decide whether the intended merge should carry the candidate suffix or a final `1.2.0` version; if final, update VERSION, package metadata, changelog, README and release notes together.
3. Verify the final exact-head CI, actual 1.1.0-to-candidate package upgrade and independent rollback, root/subfolder installs, and all package hashes. Confirm no nested ZIPs.
4. Produce and record an immutable final artifact ID, checksum, source commit and publication decision. Keep prior review artifacts distinct.
5. Obtain explicit **owner acceptance and permission to merge**. Only then mark PR #39 ready, merge, tag or distribute, per separately granted authorization. Deployment to the live host needs separate approval.

## Approval record
- Human testing complete: **NO**
- Owner design/accessibility acceptance: **NOT GIVEN**
- Final package reconciled and verified: **NO**
- Permission to merge PR #39: **NOT GIVEN**
- Permission to deploy to production: **NOT GIVEN**
