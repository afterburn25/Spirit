# Session handoff — resume here

Read AGENTS.md first. Repository **afterburn25/Spirit**, authoritative **main**. Approved navy/gold/blue crowned-gorilla design remains fixed; no new mockups, native ticketing, fake content, secrets in Git or destructive reinstall.

## Current verified checkpoint

**0.7.0-dev.1**, application commit **6a9396f571a3c8658886f84f1b64b754cd6c7fe6**, tree **88dfa6fcdd316f9ef258f2c17bff74540bb32685**, passed Actions **38483296156** on PR #2. PHP8.4/MySQL8.4 job115590780970 and PHP8.2/MariaDB11.4 job115590781333 passed; Chromium exercised actual catalog/bag/customer and owner screens at1440/390. Screenshot artifact11857557964 contains40 synthetic renders. Contact sheets and selected full-size forms were reviewed. This documentation-only descendant changes no code/assets/schema/tests. Check live main and newest exact run.

Adds catalog/variants/stock/bag/verified customer accounts and US address book. See PROJECT_STATUS.md and docs/COMMERCE.md. Original migrations001–003 are unchanged;004 is additive. Preserve private runtime/config/encryption secret, content/media, bookings/subscribers and all new customer records.

## Continue next

1. Inspect live main and exact latest Actions. Use this verified .7 checkpoint, not an old installer-only or .6 copy. Preserve concurrent changes and never force-push.
2. Implement .8 Authorize.net sandbox, server-priced inventory reservations, guest checkout, orders/fulfillment/shipping/tax. Bags do not reserve stock or establish payment. Never store cards, trust a return URL as payment proof or put provider credentials in chat/Git. Preserve all .7 regression checks.
3. Continue .9 staff roles, full backup/restore/ZIP upgrades/retention, advanced CMS/SEO/accessibility/performance and staging acceptance. Production artwork/real content still need approval. No accepted v1.0 without complete tested scope and explicit acceptance.
4. Deliver cumulative immutable full packages with separate PRIVATE setup receipts. Read back main after every promotion. Future changes need their own CI results; source-artifact success is not verification.

## Important boundaries

Customer identity is separate from staff. Email-first activation avoids attacker-preselected passwords. Tokens are hashed, single-use, expiring and encrypted in queued messages. Password changes/owner suspension revoke sessions and links. Address operations bind customer and revision. Registration is never newsletter marketing consent.

Catalog drafts preserve public snapshots. Prices/stock/variant activity are immediate operational edits. Bag calculations use current integer-cent server data; repeated add/stock submissions are idempotent; stale/unpublished lines remain removable without exposing drafts. Customer logout forgets this browser's bag. Checkout is closed. Expired bags cannot be accessed; retention cleanup remains later work.

## Environment

Local container has no pdo_mysql/GD/browser or network DNS. Canonical tracked source is downloaded via the GitHub source-artifact workflow. Lint, non-root storage/foundation, units and packaging run locally; real SQL/TLS/HTTP/Chromium evidence comes from Actions. Test scripts and synthetic data never enter deployment packages. Account email is queued and requires hosting-panel PHP schedule or owner queue processing. Actual provider/host acceptance remains separate.
