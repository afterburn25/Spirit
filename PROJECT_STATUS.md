# Project status — 1.0.0-rc.3

Application **28536a6102047fb89dd9cd6b04b5be3eed85e619**, tree **5989bf3f21c3e9b74c3db18d75c3c026b0402055**. Full workflow38667039903, focused38667039834 and three-engine38667039771; see TEST_RESULTS.md for exact conclusions and retry evidence. Final documentation-only changes leave application bytes unchanged. Inspect live main before new writes.

## Completed

Eight placement cards under **Pages & homepage** reopen existing Page records or prepare an unsaved new one. Exact draft/published URL lookup reaches records beyond the general listing's1000-row ceiling. Conflicting records are disclosed for review, never silently merged. Merely opening preparation/editor/preview pages does not publish content.

Home, Music, Shows, Band, Media, Booking, Press and Merchandise accept approved Page artwork and introductions inside the existing designs. Saved-draft previews show the actual section layout while related content remains published-only. Existing generic Page aliases resolve to the corresponding section; standalone custom/policy pages remain supported. Booking preview forms are disabled and do not consume public feedback. Preview requests are GET/HEAD-only with existing staff authorization and no-store/noindex/no-referrer protections.

The artwork inspector shows actual pixel dimensions, alternative text and source-width guidance. It warns about undersized reference images without upscaling or claiming rights/quality approval. Private changes, history restoration, publication and withdrawal retain existing CMS/media controls. Unpublishing removes anonymous access to otherwise-private original/derivative files. Sitemap mapping avoids duplicated section URLs and follows saved visibility decisions.

## Evidence

223 PHP/inc syntax checks, 747 distinct pure assertions (689 retained +58 section checks), six JavaScript syntax checks, 40 Python compilation checks and 12 packaging tests passed locally. Local PHP is8.4.24 and lacks PDO MySQL/GD/cURL/ZIP; no local SQL/browser success is implied.

Actual three-engine artifacts show137 section assertions per engine, alongside212 gallery,1491 release-review and251 navigation assertions. Section journeys cover actual editor/popup/public layouts at1440/390/320, root/subfolder and native no-JavaScript preview. Selected editor/public renders were visually reviewed;36 section screenshots are retained. These patterned images and texts are test fixtures, not production band artwork or inventory.

## Unchanged scope and remaining work

RC2 collapsible categories and browser-local state, one-page manual A2 installer, six migrations and five pinned recovery/access files remain unchanged. Every earlier RC2 deployment path is retained. No20i/token/setup code/automatic DB; no production service or host setting change. Existing installations upgrade rather than reinstall or replace their databases.

This completes the content placement/preview tooling, **not the production master artwork itself**. Approved genuine band/store content and policies, protected A2 staging/extension/cleanup/recovery checks, real mailbox delivery, genuine merchant sandbox, populated real-device quality and final owner approval remain. Same-schema updates and same-instance guarded restoration retain their documented limits.
