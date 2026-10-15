# Spiritual Gorillaz — 1.0.0-rc.7 candidate

This pass improves the existing public phone menu and native admin checkboxes, without a redesign or new business module. Tab can enter and leave the public disclosure freely; Escape returns focus, outside interaction dismisses it, responsive transitions avoid hidden keyboard focus, and a short-screen menu scrolls above the music player. Shared admin checkbox geometry no longer inherits the text-input height. Existing server-side consent/publication/payment rules are unchanged.

Exact new Actions results are pending. Local 230 PHP/inc syntax checks, 812 existing pure assertions, eight JavaScript checks and 12 packaging tests passed. An isolated Chromium DOM/style check exposed and corrected CSS hiding focus before a media-query event. It is not an end-to-end site test. Read docs/NAVIGATION_QUALITY.md and the current handoff before promotion.

## Application and deployment contract

Cumulative public band website, content/media/music/shows/press, booking/email/newsletter, merchandise/accounts/orders, owner branding, staff permissions and guarded maintenance. Source of truth is afterburn25/Spirit main. Keep the selected navy/black/gold/blue design and external venue ticket sellers. Private drafts require explicit publication; examples are never real band content.

A2/cPanel manual one-page installer only: no setup code, hosting token,20i or automatic database/user creation. Existing sites upgrade with backups, never reinstall or create replacement databases. Preserve original private config/encryption secret/database/media/recovery receipts. PHP cURL and ZIP notices describe real host requirements and are not bypassed. Same-schema upgrades and same-instance guarded restoration retain their limits. Build with `python3 tools/package.py --output ./dist`; deploy the cumulative Full ZIP including hidden access rules. The hosting server requires no Python/Node build, SSH or root.

Approved production artwork/content/policies and actual host/mailbox/merchant/recovery/device/owner acceptance remain before final1.0. No live hosting or provider operation is performed by this source change. Read AGENTS.md, SESSION_HANDOFF.md, docs/INSTALLATION.md, docs/UPGRADES.md and docs/LAUNCH_CHECKLIST.md. Historical results are retained in Git, not represented as proof for a new candidate.
