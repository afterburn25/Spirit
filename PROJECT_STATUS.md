# Project status — 0.9.0-dev.1 staff checkpoint

Working branch **work/staff-access**. New workflow results pending. Last verified application .8 cd3d645bf031e7ab8cf2a7c32b44278e8bc8d89e passed38493194973; documentation descendant eb995810e35f23af28080f7f7b2b65f3021085c2 was promoted to main. Inspect live refs before writing.

Implemented in this branch: deny-by-default roles (owner/content_editor/store_manager), route-level capabilities and tailored navigation, owner-password-confirmed invitations/access changes, hashed expiring single-use invite/reset tokens with encrypted mail, scanner-safe POST completion, credential/session revocation, serialized last-owner protection, restricted store fulfillment without keys/refunds/reporting/customer administration, and owner-only searchable activity history. Authentication/password changes now recheck locked current credentials/version to prevent stale reset/rehash races. Shared media access is intentional for both staff roles; private config/mail/subscriber/customer control remains owner-only.

Local PHP lint, 38 capability units, Python compilation and packaging checks passed. New root/subfolder SQL/HTTP, actual editor publishing/private-media, stock adjustment/shipment, invitation/recovery/revocation/owner invariants, additive .8 upgrade and desktop/mobile browser tests await the exact Actions result. No claim of green integration until that run passes.

All cumulative installer, design, content, music, shows, booking, communications, catalog, customers and checkout code is preserved. Migration006 only adds staff_tokens; prior migrations unchanged. No demo accounts/products are seeded.

Still unfinished .9: full backup/restore/browser ZIP upgrades, retention/cleanup, advanced CMS revisions/pagination, final search metadata/redirects/accessibility/performance and master artwork. Actual shared-host/TLS/ACL/provider sandbox/webhook/inbox/content approval remain. A staff checkpoint is not completed .9 or 1.0.
