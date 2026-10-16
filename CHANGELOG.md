# Changelog

## 1.0.0-rc.9 — dormant-store customer navigation fix

RC9 fixes one real post-RC8 runtime inconsistency discovered during live acceptance: customer account pages were hard-coded to present themselves as shop pages and always advertised Merchandise / Your bag even when the storefront was disabled.

Changes:
- customer/account views no longer force the global navigation into the shop-active state;
- customer-local Merchandise and Your bag links render only while the storefront is enabled;
- closed-store account pages use non-commerce wording and no longer advertise guest checkout;
- unavailable registration/recovery routes return to sign-in instead of sending visitors to a closed merchandise catalog;
- customer sign-in/account access remains available with a dormant storefront;
- browser coverage now enforces both closed-store and enabled-store customer-navigation and copy behavior.

RC8 remains frozen as the all-green historical candidate. RC9 is a genuine new release candidate because this changes public runtime behavior.


## 1.0.0-rc.8 — software-complete candidate

RC8 is a genuine new release candidate because runtime and verification behavior changed after the immutable RC7 delivery. It includes final launch-copy fallbacks, confirmed-lineup reconciliation, the scalable bundled logo, contained fallback hero, dormant-store navigation/diagnostics, checkout validation hardening, scheduled-worker heartbeat evidence, expanded 360 px/cross-browser coverage, and the deterministic software release gate.

RC7 remains immutable and historical. Do not replace or relabel an RC7 archive with RC8 bytes. No provider acceptance, deployment, CMS publication, or owner approval is inferred by this version bump.


## RC7 software-completion pass — application remains RC7

Complete the remaining code-side launch work without inventing external evidence or optional content.

- Wire approved launch copy into runtime fallbacks and metadata.
- Preserve the confirmed Chad Lindsey / Ron Green / Charlie Savell lineup even when member profiles are partially or fully unpublished.
- Add a scalable bundled crown/wordmark fallback while preserving owner-managed logo publication.
- Present the small bundled hero as contained artwork instead of upscaling/cropping it like a production photograph.
- Harden public/store/checkout/booking layouts at 360 px and extend cross-browser checkout/cancellation review to Chromium, Firefox and WebKit.
- Align separate-billing browser validation with server validation.
- Hide Merch/Bag globally while the storefront is intentionally closed.
- Distinguish dormant commerce from real activation blockers in Launch Checks.
- Record timestamp-only successful-run heartbeats for the actual mail/payment CLI workers in protected private runtime cache and expose freshness in diagnostics.
- Add direct integration coverage for the real task scripts.
- Add `tools/release_gate.py` plus a fast GitHub workflow; the gate validates required source/package files, package manifest hashes, private/development exclusions, responsive coverage and final-version blockers.
- Keep payments/provider calls, schema, installer contract, external-ticket model and immutable RC7 archive unchanged.

No genuine provider acceptance, deployment, CMS publication or owner approval is inferred.

## Final checkout validation alignment — application remains RC7

Align the public checkout form with existing server-side billing-address validation. When “billing address matches shipping” is cleared, separate billing fields are immediately marked required and the billing disclosure opens; billing city/state/ZIP expose appropriate browser autocomplete hints.

## Final launch runtime copy alignment — application remains RC7

Wire approved launch wording into public fallbacks for Home, Music, Shows, Band, Media, Booking, Press and Merch while preserving CMS overrides and truthful empty states.

## Previous history

Earlier detailed checkpoints and release history remain in `docs/history/`, `docs/acceptance/` and prior commits.
