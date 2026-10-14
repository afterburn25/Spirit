# Session handoff — preserve progress and continue

Repo afterburn25/Spirit, main authoritative. Fixed approved design and organized shared-host installation remain; no mockups, native ticketing, fake public content, destructive reinstall, permission weakening or force-push.

Verified cumulative backup/staff app7d22bf8da7ec8bdb1ec676792af10530bac5a40d passed38502208276 on both DB engines and seven Chromium suites. Four new backup screenshots inspected; a621b4b is the documentation-only main checkpoint. Main's exact new run should be checked on resume.

Current development **0.9.0-dev.3 on work/browser-upgrades** adds code-only browser ZIP upgrades with a pinned early recovery kernel, owner confirmation, private staging/originals, complete hash/syntax/bounds checks, encrypted pre-update snapshot, persistent guard, separate post-update owner verification, and independent recovery when new bootstrap fails. Read UPGRADES.md. No cross-schema automatic rollback: packages changing immutable schema/recovery layer are refused. Original private config/keys/media and all DB business activity remain current.

Next inspect exact branch/head/full run; fix real failures without bypassing tests or weakening production CSP. Tests cover traversal/link/duplicate/secret/kernel/schema/syntax/checksum rejection, real upload/replacement/confirmation/rollback, later data preservation, tampered staged/current code, process-death partial update, broken startup and later-schema downgrade refusal. Run all eight browser suites and inspect new renders, then promote only tested source and refresh docs. Local PHP lacks SQL/GD/cURL/ZIP; complete integration is Actions-only. Use canonical source artifacts for package verification.

Continue with safe retention/temp cleanup and advanced CMS/meta/accessibility/performance/master-art/content acceptance. Keep all actual setup/recovery receipts out of Git/public archives. This is development, not final1.0. New code needs its own green run; historical backup verification is not proof of the updater.
