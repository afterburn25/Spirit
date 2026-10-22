# 1.1.0-rc.12 — footer at the bottom of short pages

This cumulative review candidate retains the rc.11 Music filter wrapping and
rc.10 grouped directory layout. The public shell fills the viewport, leaving
the compact footer at the bottom on short pages and after content on long pages.
The audio player's measured height supplies clearance for accessible footer
links on narrow screens. Original newsletter spacing is retained.

The source stylesheet and its generated bundle change. Approved artwork,
JavaScript, all eight migrations, routing, recovery, analytics, logo/smoke,
footer transparency, editable copyright and fixed Cajun Web Pro credit remain.
Read TEST_RESULTS.md for observed local checks and their limits. Check the
exact published branch head's Actions before claiming CI passed.

**Existing rc.8 or earlier:** use the documented one-time same-root hosting-panel
transition. The old Browser upgrades system must reject this layout change.
**Existing rc.10 or rc.11:** same-layout, same-schema Browser upgrades may be used
when all existing integrity/recovery checks pass. Do not bypass any check.
**Earlier rc.9:** compare the actual installed package before assuming compatibility.

No database migration is added. Preserve configuration, original owner, private
runtime, uploads and absent installer. Do not reinstall. Target host, real mail,
merchant and final owner acceptance remain separate. Keep PR 37 draft/unmerged.
