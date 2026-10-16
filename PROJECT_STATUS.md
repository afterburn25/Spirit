# Project status — RC7 software / launch source review 02

## Latest continuation

Limited read-only web retrieval returned the redesigned homepage, Music, Band, Shows, Booking and Shop. Their text still shows unpopulated band/music/show content and a collection not yet open. Booking displays a form and contact address, but no form was submitted and mailbox delivery remains unverified. Media, Press, Search and Account could not be read through the tool; cache misses are not proof of broken routes. Local browser navigation was blocked by administrator policy and local HTTP failed DNS resolution. No restriction was bypassed and no live-browser/mobile/performance or hosting acceptance is claimed.

An older search-index result for the same site is explicitly a demo and must not be imported as genuine live content. Relevant band-originated sharing notes supplied one musician/role credit and three recording labels. A private conversation worksheet now has one minimal musician-credit draft, two unconfirmed-name leads and three incomplete video-review records. All four draft records pass existing field and slug validation; videos without actual provider URLs fail publication validation. No database was accessed. Source notes do not establish the complete current lineup, title/credits of Part 3, or publication approval. Private source details and access links stay out of Git.

Read `design/launch-review-01/PUBLIC-CONTENT-REVIEW-02.md`. The next useful content decision is confirmation of the current roster. Issue #21 remains open; recent generated page mockups remain rejected and unapplied. No new image-generation attempt was made.

## Selected recording remains unchanged

Candidate 3 / Part 3 is still selected. Its full 180.600-second segment, byte-identical 1080p copy, smaller 720p viewing copy and selected poster variants were prepared previously; see `design/launch-review-01/PART-3-PUBLICATION-PREP.md` for exact hashes and verification. No media file was modified in this continuation. The neutral title remains Performance — Part 3 until its exact metadata is confirmed. None of the three separate source-recording labels has been assigned to it. Actual approved YouTube/Vimeo playback URL is still needed by the existing video editor.

## Remaining completion milestones

Milestone 1 is in progress, not complete. Eight page-copy proposals remain unapproved. Final logo/hero artwork, current-member biographies and portraits, chosen recordings/press, genuine shows/merchandise and policies remain. Milestones 2–6 stay open: actual A2/private storage/extensions/cleanup/recovery, receiving mailbox and scheduled work, genuine merchant sandbox, populated physical devices and owner acceptance.

Application and last delivered installer remain **1.0.0-rc.7**, software baseline 5f721a3ab785690b99e966d6ca976e810c2431ee. No runtime, VERSION, schema, tests, workflows, installer, payment rules, administration state or prior delivery archive changed. This is content/source preparation, not a new software release or full regression run. Prior application evidence remains in TEST_RESULTS.md and PR20.

Preserve the approved design, external venue ticket sellers, remembered collapsible navigation and manual one-page A2/cPanel installer. No setup code/token/20i/automatic database creation. Existing sites upgrade with backups and keep their original private configuration/encryption secret/database/media/recovery materials. Nothing was deployed or enabled; final 1.0 is not released.
