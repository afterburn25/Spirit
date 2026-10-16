# Changelog

## Final checkout validation alignment — application remains RC7

Align the public checkout form with existing server-side billing-address validation. When “billing address matches shipping” is cleared, separate billing fields are immediately marked required and the billing disclosure opens; billing city/state/ZIP now expose appropriate browser autocomplete hints. No gateway request, charge logic, schema, installer or payment credentials changed. Browser regression coverage now verifies separate-billing required fields and the return to shipping-matches behavior.


## Final launch runtime copy alignment — application remains RC7

Wire the approved launch wording into the public runtime as safe fallbacks for Home, Music, Shows, Band, Media, Booking, Press and Merch when matching CMS content is empty. Align default public SEO titles/descriptions with the final content map while preserving CMS overrides.

No fabricated releases, shows, videos, portraits or merchandise were added. No CMS records were published. VERSION remains 1.0.0-rc.7; schema, installer, payment code and the immutable RC7 delivery are unchanged.


## Final launch content consolidation — application remains RC7

Consolidate confirmed roster, factual Band/member/Press text, prepared Band & Booking PDF references, general launch page copy and selected Candidate 3 / Part 3 working metadata into `design/final-launch-content/`. Add an explicit placement checklist for the existing CMS and a single list of genuinely missing launch inputs. No importer, seed data, fake shows/products/biographies or direct-MP4 feature is added.

The live homepage was reread and still shows the redesigned layout with no published releases and no confirmed dates. No direct CMS edit or publication occurred; Git preparation is not website state. Keep the paid sandbox purchase and other unseen final-review evidence pending rather than inferring success.

Documentation/content worksheet changes only. Runtime, VERSION, tests/workflows, schema, installer, payment code, saved navigation and the immutable RC7 delivery remain unchanged.

## Previous history

Earlier detailed checkpoints and release history remain in `docs/history/`, `docs/acceptance/`, TEST_RESULTS.md and PR20.
