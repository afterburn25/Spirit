# Consolidated final launch pass — RC10

## Latest user confirmation and workflow decision

Asked whether unpaid checkout cancellation restores the original stock and keeps the quantity correct after refresh, the user replied **“yes can we move on please”**. Accept that cancellation/stock-restoration/refresh check as passed according to the user. No order ID, quantity, database row or provider record was inspected by the assistant. Do not repeat this check without a specific new defect. The prior procedure remains in UNPAID-CHECKOUT-CHECK.md as historical instructions, not the latest status.

The user also wants to move beyond the serial confirmation loop. **Stop issuing one minor test question after another.** Keep accepted results closed, consolidate outstanding evidence into this final pass, and prioritize completion of the actual site presentation/content. Moving on changes the workflow; it does not declare unseen results passed, waive required release checks, authorize live charges or authorize publication of unapproved material.

## Accepted results — do not restart

| Area | Recorded scope |
|---|---|
|Installation, upgrade, administration and Launch checks/report access|User-confirmed normal operation, checkpoint 01.|
|Owner email|User-confirmed receipt, checkpoint 02.|
|Booking|User-confirmed saved inquiry and both notification/acknowledgement emails, checkpoint 03.|
|Newsletter|User-confirmed signup, confirmation, welcome and unsubscribe sequence, checkpoint 04.|
|Mail schedule configuration|User-confirmed existing tasks/mail.php cron entry, checkpoint 05; no duplicate setup.|
|Customer accounts|User-confirmed registration/activation/sign-in/password recovery and associated emails, checkpoint 06.|
|Unpaid checkout cancellation|User-confirmed original stock restored and stable after refresh, latest reply.|

These are owner-reported acceptance results, not new assistant-operated or automated test runs. Mail-job existence is not a particular execution record. Unpaid cancellation does not retrospectively confirm the earlier paid sandbox purchase, which was never reported passed.

## Workstream A — finish the actual site content

Use the existing CMS and locked design. No alternative mockups, optional features or arbitrary RC increment.

- Band and Press: carry forward confirmed Chad Lindsey (guitar, keyboards and vocals), Ron Green (harmonica) and Charlie Savell (drums). Use the prepared factual copy, member entries and Band & Booking PDF in design/launch-review-03; review before public placement. Preserve existing IDs, revisions and media, and reconcile existing records instead of duplicating them.
- Media: retain selected Candidate 3 / Part 3, its full prepared videos and matching poster. Obtain its actual approved YouTube/Vimeo destination and recording metadata rather than inventing a title or direct-MP4 feature.
- Presentation and remaining pages: finish approved logo/hero masters and labelled portraits; reconcile the existing Home/Music/Shows/Booking/Media/Merch copy proposals with actual launch content. Do not invent biographies, shows, products, rates or policy promises. Truthfully empty optional sections are preferable to sample inventory presented as genuine content; any change to the agreed launch scope requires explicit approval.

Preparation is not CMS publication. No connected site-editing session or new content upload was performed here. When remaining inputs are genuinely needed, gather them in one focused content request rather than interrupting repeatedly or re-asking confirmed names/choices.

## Workstream B — one consolidated remaining acceptance review

Retain the existing launch checklist's open items and evidence standards: genuine sandbox purchase/provider-ID-and-amount agreement, receipt/history/stock and notification/reporting/reversal/error paths; scheduled mail/payment worker execution; protected backup/restoration/rollback; populated-device, accessibility, important-URL and performance review. Group the relevant outcomes in a single review record instead of presenting them as a series of one-button gates.

Do not rerun accepted basic flows merely to fill a new worksheet. Perform only applicable outstanding checks and any regression tests justified by actual code changes. Credentials, full token links and private configuration remain out of chat/Git. Use authorized isolated environments; never change a production store, send real orders or blindly retry uncertain charges/reversals. Missing evidence stays pending, not passed. No live transaction, queue operation, cron change, restoration or deployment is authorized by this documentation update.

## Workstream C — explicit final approval and immutable release

After reviewed content is actually placed and remaining applicable acceptance is complete, present the final site and outstanding limitations together for owner acceptance. Then package/tag 1.0.0 against verified source under the existing release rules. Do not mark final approval from this request to move on. The delivered RC7 ZIP remains immutable. RC8 and RC9 remain immutable historical candidates. RC10 is the current verified candidate because genuine runtime/test changes landed after those releases; never overwrite historical candidate archives.

Application VERSION is 1.0.0-rc.10. RC10 is fully green on commit `2a833986f5a39d2f8de99f41bd8640b0642648e8`. This documentation pass is isolated on the final-prep branch and does not alter the verified RC10 runtime package. Preserve manual one-page A2/cPanel installation with no setup code/token/20i/automatic database; approved navy/gold/blue design, external venue ticket links and original private config/encryption secret/data/media/recovery materials remain fixed.
