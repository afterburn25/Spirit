# Encrypted snapshots and guarded restoration

Development checkpoint 0.9.0-dev.2. Consult TEST_RESULTS.md for exact execution evidence. This is an application-data backup and same-installation restore module, not a completed cross-host disaster-recovery installer or browser ZIP code updater.

## Owner workflow

Admin > Backups & safe restore supports creation, full archive/media verification, encrypted download, a separate recovery-key download, import, and explicitly confirmed restoration/deletion. Every mutation and sensitive download requires owner authorization, POST/CSRF and the current owner password. Staff editor/store roles cannot inherit these controls. Retain hosting-provider backups independently.

Create a snapshot, verify it, download its .sgbackup archive, and separately download its PRIVATE-KEY receipt. Store them separately and outside the public website. A backup archive contains confidential configuration, staff/customer/account records, booking information, orders, encrypted service settings and uploaded media. Never commit an archive made from a real installation or its recovery receipt to Git. This repository contains only implementation and disposable test fixtures.

## Archive contents and authentication

All known application tables are recorded with exact column shape and migration hashes, along with the private installation config and all database-referenced uploaded media. Code is obtained from the matching cumulative installation ZIP. Snapshot reads use primary-key ordering, a repeatable-read database transaction, and unbuffered row iteration. Each media file is checked against its database checksum and checked again while copying.

Records are individually AES-256-GCM authenticated with a random per-archive key, per-record nonce, sequence, previous authentication tag and archive header. An authenticated footer and table/file counts detect incomplete output. An import does not execute SQL strings, PHP or serialized objects from an archive. A wrapped recovery key is persisted in private metadata before archive bytes are written; interrupted building entries are not represented as completed backups.

Current safety limits: 512 MB encrypted archive, 2 MB per record and 100,000 records. Creation/verification/import use a 15-second checked work budget, restoration 20 seconds including its pre-restore copy. Hosting upload, execution and memory limits may be lower. An individual slow filesystem/database call can still reach a host timeout. Larger or unusual installations require hosting-panel backup tooling; do not promise unlimited shared-host backups.

## Two restore scopes

**Content only** replaces pages, releases, shows, band/gallery/press content, content media references, revision records and website text settings. Current staff/customer credentials, payment/order history, subscriber consent, service secrets, inventory and carts are preserved. Media is merged; newer uploads are not deleted. Restored content revisions advance so stale editing forms cannot overwrite a restored version.

**Site data** also replaces merchandise products and category descriptions, but is refused if protected operational-table fingerprints differ since the snapshot. It does NOT roll back external payments, identity, newsletter consent, inventory history or messaging. A refusal is intentional: restoring an old payment or email operation could replay a completed action or lose a recent customer/order. Use the narrower content scope or a separately planned disaster-recovery procedure.

Both modes require the same installation binding, installation secret, migration hashes and column shape. Changing domains/database identity or migrating the schema makes an old archive ineligible for this browser restore path. The archive remains an encrypted historical record, not an automatically portable restoration promise.

## Pre-restore copy and rollback boundaries

The entire selected archive is authenticated and privately staged before any live data changes. Existing immutable media identities are checked for collisions. A new automatic pre-restore archive is made first. If the current installation already has missing/damaged media, this automatic archive is explicitly marked integrity_warning and is diagnostic, not a complete verified restore point. That warning does not prevent a valid older backup repairing the matching media.

Target database replacements occur in one InnoDB transaction with no uploaded DDL. A failure rolls the transaction back. Files are restored atomically by immutable storage identity; newer uploads are kept. A file staged before a failed transaction may remain privately unreferenced, or may have repaired corrupt bytes belonging to the same existing identity. This is not an arbitrary multi-filesystem rollback guarantee.

## Maintenance locking

Normal application HTTP and default CLI database connections share a private filesystem lock. Snapshot/restore requests require an exclusive lock and refuse to overtake active requests. While exclusive maintenance runs, new application requests return a temporary 503 with Retry-After. No persistent web-controlled bypass flag is added; process exit releases the lock, and database disconnection rolls back an open restore transaction.

Hosting control-panel/database tools and external services do not participate in the application's filesystem lock. Do not make out-of-band changes concurrently with a restore. A signed payment notification may need provider retry after a brief maintenance response.

## Incomplete work and staging requirements

Automatic retention, cleanup of abandoned temporary files, resumable large snapshots, cross-host/full-business-state disaster recovery and browser ZIP upgrades remain separate work. Failed/hard-interrupted temporary files are private but can consume space. Confirm host storage, file permissions, lock behavior, timeouts and a real recovery drill before relying on this module in production. The full regression suite uses generated data and must pass on the exact code checkpoint before this feature is called verified.
