# Resume — RC8 software complete; final acceptance only

Repository **afterburn25/Spirit**, `main` authoritative. Current source candidate is **1.0.0-rc.8** because genuine runtime and verification changes landed after the immutable RC7 delivery. Do not overwrite RC7. Do not create RC9 for editorial placement, optional assets or acceptance paperwork.

## Current state

The planned application implementation is complete. Continue only with:
- failures found by current CI;
- final real-world acceptance;
- genuine content/assets supplied or explicitly approved by the owner;
- final 1.0.0 packaging/tagging after acceptance.

Do not reopen finished modules or redesign the site.

## Fixed decisions

- Chad Lindsey — guitar, keyboards and vocals.
- Ron Green — harmonica.
- Charlie Savell — drums.
- Candidate 3 / Part 3 remains selected.
- Preserve the black/deep-navy, gold and blue crowned-gorilla design.
- Show tickets continue to each venue's configured external seller.
- No native ticketing.
- No synthetic member portraits.
- No fake shows, products, rates, testimonials or recording metadata.

## Runtime finalization now landed

- Approved factual launch copy is wired into public fallbacks and default metadata.
- Band page always preserves the confirmed three-member lineup. Published profiles keep their real links/media; only missing confirmed members receive factual fallback cards.
- Bundled scalable logo fallback: `images/spiritual-gorillaz-logo.svg`. Owner-published branding still overrides it.
- Small built-in hero is rendered as contained artwork, not stretched into a fake production master. Real CMS hero artwork still uses the normal cinematic treatment.
- Closed commerce is intentionally dormant: global Merch/Bag links disappear while disabled; customer accounts remain reachable.
- Launch diagnostics treat dormant store/payment/account/key/policy/product states as safe rather than false blockers.
- Mail/payment CLI workers write protected timestamp-only heartbeats in private runtime cache after successful runs.
- Launch Checks surface worker heartbeat freshness without claiming inbox/provider acceptance.
- Checkout separate-billing browser validation matches server rules.
- 360 px / 390 px / desktop browser coverage spans public, commerce/customer, payment/checkout and booking/communication flows.
- Release-candidate storefront checkout/cancellation runs at 390/360 px across Chromium, Firefox and WebKit.
- `tools/release_gate.py` validates package/source integrity and is enforced by `.github/workflows/release-gate.yml` and the main verification workflow.

## Optional launch content

The site can launch truthfully without fabricating missing optional material:
- no confirmed show -> Shows remains empty;
- no real merchandise -> storefront remains closed and hidden from global store navigation;
- no labelled portraits -> factual member fallback cards remain;
- no Part 3 provider URL/title -> Part 3 remains unpublished;
- no longer biography -> use factual one-line member copy.

Prepared Band & Booking Sheet:
`design/launch-review-03/Spiritual-Gorillaz-Band-and-Booking-Sheet.pdf`

Preparation in Git is not CMS publication.

## Final blockers

1. Observe the latest CI matrix on the final source head and fix only genuine failures.
2. Obtain explicit owner acceptance of the final site/launch choices.
3. Record actual A2/real-host observations still required by the launch checklist (including scheduled worker evidence/recovery drill/performance where applicable).
4. If commerce will be enabled, complete genuine Authorize.net sandbox/provider acceptance before enabling it.
5. Publish only owner-approved real content/assets.

Do not tag **1.0.0** until these are satisfied. The release gate intentionally refuses a final version while PROJECT_STATUS still declares blockers.
