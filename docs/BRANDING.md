# Site logo — 1.0.5

## Bundled Concept 2 identity

Version 1.0.5 uses the owner-selected Concept 2 crown-and-shield wordmark as the bundled public identity whenever no owner-published Media-library logo is active.

- The public header loads the transparent animated PNG (`images/spiritual-gorillaz-logo.png`).
- The footer uses the matching transparent static PNG (`images/spiritual-gorillaz-logo-static.png`).
- Visitors using `prefers-reduced-motion: reduce` receive the static PNG instead of the APNG.
- The animation changes only the metallic light, blue-jewel glow and small sparkles; the logo silhouette and transparent background remain fixed.
- Responsive sizing preserves the full crown, lettering and ornamental frame without cropping or stretching.
- The header retains centered navigation, the gold booking action and Search alongside the larger square identity.

## Owner-managed logo workflow

Open **Settings & access > Website settings > Manage site logo**. Upload approved artwork in the existing Media library, select its image, choose **Save private logo draft**, then **Preview saved logo**. The preview is the actual homepage with the saved logo in its header and footer; published page content stays unchanged.

Check the public-use approval box and choose **Publish approved logo** to display it publicly. **Withdraw published logo** restores the bundled Concept 2 identity while preserving the saved draft. No image is published merely by uploading, selecting or previewing it.

An owner-published logo always overrides both bundled Concept 2 assets. The whole uploaded image remains contained without arbitrary cropping or stretching, and the existing upload pipeline preserves PNG/WebP transparency.

## Accessibility and runtime behavior

Header and footer home links retain the configured website name as their accessible label. The static reduced-motion alternative is selected through the native `<picture>` element without JavaScript. Enhanced navigation can replace owner-managed logo markup without replacing the persistent audio node.

The APNG and static files are decorative within a named home link, so their image `alt` values remain empty. If either bundled file is unavailable, the branding renderer falls back safely to the remaining bundled asset or the configured text mark.

## Privacy and safe updates

The editor and saved-logo preview require an active owner. Preview is GET/HEAD-only, no-store, noindex and no-referrer. Save, publish and withdraw require CSRF protection and recheck the current locked owner/session, saved revision and image integrity. Missing, wrong-type or damaged media and stale forms are rejected before changing saved state.

Draft and published selections live in one versioned settings record. Only the published selection is added to public media authorization. Existing encrypted snapshots include that record and original media; restoration safeguards and the supported backup/recovery model remain unchanged.

## Package contents

The cumulative 1.0.5 package includes:

- `images/spiritual-gorillaz-logo.png` — website-ready transparent APNG;
- `images/spiritual-gorillaz-logo-static.png` — matching transparent static fallback;
- `assets/css/animated-logo.css` — responsive header/footer integration;
- the existing owner-managed branding editor, preview, publishing and withdrawal workflow.

The full-resolution promotional APNG is not shipped in the website package because it is substantially larger and is intended for video overlays or print/promotion work rather than page delivery.
