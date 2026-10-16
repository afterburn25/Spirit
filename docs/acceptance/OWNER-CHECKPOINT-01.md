# Owner checkpoint 01 — move beyond installation checks

## User report

After receiving the latest RC7 Full ZIP and the existing upgrade/support-report instructions, the user replied: **“yes all that works lets move on”**.

Record the normal installation/upgrade/administration and Launch checks/report path as **working according to the user**. This is meaningful owner-reported evidence, not another automated-test result. Do not keep requesting the same support report or asking them to repeat installation solely to continue. Reopen a specific installation issue only if new evidence or an actual error requires it.

The reply does not independently establish a backup-restoration/rollback drill, every private-storage or hosting flag, a delivered email, a merchant transaction or final launch approval. No JSON report was inspected in this checkpoint. Do not invent individual extension values or a tested installed commit. The delivered software baseline remains 1.0.0-rc.7.

## Next active milestone: email and scheduled processing

Use the existing **Bookings & communication > Email settings** screen. No new installer, provider connector or mail feature is required.

1. Review Delivery queue before enabling delivery or processing a batch; pending records can include real messages. Preserve existing correct SMTP configuration and keep credentials out of chat/Git. The owner test action enqueues a test and processes up to three due messages, not necessarily only the new test.
2. Use **Send a test to my owner email**. Confirm receipt of **Spiritual Gorillaz delivery test** in the mailbox belonging to the signed-in owner. An SMTP-accepted count or SENT queue row alone does not prove delivery. A different configured owner mailbox need not be the connected Gmail account.
3. In an authorized test context, submit one clearly marked booking inquiry using the operator's own contact address. Check the saved inquiry, owner notification and organizer acknowledgement without creating a real reservation or marketing enrollment.
4. Exercise newsletter signup, its explicit confirmation button, welcome mail and explicit unsubscribe. Pending is not confirmed; opening a link alone is not the consent-changing action.
5. Verify the existing CLI-only `tasks/mail.php` through the actual cPanel schedule. Use the correct PHP executable and absolute public document root; do not guess them or invoke the worker over HTTP. Confirm processing while the website is idle, rather than counting manual processing as cron evidence. Payment follow-up remains a distinct later checkout test.

Do not blindly retry an UNCERTAIN message. Inspect provider/mailbox evidence before any action that might resend it. Do not enable public signup or send to a customer list merely to produce a test result. The current task is verification, not a marketing campaign.

## Source basis and current limit

Labels and behavior were read from `views/admin/communication.php`, `app/CommunicationAdmin.php` and `tasks/mail.php` at source commit 0654748da766ad8244a7c2ee9675373fcbe6a744. The older communication guide has historical deployment notes; current source governs the controls above.

This checkpoint records user feedback and prepares the next test sequence. No SMTP setting, queued message, website record, cPanel job, provider account, payment or deployment was changed. No new email or hosting result is claimed. Artwork/content, protected recovery, genuine sandbox, physical-device and final owner acceptance remain under the existing six milestones.
