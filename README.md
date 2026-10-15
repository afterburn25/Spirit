# Spiritual Gorillaz — 1.0.0-rc.5 candidate

Repository **afterburn25/Spirit**, main authoritative. Preserve the selected black/deep-navy, gold and blue design. Read AGENTS.md, PROJECT_STATUS.md and SESSION_HANDOFF.md before changes.

## New: owner-managed site logo

Website settings now links to a real Site logo editor: private draft, actual-homepage preview, explicit approved publication and withdrawal. The public header and footer display the full contained image with their named home links. A small separate branding script updates the header after existing public navigation; the original player/navigation script is unchanged. No new database migration is needed.

Read docs/BRANDING.md. Current application code is implemented and local checks pass; exact new Actions verification is pending. This is not a final 1.0 release, a new visual design or a generated master-artwork package. The erroneous generated release graphics from this conversation are not screenshots or evidence and are excluded.

## Preserved application and deployment

All RC4 public/content/media/music/shows/booking/newsletter/store/accounts/orders/staff/backup/upgrade/diagnostic features and the remembered collapsible menu remain. Show tickets continue to the venue's external seller. No production content or service setting is changed by this source update.

A2/cPanel only: one manual browser installation page with existing database and owner details; no setup code, hosting token, 20i or automatic database/user creation. PHP cURL/ZIP remain separate optional-installation notices. Private write probes, permanent locks and safe installer cleanup remain. PHP cannot bypass hosting ownership controls.

Existing sites upgrade with backups, never reinstall or create replacement databases. Preserve original private configuration/encryption secret, database, media and offline recovery receipts. Same-schema browser upgrade and guarded same-instance restoration limits remain. Build the cumulative package with `python3 tools/package.py --output ./dist`; the host requires no Python, Node, Composer build, SSH or root. Read docs/INSTALLATION.md and docs/UPGRADES.md.

Production master artwork and genuine content/policies, actual A2/mailbox/merchant/recovery/device testing and owner approval remain before final 1.0.
