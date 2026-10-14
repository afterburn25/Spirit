# Content search and private revision recovery

Development checkpoint 0.9.0-dev.5. Read TEST_RESULTS.md for current execution evidence.

## Find and manage content

Every content section now has literal title/URL search, visibility filters, display/recent/title ordering and 25-row pagination. Records beyond the previous 1000-row administrative cap are reachable. Filters remain in Previous/Next URLs. Invalid array/decimal/out-of-range parameters are refused, not interpolated into SQL. Search does not treat percent/underscore as wildcards.

## Compare and restore

Open an item's **Version history & restore** link. Saved versions are paginated and show revision number, action, staff display name and time. Choose a version to compare its title, URL, declared content fields and recorded display order with the current draft. Comparison values are escaped plain text; stored markup is not executable.

**Restore as private draft** creates a new revision and audit event. It preserves all later history and the current live snapshot, publication date and public-media links. Review in the ordinary editor, preview, then explicitly publish. Neither viewing history nor restoring implicitly publishes an old page, show, biography, recording or file. A replay/stale editor revision fails instead of overriding newer work.

Restoration reloads the selected revision from the database under the content lock, revalidates typed fields, references and the active staff capability, and commits the new draft/history/media references together. It does not trust hidden copies of old field data. Missing/damaged source media stops the restore without dropping references or partially changing the page. Repair the media through the existing backup process before retrying.

Older revisions did not record sort order. They retain the current order rather than inventing a historical value. New versions record it. Editing sort order on a private draft no longer reorders the published content prematurely. Historical selected images/releases remain in the editor's bounded pickers even when over 1000 newer items exist.

## Access and limits

Only active owners/content editors with content.manage can read or restore content history. Store managers/customer/anonymous access is denied. Save/restore rechecks the locked staff role and session version so a suspended or changed session cannot mutate content based solely on an earlier page check. CSRF, private cache headers and noindex/no-referrer protections remain.

No migrations are changed or added. Existing published URLs are not automatically redirected if an owner changes them; redirect management belongs to the separate metadata work. Public collections and unselected media/release pickers retain their existing 1000-entry bound; this checkpoint removes that cap from administrative content browsing, not every public collection. Revisions are retained, not automatically purged. Very large installations still need database sizing/backup planning.
