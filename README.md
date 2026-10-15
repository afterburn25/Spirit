# Spiritual Gorillaz — 1.0.0-rc.6 candidate

Source of truth: **afterburn25/Spirit**, **main**. Preserve the approved navy/black/gold/blue design, external venue ticket links, and remembered collapsible administration menu. Read AGENTS.md, PROJECT_STATUS.md and SESSION_HANDOFF.md before changes.

## Authoring safeguards

Content, product-description and logo editors now warn about unsaved changes before supported browser navigation or refresh. The warning is not autosave: draft contents are not stored in the browser or sent anywhere. Product errors also retain submitted editable fields for correction, rather than replacing them with older saved values. Stale submissions keep their original revision so they cannot silently overwrite a newer draft.

Exact candidate CI is pending. Local230 PHP/inc syntax files,812 pure assertions,8 JavaScript syntax checks,27 isolated Chromium lifecycle checks and12 packaging tests passed. See docs/EDITING_SAFETY.md and TEST_RESULTS.md. Earlier RC5 verification is not proof for changed runtime.

## Preserved application and deployment

All prior content/media/music/shows/booking/newsletter/store/accounts/orders/staff/branding/backup/upgrade/diagnostic features remain. No master artwork or real content is invented or published by this update. No runtime changes to payment logic, storage, recovery, public navigation/player or the approved public design.

A2/cPanel only: one manual browser installation page with existing database and owner details; no setup code, hosting token,20i or automatic database/user creation. Private write probes, permanent locks and safe installer cleanup remain. Missing PHP cURL/ZIP still needs the actual host's configuration. PHP cannot bypass hosting ownership controls.

Existing sites upgrade with backups, never reinstall or create a replacement database. Preserve original private config/encryption secret/database/media and offline recovery receipts. Same-schema upgrade and same-instance restoration limits remain. Build the cumulative package using `python3 tools/package.py --output ./dist`; no host-side Python/Node/Composer build, SSH or root is required. Read docs/INSTALLATION.md and docs/UPGRADES.md.

Approved production artwork/content/policies, actual A2/mailbox/merchant/recovery/device testing and owner approval remain before final1.0. Browser warnings cannot guarantee recovery after mobile app termination, tab closure or a crash.
