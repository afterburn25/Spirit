# Resume — newsletter confirmed; quiet-site mail scheduling next

Repository **afterburn25/Spirit**, main authoritative. Inspect live refs, preserve concurrent work and never force-push. Software and last delivered installer remain **1.0.0-rc.7**, baseline 5f721a3ab785690b99e966d6ca976e810c2431ee. Do not rebuild the immutable RC7 archive or create RC8 for a documentation checkpoint.

## Latest accepted result — do not repeat newsletter verification

The user replied **“yes they all work continue”** after the requested newsletter signup, confirmation email and website confirmation, welcome email, and unsubscribe sequence. The basic newsletter check is complete according to the user. `docs/acceptance/OWNER-CHECKPOINT-04.md` preserves the exact scope. No subscriber address, token, headers, delivery timing, inbox/spam placement or independent row inspection was supplied. The prior instructions included the Pending/Confirmed/Unsubscribed sequence; this is acceptance of that requested flow, not assistant-operated evidence or coverage of all failure paths.

Earlier accepted outcomes remain: saved booking inquiry plus band notification and organizer acknowledgement (checkpoint 03); owner test-email receipt (checkpoint 02); normal installation/upgrade/admin and Launch checks/report path (checkpoint 01). Do not request those tests or the same support report again without a concrete new problem. Do not reset the test subscriber or infer a real booking, all hosting permissions, merchant transactions, protected recovery or final approval.

## Next action: inspect and verify the existing scheduled mail worker

Open cPanel > Advanced > Cron Jobs > Current Cron Jobs and look for the relevant `tasks/mail.php` command. First establish whether it already exists; do not create duplicate jobs or touch unrelated schedules. The existing CLI-only task requires the host's actual PHP executable, absolute script path and real public document root through `--document-root` or SPIRIT_DOCUMENT_ROOT. Do not guess usernames/paths, assume addon-domain roots equal public_html, or invoke the worker over HTTP. A redacted screenshot of the relevant row is sufficient for the initial configuration review. A job merely being listed is not proof it ran.

The project example cadence is every five minutes. Confirm actual successful scheduled execution while the website is idle using host cron output or other private evidence, then distinguish a real operator-controlled due-message delivery from an empty batch and from interactive form/manual processing. Do not disable working SMTP, corrupt queues, send unrelated mail or blindly retry UNCERTAIN messages to create evidence. Review due mail before enabling any job. Keep logs outside public paths and redact private details. Read checkpoint 04 for the cPanel references and evidence limits.

Current tasks/mail.php at39c0e6f6 was reread: it enforces CLI, document root and installed schema, then prints a bounded processing summary. No cron command has been installed or run here. Account/order email, protected recovery, genuine sandbox and other external acceptance remain distinct.

## Confirmed content and prior files

Roster confirmed by the user: Chad Lindsey — guitar, keyboards and vocals; Ron Green — harmonica; Charlie Savell — drums. Exact record: `design/launch-review-01/CONFIRMED-LINEUP.md`. Do not ask again. Longer biographies/identified portraits and recording-specific credits are still distinct; no face recognition.

`design/launch-review-03/` preserves the real one-page Band & Booking PDF, ReportLab generator and six text-only CMS field sets. Its README contains exact PDF/packet hashes and document validation. It is not a complete EPK, installer or automatic importer. No PDF upload, content save or publication has been performed through these preparation steps. Preserve existing IDs/revisions/media when content is entered later; omitted fields do not authorize clearing them.

Candidate 3 / Part 3 remains selected. Full 180.600-second remastered1080p and smaller720p plus the matching45-second-frame poster are unchanged. `PART-3-PUBLICATION-PREP.md` retains exact hashes and previous checks. No other source-recording label has been assigned to it; neutral Performance — Part 3 remains until metadata is known. Actual approved YouTube/Vimeo URL is still needed by the existing Videos module. Do not invent a placeholder URL or add a direct-MP4 importer.

Eight earlier page-copy proposals remain unapproved beyond the separately confirmed roster facts. Logo/hero masters, identified photos, genuine shows/merchandise/policies and actual placement remain. Issue21's generated page images are rejected; do not repeat the same failing generation path or call them real assets.

## Preserve scope and evidence

Existing six milestones remain. Normal installation/update/report access, basic owner-email receipt, booking inquiry/both emails and the newsletter flow are no longer unconfirmed gates. Protected recovery, scheduled processing, account/order email, genuine Authorize.net sandbox, populated physical-device review and final approval remain as applicable. Old read-only review restrictions and uncertainty are preserved in prior review documents; do not turn a tool cache miss into a site defect or bypass administrator browser restrictions.

Keep approved navy/black/gold/blue design, external ticket sellers, remembered grouped admin navigation and one-page manual A2/cPanel setup. No setup code/token/20i/automatic DB. Preserve original private configuration/encryption secret/database/media, migrations, pinned recovery/access files and offline receipts. Existing installations upgrade with backups, never reinstall or replace the database. No runtime, tests/workflows, deployment, email/queue action, job or prior delivery changed; no final1.0 tag or acceptance result beyond the user's scoped reports.
