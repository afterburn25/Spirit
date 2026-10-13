# Owner guide — current development modules

Sign in with the owner account created by the browser installer. There is no public administrator registration and no default password. Before editing content on an older foundation installation, open **Database updates** and apply the additive schema after taking a hosting-panel backup.

## Content workflow

Choose Pages, Music releases, Audio tracks, Shows, Band members, Gallery, Videos or Press downloads. Add the title and structured fields. **Save private draft** does not change the published version. **Preview saved draft** opens an owner-only view with no indexing or caching. **Publish approved content** updates the public snapshot and makes its referenced media available. **Remove from public site** keeps the draft but unpublishes its public page and media references.

Another session's edit will cause a conflict rather than silently overwrite data. Reload and reconcile your changes. A revision history is stored; restore/archive controls are not available yet.

## Media and pages

Upload permitted JPEG/PNG/WebP, MP3/OGG/WAV or PDF files through Media library. Confirm that the band has usage rights. Images are decoded/re-encoded; SVG/scripts/archives are refused. Uploaded media remains private until a published item uses it. PDF documents are delivered as downloads. Only the owner can access otherwise-private media. Supply meaningful image alternative text.

Reserved page URL names: `home`, `band`, `booking`, `press`. Band and press text appears in their corresponding designed sections. Generic pages have their own URL. The home headline composition remains locked; website name/tagline/welcome are controlled under Website settings. Hero artwork can be selected on the home content record.

## Music and shows

Create a release, upload authorized audio, then create tracks attached to that release. Publish both. The public player is user-initiated and remains active during supported internal public-page navigation. External platforms use verified outbound links; uninterrupted playback across unrelated platforms is not promised.

For a show, enter full local date/time, named timezone, venue, city and optional address. Paste the venue's HTTPS ticket-seller URL. Scheduled shows with a URL display Get Tickets. Missing links, sold-out, free, postponed or cancelled events have appropriate non-purchasing labels. Past shows archive automatically. Do not use sample concept dates as actual bookings.

## Not operational yet

The booking page does not yet submit requests. Newsletter, customer accounts, inventory/orders and payment processing are not active in this checkpoint. The store explicitly states that no payments are collected. Full backup/restore/ZIP-upgrade tooling and delegated staff management remain later milestones.
