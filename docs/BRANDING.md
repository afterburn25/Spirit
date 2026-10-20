# Site logo — 1.1.0-rc.6

## Approved sculpted-gold identity

The bundled identity is the approved transparent crowned gorilla beside the horizontal two-line SPIRITUAL GORILLAZ wordmark. It replaces the old active Concept 2 square logo. The separately delivered artwork kit had not been integrated into rc.5; rc.6 installs its exact approved files.

| Surface | Artwork and behavior |
|---|---|
| Public header | Steady six-second shimmer WebP; static PNG for reduced motion or unsupported WebP. |
| Public footer | Matching static PNG, loaded lazily. |
| Administration sidebar | Same shimmer/static selection, contained within the sidebar. |
| Staff login and installer | Same identity from a database-independent helper, safe before setup. |
| Branding editor default previews | Static approved identity when no custom media image is selected. |

`Branding::bundledMark()` provides the bundled markup. The public header/footer and enhanced-navigation template use the existing `Branding::mark()` path, which still prefers a published owner image. Asset URLs use the configured installation prefix and release version. No setting or media record is rewritten to install the new default.

The public horizontal header mark expands within its existing track, up to 260px, while retaining its left anchor. Phone sizing reserves room for Menu and Search. The navigation background is 25% transparent (75% opaque); logo artwork, text and controls remain fully opaque. Images are contained without cropping or stretching. The footer mark uses a 240 × 84px box; its surface extends across the viewport while its content stays aligned.

## Owner-managed logo workflow

Open **Settings & access > Website settings > Manage site logo**. Upload approved artwork in the existing Media library, select its image, choose **Save private logo draft**, then **Preview saved logo**. The preview is the actual homepage with the saved logo in its header and footer.

Check the public-use approval box and choose **Publish approved logo** to display it publicly. **Withdraw published logo** restores the approved bundled identity while preserving the saved draft. Uploading, selecting or previewing an image does not publish it.

An owner-published logo overrides the public bundled artwork. The complete upload remains contained at its natural proportions, and the existing pipeline preserves PNG/WebP transparency. The fixed staff/installer identity does not depend on private media or an installed database.

## Accessibility and runtime behavior

Header and footer home links retain the configured website name as their accessible label. Staff links have explicit home/administration labels. The decorative image has empty alternative text inside the named link.

A native `picture` source selects the animated WebP only for `prefers-reduced-motion: no-preference`. The underlying image is the static PNG, so reduced motion and unsupported WebP need no JavaScript. The footer is static. Enhanced navigation can refresh owner-managed branding without replacing the persistent audio node.

If the static bundled asset is missing or is a symlink, the helper returns the existing safe text fallback. If only the animated asset is unavailable, it uses the static original. The helper does not query the database.

## Privacy and safe updates

The editor and saved-logo preview still require an active owner. Preview remains GET/HEAD-only, no-store, noindex and no-referrer. Save, publish and withdraw retain CSRF protection, current locked owner/session checks, revision checks and image-integrity checks. Missing, wrong-type or damaged media and stale forms are rejected before saved state changes.

Draft and published selections remain in one versioned settings record. Only the published selection is added to public media authorization. Existing encrypted snapshots include that record and original media; restoration safeguards and supported backup/recovery behavior are unchanged.

## Shipped assets

| File | Dimensions | SHA-256 |
|---|---:|---|
| `images/branding/spiritual-gorillaz-sculpted-gold.png` | 2172 × 724 | `03e4f5ad93d72a8c3ad66ecabae817c09239ce767c02b9f1230447a9b2dc58de` |
| `images/branding/spiritual-gorillaz-sculpted-gold-nav.webp` | 384 × 132 | `11f021d1e1a04f0261fed84c3ab5baf9e5b34bc227729308a2d4c3e227facd70` |

Both are byte-identical to the approved kit originals. The larger promotional animation is not required by the website. Old PNG/static PNG/SVG paths remain in the cumulative package for compatibility, but active application templates no longer reference them.

Public styling is included in the reproducible `assets/css/site.css` bundle. Shared staff/setup styling is in versioned `assets/css/admin-branding.css`. The PHP host requires no image conversion or frontend build. See `TEST_RESULTS.md` and the delivery receipt for current installed-browser observations.
