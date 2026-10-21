# One-time layout transition from rc.8 or earlier to the grouped layout

**Do not send rc.10 through rc.12 to the old Browser upgrades screen. It must reject this
package because the pinned routing and recovery foundation changed. Do not
weaken that guard, reinstall, create a replacement database, or delete uploads.**

The previously mentioned rc.9 working tree was not recoverable in the resumed
session. rc.10 is reconstructed from the independently verified rc.8 package and
its complete GitHub source. It is not a byte-identical reissue of rc.9. A site
already using the earlier rc.9 package requires a comparison of that actual
package before installation; compatibility is not assumed.

## Before the transition

1. In the existing site, finish or roll back any staged/applied update that is
   awaiting confirmation. No update may be active. Save the independent recovery
   receipt and a full cPanel backup of the site files, database and private
   `.spirit-*` runtime. Check that the backup can be opened.
2. Test the change on a staging copy first. Record the current root directory,
   URL, database identity, owner sign-in and scheduled task paths. On the live
   host, temporarily restrict visitor access in the hosting panel while files
   are being replaced; this is not an atomic live update.
3. Keep the installation at the **same absolute filesystem root** and public
   URL. The private runtime identity is derived from that root. Do not rename
   the website folder to perform the swap.

## Using cPanel File Manager

4. Extract the new Full ZIP into a temporary staging directory OUTSIDE the live
   public site. Confirm that the ten folders match DIRECTORY_LAYOUT.md and that
   `.htaccess` is present. Do not put the ZIP or private backup inside uploads.
5. Copy the new application files into the existing site root. Preserve the
   original account-private runtime/configuration, media, installed lock and
   `admin/upgrades/location.php`. Do not recreate `install/` when the old installer
   was already removed. No database migration is needed for rc.8 → rc.10.
6. After confirming the new public URLs and owner login work, remove only the
   obsolete DISTRIBUTED source files from the old root folders: account, band,
   booking, media, music, newsletter, pages, payments, press, shop, shows, tasks,
   views and database. Compare with the old package manifest first. Move any
   custom/unknown files to a private backup for review; never delete a directory
   recursively merely because its name is on this list. Remove empty folders.
7. Update cPanel cron paths to app/tasks/*.php. Source checkouts additionally
   move tests/tools under app and design under docs; those development folders
   are excluded from deployment packages.

## Acceptance before reopening

Verify the homepage, every main navigation link, Search, phone menu, booking
validation/submission, media delivery, account forms and any enabled checkout
or payment callbacks. Verify admin login, editing/publishing, Analytics and the
footer settings. Confirm the existing owner and data are retained and that all
assets load under the site's original root/subdirectory URL.

If anything fails, restore the complete pre-transition site FILES at the same
root and inspect the original private runtime. Do not attempt an old partial
code checkpoint against the new layout. Do not rewind the database or payment
state without a separately reviewed recovery plan; this release changes no SQL.
Keep the old update journal/receipts private and intact for audit. New updates
carry a separate layout identity so old checkpoints cannot be applied.

Actual A2/cPanel permissions, real mail, live payments, physical devices and
owner acceptance remain separate from automated test results.

## rc.11 and rc.12 continuation

The directory/recovery foundation in rc.11 and rc.12 is unchanged from published
rc.10. The transition above still applies to rc.8 and earlier installations.
An rc.10 or rc.11 site can use guarded same-layout, same-schema Browser upgrades
if all existing integrity checks pass. Keep the full pre-update backup and
independent recovery receipt. Neither release adds a SQL migration or requires
reinstalling. See TEST_RESULTS.md for the tested upgrade scope; actual A2
permissions and acceptance must be checked on the target host.
