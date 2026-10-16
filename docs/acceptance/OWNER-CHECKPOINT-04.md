# Owner checkpoint 04 — newsletter flow confirmed

## Direct user confirmation

After the newsletter signup, confirmation-email, explicit website confirmation, welcome-email and unsubscribe sequence, the user replied **“yes they all work continue”** (with a trailing backslash). Record the requested newsletter flow as **working according to the user**. This completes its basic acceptance check. Do not ask for another subscription test, reset the test subscriber, or reopen the accepted installation, owner-email or booking checks without a new specific problem.

The prior instructions included checking Pending, Confirmed and Unsubscribed in administration. The user's broad confirmation accepts that requested sequence; no independent subscriber-row inspection, address, token, headers, timing or inbox/spam placement was supplied. Do not fabricate those details. No assistant-run website/mailbox operation, scheduled-worker execution, account/order email or universal deliverability is established by this report.

## Next bounded check: quiet-site mail processing

First inspect **cPanel > Advanced > Cron Jobs > Current Cron Jobs** for an existing command invoking this installation's `tasks/mail.php`. Do not create a duplicate, replace a working job, or change unrelated hosting tasks. The command must use the host's actual compatible PHP CLI executable, the absolute path to this site's task, and the actual public document root through `--document-root` or the supported environment variable. Never guess the cPanel username, addon-domain root, installation directory or PHP path. The public document root and an installation subfolder are not necessarily identical.

The project has previously documented an every-five-minutes cadence. cPanel provides a Once Per 5 Minutes selection. Job existence alone is configuration evidence, not successful execution. If a job already exists, establish whether it executes successfully while the website is idle through the host's cron output/notifications or private execution evidence. If absent, resolve actual paths before giving an executable command. Do not invoke the CLI-only task as a public URL or add a web cron service.

The task prints a processing summary. A successful empty batch establishes worker execution, not delivery of queued mail. Separately observe an authorized operator-controlled due message being processed by the scheduled worker rather than by a manual batch or form-submission shutdown processing. Do not disable working SMTP, interfere with real messages, edit queue rows, deliberately break delivery, blindly retry UNCERTAIN mail or send to unrelated people to manufacture evidence. Review pending messages before enabling a worker because it processes due mail, not just a selected test.

Retain output privately. A temporary cron notification address must belong to the operator, and sustained notifications should not flood the mailbox. Never share private credentials, raw application configuration, recipient lists or public logs. A redacted screenshot of only the relevant Current Cron Jobs row is enough to establish the initial setup, not to certify execution.

## Sources and boundaries

Read `tasks/mail.php` at 39c0e6f6ca471ef3914c3690b96fb947dfa56e7a (blob 766c9b0a35a2aac4d399d58bfd54e474bfcc1614): CLI-only, real document root required, installed schema required, bounded processing and JSON summary. Current cPanel references consulted: https://docs.cpanel.net/cpanel/advanced/cron-jobs/ and https://support.cpanel.net/hc/en-us/articles/360053606793-How-to-create-or-edit-cron-jobs-in-the-cPanel-user-interface . Host-specific availability and paths remain uninspected.

No cron job, SMTP setting, queue, subscription, message, website content or deployment was changed by the assistant. This is an acceptance/documentation checkpoint, not a new application test or version. RC7 and all prior artifacts remain unchanged. Account/order email, genuine merchant sandbox, protected recovery, production content/artwork, real-device review and final owner approval retain their existing scope.
