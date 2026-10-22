# Current 1.1.0-rc.11 verification

## Exact scope
Local continuation of published rc.10 commit
5703ca41a13a6ff01a3b7807287abdeb72c151a5, tree
7fed2c9ae5638bd2af3e971b11b536411b7a3143. The sole production-template change is
adding the existing button-row wrapping utility to the Music release filters.
Public bundles, artwork, eight migrations, routes and recovery pins are unchanged.

## Observed checks
- PHP 8.4.24: 267 PHP files passed syntax validation.
- 26 pure PHP suites passed, totaling 1,056 individual PASS assertions.
- All 15 package unit tests passed.
- Python test/build files compiled successfully.
- The deterministic release gate passed; final packaging is independently
  checked against its per-file manifest and the published source baseline.
- Chromium 144.0.7559.96: 198 focused component checks passed. The Music filter
  was rendered from the real production PHP template and committed stylesheet
  at widths 320, 360, 390, 768, 1024 and 1440 for root and /band URL contexts.
  All four links fit the padded content area, preserve their URLs, expose the
  correct PHP-selected state, and remain keyboard-accessible. Native layout
  and PHP selection were also checked with JavaScript disabled.
- The unchanged published template reproduced the 320px defect: EPs ended at
  x=290.046875px while its padded content boundary ended at x=281px. The rc.11
  row wraps instead. The comparison uses identical CSS and viewport settings.

The component fixture intentionally extracts the filter navigation with its
original layout ancestors, substitutes transparent decorative backgrounds, and
omits scripts. It is NOT a full-page, HTTP, database-installation, or upgrade
test. The component screenshots are labeled accordingly. A separate 320px
full-template preview was rendered offline with original artwork; it is a
preview, not installed-site acceptance.

An initial storage test attempt inherited the runner's explicit trusted
SPIRIT_ACCOUNT_HOME server environment and failed the test that deliberately
removes all private-home configuration. Re-running the unchanged test with a
clean environment as an unprivileged user passed all 26 checks, including the
three real permission-denial cases. Both attempts are retained in the external
verification record. No production security check was removed or relaxed.

## Not claimed
No rc.11 GitHub CI run or production deployment occurred. The current connector
has read actions only, so the local patch still needs publishing. The published
rc.10 storefront/media-quality failures require complete reruns; the focused
filter result is not a claim that every earlier failing workflow is fixed.
Local installed SQL suites and the exact rc.10-to-rc.11 upgrade were not run
because PDO MySQL and a database service are unavailable. Loopback navigation
was blocked by the environment; the visual regression used offline rendering
without altering browser policies. No real mail, payment, physical-device or
A2/cPanel acceptance is asserted.

## Layout and upgrade boundary
The deployment ZIP has ten primary folders. Images remain at root. Tests/tools
are under app, source design under docs, and database migrations under config.
The old rc.8-or-earlier updater must reject this foundation change. Use
LAYOUT_TRANSITION.md for the backed-up same-root transition; never reinstall.
Published rc.10 has the same layout/recovery generation as rc.11, but normal
integrity, backup and recovery checks still apply. Earlier unrecovered rc.9
installations need comparison of their actual package, not assumed compatibility.

## Evidence
The companion verification ZIP includes logs, the component report, image
captures and a final package/hash receipt. Historical rc.10 notes remain under
docs/history/rc10-source-checkpoint and are not presented as new rc.11 results.
The PR stays draft/unmerged; the live site has not been changed.
