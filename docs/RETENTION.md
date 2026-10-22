# Technical retention and private temporary cleanup

Current behavior for 1.1.0-rc.3. This module originated in checkpoint 0.9.0-dev.4; consult TEST_RESULTS.md for observed results on the current candidate rather than treating that earlier checkpoint as current evidence.

## Owner workflow

Open Admin > Temporary data & retention. A GET only previews counts. To perform a bounded batch, confirm the current owner password and type `CLEAN TEMPORARY DATA`. Operational technical records are processed in one database transaction. Temporary-file removal follows as a separate best-effort phase. Analytics then runs its own bounded database transaction. A failure rolls back the transaction in which it occurs; it cannot undo an earlier committed phase or deleted file. Completed output reports the actual operational, analytics and file counts, including skipped temporary items.

Each default batch affects at most 200 records in each category:

- Sign-in/request-limit buckets that expired more than a day ago. Active limits remain in force.
- Customer activation/reset and staff invitation/reset tokens expired more than 30 days ago. Customer/staff accounts and current usable tokens remain.
- Shopping bags expired more than 30 days ago **only when no order references them**, including their items and operation nonces. All order-linked bags remain.
- Encrypted bodies of accepted messages more than 90 days after SMTP acceptance, or cancelled messages created more than 90 days ago. The delivery record, status, metadata and unique deduplication key are retained. Queued, retrying, failed, uncertain and sending bodies are never cleared by this policy.
- Analytics pageviews, completed visits and crawler daily records older than the owner-selected retention period. The default is 90 days, with 30/90/180/365-day choices in Analytics. The cutoff is UTC midnight on the retention boundary. An old visit is deleted only after its pageview batches have cleared; recent traffic is retained. Imported Search Console query snapshots are not purged by traffic retention.

The separate **Clean up expired analytics now** action in Analytics processes at most 500 rows per analytics table after owner password confirmation. It does not clean operational records or create a hosting schedule. A retention selection alone only determines eligibility; records remain until an authorized cleanup runs.

A shared/exclusive maintenance lock prevents cleanup during website writes or code replacement. Repeat a reviewed batch when eligible records remain; this is not an unbounded DELETE of an entire table. No retention period is a claim of legal compliance: the owner must separately determine the business's applicable record-retention and privacy obligations.

## Conservative file cleanup

Only recognized files under the canonical account-private runtime are candidates after at least seven inactive days: `.restore-<32 hex>` temporary files under media, abandoned backup metadata `.tmp` files, and old `stage-<32 hex>` backup/restore working directories containing only known staged media names and rows.jsonl. Every child must also be old and regular; one unknown, recent or linked child preserves the entire working directory. The scan is bounded to 1,500 examined entries/two seconds, so the preview is explicitly a bounded candidate scan rather than a complete disk inventory.

No symlink or non-canonical parent is traversed. Identity is rechecked before file removal. An unknown file, active temporary file, permission-denied item, actual uploaded media, completed or partial backup archive, metadata/key for a backup, or any code rollback checkpoint is preserved. Completed backups have their own explicit per-backup deletion controls; save and verify a separate copy before removing one.

## Optional hosting-panel task

Automatic permission is disabled by default. Enabling it in the owner screen does not create a schedule. The host may run, for example, a daily command using its actual PHP binary and public document root:

```text
/usr/bin/php /home/ACCOUNT/public_html/band/app/tasks/cleanup.php --document-root=/home/ACCOUNT/public_html
```

The task is CLI-only, refuses an uninstalled/outdated application, respects the maintenance gate, and exits without changes unless the owner enabled scheduled cleanup. It uses no public cron token or secret in the command. The hosting panel provides scheduling without SSH; actual execution must be verified on the host. This is not a permanent background service.

## Protected data

Never purged: orders/payment operations and signed-notification inbox records; stock adjustments; customers/addresses or staff accounts; booking inquiries; consent/unsubscribe suppression; public/draft content and revision history; real uploaded media; audit logs; private configuration and encryption keys; completed backups/partial recovery archives; code rollback checkpoints. Body clearing intentionally prevents old private email content being retained indefinitely, without deleting its delivery/idempotency history.

Tests use only disposable data. The fault test takes a test table offline to prove a failed SQL batch rolls back earlier deletions, while the filesystem unit checks prove uploaded media, active files, unknown directory contents, symlinks and recovery archives survive. Browser tests perform actual cleanup at desktop/phone widths. Check the exact current Actions result before treating them as passed.
