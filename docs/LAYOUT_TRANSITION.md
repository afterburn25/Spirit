# One-time layout transition from rc.8 or earlier to the grouped layout

**Do not send a grouped package from rc.10 through final 1.1.0 to an rc.8-or-earlier Browser upgrades screen. It must reject this
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
   was already removed. No database migration is needed for rc.8 → final 1.1.0.
   A 1.0.x installation still needs the additive analytics migrations described
   in INSTALLATION.md and ANALYTICS.md.
6. After confirming the new public URLs and owner login work **from a refreshed
   PHP worker/opcode cache**, remove only the obsolete DISTRIBUTED source files
   from the old root folders: account, band,
   booking, media, music, newsletter, pages, payments, press, shop, shows, tasks,
   views and database. Compare with the old package manifest first. Move any
   custom/unknown files to a private backup for review; never delete a directory
   recursively merely because its name is on this list. Remove empty folders.

   **PHP worker safety:** The old worker can temporarily execute cached
   `app/View.php` bytecode that still requires the old root `views/header.php`,
   even after the new grouped files were copied. Leave legacy distributed
   views in place until the host has refreshed its PHP workers/opcode cache
   **and** the new dashboard and other routes work. Test a PHP worker recycle
   on staging; for cPanel/LiteSpeed/PHP-FPM arrange a supported host cache
   refresh/reload or wait for confirmed normal revalidation. Do not delete old
   view files early, change private runtime paths, bypass recovery pins, or
   force a reinstall to address stale compiled PHP. If the new worker cannot
   serve the site, stop cleanup and restore from the verified backup.
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

Actual A2/cPanel permissions, new provider observations and physical-device
review remain separate from automated test results. Preserve the owner's
previously accepted installation/mail/account results in
acceptance/CONSOLIDATED-FINAL-PASS.md; this transition does not reset them.
Commerce stays disabled until its separate activation checks are completed.

## Same-layout updates from rc.10, rc.11 and rc.12 to 1.1.0

The directory/recovery foundation in final 1.1.0 is unchanged from published
rc.10 and retained by rc.11/rc.12. The panel transition above applies to rc.8 and
earlier installations. An rc.10, rc.11 or rc.12 site can use guarded same-layout,
same-schema Browser upgrades when all existing integrity checks pass. Keep the
full pre-update backup and independent recovery receipt. Final 1.1.0 adds no
SQL migration and requires no reinstall. See TEST_RESULTS.md for the exact
tested upgrade scope; local or CI results do not establish a completed update
on the target A2 host.
