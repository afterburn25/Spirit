# Spiritual Gorillaz — 1.2.0-rc.1 (development candidate; not released)

## Unreleased 1.2.0 content administration

This review candidate adds owner-editable page and section text, background/media
selection and safe optional calls to action while retaining the approved public
design when no override is published. Published Band members are arranged in
responsive portrait-left/biography-right rows; the admin CMS manages profiles,
order, drafts and soft removal. An explicit, one-time legacy-lineup import
preserves existing names without inventing portraits or biographies. The
searchable, role-filtered Help Center covers current administration routes.

**Do not deploy or distribute this candidate as the final 1.2.0 release.**
PR #39 remains draft pending exact-package 1.1.0-to-1.2.0-rc.1 upgrade and
independent recovery tests, desktop/tablet/mobile review, accessibility and
owner acceptance. The existing production baseline is final 1.1.0 on main.
No live website has been changed. Build candidate packages in a separate
folder and never overwrite a delivered ZIP or place a ZIP inside another ZIP.

The current one-page installer, ten deployable root folders, owner account,
database, private runtime, approved logo/smoke, external-ticketing model and
intentionally disabled commerce remain in scope. See
[the roadmap](docs/ROADMAP.md), [status](PROJECT_STATUS.md), and
[upgrade instructions](docs/UPGRADES.md) before reviewing this candidate.

## Historical release baseline — final 1.1.0

## Final 1.1 release

This cumulative release includes owner analytics and Visitor Explorer, the
approved animated gold gorilla and blue stage/smoke, the ten-folder layout,
wrapping Music filters and the short-page footer correction. The compact
footer reaches the viewport bottom when content is short and follows content
on longer pages. Measured player clearance keeps footer links accessible.

The final promotion preserves rc.12 application code, CSS, JavaScript, artwork,
all eight SQL migrations and the pinned recovery layer. Deployment changes are
the version and current release/operating documentation. Development source
also gains repeatable verification of unchanged baseline and target ZIPs.

## Installation and updates

The ten deployment folders are **admin, app, assets, config, docs, images,
includes, install, storage and uploads**. Public URLs are unchanged. Read
docs/DIRECTORY_LAYOUT.md for the complete map.

- **Fresh site:** use the one-page manual A2/cPanel installer described in
  docs/INSTALLATION.md. No setup code or hosting API token is required.
- **Existing grouped rc.10, rc.11 or rc.12:** use owner Browser upgrades after
  saving a full backup and the private recovery receipt. Normal integrity,
  version, schema and recovery checks must pass; see docs/UPGRADES.md.
- **rc.8 or an earlier layout:** follow docs/LAYOUT_TRANSITION.md at the same
  absolute site root. Its old updater must refuse the layout change. The
  unrecovered earlier rc.9 requires comparison with its actual package.

Keep the original configuration, installation secret, database, owner account,
private runtime and media. Do not reinstall an existing site or recreate its
removed installer. The final promotion adds no SQL migration.

## Retained launch scope

The owner CMS, media/private previews, booking/newsletter, staff permissions,
customer accounts, backups and independent code recovery remain available.
Show tickets open external venue sellers. Commerce remains optional and disabled
until the owner completes activation acceptance. No real performances, customers,
traffic or products are seeded by the package.

Previously accepted installation, communication and account results remain in
docs/acceptance/CONSOLIDATED-FINAL-PASS.md. They are owner-reported acceptance,
not new provider tests.

## Development and evidence

Build: `python3 app/tools/package.py --output ./dist`

Release checks: `python3 app/tools/release_gate.py`

Public asset check: `npm ci --prefix app/tools/public-assets --ignore-scripts`
then `node app/tools/build_public_assets.mjs --check`.

Tests live under app/tests and remain outside the installation ZIP. The host
needs PHP/MySQL and Apache/LiteSpeed rewriting, with no host-side Python, Node,
SSH or root requirement. See PROJECT_STATUS.md, docs/TEST_RESULTS.md and
docs/acceptance/FINAL-1.1.0.md for the evidence and operational limits.

Repository: https://github.com/afterburn25/Spirit

The final release is consolidated through PR 37 into authoritative main.
Previously delivered archives remain immutable. Shipping this package does not
install it on the owner's live hosting account.
