# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**.

## Current state

**0.1.0-dev.2** is a working development foundation, not the accepted 0.1.0 milestone or the complete website. It contains the browser installer, private runtime provisioning, owner authentication, basic administration, editable holding-page settings, installation diagnostics and reproducible tests.

GitHub Actions run **38454993289** passed on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4, including real packaged root/subfolder installs and HTTP owner workflows. Chromium installer/admin smoke checks passed at 1440px and 390px on the PHP 8.4 job. This is not live shared-host acceptance or approval of the final public design. See docs/TEST_RESULTS.md for commit-specific evidence and later package checks.

The public page is still a temporary holding page. Do not deploy this over the existing live band website.

## Fixed scope

PHP/MySQL shared-hosting band website, full administration and the approved black/deep-navy, gold, blue-stage-lighting and crowned-gorilla design. Music, shows, band, media, booking, press kit and merchandise remain the agreed feature roadmap. Shows use external venue ticket-seller URLs; native tickets are excluded. Merchandise payments are planned separately with Authorize.net, sandbox first. No more mockups or unrequested redesigns.

Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md, docs/ROADMAP.md, docs/DESIGN_LOCK.md, docs/INSTALLATION.md and docs/SECURITY.md before changing code.

## Development and packaging

Application target: PHP 8.2+ with PDO MySQL and OpenSSL. Run `php tests/run.php`, `php tests/storage.php`, `python3 tests/package_test.py` and lint PHP source. Run permission tests as non-root. Disposable database/HTTP and Chromium instructions are in docs/CI.md.

On a development checkout, `python3 tools/package.py --output ./dist` creates one cumulative deployment ZIP and a separate PRIVATE setup receipt. Python/SSH are not needed on the shared host. The receipt must never be uploaded or committed. Source checkouts deliberately have no usable installer claim key.

Packaging includes only known deployment paths; excludes test tools, runtime/customer data, local configs, setup receipts, symlinks and output folders; and refuses to overwrite existing versioned artifacts. Tests and build tools remain in Git, not in the deployment ZIP.

Private runtime uses a validated account-level base, not merely the parent of the current document root. See docs/STORAGE.md for host configuration, addon domains and legacy development-runtime migration. No automatic production upgrade is available yet. Never remove configuration or locks to reinstall.
