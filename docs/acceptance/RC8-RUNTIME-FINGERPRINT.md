# RC8 runtime fingerprint / stale-cache diagnostic

Use this only because the owner confirmed RC8 is installed while the public Band page is still rendering the pre-RC8 generic member-empty-state.

Do **not** reinstall and do **not** edit the database during this diagnostic.

## 1. Verify the installed files in A2 File Manager

Open the public site document root and inspect these exact files.

### VERSION

File:
`VERSION`

Expected exact contents:
`1.0.0-rc.8`

### Band runtime

File:
`views/public/sections.php`

Search inside the file for:
`member-card-factual`

The RC8 file must contain that class and the confirmed names:
- Chad Lindsey
- Ron Green
- Charlie Savell

If the installed file does not contain these, the RC8 code replacement did not fully land.

### Branding runtime

File:
`app/Branding.php`

Search for:
`bundledMark`

RC8 must contain the bundled scalable-logo fallback method.

Also confirm this file exists:
`images/spiritual-gorillaz-logo.svg`

## 2. Interpret the result

### A. All RC8 fingerprints are present on disk

If VERSION is RC8, sections.php contains `member-card-factual`, Branding.php contains `bundledMark`, and the SVG exists—but the public Band page still shows:

`Approved member profiles and portraits will appear here. Concept identities are not presented as real musicians.`

then old PHP/HTML is being served after the file replacement.

Likely causes:
- PHP OPcache still holding pre-upgrade bytecode;
- LiteSpeed/Turbo/server page cache;
- upstream/CDN cache if one is enabled.

Next action:
1. Purge any A2/LiteSpeed/Turbo page cache available in the hosting panel.
2. Restart/reset the site's PHP worker/OPcache using the hosting panel if that control is available.
3. If there is no cache/PHP reset control on the plan, ask A2 support to clear LiteSpeed cache and PHP OPcache for the domain after an in-place application upgrade.
4. Recheck the Band page in a private/incognito browser.

Do not change PHP versions merely to force a restart unless A2 specifically recommends it.

### B. VERSION says RC8 but one or more RC8 runtime fingerprints are missing

This means the installation is internally inconsistent.

Do not publish more content and do not manually copy random individual application files into place.

Use the Browser upgrades/recovery history and the saved PRIVATE recovery receipt to determine whether the previous RC7 -> RC8 apply/confirm cycle completed cleanly.

Because the updater rejects same-version replacement packages, do not try to force-upload another RC8 over an already-recorded RC8 state.

Preferred recovery path:
- use the supported recovery/rollback workflow to return to the verified previous code state if the installation is incomplete;
- then stage/apply the verified RC8 package again through the normal upgrade flow.

### C. VERSION is not RC8

The live installation did not actually complete the RC8 upgrade. Use the normal Browser upgrades & recovery workflow with the verified RC8 package.

## 3. Only after the runtime fingerprint is correct

Then align saved CMS page content and store settings using:

`docs/acceptance/RC8-POST-UPGRADE-CONTENT-ALIGNMENT.md`

The old Home/Music/Booking/Press text can legitimately persist from published CMS records.

The old generic Band member empty-state cannot come from a current RC8 `views/public/sections.php`; that is the key runtime fingerprint.
