# Continue the RC5 logo workflow

Repository **afterburn25/Spirit**, main authoritative. Branch **work/site-branding**, based on main49d5bbbbab146e7833dced698af51458ec625ddc / tree6cf6f3d2b8abbddfb64e98c9c0c2720ada286b05. Inspect live refs before writes, preserve concurrent work and never force-push. Read AGENTS.md, DESIGN_LOCK.md, status and test records.

## Current implementation

app/Branding.php provides a versioned settings record with separate draft/published logo IDs. admin/branding offers owner-only editor and real-homepage preview. Save/publish/withdraw recheck the locked owner and session, CSRF, revision and image integrity. Preview is GET/HEAD-only and private/no-store/noindex/no-referrer. Public Media::serve permits only the published branding image in addition to its existing typed references.

Header/footer use contained responsive images and accessible named home links. Header includes an inert logo template; assets/js/branding.js clones its already authorized link on the existing spirit:navigated event without fetching HTML or touching the audio node. Original assets/js/public.js is unchanged. Website settings has a management link and corrected outdated copy. No new migration, upload system or navigation category.

Forms carry the last backup-restore audit epoch. Backups preserve audit, so a restore cannot revalidate an old form even if the numeric branding revision rolls back. Existing Backups.php and all recovery logic remain byte-identical. New SQL tests exercise both current-logo and pre-branding snapshot restoration.

## Verification required

Local 228 syntax files, 781 pure assertions, seven JavaScript syntax checks, Python compilation and 12 packaging tests passed. Local PHP lacks PDO MySQL/GD/cURL/ZIP. Exact new CI is pending: run Site logo workflow verification plus every unchanged full, focused and three-engine storefront suite. New tests are branding_unit.php, branding_integration.py and branding_browser.py. Do not claim old RC4 results prove new code, skip tests or fabricate acceptance. Inspect actual new renders before packaging.

Three image-generation attempts returned erroneous release graphics instead of master artwork. None is a real screenshot, commit or feature report; none is included in the source or package. Real crowned-gorilla production master artwork is still outstanding. Do not implement or claim the unrelated features shown in those graphics.

After successful CI, finalize status/roadmap/test records, verify canonical source and all cumulative payload hashes, and deliver a new immutable RC5 Full ZIP. Do not overwrite RC4. Keep all prior paths, six migrations and five pinned access/recovery files.

## Fixed scope

Preserve the approved navy/gold/blue design, external venue ticket sellers, grouped remembered admin navigation, and manual one-page A2/cPanel installer. No setup code, token, 20i or automatic DB. Existing sites upgrade with backups, never reinstall or replace their original private config/encryption secret/database/media and recovery keys. No host/provider operation or production publication occurs here. Approved master artwork/content/policies, real A2/mailbox/merchant/recovery/device checks and final owner acceptance remain.
