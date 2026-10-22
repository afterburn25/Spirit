# Site logo — 1.1.0-rc.8

## Approved sculpted-gold identity

The approved transparent crowned gorilla sits beside the horizontal two-line SPIRITUAL GORILLAZ wordmark. The original PNG is unchanged from the approved kit. Recovery of the final transparent-image record found the same 2172 × 724 dimensions and 1,500,566-byte size as the kit master; no different later approved image was established. The actual display defects were a legacy published-media override, a weak animation with long holds, and an explicitly static footer.

The current helper renders the native PNG inside a CSS motion wrapper. A metallic light sweep repeats every 3.8 seconds, while a gentle depth movement takes four seconds in each direction. Header, footer, admin sidebar, staff login, installer and default Branding previews share this treatment. The footer image loads lazily. Reduced motion, print and forced colors keep the artwork still and omit its light overlay.

The image and its silhouette mask use the same immutable asset URL. Their 3:1 boxes match, keeping the light on the artwork instead of its transparent surrounding canvas. No low-resolution animation image, video, extra JavaScript, image conversion or host-side build is needed. The earlier WebP remains in the cumulative package for path compatibility.

| File | Native dimensions | SHA-256 |
|---|---:|---|
| images/branding/spiritual-gorillaz-sculpted-gold.png | 2172 × 724 | 03e4f5ad93d72a8c3ad66ecabae817c09239ce767c02b9f1230447a9b2dc58de |
| images/branding/spiritual-gorillaz-sculpted-gold-nav.webp — retained compatibility asset | 384 × 132 | 11f021d1e1a04f0261fed84c3ab5baf9e5b34bc227729308a2d4c3e227facd70 |

The public header keeps its existing left anchor and available first grid track, up to 260px. Phone sizing reserves room for Menu and Search. The compact footer uses a 168 × 56px mark. Transparent pixels remain transparent. Navigation/footer backplates are 75% opaque; artwork and text have opacity 1.

## Explicit source and retained owner media

The user's latest correction explicitly requests the approved 3D logo in place of the older displayed mark. An absent site_branding_source_v1 setting therefore means bundled artwork. Reading a legacy installation does not insert, rewrite or delete any setting.

The existing site_branding_v1 JSON remains exactly the same three-integer format: revision, draft_logo_id and published_logo_id. Its values and original media are retained. A deliberate custom publication selects the separate custom source. The source setting accepts only bundled or custom.

Open **Settings & access → Website settings → Manage site logo**. The editor identifies the current source and provides:

- **Use animated 3D logo**: select the approved bundled identity while preserving both retained media IDs and all uploads.
- **Save private logo draft**: update the draft without changing the active source.
- **Preview saved logo**: show the saved draft on the actual homepage, privately.
- **Publish approved logo**: require the approval checkbox, validate the chosen image and explicitly activate custom artwork.
- **Withdraw published logo**: retain its existing behavior of clearing the published selection, preserving the draft and selecting bundled artwork.

Publishing an empty selection also restores the bundled source. An owner can deliberately republish a previously retained upload. No private image becomes public merely because its old ID still exists.

## Security, restoration and navigation

Every source change uses the existing active-owner/session check, CSRF boundary, revision check, restore epoch and locked transaction. The state and source update atomically, with an audit entry. Saving or publishing still rejects missing, wrong-type or damaged media. Selecting the bundled source does not require opening or deleting old media.

Branding-specific public media authorization requires the custom source and its matching published ID. A media item may remain public for another legitimate published-content reference. Draft previews still require an owner and remain no-store, noindex, no-referrer, GET/HEAD-only.

Supported encrypted snapshots retain settings and media through the existing snapshot mechanism. Restoration checks prevent pre-restore forms from overwriting recovered branding. Enhanced navigation refreshes the authorized logo template without replacing the persistent audio node.

The database-independent bundled helper also works before installation. A missing or symlinked PNG returns the existing text fallback. Named home/admin links retain their accessible labels and contain decorative images with empty alt text.

Current installed results, original-image hashes and natural-speed recordings are documented in TEST_RESULTS.md and the delivery receipt. Source CSS is assets/css/site-source.css; the compiled public bundle is reproducible. Shared staff/setup motion is in assets/css/admin-branding.css.
