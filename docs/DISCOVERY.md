# Search, sharing and sitemap controls

Development 0.9.0-dev.6. New complete verification is pending; consult TEST_RESULTS.md rather than applying an earlier green result to these changes.

## Published page metadata

Pages, releases, shows and band members have optional search/share title, description, image and discovery visibility fields. Private drafts do not change published tags, search projections or sitemap entries. Selected sharing images follow the same public-media publication rules as other images. Exclusion sets noindex metadata and removes the item from public search and sitemap, but the published URL remains accessible: it is not authentication.

Public descriptions are normalized without splitting UTF-8 characters. Canonical and image URLs come from the configured installation URL, not a visitor's Host header. Image dimensions are read from the actual uploaded image. Product tags use the approved product name, description and first image; customer/cart/order/token pages are noindex and never emit private sharing artwork. Public navigation refreshes title, canonical, robots, social tags and event JSON-LD while retaining actual audio playback. Leaving an event removes its old schema and image tags.

## Global visibility

Owner > Search & sharing can discourage indexing on staging, suppress sitemap URLs and event schema, or explicitly allow eligible public pages. Existing installations retain their earlier indexable behavior by default; disable indexing on a publicly reachable staging installation. A robots directive cannot protect confidential content or force a search engine to forget a previously indexed page immediately. Use authentication or hosting access controls for genuinely private previews.

The setting preserves unrelated site configuration. Staff content editors cannot change global indexing. Individual content metadata follows ordinary private-draft/preview/publish permissions.

## Sitemap

The sitemap index URL is the installation home URL with `?sitemap=1`. Child parts also use that root URL, so they remain within the installation's URL scope without adding loose root scripts or changing pinned web-server access rules. The index references a static-page part and content/product parts of at most 1,000 URLs each. URLs are absolute and XML-escaped. Only published modification timestamps are used for content; product last-modified is omitted because its storage timestamp also changes for private edits. Conditional GET/HEAD is supported. Invalid pages/methods are refused.

Private drafts/history, administration, account/order/token pages, search results and the booking form are excluded. Closed catalog and inactive category products are excluded. Submit the index in the site's verified search-engine account. This implementation does not submit it for the owner or edit another site's/domain-level robots.txt on shared hosting. A sitemap does not guarantee crawling or indexing.

## Public search and old links

Footer > Search searches approved public page/release/show/member/product text. Results are 20 per page with validated filters, literal wildcard handling and no private draft/title-column search. It is database search for this band website, not a general web search service. Large collections still need host capacity and query-performance review.

A formerly published leaf URL redirects to its current published content item only when the history match is unique. A current exact URL wins; draft-only names, unpublished targets and ambiguous historical reuse do not redirect. Product URLs remain immutable. This is not an importer for redirects from the old unrelated site, nor a user-configurable external redirect engine.

## Single-show structured data

Only a published single-show page explicitly marked open to the public, with supplied venue street address, city and country, produces MusicEvent JSON-LD. State/region and postal code are included when supplied. Date/time comes from the named event timezone; cancelled/postponed states retain the entered date. Leave the original scheduled date while postponement has no confirmed replacement. Audience, address and status remain visible on the page.

Invitation-only/unconfirmed events and non-event pages do not claim public event eligibility. No price, rating, endorsement or audience count is invented. Run the provider's rich-result validation with approved real event information before launch. Structured data does not guarantee a special search display.

## Primary implementation references

Google sitemap guidance: https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap
Google event guidance: https://developers.google.com/search/docs/appearance/structured-data/event
MDN image sizing: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/img

This checkpoint does not claim field Core Web Vitals, a complete accessibility/security audit, real merchant/inbox acceptance, final master artwork or accepted v1.0.
