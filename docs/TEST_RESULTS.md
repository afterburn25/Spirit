# Local test evidence — 2026-10-13

Development build: 0.1.0-dev.1. Environment: PHP 8.4.24 CLI on Linux. These are local checks, not CI or live-host results.

## Executed
- `find . -name '*.php' -print0 | xargs -0 -n1 php -l`: 23 PHP files passed syntax checking. After the final HTTPS guard change, app/Security.php was re-linted successfully.
- `php tests/run.php`: initial root run passed 40 checks; root skips permission-denial scenarios. A subsequent security regression assertion was added.
- `runuser -u nobody -- php tests/run.php`: final non-root run passed **43 checks, 0 failures**. On a normal development machine, run `php tests/run.php` as a non-root user; runuser is only the local test harness command.

Coverage: escaping; CSRF generation/validation; password hashing/validation; email validation; root/subfolder paths and URL restrictions; HTTPS and rejection of untrusted forwarded headers/production loopback bypass; database input validation; path boundaries; private storage provisioning and real IO probes; restricted permissions; atomic PHP config serialization; symlink refusal; allowlisted installer cleanup; unknown-file preservation; idempotence; completion lock without config; actual permission-denied write and delete scenarios.

## Not executed
PDO MySQL is absent in this environment (PDO and OpenSSL are present). No MySQL/MariaDB provisioning, schema migration, prefix-conflict recovery, authentication, settings persistence, installer HTTP journey or database-backed cleanup test ran. No production hosting, browser rendering, device, accessibility, performance, payment, mail, upgrade or restore test is claimed. Package script exists but deployment package acceptance is pending.

Do not convert unit results into a claim that v0.1.0, much less v1.0.0, is finished.
