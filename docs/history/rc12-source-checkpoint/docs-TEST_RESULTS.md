# Current 1.1.0-rc.12 verification

## Exact scope
This cumulative continuation recovers the delivered rc.11 source over published
rc.10 commit 5703ca41a13a6ff01a3b7807287abdeb72c151a5, retaining the narrow Music
filter fix. rc.12 changes public page/footer flow and restores the audio player's
measured bottom clearance. A targeted newsletter selector preserves the former
32px gap where flex items no longer collapse margins.

The source stylesheet and generated CSS bundle change. JavaScript, approved
artwork, eight SQL migrations, public routing and recovery pins are unchanged.
All development tests remain outside the deployment ZIP.

## Observed local checks — 2026-10-21
Environment: PHP 8.3.6, MariaDB 10.11.14, Python 3.12, Playwright 1.57.0,
Chromium 143.0.7499.4 and Pillow 12.3.0. Installed tests ran as an unprivileged
user with a dedicated disposable database. No live data or provider credentials
were used; test guards and browser security policies were not weakened.

- The pinned public asset builder reproduces its committed bundles. Loaded
  public CSS/JavaScript is 129,916 bytes against the unchanged 130,000-byte cap.
- All 15 existing package unit tests passed.
- The deterministic software release gate passed, including repeated identical
  package bytes and payload/manifest integrity. This is not hosting acceptance.
- The existing installed media/keyboard browser regression passed 112 assertions.
  It exercised real audio playback, image sizing, gallery keyboard behavior,
  validation focus and footer-link clearance at 1440, 390 and 320 pixels wide.
  The earlier 320px focused-footer-link obstruction no longer reproduces.
- The retained real-template Music filter component check passed 197 assertions
  at 320, 360, 390, 768, 1024 and 1440 pixels for root and /band URL contexts,
  including native PHP-selected states without JavaScript. This isolated check
  is not an installed-site, storage or upgrade test.
- A disposable installed comparison of the original rc.11 archive and the
  final rc.12 runtime passed 53 focused geometry checks with no uncaught browser
  errors. Short-page footer bottoms equal viewport heights: 1100px on desktop,
  1200px at 390px width and 800px at 320px width. Footer heights remain unchanged
  at 77px, 167px and 183.5px respectively. Private preview and newsletter-enabled
  short pages also reach the viewport bottom.
- Long-content geometry is unchanged: footer/document bottom 3975.75px. The Home
  booking-callout/newsletter gap stays 32px in both versions. The footer retains
  rgba(2,7,12,0.75), original links/credit and no horizontal overflow.
- With actual playback at 320px width, the measured player height is 152px and
  body bottom clearance is 168px, including the existing 16px allowance. Footer
  keyboard focus stays above it, including enhanced navigation during playback.

The geometry fixture uses clearly labeled synthetic content where needed.
User review screenshots are captured separately from the frozen final Full ZIP
with unseeded public content; their companion manifest identifies its exact hash.

## Package and upgrade boundary
The Full ZIP retains ten primary deployment folders, with images at root and
all eight original SQL migrations. No installer, update or recovery behavior
changes. The rc.8-or-earlier updater must reject the grouped foundation change;
use LAYOUT_TRANSITION.md for the backed-up same-root transition and never
reinstall. rc.10/rc.11 share rc.12's layout/schema, but ordinary integrity,
backup and recovery checks still apply. An exact installed rc.10/rc.11-to-rc.12
upgrade was not exercised in this focused footer session.

## Not claimed
Observe GitHub Actions for the exact published rc.12 commit before reporting
current CI passed. Focused local results are not a new full SQL/provider suite,
Firefox/WebKit validation, physical-device test or A2/cPanel acceptance. No live
mail/payment operation, merge, deployment or owner approval occurred. PR 37 stays
draft/unmerged. Historical rc.10/rc.11 records are retained under docs/history
and are not represented as new results.
