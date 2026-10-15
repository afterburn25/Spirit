# Site logo workflow — RC5 candidate

Open **Settings & access > Website settings > Manage site logo**. Upload authorized artwork through the existing Media library, select its logo image, save a private logo draft, and open **Preview saved logo**. The preview is the real homepage with its saved logo in the header and footer; the band's published page content is unchanged. The actual graphic must still be approved. This workflow does not generate a logo, replace the approved design, or certify image quality.

**Publish approved logo** requires an explicit public-use confirmation. **Withdraw published logo** restores the existing text wordmark while keeping the saved draft. Selecting no file and publishing explicitly clears the image selection. A draft-only file stays unavailable anonymously unless a separately published page or product references it. Only a published logo is added to the media endpoint's public-image authorization. Owner/editor/store-manager media-library rules otherwise remain unchanged.

## Presentation and operation

The entire image is contained rather than cropped or stretched; PNG/WebP transparency is retained by the existing upload pipeline. Header and footer home links retain the configured website name as their accessible label. Small responsive variants serve the compact wordmark instead of loading an unnecessarily large original. Native pages work without JavaScript. Enhanced public navigation refreshes both logo locations without replacing the persistent audio node.

Private preview is owner-only, GET/HEAD-only, no-store/noindex/no-referrer. Saving, publishing and withdrawal are CSRF protected and verify the current locked owner role/session before committing. Invalid, missing, wrong-type or damaged image selections fail before changing saved state. Revision checks reject stale forms. Draft/published selections are separate values in one versioned settings record; no migration or second upload system is added. Merely opening the editor or preview never creates a row.

Existing encrypted snapshots include this settings record and the original media. The editor also sends the latest restore audit epoch, read from audit entries that supported restoration does not rewind. After any supported restore, an old form is rejected even if its numeric revision matches the recovered state. A pre-branding snapshot returns to the text wordmark. The backup engine itself is unchanged. The same-schema updater and same-instance restore limits are unchanged.

## Exact scope and rejected generated graphics

This candidate adds the logo draft/preview/publication workflow and corrects outdated Website settings descriptions that still referred to a holding page and future CMS controls. It does not add recurring shows, bulk inventory actions, a new page builder, editable email templates, social integrations, or other features pictured in the erroneous generated release graphics from this conversation. Those graphics are not real screenshots, commits, release reports or testing evidence and are excluded from all repository/package assets. Production crowned-gorilla master artwork is still outstanding.

## Verification state

Local 228 PHP/inc syntax checks, 781 distinct pure assertions (747 retained plus 34 branding checks), seven JavaScript syntax checks and 12 packaging tests pass. New SQL/HTTP tests cover owner permissions, private/published media, safe replacement/withdrawal, stale edits, real snapshot restoration and old-backup behavior. New Chromium/Firefox/WebKit tests exercise desktop/mobile, root/subfolder, native previews and persistent playback while branding changes. Exact new Actions results are pending and must pass before promotion. No live hosting setting, real email, merchant transaction or production content is changed.
