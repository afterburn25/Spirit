# Changelog

## 1.0.0-rc.6 — authoring safety candidate

Add opt-in unsaved-change warnings to content, product and logo editors, without autosave, browser draft storage or network calls. Restore clean state when edits are reverted; keep rejected responses unsaved; allow legitimate submissions and saved-preview popups. Browser limitations remain explicit.

Correct a product editor bug that redisplayed old stored fields after rejected saves. Redisplay only escaped authored fields, preserve the original stale revision and do not mutate publication, inventory or customer records. Native no-JavaScript error correction remains usable. Local230 syntax/812 pure/8 JS/27 isolated helper/12 package checks passed; exact new CI pending.

## Prior cumulative releases

RC5 added owner logo drafts/preview/explicit publication/withdrawal and stale-form protection across backup restore. Verified sourceb16e9780 and final-main1101be37 evidence are preserved in Git at1101be37414701ae3488073e6cddee9348c1cae8.

RC4 hardened native pagination and private-history tests. RC3 connected eight existing Page artwork/copy placements to real-layout previews. RC2 added grouped remembered administration navigation. RC1 added native public access and policy links; earlier releases implemented the complete application, installer, commerce and guarded maintenance foundations. Historical detailed changelogs remain in Git and docs/history; no previous delivered archive is overwritten.

No approved production content is invented or published, and no hosting or provider setting changes here. Final1.0 acceptance remains outstanding.
