# RC6 authoring safeguards — work in progress

Source of truth: afterburn25/Spirit main. Base1101be37414701ae3488073e6cddee9348c1cae8, treec30bef92ff8f4718b15f72185e8b165c3fe3439e. Candidate branch work/editor-safety. Inspect live refs before writing; no force pushes or concurrent overwrites.

New: opt-in unsaved-change warnings on content, product and logo authoring forms. No autosave, storage or network calls. Approval/hidden/password/file inputs and sensitive operational forms are excluded. Browser controls the leave dialog and may suppress it; no guarantee on mobile app termination. Defaults versus live fields distinguish restored browser edits, and rejected server responses remain dirty. Native reset timing was reproduced locally and handled after its default action; cancelled submissions cannot permanently suppress warnings.

Also fix the product editor losing authored fields after a rejected save. ProductEditForm projects only editable names/descriptions/selection/slug/revision back into escaped controls. Stale errors retain the old revision, avoiding silent adoption of a newer draft. Server write rules remain unchanged. Native forms retain correction without JavaScript.

Local230 PHP/inc syntax,812 pure assertions,8 JavaScript checks,27 isolated Chromium checks and12 packaging tests passed. Exact candidate SQL/browser/Apache verification is pending. Preserve every prior suite. New editing.yml covers PHP8.2/MariaDB, PHP8.4/MySQL and Chromium/Firefox/WebKit. Complete runs, inspect real renders, record honest results, compare canonical source and preserve every prior payload path before promotion. Local PHP has no PDO MySQL/GD/cURL/ZIP, so do not claim local application integration.

The actual public domain now renders the redesign, but music/band/shows/store remain unpopulated. This was a limited read-only web observation, not authenticated acceptance or deployment performed by us. Do not invent content or infer PHP/private-storage/mailbox/payment success from it.

Fixed scope remains approved navy/gold/blue design, external venue ticket sellers, remembered collapsible menu, manual one-page A2/cPanel setup and no20i/token/setup code/automatic DB. Six migrations and pinned recovery/access files remain unchanged. Existing sites upgrade with backups; never reinstall or replace original config/encryption secret/data/media/recovery keys. Approved master art/real content and real environment/device/owner approval remain before final1.0.
