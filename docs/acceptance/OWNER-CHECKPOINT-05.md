# Owner checkpoint 05 — mail job listed; customer accounts next

## Direct user confirmation

Asked whether a `tasks/mail.php` job was already listed in cPanel Current Cron Jobs, the user replied **“yes next”**. Record the job's presence as **confirmed by the user** and move on. Do not ask for another screenshot of its existence, create a duplicate job or alter working SMTP settings.

This answers the configuration-presence question. No command, cadence, output, exit status, document root or scheduled delivery was inspected. Do not mark quiet-site execution or the whole communication milestone passed from job existence alone. Keep scheduled execution as a separate remaining observation without repeating the same setup question. Prior accepted installation, owner-email, booking/two-email and newsletter results remain accepted at their recorded scope.

## Next bounded check — customer registration and recovery

Use a separate private browser window and an operator-controlled email address that is not already registered as a customer. Keep the owner account and its password untouched. Public customer identities are separate from staff, and customer sign-in replaces that browser session's authentication state.

1. Open the public **Customer account** link and **Create an account**. Submit the name, controlled address and account consent once with **Send secure email**. Do not register an unrelated person or change an intentionally disabled account service without approval.
2. Confirm that **Spiritual Gorillaz — Complete your account** arrives. Open its private link, choose and confirm a password, and press **Save password**. Opening the link alone does not complete activation. Sign in to the customer account and verify its account overview is available.
3. Sign out, use **Forgot your password?**, and request one reset for that same account. Confirm that **Spiritual Gorillaz — Reset your password** arrives. Complete its password form and sign in with the new password. A single attempt with the old password should be refused; do not loop invalid sign-ins or reset the administrator password.

Keep passwords and full email-token links private; no need to send them to the assistant. This test creates a customer account, not an order or payment, and registration must not enroll it in marketing. Existing registration/recovery availability depends on the current account and SMTP settings. Report a disabled or failed flow as such rather than marking it passed or opening production checkout.

The customer request handler enqueues mail without directly processing SMTP. Let the existing worker deliver the messages without pressing a manual delivery-batch button during the wait; report any manual processing separately. Receiving these emails and observing the actual scheduled job's execution are related but not interchangeable evidence. Do not invent a cron run from a message receipt alone.

## Source and boundaries

Read `views/public/customer.php`, `app/Customers.php`, `app/CustomerPortal.php` and `account/register.php` at 8378b39a30d3a8dac9792b6ef2f75a8f4c81ea51. They establish the labels, email subjects, explicit password completion, separate customer identities and queued-delivery behavior. This is source inspection, not a newly run account test.

No customer, password, message, queue, SMTP setting, cron job, payment, content or deployment was changed by the assistant. Five documentation files only; RC7 software, tests, workflows, schema, installer and all delivered archives remain unchanged. Content/artwork, protected recovery, actual scheduled processing, account/order email, genuine sandbox, physical-device and final owner approval retain their existing scope.
