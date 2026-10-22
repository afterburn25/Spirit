# Spiritual Gorillaz — 1.1.0-rc.12

## rc.12 footer placement

This cumulative candidate places the compact footer at the bottom of the
viewport on short public pages. Longer pages keep the footer after their
content. The page reserves the audio player's measured height when playback is
open, keeping footer links accessible on narrow screens. The rc.11 Music filter
wrapping fix and the published rc.10 grouped layout are retained.
Read docs/TEST_RESULTS.md for the precise scope of current verification.

## Grouped directories, unchanged visual identity

This cumulative review candidate groups the site into ten primary folders:
**admin, app, assets, config, docs, images, includes, install, storage, uploads**.
Images stay in root. Database migrations move to config/database; public PHP
handlers, templates, scheduled workers and development tools live under app.
Design source/evidence lives under docs/design. Public URLs are unchanged.

Read **docs/DIRECTORY_LAYOUT.md** for the map and **docs/LAYOUT_TRANSITION.md**
before updating an existing site. This is a one-time layout/recovery transition,
not a package that the rc.8 browser updater can safely apply. Do not reinstall.
The private configuration/database/owner/uploads must be retained at the same
absolute site root. No SQL file or table definition has changed.

The earlier rc.9 source and artifacts could not be recovered from the resumed
session or Library, and never reached GitHub. This release reconstructs the
requested organization from verified rc.8. It does not overwrite rc.9.

## Retained work

The animated 3D logo, fixed blue stage background and smoke, transparent
navigation, compact full-width footer with editable copyright and fixed linked
Cajun Web Pro LLC credit, maximum-fit favicon, charts, Visitor Explorer and
Overview analytics are retained without design changes. The public stylesheet
changes only page/footer flow and the player clearance rule. Original images,
public JavaScript and all eight migrations are preserved byte for byte.

The existing owner CMS, media/private previews, booking/newsletter, staff
permissions, optional merchandise/Authorize.net workflows, backups and manual
one-page A2/cPanel installer remain. Show tickets continue to open external venue
sellers. No real performances, customers, traffic or product records are seeded.

## Development

Build: `python3 app/tools/package.py --output ./dist`

Release checks: `python3 app/tools/release_gate.py`

Public asset check: `npm ci --prefix app/tools/public-assets --ignore-scripts`
then `node app/tools/build_public_assets.mjs --check`.

Tests are under app/tests and workflows reference those paths. Development
scripts, design evidence, credentials and private runtime data are excluded from
the installation ZIP. The host needs PHP/MySQL and Apache/LiteSpeed rewriting,
not Python, Node, SSH, a setup code or hosting API tokens.

## Status

See PROJECT_STATUS.md and docs/TEST_RESULTS.md for observed results. The draft
branch is work/1.1.0-analytics-and-navigation, PR 37. No production deployment,
merge, merchant acceptance or final release approval is claimed.
