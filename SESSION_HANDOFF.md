# Resume — 1.1.0-rc.4 transparent crowned-gorilla favicon

Repository **afterburn25/Spirit**. Continue from **`work/1.1.0-analytics-and-navigation`**, [draft PR 37](https://github.com/afterburn25/Spirit/pull/37), retaining delivered rc.3 commit `c3584d348067d035ec14a739c9783b5880b95321` and all earlier analytics/artwork. Live main remains `6ef03a24ea87fdbfb0d053f7b54b4c16fce42098`. Do not merge before owner approval.

## Current user decision

The favicon must use the same transparent, dimensional gold crowned-gorilla head as the newly selected logo and appear as large as possible in the browser tab. Use the whole head/crown without lettering, a background tile or distorted proportions. Browser UI controls the slot; fit the visible artwork to 98% of a square instead of adding transparent padding.

## Implementation

- `design/favicon/sculpted-gold-gorilla-master.png`: original1254px transparent extracted master, SHA256 `3f3232cd3c314b3f227c1c598c8bf75f01c6b04b779cb6ece1bc7f51334f66bc`.
- `tools/build_favicons.py`: browser SVG-viewport rendering, alpha-bound fitting and ICO packaging. Python only orchestrates rendering, inspects pixels and packages bytes; it does not retouch the master.
- `images/icons/`: ready-to-use16/32/48/64/180/192/512px PNG assets and ICO16/32/48/64.
- `images/favicon.svg`: retained compatibility URL, now containing the same head.
- `views/favicon.php`: one shared versioned link set included by both actual headers. Preserve `app_url()` subfolder resolution.
- `design/favicon/`: source prompt, rendered icon-size comparison, export receipt and focused HTTP evidence. These are actual icon-rendering previews, not screenshots of browser chrome.

The prior animated horizontal-logo kit was delivered separately. This favicon change does not silently replace the site's bundled navbar logo or revise header spacing.

## Verification and delivery

See `docs/TEST_RESULTS.md` for scoped current verification. The focused PHP/static-file fixture exercised all 8 declared icons at `/` and `/band` with 16 successful HTTP requests, correct MIME types, exact bytes, dimensions, ICO frames, transparency and rc.4 cache keys. No database or production site was accessed. Actual small-size Chromium compositions were visually inspected on light/dark backgrounds.

Archive the exact package hash, source commit, comparison evidence and observed GitHub outcomes in the final delivery receipt. Never count pending checks as passed. Delivered rc.3 final evidence is preserved under `docs/history/VERIFICATION-1.1.0-rc.3.json` and its earlier source checkpoint under `TEST_RESULTS-1.1.0-rc.3.md`.

Build a new immutable cumulative Full ZIP with `tools/package.py`, using the existing Pillow 12.3.0 hero generator. Keep the 15 packaging tests and deterministic release gate. Ship the favicon set separately for convenience and the actual-size comparison screenshot. Never overwrite older delivered archives or introduce a new installation requirement.

## Preserved scope

No analytics, collection, navigation, payment, installer, database migration or pinned recovery/access code changes are required for this favicon update. Installed rc.1/rc.2/rc.3 use normal same-schema Browser upgrades when checks pass. For1.0.x retain the existing additive schema-update instructions. Preserve private configuration, encryption secret, database/owner, media and removed installer; do not reinstall or bypass safeguards.

Actual A2/cPanel, physical-device and merchant/provider acceptance remain separate. No native tickets, hosting token, setup code or automatic database provisioning.
