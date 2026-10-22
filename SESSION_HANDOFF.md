# Resume — final 1.1.0

Repository: afterburn25/Spirit. Main is authoritative. PR 37 consolidates
work/1.1.0-analytics-and-navigation. Inspect live branch/PR state and exact
Actions before changing code; do not repeat completed release work or infer
deployment from a merge.

The owner asked to continue after the plan to verify exact upgrades, finish
1.1.0, merge PR 37 and clean superseded pull requests. This supersedes the older
rc.12 instruction to keep PR 37 draft. It does not authorize a live-host
reinstall or payment activation.

## Source and package continuity

Published rc.12 source: 46744207a0916ae2256d84fbaa312c9c4fc67768. All eleven
workflows succeeded. Final deployment changes are VERSION/docs only;
application PHP, CSS, JavaScript, artwork, eight migrations and the grouped
recovery foundation remain byte-identical to rc.12.

Original rc.11 ZIP SHA-256:
4abbc7d59dc6e1cd003950eecca628d73fa8fb3ecffb1466b73dd1147107168c.
Original rc.12 ZIP SHA-256:
f65a5db2e5b226c54409d909b390f174dcc6e0c2e3dc1408f0b0a06e26a0e8ec.
The rc.10 baseline was reproduced from published source
5703ca41a13a6ff01a3b7807287abdeb72c151a5 using its original packager and pinned
Pillow. It is not represented as recovered original delivery bytes.

Use app/tests/exact_package_upgrade.py with explicit --baseline, --target and
--output arguments in a non-root disposable SQL environment. It verifies real
owner upload/receipt/apply/confirmation and independent recovery while retaining
configuration, owner/database state and media. Read docs/TEST_RESULTS.md and
the companion receipt for observed runs and the final ZIP hash.

## Fixed constraints

Keep admin, app, assets, config, docs, images, includes, install, storage and
uploads at root. Do not restore old root clutter. Keep cumulative manifest
paths, original private state, public URLs and removed-installer state.
Read docs/LAYOUT_TRANSITION.md before changing a pre-grouped installation.
Never bypass recovery pins, replace its database or rerun fresh setup.

Retain the approved animated 3D gorilla, fixed stage/smoke, compact 75%-opaque
footer, editable copyright and fixed linked Cajun Web Pro credit. Short-page
footer flow and measured player clearance are completed work, not redesign
tasks. Public CSS/JS has only 84 bytes of budget headroom.

Accepted installation/mail/account checkpoints remain accepted. Commerce is
disabled; optional content and merchant activation are separate. No live-host,
new provider or physical-device result is claimed. Never overwrite delivered
versions, force-push, expose recovery receipts or commit private data.
