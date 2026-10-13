# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**.

## Current state

`0.1.0-dev.1` is an unfinished development foundation, NOT the completed 0.1.0 milestone and NOT a production release. The repository now includes PHP source for a browser installer, private runtime provisioning, owner authentication, basic administration, editable holding-page settings, installation diagnostics, and reproducible unit tests. Database-backed installation/authentication and rendered-browser acceptance have not yet been verified. Do not deploy over the existing live band site.

Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md, docs/ROADMAP.md, docs/DESIGN_LOCK.md, docs/INSTALLATION.md and docs/SECURITY.md before making changes. Preserve the approved design and user decisions.

## Fixed scope

PHP/MySQL shared-hosting band website. Full owner administration. Approved black/deep-navy, gold, blue-stage-lighting and crowned-gorilla design. Music, shows, band, media, booking, press kit and merchandise. Each show's ticket button links to its venue's external seller; no native ticketing. Merchandise payments are planned separately using Authorize.net, sandbox first. No more mockups.

## Development

Requires PHP 8.2+ for this source. Run `php tests/run.php` and lint PHP files. The tests do not require a database; they are not an installation acceptance test. Run tests as a non-root account to exercise permission-denial scenarios.

`python3 tools/package.py --output ./dist` builds a versioned deployment ZIP and a separate PRIVATE setup receipt on the development machine. Python/SSH are NOT required on the shared host. Do not upload the receipt. Source checkouts deliberately lack a usable installer claim key.

See docs/INSTALLATION.md for hosting constraints and staging setup. No production credentials, runtime data or generated setup secrets belong in Git. See docs/TEST_RESULTS.md for the exact checks performed and the untested workflows.
