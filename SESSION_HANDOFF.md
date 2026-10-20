# Resume — 1.1.0-rc.8 visible motion and compact footer

Repository: afterburn25/Spirit. Continue on work/1.1.0-analytics-and-navigation, [draft PR 37](https://github.com/afterburn25/Spirit/pull/37). Baseline published rc.7 is 8eb75d7e8073a72bdc4b1fd8ca432408711ee3ea, tree 0f3531236f3f46da56198472244645c095c8012d. Main inspected before writes: 6ef03a24ea87fdbfb0d053f7b54b4c16fce42098. Do not merge or deploy live without owner approval.

## Latest user requirements

Correct the apparently old/nonanimated logo, use the approved 3D crowned gorilla, make the background smoke visibly drift, remove the footer Music. People. Purpose. tagline, minimize footer height while fitting all links, make the footer background 25% transparent, add owner copyright editing in the right admin area, and render a fixed linked Cajun Web Pro LLC developer credit below copyright.

## Runtime decisions

- Use the unchanged 2172 × 724 approved PNG and matching alpha mask. Public/header/footer/admin/login/setup share a 3.8-second metallic light sweep and four-second alternating depth movement. Reduced motion, print and forced colors stop it. The old animated WebP stays packaged but inactive.
- The image recovered from the final transparency prompt matches kit dimensions/byte count. No later different approved image was established. Do not claim a newly recovered different master or a production database observation.
- Keep the original three-integer site_branding_v1 record. A separate site_branding_source_v1 is absent=bundled, or explicitly bundled/custom. The requested corrected default preserves old IDs/uploads without GET writes. Use animated 3D logo preserves both IDs; deliberate custom Publish activates custom; legacy Withdraw still clears only the published ID. Active-source media authorization, revision/restore/CSRF/owner transaction checks remain.
- Smoke is one fixed pointer-transparent CSS layer over the fixed stage: six brighter wisps, 18 seconds each direction, -4-second native start delay, 12% overscan and wider travel. No extra image/video/JavaScript. Omit for reduced motion, print and forced colors; exclude admin/setup.
- Footer has full-width rgba(2,7,12,.75) backplate, 168 × 56px logo, 10px vertical padding, wrapping links and no height floor. Phone links retain 44px targets. Both footer taglines are removed; preserve the tagline elsewhere.
- Copyright is optional plain text, max 240 UTF-8 bytes, under Settings & access → Website settings. Blank follows current UTC year/site name. Missing old POST fields preserve it. Validate before writing and escape output.
- The fixed line beneath is Website designed & developed by Cajun Web Pro LLC, with the company name linked to https://cajunwebpro.com/. It has no admin edit/hide control. Do not claim server-source owners cannot edit a template.

Runtime changes are limited to VERSION; app/Branding.php; admin/branding/index.php; views/admin/branding.php; app/Settings.php; admin/settings.php; views/public/footer.php; assets/css/admin-branding.css; assets/css/site-source.css; assets/css/site.css. Shared staff CSS mirrors public motion. Current public bundle: 129735/130000 bytes. Preserve the budget.

## Verification and delivery

Use one frozen candidate for actual root and /band installed checks. Cover natural page-entry logo/smoke motion, same-aspect mask/image geometry, compact footer at desktop/phone widths, maximum copyright/all optional links, admin save/reload, role/CSRF guards, private drafts, explicit source switching, public media authorization and supported restore.

Record genuine screenshots, natural-speed MP4s, hashes and fixture labels. No animation.currentTime seeking or accelerated playback in user-facing previews. Pixel changes alone are insufficient visual acceptance. Any cropped logo frame must be labeled as a detail from the real page.

Current results: 1,131 browser + 88 copyright HTTP + 142 branding-source HTTP checks passed, plus 6 separately recorded capture checks, 48 branding units, 19 copyright units and 15 package units. There are 20 full-viewport screenshots (3 synthetic), 24 labeled native logo crops and two 10-second normal-speed MP4s. The 390px settings frame was recaptured after its existing drawer transition settled; no runtime changed.

Current observations belong in docs/TEST_RESULTS.md, design/footer-motion/ and the final delivery receipt. Final documentation can follow captures only when all recorded runtime hashes match the final Full ZIP. Distinguish that runtime equivalence from an exact final-archive capture. Queued/running Actions are not passes.

The rc.7 Full ZIP remains immutable: 26,444,170 bytes, SHA-256 d2c18d33028337dd7a5ffc3da3e9b423f185b794c9de17a8bf03971c12f1bd02. Preserve all 421 prior payload paths, eight migrations, five pinned recovery/access files and every previous compatibility asset. Do not add root favicon.ico because pinned recovery excludes it. Deliver a new cumulative rc.8 Full ZIP, actual screenshots and motion previews, checksums, source publication and GitHub artifact fallback.

## Deployment and remaining acceptance

rc.1 through rc.7 uses normal guarded same-schema Browser upgrades when checks pass. Preserve original database, owner, configuration/encryption identity, private state and uploads. Never reinstall. Existing 1.0.x additive schema-transition instructions remain.

Keep A2/cPanel manual database setup only, with no hosting token, automatic provisioning, setup code or 20i integration. Production hosting, real mailbox/merchant, physical-device/Safari and owner acceptance remain separate. No merge or live deployment is authorized.
