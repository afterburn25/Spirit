# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before changing anything.

## Current development checkpoint

**0.5.0-dev.1** integrates the installer foundation with the approved visual direction, structured owner content management, protected media, band/press/gallery pages, releases and local audio, and shows with external venue ticket links. This is a cumulative development checkpoint, **not an accepted 0.5 or 1.0 release**. Newly added workflows have an expanded CI suite; consult the current run and docs/TEST_RESULTS.md rather than assuming every test passed.

No invented band identities, releases, performances, products, orders or testimonials are seeded. Empty states are intentional until approved content is entered. Booking and merchandise pages are styled but do not yet submit inquiries or accept payments. Staff delegation, communications, commerce and complete maintenance tooling remain further work.

## Deployment

PHP 8.2+, PDO MySQL, OpenSSL, fileinfo and GD; MySQL/MariaDB; HTTPS; Apache/LiteSpeed rules as documented. No host-side Node, Composer, Python, SSH or root requirement. Browser installer and initial owner account; real private-storage probes; permanent lock and safe installer-folder removal attempt.

On a development machine: `python3 tools/package.py --output ./dist`. It creates a cumulative deployment ZIP and a **separate private setup receipt**. Upload/extract only the ZIP. Keep the receipt and all credentials out of Git and the website. Source archives deliberately have no setup code. Existing foundation installations use the owner-only additive database update, never reinstall.

## Verification

`php tests/run.php`, `php tests/storage.php`, `php tests/foundation_paths.php`, `php tests/content_schema.php`, and `python3 tests/package_test.py` are local checks. SQL/HTTP/browser tests require the isolated environment in `.github/workflows/verify.yml`. Their fixtures refuse non-test databases and run without root. CI never deploys or contacts a live payment provider.

## Design

The selected black/navy, gold, blue-lighting and crowned-gorilla direction is fixed. The original concept is not a clickable screenshot. See docs/DESIGN_LOCK.md for source-art provenance and the current low-resolution reference-crop limitation. See docs/OWNER_GUIDE.md for content and publication workflows.
