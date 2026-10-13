# Session handoff — resume here

Repository: afterburn25/Spirit; authoritative branch: main. Current development version: 0.1.0-dev.1. No accepted release or live deployment.

## What happened
The repository initially contained only AGENTS.md. A local installer/admin source draft existed but had not been committed. This change saves that draft, reproducible tests, the fixed project scope, roadmap and honest verification status. GitHub access has successfully supported writes; do not ask the user to reconnect or grant wider permissions without a new, actual permission failure.

## First actions next session
1. Read AGENTS.md, README.md, PROJECT_STATUS.md, docs/ROADMAP.md, docs/DESIGN_LOCK.md, docs/INSTALLATION.md, docs/SECURITY.md and docs/TEST_RESULTS.md from current main. Preserve concurrent changes; never force push.
2. Provision a development-only PHP/pdo_mysql and MySQL/MariaDB test environment. Use isolated databases and temporary fake credentials, never request production secrets in chat.
3. Run the unit suite as non-root, lint all PHP files, then add database and browser integration tests. Test both / and a subfolder URL; wrong claim code; CSRF rejection; non-owner denial; fresh installation; prefix conflicts; interrupted installation; owner login/logout/password rotation; settings persistence; lock enforcement; cleanup success and host denial.
4. Review automatic private-runtime selection on realistic shared-host addon/subdomain layouts. A path outside this document root may be under another domain's web root. Do not claim the current algorithm proves account-wide privacy. Implement a safe verified strategy; never fall back to publicly exposed credentials or chmod 0777.
5. Verify private sessions, HTTPS detection behind explicitly trusted hosting proxies, deletion permissions after panel extraction, and installation retry/crash states. Fix issues before calling 0.1.0 complete.
6. Build and inspect a cumulative staging package, test its installer, and preserve the setup receipt privately. No live-site replacement without explicit deployment authorization.
7. Commit the approved moodboard reference asset when practical, then implement v0.2.0 as real responsive HTML/CSS using its locked direction. Current public holding page is temporary, not the chosen design.

## Non-negotiables
Full admin, PHP/MySQL shared hosting, organized folders, browser installer with actual write tests and attempted safe self-deletion. Permanent lock remains when deletion is blocked. Venue ticket links only; merchandise payments separate and sandbox-first. No invented band content or metrics. No more mockups or unrequested redesigns. Preserve data/config/uploads during future supported upgrades.

Update status, handoff, changelog, roadmap and test evidence in every implementation commit. Track implemented, tested, untested and blocked separately. Do not label this draft 1.0.0.
