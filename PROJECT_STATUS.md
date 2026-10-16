# Project status — RC7 / checkout verification in progress

## Latest continuation — no new pass inferred

After receiving the first sandbox-purchase instructions, the user replied **“continue”**. No purchase result was supplied. The paid Sandbox order, matching provider transaction, receipt/history and single stock deduction remain pending. The next independent test is an unpaid checkout cancellation using the existing protected test storefront; see `docs/acceptance/UNPAID-CHECKOUT-CHECK.md`. Source inspection confirms the actual controls and expected one-time stock release. No hosted cancellation test was executed or accepted by this preparation.

## Accepted user feedback retained

After the requested customer activation, sign-in/out and password-reset sequence, the user replied **“yes all this passed”**. Customer registration, activation-email receipt, password creation, sign-in, reset-email receipt, successful new-password sign-in and old-password rejection are **user-confirmed passed**. Do not repeat those checks without a specific new problem. No customer address, credentials, token, headers, timing or rows were inspected. See `docs/acceptance/OWNER-CHECKPOINT-06.md`.

Previously accepted outcomes remain: mail-job presence (checkpoint 05), newsletter signup/confirmation/welcome/unsubscribe (04), saved booking inquiry plus band and organizer emails (03), owner test-email receipt (02), and normal installation/upgrade/admin/Launch checks/report access (01). Do not reopen those loops. A listed cron job and successful account emails do not identify a particular scheduled execution or establish every hosting flag, protected recovery or final release acceptance.

## Current checkout work

On an already prepared isolated Sandbox storefront, one unit in the bag should not reserve stock. **Review order and total** creates the awaiting-payment order and one hold. Before paying, **Cancel this checkout and release stock** should leave that same order cancelled and return the quantity once; refreshing must not release stock twice. No card input, Pay click or real shipment is part of this check. Stop on submitted/held/uncertain payment states or unavailable prerequisites; do not force settings or data changes.

The first genuine Authorize.net sandbox purchase remains independently required under checkpoint 06. Confirm the SAVED website Sandbox mode and separate credentials; opening the settings tab is not enough. Keep production disabled in the isolated test setup and do not alter a real operating store. Use an operator-controlled buyer and isolated inventory; compare the paid order's transaction ID/amount with the actual sandbox account. Authentication alone is not payment acceptance.

Signed notifications, reporting, guest/declined/held/uncertain/duplicate paths, supported reversals, payment-worker execution and remaining order email remain open. Preserve working SMTP/cron settings, never use real cards or request keys in chat, and do not blindly retry uncertain payments. Actual scheduled-mail output remains a separate outstanding observation.

## Prepared content preserved

The lineup is user-confirmed: **Chad Lindsey — guitar, keyboards and vocals; Ron Green — harmonica; Charlie Savell — drums.** Do not repeat the roster question. Factual short entries and the Band & Booking Sheet PDF, reproducible source and six text-field sets remain in `design/launch-review-03/`. Its README preserves prior document/field checks and exact hashes. The document remains prepared for review and placement, not a complete EPK or a file already uploaded to the website.

**Candidate 3 / Part 3** remains selected with its prepared full videos and matching poster unchanged. Its actual approved YouTube/Vimeo URL and recording-specific title/credits remain open. No faces are identified, biographies invented or unrelated page copy approved by the account confirmation. Logo/hero artwork, labelled portraits, genuine shows/products/policies and content placement remain under Milestone 1. Rejected mockups in Issue 21 remain unapplied.

## Release boundary

Application and delivered installer remain **1.0.0-rc.7**, verified software baseline 5f721a3ab785690b99e966d6ca976e810c2431ee. This is a test-instructions/handoff documentation update only: no runtime, VERSION, test suite, workflow, schema, installer, payment, navigation state or prior archive changes. No website account, password, email, queue, SMTP setting, order, stock or cPanel job was changed by the assistant. No new automated execution or external pass is claimed.

Preserve the approved design, external venue ticket sellers, remembered administration menu and manual one-page A2/cPanel installation. No setup code, token,20i or automatic database. Existing sites upgrade with backups and preserve original private configuration/encryption secret/database/media/recovery receipts. Final1.0 is not released.
