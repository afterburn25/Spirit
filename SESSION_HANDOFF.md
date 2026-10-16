# Resume — customer accounts passed; genuine sandbox checkout next

Repository **afterburn25/Spirit**, main authoritative. Inspect live refs, preserve concurrent work and never force-push. Software and last delivered installer remain **1.0.0-rc.7**, baseline 5f721a3ab785690b99e966d6ca976e810c2431ee. Do not rebuild the immutable RC7 archive or create RC8 for a documentation checkpoint.

## Latest accepted result — customer accounts

The user replied **“yes all this passed”** after the requested customer registration/activation-email/password-creation/sign-in/out and reset-email/new-password/old-password-rejection checks. Record that sequence as passed according to the user and move on; no repeat registration/reset without a concrete new problem. `docs/acceptance/OWNER-CHECKPOINT-06.md` preserves exact scope. No credentials, address, token link, headers, timing or rows were independently inspected; no assistant-run account test is implied.

Earlier accepted outcomes remain: existing tasks/mail.php cron job listed (checkpoint05), newsletter signup/confirmation/welcome/unsubscribe (04), saved booking inquiry plus both emails (03), owner test-email receipt (02), and normal installation/upgrade/admin/Launch checks/report path (01). Do not reopen those loops. Job presence and account email receipt do not identify a particular scheduled execution. No exact cron output/cadence/path was supplied; quiet-site execution remains distinct. These confirmations do not establish protected recovery, merchant transactions or final approval.

## Next action: one genuine sandbox order

Use protected staging or an explicitly isolated test storefront, not a real operating storefront switched into test mode. Existing Orders & payments > Authorize.net / Payment configuration > Sandbox settings provides the controls. Check the SAVED Current mode is sandbox (tab selection alone does not change it), use separate sandbox credentials, preserve production settings and do not type ENABLE LIVE PAYMENTS. Test saved API authentication only is a no-charge prerequisite, not checkout acceptance. No provider settings have been changed by the assistant.

The separate Authorize.net sandbox merchant interface should have internal Test Mode off (its label is Live Mode); the WEBSITE must remain Sandbox. This never authorizes switching the production merchant live. Sandbox internal Test Mode yields transaction ID zero/no stored transaction. Primary reference https://developer.authorize.net/hello_world/testing_guide.html was read. Public sandbox Visa4111111111111111, future expiry and CVV900 are documented test inputs. Never use real card details, request secrets in chat, alter production orders, invent tax/terms or publicly seed dummy products to complete this check.

Use an operator-controlled signed-in customer and one isolated item. Note stock before starting, review totals and pay once. Check paid/Sandbox status, the same nonzero transaction ID and amount in the actual sandbox account, website-generated receipt delivery, account order-history entry and one net inventory deduction (the hold already deducts available stock). Refresh must not create another order/payment/stock change. Stop on uncertain/held/processing errors rather than repeatedly paying. Missing sandbox prerequisites should be reported, not bypassed. Current docs/PAYMENTS.md, app/OrderAdmin.php and views/admin/orders.php at39428c9e establish the site's exact controls.

This first order remains unverified. Later tests cover real signed notifications/reporting, guest receipts, decline/held/duplicate/interruption/cancellation/expiry, supported full void/refund, payment-worker execution and order/fulfillment/reversal mail. One reversal per order means separate eligible orders for void versus refund; settlement matters. Do not imply whole merchant acceptance from one successful order or actually ship test merchandise.

The existing mail worker remains CLI-only and needs real PHP/task/document-root paths. Its presence is no longer a question; successful scheduled output/controlled due-message evidence remains distinct from interactive or empty-batch activity. Do not disable working SMTP, corrupt queues, send unrelated mail or blindly retry UNCERTAIN messages to manufacture evidence. Prior checkpoint04 documents this boundary.

## Confirmed content and prior files

Roster confirmed by the user: Chad Lindsey — guitar, keyboards and vocals; Ron Green — harmonica; Charlie Savell — drums. Exact record: `design/launch-review-01/CONFIRMED-LINEUP.md`. Do not ask again. Longer biographies/identified portraits and recording-specific credits are still distinct; no face recognition.

`design/launch-review-03/` preserves the real one-page Band & Booking PDF, ReportLab generator and six text-only CMS field sets. Its README contains exact PDF/packet hashes and document validation. It is not a complete EPK, installer or automatic importer. No PDF upload, content save or publication has been performed through these preparation steps. Preserve existing IDs/revisions/media when content is entered later; omitted fields do not authorize clearing them.

Candidate 3 / Part 3 remains selected. Full 180.600-second remastered1080p and smaller720p plus the matching45-second-frame poster are unchanged. `PART-3-PUBLICATION-PREP.md` retains exact hashes and previous checks. No other source-recording label has been assigned to it; neutral Performance — Part 3 remains until metadata is known. Actual approved YouTube/Vimeo URL is still needed by the existing Videos module. Do not invent a placeholder URL or add a direct-MP4 importer.

Eight earlier page-copy proposals remain unapproved beyond the separately confirmed roster facts. Logo/hero masters, identified photos, genuine shows/merchandise/policies and actual placement remain. Issue21's generated page images are rejected; do not repeat the same failing generation path or call them real assets.

## Preserve scope and evidence

Existing six milestones remain. Normal installation/update/report access, owner-email receipt, booking inquiry/both emails, newsletter and customer activation/sign-in/reset are no longer unconfirmed gates. Mail-job presence is confirmed but actual scheduled execution remains separate. Protected recovery, order email, genuine Authorize.net sandbox, populated physical-device review and final approval remain. Do not turn a tool cache miss into a site defect or bypass administrator browser restrictions.

Keep approved navy/black/gold/blue design, external ticket sellers, remembered grouped admin navigation and one-page manual A2/cPanel setup. No setup code/token/20i/automatic DB. Preserve original private configuration/encryption secret/database/media, migrations, pinned recovery/access files and offline receipts. Existing installations upgrade with backups, never reinstall or replace the database. No runtime, tests/workflows, deployment, account/password/email/queue/payment/job action or prior delivery changed; no final1.0 tag or acceptance result beyond the user's scoped reports.
