# Site logo — 1.0.0-rc.5

Open **Settings & access > Website settings > Manage site logo**. Upload approved artwork in the existing Media library, select its image, choose **Save private logo draft**, then **Preview saved logo**. The preview is the actual homepage with the saved logo in its header and footer; published page content stays unchanged.

Check the public-use approval box and choose **Publish approved logo** to display it publicly. **Withdraw published logo** restores the existing text wordmark and preserves the saved draft. Publishing an empty selection explicitly returns to the text mark. No image is published merely by uploading, selecting or previewing it.

## Presentation

The whole image is contained without cropping or stretching. The existing upload pipeline preserves PNG/WebP transparency. Header and footer home links keep the configured website name as their accessible label; compact responsive image variants are used where appropriate. Native pages work without JavaScript. Enhanced navigation updates the logo without replacing the persistent audio node. The administration sidebar logo and favicon are not changed by this feature.

## Privacy and safe updates

The editor and saved-logo preview require an active owner. Preview is GET/HEAD-only, no-store, noindex and no-referrer. Save/publish/withdraw require CSRF and recheck the current locked owner/session, saved revision and image integrity. Missing, wrong-type or damaged media and stale forms are rejected before changing saved state.

Draft and published selections live in one versioned settings record. Only the published selection is added to public media authorization. A withdrawn logo still used by another published page or product remains public through that separate reference; otherwise its original and derivatives are withdrawn. Media-library staff permissions remain unchanged.

Existing encrypted snapshots include the settings record and original media. Each form also carries the most recent preserved backup-restore audit marker. Restoration rejects old forms even when the numeric revision matches recovered data. Restoring a snapshot from before this feature returns to the text wordmark. The backup engine and its same-instance safeguards remain unchanged; no migration or second upload system is introduced.

## What this release does not supply

This feature does not create or approve a production logo, replace the selected visual design, or change hosting/payment settings. It also corrects outdated Website settings copy about a holding page and future content tools.

The earlier generated release infographics are not screenshots, code changes or verification reports. They are excluded from the repository and package. RC5 does not add their unrelated recurring shows, bulk inventory actions, a new page builder, editable email templates or other pictured features. Approved crowned-gorilla master artwork and actual band content remain outstanding.

## Verification

Tested b16e9780 passed full 38681481396, focused 38681481344, storefront 38681481322 and logo 38681481271. Each of four logo jobs reports 109 SQL/HTTP and 179 browser checks, including snapshot restoration, media visibility and continued playback. See TEST_RESULTS.md for precise environments, artifact hashes and limitations. Final 1.0 requires actual host/provider/content/device/owner acceptance.

## Bundled animated identity

When no owner-published Media-library logo is active, the site uses the approved Concept 2 transparent crown-and-shield logo. The header loads the transparent APNG, while the footer and visitors using `prefers-reduced-motion: reduce` receive the matching static PNG. Owner-published logos still override the bundled identity without cropping or stretching.
