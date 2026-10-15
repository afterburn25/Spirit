# Project status — 1.0.0-rc.3 preparation

Preparing managed section artwork/copy and actual-layout private previews from existing Page records. Current branch target: **work/section-content**. Exact Actions verification pending. Baseline main7321f23435936087fbc24e099d612a92b38189d8 passed exact full38662019528.

## Implemented

Eight placement cards under Pages & homepage find existing records by exact draft/published URL name, without the general listing's1000-row ceiling. New preparation opens an unsaved prefilled editor. Conflicting records are shown for review rather than merged. No GET publishes or creates content.

Home/Music/Shows/Band/Media/Booking/Press/Merchandise use the corresponding published Page artwork and text in their approved layouts. The Page alias opens the same actual section. Private preview substitutes only its Page draft; related content remains published-only. Booking preview disables the form without consuming public feedback. Preview accepts GET/HEAD only, keeps staff authorization and private response/media rules.

Image inspector shows actual dimensions, alternative text and source-width guidance. Adequate pixel width is not approval or a quality guarantee; a small image is warned about, not silently upscaled. Defaults remain when no replacement is published. Static/dynamic sitemap handling avoids duplicated designed Page routes and honors publication/exclusion.

## Verification

Local223 syntax checks,747 distinct pure assertions and12 packaging tests passed. New SQL/HTTP tests cover each placement's private save, actual preview, publish, alias, replacement draft, history restore, unpublish, private media, conflicting records, large libraries and role boundaries. Three-engine browser tests cover actual editor/preview/public layouts at1440/390/320, root/subfolder and native preview fallback. Exact new full/focused/storefront results remain pending; retained suites are mandatory.

## Preserved and pending

No changes to RC2 grouped-nav behavior, one-page manual A2 installer, six migrations, five pinned recovery/access files, payment mode or hosting settings. Keep all earlier payload paths and private configuration/encryption secret/data/media/recovery keys. No20i/token/setup code/automatic database. Existing sites upgrade, never reinstall.

Approved production masters and genuine band/store content, actual A2/mailbox/genuine merchant sandbox/recovery, populated real-device quality and final owner approval remain. This implements the editing/preview path; it does not claim new production artwork or final1.0 acceptance. See docs/SECTION_PAGES.md.
