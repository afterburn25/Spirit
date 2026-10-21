# Project status — 1.1.0-rc.10 directory reorganization

Implemented locally from the verified rc.8 source: ten primary folders, retained
root images, private config/database migrations, grouped handlers/views/workers,
strict public URL dispatcher, grouped development/test files, updated workflows
and a grouped-v2 recovery boundary. Public artwork, CSS/JS and SQL are unchanged.
The earlier rc.9 working files were unavailable in this resumed environment; its
version is not reused. GitHub writes now respond, and the read-only source
snapshot is preserved as an Actions artifact. No production site was changed.

## Verification
See docs/TEST_RESULTS.md. Only recorded completed checks are passes.

## Remaining blockers
Cross-layout transition acceptance on A2, full current CI completion, actual
browser/owner visual review and any enabled provider acceptance remain. PR 37
must remain draft and unmerged. Do not imply that the old browser updater can
apply this structural transition or that a live site has been updated.
