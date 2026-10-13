# Continuous integration

The installer/admin workflow runs permission-aware unit tests, PHP lint and real database/HTTP integration on PHP 8.2 with MariaDB 11.4 and PHP 8.4 with MySQL 8.4. It has read-only repository permissions, pinned action commits, no deployment step and no production secrets.

`tests/http_integration.py` builds a fresh package, verifies its manifest, and extracts isolated root/subfolder/recovery installations. It exercises claim-code/CSRF denial, operation locking, actual installation, private configuration, deletion success and permission denial, owner access, non-owner denial, settings escaping/persistence, password rotation, session revocation, cleanup retry, prefix preservation and interrupted-migration retry. Test database names and generated prefixes are explicitly restricted. Only known fixture tables are removed.

Run as a non-root development user with SPIRIT_TESTING=1 and SPIRIT_DB_HOST, SPIRIT_DB_PORT, SPIRIT_DB_NAME=spirit_ci, SPIRIT_DB_USER, SPIRIT_DB_PASSWORD for a disposable database. Then `python3 tests/http_integration.py`.

This is PHP local-server HTTP testing. It does not prove Apache/LiteSpeed rules, real shared-host ownership, production TLS/proxy integration, or rendered-browser acceptance. Do not mark a workflow green until its actual run completes successfully. No setup receipts, passwords, session files, configs or test databases are uploaded as artifacts.

Status at commit: test infrastructure added; execution results pending. Refer to docs/TEST_RESULTS.md and Actions for verified outcomes.
