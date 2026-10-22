# Project status — 1.1.0-rc.12

## Implemented
The cumulative development branch is work/1.1.0-analytics-and-navigation, draft
PR 37. The rc.11 source recovered from its delivered patch retains the rc.10
grouped layout and wraps the Music release filters on narrow screens.

The rc.12 public layout fills the viewport on short pages, allowing main content
to take the spare vertical space while the compact footer stays at the bottom.
On longer pages the footer remains after the content and optional newsletter.
The fixed audio player's measured height supplies its bottom clearance instead
of an obsolete 116px override, keeping narrow-screen footer links accessible.

Deployment still has ten primary folders: admin, app, assets, config, docs,
images, includes, install, storage and uploads. Approved artwork, logo/smoke,
footer appearance and settings, JavaScript, eight SQL migrations, public URLs
and the grouped recovery foundation are retained.

## Verification
Observed results and exact limitations are in docs/TEST_RESULTS.md. The rebuilt
public CSS/JavaScript totals 129,916 bytes against the unchanged 130,000-byte
budget. The existing installed media/keyboard regression passed all 112 checks,
including the formerly failing 320px footer-focus clearance check, using
Chromium 143, PHP 8.3 and disposable MariaDB 10.11 as an unprivileged user.

Historical rc.10 and rc.11 results remain in docs/history and must not be
presented as new CI or hosting acceptance.

## Remaining blockers
Observe GitHub Actions for the published rc.12 commit before claiming all CI
passed. Target A2 layout-transition/update acceptance, enabled-provider
acceptance, physical-device review and owner visual approval remain. PR 37
stays draft and unmerged; main and the live host are not changed.
