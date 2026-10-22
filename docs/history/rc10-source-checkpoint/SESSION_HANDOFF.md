# Resume — 1.1.0-rc.10 grouped layout

Repository afterburn25/Spirit; existing draft PR 37 and branch
work/1.1.0-analytics-and-navigation. main remains 6ef03a24ea87fdbfb0d053f7b54b4c16fce42098.
Verified rc.8 source is a0357ff18a2b927bd2107254086aadae47866276; source snapshot
456e8dc82e8013605494f60a5af48d3f1f4d44ff adds only the recovery workflow.
Prior rc.9 source/ZIP were NOT retained/recovered; do not claim those hashes or
tests belong to this reconstruction. This new release is rc.10, never a rewritten rc.9.

Latest user request is to finish the functional directory organization and publish.
Read docs/DIRECTORY_LAYOUT.md, docs/LAYOUT_TRANSITION.md and docs/TEST_RESULTS.md.
Preserve images in root, every original asset and migration, private runtime and
all owner data. Do not merge or deploy without approval. New recovery jobs carry
grouped-v2; old layout jobs are retained but not valid for partial code rollback.

The public dispatcher uses an exact route allowlist, retains query strings and
machine-request initialization order, and derives the original install base from
the front controller. app/http is NOT publicly executable. Never add a
user-controlled include, weaken access denial, or recreate route folders at root.

Complete fresh-install, URL routing, maintenance and browser checks at root and
/band. New source commands use app/tests and app/tools. Capture actual pages,
not generated mockups. Mark old screenshots as historical. Deliver a full
cumulative ZIP plus source/patch backup and transition instructions.
