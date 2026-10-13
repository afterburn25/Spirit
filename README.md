# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before changing anything.

## Current development checkpoint

**0.6.0-dev.1** adds database-first booking inquiries, owner review/assignment/private notes, TLS SMTP configuration, encrypted queued messages, retry/uncertainty handling, and newsletter confirmation/unsubscribe to the cumulative public website, CMS, media, music/player and shows foundation.

This is a development checkpoint, **not an accepted 1.0 release**. The recovered communication code was never previously committed to main. Its new integration suite must pass before merging the recovery branch; see docs/TEST_RESULTS.md for exact evidence rather than treating source presence as a passing test.

No invented band identities, releases, performances, products, orders or testimonials are seeded. Only owner-published approved content appears. Booking requests do not reserve a date, publish a show, take payment or subscribe an organizer to marketing. The merchandise store remains closed pending its implementation. Shows use the venue's external ticket seller; there is no native ticketing.

## Deployment

PHP 8.2+, PDO MySQL, OpenSSL, fileinfo and GD; MySQL/MariaDB; HTTPS; Apache/LiteSpeed rules as documented. No host-side Node, Composer, Python, SSH or root requirement. Browser installer and initial owner account; real private-storage probes; permanent lock and safe installer-folder removal attempt.

On a development machine: `python3 tools/package.py --output ./dist`. It creates a cumulative deployment ZIP and a **separate private setup receipt**. Upload/extract only the ZIP. Keep the receipt and credentials off the website and out of Git. Source archives deliberately have no setup code. Existing supported foundation installations use the owner-only additive database update, never reinstall.

See docs/INSTALLATION.md and docs/COMMUNICATION.md before deployment. A quiet website needs the optional hosting-panel PHP scheduled task or manual owner queue processing for email retries. SMTP acceptance is not proof of inbox delivery.

## Verification

Local checks: `php tests/run.php`, `php tests/storage.php`, `php tests/foundation_paths.php`, `php tests/content_schema.php`, `php tests/communication_unit.php`, `python3 tests/package_test.py`. SQL/HTTP/browser/TLS transport tests run in the isolated environment in `.github/workflows/verify.yml`. Their fixtures refuse non-test databases and run without root. SMTP tests contact only loopback; CI never deploys or contacts a live payment provider.

## Design

The selected black/navy, gold, blue-lighting and crowned-gorilla direction is fixed. The implementation uses real components, not a clickable screenshot. See docs/DESIGN_LOCK.md for source-art provenance and the low-resolution reference-crop limitation. High-resolution production artwork, genuine content, delegated staff permissions, commerce and complete maintenance/acceptance remain further work.
