# Spiritual Gorillaz — 1.0.0-rc.7

This pass improves the existing public phone menu and native admin checkboxes, without a redesign or new business module. Tab can enter and leave the public disclosure freely; Escape returns focus, outside interaction dismisses it, responsive transitions avoid hidden keyboard focus, and a short-screen menu scrolls above the music player. Shared admin checkbox geometry no longer inherits the text-input height. Existing server-side consent/publication/payment rules are unchanged.

Tested adcafab92035816571e7951e71d73d6c0a22af89 (tree5d2d70be796b3ae9e293749126f189f2326442d1) passed all six required workflows: full38802392834, focused38802392855, storefront38802392802, branding38802392914, authoring38802392808 and navigation38802393254. Finalization changes documentation only. The three new browser jobs each report361 passing checks, and actual screenshots/source archives were inspected and verified. Local230 syntax/812 existing pure assertions/8 JS/12 packaging checks passed. See docs/TEST_RESULTS.md for precise scope; additional final-main results are recorded separately after observation.

## Application and deployment contract

Cumulative public band website, content/media/music/shows/press, booking/email/newsletter, merchandise/accounts/orders, owner branding, staff permissions and guarded maintenance. Source of truth is afterburn25/Spirit main. Keep the selected navy/black/gold/blue design and external venue ticket sellers. Private drafts require explicit publication; examples are never real band content.

A2/cPanel manual one-page installer only: no setup code, hosting token,20i or automatic database/user creation. Existing sites upgrade with backups, never reinstall or create replacement databases. Preserve original private config/encryption secret/database/media/recovery receipts. PHP cURL and ZIP notices describe real host requirements and are not bypassed. Same-schema upgrades and same-instance guarded restoration retain their limits. Build with `python3 tools/package.py --output ./dist`; deploy the cumulative Full ZIP including hidden access rules. The hosting server requires no Python/Node build, SSH or root.

Approved production artwork/content/policies and actual host/mailbox/merchant/recovery/device/owner acceptance remain before final1.0. No live hosting or provider operation is performed by this source change. Read AGENTS.md, SESSION_HANDOFF.md, docs/INSTALLATION.md, docs/UPGRADES.md and docs/LAUNCH_CHECKLIST.md. Historical results are retained in Git, not represented as proof for a new candidate.
