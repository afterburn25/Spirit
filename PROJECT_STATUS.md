# Project status — RC7 / customer accounts passed; sandbox checkout next

## Latest user feedback

After the requested customer activation, sign-in/out and password-reset sequence, the user replied **“yes all this passed”**. Customer registration, activation-email receipt, password creation, sign-in, reset-email receipt, successful new-password sign-in and old-password rejection are **user-confirmed passed**. Do not repeat those checks without a specific new problem. No customer address, credentials, token, headers, timing or rows were inspected. See `docs/acceptance/OWNER-CHECKPOINT-06.md`.

Previously accepted outcomes remain: mail-job presence (checkpoint 05), newsletter signup/confirmation/welcome/unsubscribe (04), saved booking inquiry plus band and organizer emails (03), owner test-email receipt (02), and normal installation/upgrade/admin/Launch checks/report access (01). Do not reopen those loops. A listed cron job and successful account emails do not identify a particular scheduled execution or establish every hosting flag, protected recovery or final release acceptance.

## Next active work

One genuine Authorize.net sandbox purchase on protected staging or an explicitly isolated test storefront. Confirm the SAVED website mode is Sandbox with separate sandbox credentials; opening the settings tab is not enough. Keep production disabled and do not alter a real operating store for this test. Use an operator-controlled buyer and isolated inventory; review totals, pay once, and compare the order's transaction ID/amount with the actual sandbox account. Check the website receipt email, signed-in order history and single net stock deduction. A connection-authentication test alone is not payment acceptance. Read checkpoint06 for exact controls, official test values and the sandbox merchant interface's separate internal Test Mode distinction.

No sandbox result is claimed. Signed notifications, reporting, guest/declined/held/uncertain/duplicate/cancelled paths, supported reversals, payment-worker execution and remaining order email will follow. Preserve working SMTP/cron settings, never use real cards or request keys in chat, and do not blindly retry uncertain payments. Actual scheduled-mail output remains a separate outstanding observation.

## Prepared content preserved

The lineup is user-confirmed: **Chad Lindsey — guitar, keyboards and vocals; Ron Green — harmonica; Charlie Savell — drums.** Do not repeat the roster question. Factual short entries and the Band & Booking Sheet PDF, reproducible source and six text-field sets remain in `design/launch-review-03/`. Its README preserves prior document/field checks and exact hashes. The document remains prepared for review and placement, not a complete EPK or a file already uploaded to the website.

**Candidate 3 / Part 3** remains selected with its prepared full videos and matching poster unchanged. Its actual approved YouTube/Vimeo URL and recording-specific title/credits remain open. No faces are identified, biographies invented or unrelated page copy approved by the account confirmation. Logo/hero artwork, labelled portraits, genuine shows/products/policies and content placement remain under Milestone 1. Rejected mockups in Issue 21 remain unapplied.

## Release boundary

Application and delivered installer remain **1.0.0-rc.7**, verified software baseline 5f721a3ab785690b99e966d6ca976e810c2431ee. This is an acceptance/handoff documentation update only: no runtime, VERSION, test, workflow, schema, installer, payment, navigation state or prior archive changes. User-reported account success is not a new assistant-run test or confirmation of all communication/payment workflows. No website account, password, email, queue, SMTP setting, payment, stock or cPanel job was changed by the assistant.

Preserve the approved design, external venue ticket sellers, remembered administration menu and manual one-page A2/cPanel installation. No setup code, token,20i or automatic database. Existing sites upgrade with backups and preserve original private configuration/encryption secret/database/media/recovery receipts. Final1.0 is not released.
