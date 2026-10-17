# Project status — 1.0.1 concept rebuild upgrade

The application is **1.0.1**. This is the supported in-place visual upgrade for the installed 1.0.0 release.

## Software implementation status

**Implementation scope: complete.**

The public site, owner administration, content/media/music/shows/press, booking/email/newsletter, customer accounts, merchandise/orders/payments, branding, backups, guarded upgrades/recovery, diagnostics and shared-host installer are implemented.

The 1.0.1 update adds:
- native-resolution Home, Music, Shows, Band, Booking and Merch artwork aligned to the approved concept;
- strict title containment at 1440 px, tablet widths, 390 px and 360 px;
- release-version cache busting for bundled CSS, JavaScript, logo and image assets;
- a genuinely newer cumulative package that an installed 1.0.0 site can stage and install;
- preserved database content, settings, media, bookings, customers, orders, backups and private configuration;
- full browser-upgrade/recovery verification.

The complete verification matrix for the concept-rebuild branch covers PHP 8.4/MySQL, PHP 8.2/MariaDB, Apache HTTPS access rules, desktop/mobile public rendering, booking, commerce, customer accounts, staff permissions, backup/restore and ZIP upgrade/recovery.

## Launch-safe content behavior

The confirmed roster and factual Band/Press copy are available without requiring invented content. Published CMS records may still supply owner-approved copy and media.

Truthfully empty optional sections are supported:
- Shows may remain empty until a genuine event exists.
- Part 3 may remain unpublished until its real YouTube/Vimeo URL and exact public metadata are confirmed.
- Member portraits may remain unset; factual non-clickable fallback cards are used for unpublished profiles.
- Merchandise may remain closed. When closed, Merch/Bag and merchandise search affordances are hidden.
- Longer biographies are optional.

## Deployment decision

Existing A2/cPanel installations must use **System & maintenance → Browser upgrades** and upload the cumulative `Spiritual-Gorillaz-1.0.1-Full.zip`. Do not rerun the installer, replace the database, or use the obsolete same-version 1.0.0 concept package.

The upgrade remains cumulative and uses the existing staging, private recovery receipt, install, verification and rollback workflow.

## Remaining blockers

None.

## Operational follow-ups

- After applying 1.0.1, confirm the administration version reads 1.0.1.
- Open the public site in a normal tab; versioned asset URLs force the new CSS and artwork to load without relying on a hard refresh.
- Review Launch Checks after deployment and address any real warning that appears.
- Observe scheduled-worker heartbeat freshness during normal operation.
- Complete genuine Authorize.net sandbox/provider acceptance before enabling commerce.
- Publish only genuine owner-approved optional shows, Part 3 metadata, portraits or merchandise.
