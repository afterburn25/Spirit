# Spiritual Gorillaz

Authoritative repository: **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md before edits. The selected navy/black/gold/blue design, real components and external venue ticket links remain fixed.

## 0.9.0-dev.11 — consolidated launch checks and upload feedback

The interrupted diagnostics work is recovered and consolidated into one owner-only **Admin > Launch checks** page. It provides 43 passive observations/review tasks, current PHP capabilities and limits, private-configuration checks, service settings, published-content counts and unresolved email/payment follow-up. A support JSON export includes only fixed software/extension/limit/status fields, not raw settings or private data. Configuration is never treated as genuine host, mailbox, merchant or final owner acceptance.

Media upload, backup import and browser ZIP staging now show actual PHP upload limits. When PHP rejects an oversized whole request, these pages return actionable HTTP 413 feedback without executing its requested action or misleadingly treating the missing form data as an ordinary expired form. Normal-size forms retain CSRF checks and permission requirements.

The previously delivered dev10 preview and saved GitHub diagnostics branch had diverged. Dev11 preserves their deployable paths and resolves the former admin/readiness URL through the same active-owner handler as admin/launch. There is one dashboard, not duplicate settings. A keyboard focus-spacing defect found in the support card is fixed and regression-tested.

Application **d699fd6e9f42a8fc2d0329fd07778c4a2718f7a8** passed full workflow **38589219303** and focused maintenance **38589219399**. See docs/TEST_RESULTS.md for exact evidence and boundaries. This documentation-only descendant changes no application code, assets, migrations, workflows or tests.

## Retained manual one-page installer

**A2 Hosting with cPanel only. No 20i, automatic database/user creation, cPanel API token, package setup code or PRIVATE-SETUP receipt.** Prepare the database and its dedicated user in cPanel, then enter their full prefixed names and database-user password on the installer. Hosting checks, database/owner details, corrections and completion remain on one page, including native no-JavaScript use.

Private writes, independent installation locks, prefix/concurrency protection and safe installer self-deletion are preserved. Missing cURL or ZIP does not block manual setup; payment connections need cURL and browser ZIP upgrades need ZIP. The application cannot install missing server modules. Configure the domain's actual PHP environment through A2/cPanel or A2 support.

On a development checkout run `python3 tools/package.py --output ./dist`. Upload/extract only the cumulative Full ZIP, including hidden access rules, and open `/install/` for a fresh deployment. No host-side Node, Python, Composer build, SSH or root is required. Install promptly or protect unattended setup through the hosting panel.

**Existing sites upgrade rather than reinstall.** Preserve the original private configuration, encryption secret, database, media and separate backup/update recovery keys. Same-schema browser upgrades keep the removed installer absent. Inert retired Cpanel files and preview compatibility paths preserve cumulative-update requirements without restoring removed functionality. Read docs/INSTALLATION.md and docs/UPGRADES.md first.

## Acceptance boundaries

All cumulative website, CMS, media/music/shows, booking/email/newsletter, commerce/accounts/orders, staff, backup/recovery and maintenance modules remain. All six migrations and five pinned recovery/access files are unchanged. Automated tests do not replace actual A2 staging, real mailbox delivery, genuine merchant sandbox transactions, approved master artwork/content, populated-site quality review or final owner approval. **This is not an accepted v1.0 release.**
