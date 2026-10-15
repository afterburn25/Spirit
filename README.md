# Spiritual Gorillaz — 1.0.0-rc.6

Source of truth: **afterburn25/Spirit**, **main**. Preserve the approved navy/black/gold/blue design, external venue ticket links and remembered collapsible administration menu. Read AGENTS.md, PROJECT_STATUS.md and SESSION_HANDOFF.md before changes.

## Authoring safeguards

Content, product-description and logo editors now show an unsaved-changes notice and request the browser's departure confirmation when supported. Restoring the original values clears an ordinary edit warning; a rejected server submission remains unsaved. Normal saves and saved-preview popups remain usable. This is not autosave and does not store draft contents in browser storage or transmit them in the background.

Rejected product saves now keep submitted editable fields for correction instead of redisplaying older saved values. Stale submissions retain their original revision so they cannot silently overwrite a newer draft. Server authorization, CSRF, publication and inventory rules remain unchanged. Product correction also works without JavaScript; departure warnings require JavaScript and browser support.

Tested **bb9d850ac4653ce594837a503f7caffd336dc369** passed all five required verification workflows. The documentation-only finalization leaves application code, tests, workflows and VERSION unchanged. See docs/TEST_RESULTS.md for exact runs, observed browser limitations and source integrity; later final-main results belong in the release report and PR19 after completion.

## Preserved application and deployment

All prior content/media/music/shows/booking/newsletter/store/accounts/orders/staff/branding/backup/upgrade/diagnostic features remain cumulative. No new master artwork or real content is published by this package. The public design, original player/navigation, remembered administration menu, installer, six migrations and pinned recovery files are unchanged.

A2/cPanel only: one manual browser installation page with existing database and owner details; no setup code, hosting API token, 20i or automatic database/user creation. Private write probes, permanent locks and safe installer cleanup remain. PHP cURL and ZIP notices still describe actual host capabilities; this release does not enable missing extensions.

Existing sites upgrade with backups, never reinstall or create a replacement database. Preserve original private configuration, encryption secret, database, media and offline recovery receipts. Same-schema upgrades and same-instance restoration retain their documented limits. Build a development checkout using `python3 tools/package.py --output ./dist`; no host-side Python/Node/Composer build, SSH or root is required. Read docs/INSTALLATION.md and docs/UPGRADES.md.

Approved production artwork/content/policies, actual A2/mailbox/merchant/recovery/device acceptance and owner approval remain before final 1.0. Browser warnings are not guaranteed after mobile app termination, tab closure or a crash; save regularly.
