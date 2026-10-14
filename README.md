# Spiritual Gorillaz

Authoritative repository **afterburn25/Spirit**, **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and docs/ROADMAP.md. The approved navy/gold/blue crowned-gorilla design, organized shared-host PHP/MySQL structure, external venue ticket links and private configuration/media/data are fixed.

Current development work **0.9.0-dev.5** adds searchable/paginated content administration, private version comparison/restoration, typed validation and historical media selection. It is built on exact-main **2a9497e831f6f3740fb5955a1f37b63dadc03ad4**, whose complete run **38511337940** passed. This new checkpoint's full verification is pending; see TEST_RESULTS.md rather than assuming new code passed an earlier run.

Cumulative implemented modules include the browser installer and installer-folder removal/lock, public pages/CMS/music/player/shows, booking/mail/newsletter, merchandise/inventory/cart/customer accounts, token-only checkout/orders/fulfillment, staff roles, encrypted snapshots and guarded restore, owner-operated same-schema ZIP upgrades/independent recovery, and conservative retention. Provider payments and SMTP inbox delivery remain separate acceptance, not proved by synthetic CI.

PHP8.2+, PDO MySQL/OpenSSL/fileinfo/GD/cURL/ZIP, MySQL/MariaDB, HTTPS and the supplied tested Apache rules. No required SSH/root or host-side Node/Python/Composer build. `python3 tools/package.py --output ./dist` on a development machine produces a cumulative Full ZIP and separate PRIVATE setup receipt. Upload only the Full ZIP. Keep setup/backup/recovery receipts and real credentials offline and out of Git. Preserve the original private installation secret and data; never reinstall over an existing installation to bypass a failed update.

Read INSTALLATION.md, OWNER_GUIDE.md, CONTENT_HISTORY.md, STAFF.md, BACKUPS.md and MAINTENANCE_CHECKPOINT.md. Browser code upgrades are currently same-schema; backup restore protects operational payment/account/consent histories and is not cross-host disaster recovery. Final metadata/redirects, accessibility/performance, master artwork/real content and actual host/provider/owner acceptance remain before v1.0.
