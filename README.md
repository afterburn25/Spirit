# Spiritual Gorillaz — 1.0.0-rc.1 preparation

Source of truth: **afterburn25/Spirit**, **main**. This branch is preparing the user-requested 1.0 candidate; exact verification is pending. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md, docs/ROADMAP.md and docs/RELEASE_CANDIDATE.md before edits. Preserve the approved navy/black/gold/blue design and external venue ticket links.

## Candidate additions

Native mobile navigation, authorized full-size photo links and non-preloading native audio controls now work without page JavaScript. Enhanced dialogs, persistent audio and product-photo switching remain. Portrait merchandise cards keep a square image area; price emphasis and mobile filter/quantity text are corrected. Artwork-free image links retain escaped accessible names. Published privacy/terms/shipping/returns Pages appear in footer navigation; drafts remain private and unpublishing withdraws the link.

All earlier website/admin/content/media/music/shows, booking/email/newsletter, catalog/cart/accounts/orders, staff, guarded backup/restore, same-schema upgrades, retention and passive Launch checks remain cumulative. The new three-engine root/subfolder release review supplements the complete existing regression suite. See docs/TEST_RESULTS.md for exact evidence; do not confuse a source-artifact result with application verification.

## One-page manual A2 installation

**A2 Hosting with cPanel only. No20i, automatic database/user creation, cPanel token, setup code or PRIVATE-SETUP receipt.** Create a database and dedicated user in cPanel and enter their complete prefixed names/password on the one-page installer. Private write probes, permanent installation locks and verified safe self-deletion remain. PHP cannot bypass the host's ownership/ACL controls.

PHP8.2+, PDO MySQL, OpenSSL, fileinfo, GD, HTTPS and protected writable runtime are required. cURL/ZIP are separate optional installation notices: payment connections need cURL; browser ZIP upgrades need ZIP. Enable missing extensions in the actual domain's A2 PHP environment, not through the application.

Build a development checkout with `python3 tools/package.py --output ./dist`. Upload/extract the cumulative Full ZIP, including hidden access rules. No host-side Python/Node/Composer build, SSH or root is required. Install promptly or protect unattended setup through cPanel. Existing sites **upgrade, never reinstall**. Preserve private configuration, original encryption secret, database, media and separate backup/update recovery keys. Read docs/INSTALLATION.md and docs/UPGRADES.md.

## Final release boundary

A candidate is a staging package for completing acceptance, not an accepted live website. Actual A2/mailbox/genuine merchant sandbox/recovery results, approved production-resolution artwork and real content/policies, populated real-device review and final owner approval remain. The reference art crops are not production masters. No hosting setting, live payment mode or public deployment is changed by this work. Final **1.0.0 is not released**.
