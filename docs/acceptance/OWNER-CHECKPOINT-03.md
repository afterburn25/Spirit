# Owner checkpoint 03 — booking inquiry and both emails confirmed

## Direct user confirmation

After being asked to submit one marked booking inquiry and confirm that it appears in administration and both emails arrive, the user replied: **“yes all that works”**.

Record all three requested outcomes as **working according to the user**:

1. The inquiry appears under Bookings & communication > Booking inquiries.
2. The band notification arrives at the configured private booking-notification address.
3. The organizer acknowledgement arrives at the email entered on the form.

This completes the basic booking-inquiry and two-message acceptance check. Preserve the previously accepted installation/admin/report path and owner test-email receipt. Do not repeat these checks without a new specific problem.

This is direct user-reported acceptance, not an assistant-operated website or mailbox inspection. The inquiry reference, recipient addresses, message headers, inbox/spam placement and timing were not supplied. Do not invent those details or extend this result to all error/retry cases, newsletter consent, scheduled processing, account/order emails, merchant transactions, protected recovery or final launch approval. No test inquiry is deleted or converted into a real booking by this confirmation.

## Next check: newsletter signup, confirmation, welcome and unsubscribe

Use the existing public mailing-list form with an address controlled by the operator that is not already a confirmed subscriber. Do not use someone else's address, import customer lists or start a campaign. Leave working SMTP settings unchanged. If signup is intentionally unavailable, record that fact rather than silently enabling it or treating it as passed.

- Submit the consent form once. The confirmation message subject is **Confirm your Spiritual Gorillaz updates**. Under Bookings & communication > Subscribers, the address should be Pending until the confirmation is completed.
- Open the email link and click **Confirm my subscription** on the website. Check the status changes to Confirmed and the welcome message **You are on the Spiritual Gorillaz mailing list** arrives.
- Follow the welcome email's unsubscribe link and click **Unsubscribe from band updates** on the website. Verify the address becomes Unsubscribed. Opening either link alone is not the intended consent-changing action.

An already confirmed address does not receive a new confirmation from an ordinary signup request; use a separate controlled test address to exercise the full transition without changing an unrelated subscriber. Do not infer delivery solely from a queue status. Never blindly retry an UNCERTAIN message.

The labels, subjects and status transitions above were read from views/public/newsletter.php and app/Newsletter.php at 2bb04183befd84b25064e234a53607398b93e9f2. Quiet-site scheduled processing is a separate next step, not established by these interactive tests. Account/order email and the remaining launch milestones retain their existing scope.

No newsletter request, email send, queue processing, SMTP/configuration change, cPanel job, website publication or deployment was performed by the assistant in recording this report. Application and delivered package remain RC7; no runtime, test/workflow, schema, installer or archive is changed.
