# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before edits. Preserve the approved navy/black/gold/blue design and external venue ticket links. This is a cumulative PHP/MySQL website, not a mockup.

## 0.9.0-dev.12 — responsive product-gallery quality

Product-photo selection now changes the complete responsive image, its intrinsic dimensions and its alternative text. A failed photo leaves the previous image visible, and a delayed older response cannot replace the latest selection. Portrait main images and thumbnails stay within their intended containers without cropping or expanding the layout. Enhanced photo selection preserves keyboard focus, entered quantity and the existing audio node; native links still select approved photographs when JavaScript is disabled.

The populated storefront workflow38632644731 passed Chromium, Firefox and WebKit at1440px,390px and320px, plus native no-JavaScript, load-failure/retry and delayed-response cases. Tests use actual published synthetic products and media, not fabricated real inventory. See docs/STOREFRONT_QUALITY.md and docs/TEST_RESULTS.md for exact evidence and limits. The required full regression workflow, not an isolated screenshot, governs promotion to main.

All cumulative modules remain: public content/history/search/media/music/shows, booking/email/newsletter, merchandise/cart/customers/orders, staff, guarded backup/restore, same-schema ZIP upgrades, retention and the owner Launch checks dashboard. Actual A2, receiving-mailbox, merchant sandbox, approved master artwork/content, broader populated-site quality and owner approval remain before an accepted v1.0.

## One-page manual installation — unchanged

**A2 Hosting with cPanel only. No20i, automatic database/user creation, cPanel token, setup code or PRIVATE-SETUP receipt.** Create the database and dedicated database user in cPanel; enter their complete prefixed names and the database-user password on the one-page installer. The application creates its tables and owner account, saves protected configuration, permanently locks setup and attempts verified removal of its installer directory.

PHP8.2+, PDO MySQL, OpenSSL, fileinfo and GD, HTTPS and private writable storage are required. cURL and ZIP remain separate optional setup notices: payment connections need cURL and browser ZIP upgrades need ZIP. Configure the actual domain PHP environment through A2/cPanel or support; the application cannot enable missing server modules.

On a development checkout run `python3 tools/package.py --output ./dist`. Upload/extract the cumulative Full ZIP including hidden access rules. No host-side Node/Python/Composer build, SSH or root is required. Install promptly or protect unattended setup with the hosting panel. See docs/INSTALLATION.md and docs/CPANEL_INSTALLER.md.

**Existing sites upgrade, never reinstall.** Preserve the original configuration, encryption secret, database, media and separate backup/update recovery keys. Follow docs/UPGRADES.md; same-schema updates keep the removed installer absent and reject incompatible recovery/schema changes. Inert retired Cpanel/preview paths remain for cumulative-package compatibility without restoring removed features.

## Evidence and continuity

Application checkpoint: eefab5ab98b27397a5be48ba311bf9b9ffe869fb. Exact test results, artifact identities, corrected test assumptions and remaining acceptance boundaries are recorded in docs/TEST_RESULTS.md. Do not infer production readiness from a version number, configured credentials or test fixture. No live hosting setting, payment or provider mailbox was changed by this work.
