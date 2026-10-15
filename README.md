# Spiritual Gorillaz — 1.0.0-rc.2

Source of truth: **afterburn25/Spirit**, **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before changes. Preserve the approved navy/black/gold/blue design, real components and external venue ticket links.

## Grouped administration navigation

The owner sidebar now organizes 34 destinations into six collapsible categories: Website content; Music & shows; Bookings & communication; Store & orders; Settings & access; System & maintenance. Overview remains a direct link. Each category has a decorative icon, count, chevron, hover/focus treatment and current-section styling. All existing destinations remain; six existing store/order endpoints now have direct shortcuts.

Categories start expanded on a first visit. Expand all, Collapse all and individual choices are remembered in this browser separately for each installation/account/role. A deliberately closed current category stays closed and highlighted. Sidebar scroll and mobile drawer state are tab-specific. Refresh keeps the actual current page, query and hash: the menu never stores a destination URL or redirects back to Overview. Selecting a phone destination closes the drawer while retaining the groups.

Only permitted links are rendered for each staff role, and endpoints retain their server authorization. Native disclosures and links remain usable without JavaScript. Unavailable browser storage does not disable navigation; a notice explains when the layout cannot be saved. Unsaved forms and cross-device preferences are not part of menu persistence. See docs/ADMIN_NAVIGATION.md.

## Cumulative release candidate

This package also includes the RC1 native public menu/photo/audio fallbacks, published-policy footer, catalog/price/mobile-control refinements and broader three-engine release review. All earlier CMS/media/music/shows, booking/email/newsletter, store/cart/accounts/orders, staff, backups, same-schema upgrades, retention and passive Launch checks remain.

Application **2947013dff9942691cf9c27b128f20cc50da7e77** passed full **38654637873**, focused maintenance **38654637735** and three-engine storefront/release/navigation **38654637749**. Local verification independently passed 219 PHP/inc syntax checks, 689 distinct pure assertions and 12 packaging tests. This documentation-only descendant changes no tested runtime, assets, schema, workflows or tests. See docs/TEST_RESULTS.md for exact evidence and remaining boundaries.

## Manual one-page A2 installation

**A2 Hosting with cPanel only. No 20i, setup code, PRIVATE-SETUP receipt, cPanel API token or automatic database/user creation.** Prepare a database and dedicated user in cPanel and enter their complete names and database-user password on the one-page installer. Private write probes, permanent locks and safe self-deletion remain. PHP cannot bypass provider ownership/ACL restrictions.

PHP 8.2+, PDO MySQL, OpenSSL, fileinfo, GD, HTTPS and private writable runtime are required. cURL and ZIP are separate optional installation checks: payment connections need cURL and browser ZIP upgrades need ZIP. Enable missing extensions for the domain's PHP environment through the host, not through the application.

Build with `python3 tools/package.py --output ./dist` on a development checkout. Upload/extract the cumulative Full ZIP including hidden access rules. No host-side Python, Node, Composer build, SSH or root is required. Install promptly or protect unattended setup. Existing sites **upgrade rather than reinstall**. Preserve original private configuration, encryption secret, database, uploads and separate backup/update recovery keys. Read docs/INSTALLATION.md and docs/UPGRADES.md first.

## Final release boundary

RC2 is a staging candidate, not accepted final 1.0. Actual A2 hosting/PHP settings, receiving-mailbox delivery, genuine merchant sandbox and recovery acceptance, approved production artwork and real content/policies, populated-device review and final owner approval remain. No live site, hosting setting or production payment mode was changed. The current reference artwork is not a set of final high-resolution masters.
