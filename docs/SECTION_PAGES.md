# Managed section artwork and copy — 1.0.0-rc.3

Use **Website content > Pages & homepage**. The eight placement cards reopen the existing Page record, or prepare an unsaved Page with the correct URL name. Opening a card never creates records or publishes content. Multiple draft/published records claiming the same placement are exposed for review, not merged automatically. Lookup is exact and is not limited by the general content listing's pagination.

| URL name | Designed page | Fields used |
|---|---|---|
| home | Homepage | Subtitle in hero introduction; page text in purpose section; feature image/focus in hero. |
| music | Music | Subtitle, introduction and feature artwork above published releases. |
| shows | Shows | Subtitle, introduction and feature artwork above upcoming/past events. |
| band | The band | Subtitle, biography and band photograph above member profiles. |
| media | Media | Subtitle, introduction and feature artwork above published videos/photos. |
| booking | Booking | Subtitle, introduction and feature image beside the inquiry form. |
| press | Press kit | Subtitle and hero artwork; page text in the press biography section. |
| merch | Merchandise catalog | Subtitle, introduction and feature artwork on the catalog only. |

Headings and layout remain in the approved design. The saved title identifies the record; search/share fields control metadata. Generic custom pages and policies retain their standalone routes. The four newly connected Page names music/shows/media/merch now feed their actual sections; an older page alias shows the same layout with the proper canonical destination. Static sitemap publication/exclusion checks follow those Pages without duplicating them in dynamic sitemap results.

## Safe editing and preview

Upload approved JPEG/PNG/WebP artwork through the existing Media library. Choose it as the Page feature image, set focus, and **Save private draft**. The artwork inspector reports actual dimensions and a suggested source width. Adequate dimensions are not proof of sharpness, composition, copyright permission or owner acceptance; small artwork remains publishable with an honest warning. It does not upscale or generate replacement masters.

**Preview saved draft** now opens the real designed section, not a generic standalone article. Only that Page draft is substituted: related releases, member profiles, events, gallery items and merchandise remain published-only. Catalog preview never opens a closed store or enables payments. Booking forms are disabled in preview, do not consume saved public booking feedback, and preview POST requests are refused. Previews retain staff authorization, no-store/noindex/no-referrer and the existing private-media rules.

**Publish approved content** explicitly changes the public presentation. Saving another draft does not change the current live copy/image. Version history restores a new private draft using the existing revision and media-validation controls. Unpublishing restores default presentation and removes anonymous access to artwork that is not referenced by other published content. Previously delivered media, database content and revision history are not replaced by package installation.

## Artwork remains an acceptance item

The default concept crops are still reference-resolution images. This release completes the editing and actual-layout preview path for replacing them; it does not claim the production master artwork or real band content has been supplied. No people, shows, releases, prices or inventory were invented or seeded. There is no new content kind or database migration, no changed payment/hosting setting, and no change to the manual installer or remembered navigation.

## Verification

58 new pure checks cover route placement, fallback copy, escaping, safe focus and truthful dimension guidance. New SQL/HTTP suites cover draft/publish/restore/unpublish, native Page aliases, private media, read-only previews, permissions, conflicting records and large libraries. New real-browser journeys cover the actual editor, private preview, publication, portrait/landscape layout and inactive booking preview in Chromium/Firefox/WebKit at1440/390/320 on root and subfolder installations. The three-engine artifacts record137 section assertions per engine, and full SQL/HTTP verification covers both database environments. See TEST_RESULTS.md for exact run conclusions and the full-workflow retry. Synthetic images are not new production artwork.

Technical sources consulted: https://www.php.net/manual/en/function.htmlspecialchars.php ; https://www.w3.org/WAI/tutorials/images/decorative/ ; https://playwright.dev/python/docs/test-assertions .
