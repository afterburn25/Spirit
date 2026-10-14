# Spiritual Gorillaz

Authoritative repository **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before changing code. Preserve the approved navy/gold/blue crowned-gorilla design and organized shared-host PHP/MySQL application. No new mockups or native event ticketing.

**0.8.0-dev.1** now has a passing complete application workflow: source **cd3d645bf031e7ab8cf2a7c32b44278e8bc8d89e**, Actions **38493194973**. Both database environments and all five configured Chromium suites passed. The documentation-only descendant changes no runtime, migration or tests. See docs/TEST_RESULTS.md. This is a tested development checkpoint, not an accepted production/v1.0 release or proof of a genuine Authorize.net transaction.

Cumulative modules: browser installer/private runtime/owner authentication, selected public pages, structured content and media, releases and persistent audio, external-ticket shows, booking and transactional email, confirmed mailing-list signup, merchandise/inventory/cart, email-verified customer accounts, token-only checkout and private orders, reporting-based payment recovery and separate fulfillment. Checkout defaults closed and sandbox-first. Read PAYMENTS.md for exact restrictions and provider acceptance steps.

Deployment target: PHP8.2+, PDO MySQL, OpenSSL, fileinfo, GD and cURL, HTTPS, MySQL/MariaDB, tested access rules. No host-side Node, Composer, Python, SSH or root requirement. Package on the development machine with `python3 tools/package.py --output ./dist`. Upload/extract only the Full ZIP; keep its separate PRIVATE setup receipt off the server and out of Git.

Existing migrations001–004 are unchanged;005 is additive. Preserve private configuration, encryption secret, database and media. Installer probes real create/write/read/rename/delete operations and permanently locks before safe removal; it cannot override hosting ACLs. No 0777, destructive reinstall, invented public products or sales. Read INSTALLATION.md, OWNER_GUIDE.md, COMMUNICATION.md, COMMERCE.md and PAYMENTS.md before staging.

Next: staff delegation, backup/restore/browser upgrades, retention, advanced content/search/quality and final artwork. Real hosting, provider sandbox SDK/API/webhook behavior, mailbox delivery, merchant compliance and owner acceptance remain separate requirements.
