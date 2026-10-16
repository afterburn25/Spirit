# Remaining completion milestones — software baseline 1.0.0-rc.7

Optional features remain frozen. Complete the real presentation and existing acceptance steps, not arbitrary additional releases.

## Latest checkpoints

Normal installation, upgrade, administration and Launch checks/report access are user-confirmed working (OWNER-CHECKPOINT-01.md). Basic owner test-email receipt is user-confirmed (02). The booking inquiry, band notification and organizer acknowledgement are user-confirmed (03). Newsletter signup/confirmation/welcome/unsubscribe is user-confirmed (04). Existing mail-job presence is user-confirmed, not its exact scheduled execution (05). Do not repeat these gates without a specific new error or add a duplicate cron job.

The user now replied **“yes all this passed”** to customer registration, activation-email receipt, password creation, sign-in/out, reset-email receipt, new-password sign-in and old-password rejection. **The requested customer-account sequence is user-confirmed passed.** See `docs/acceptance/OWNER-CHECKPOINT-06.md`. Move to genuine sandbox checkout; no customer secrets or exact cron run are inferred from this confirmation.

| Final milestone | Current state | Remaining evidence |
|---|---|---|
|1. Production presentation and genuine content|In progress. Current roster confirmed; Band/Press copy and actual one-page PDF prepared. Selected Part3 files/poster retained.|Reviewed content actually placed; logo/hero masters, biographies/labelled portraits, provider links, genuine shows/merchandise and policies completed.|
|2. A2 installation, maintenance and recovery|Normal installation/upgrade/admin/report workflow accepted on the user's report. Move past repeated setup questions.|Protected backup/restore/rollback exercise and any specifically outstanding host safeguards are separate; no individual extension/permission result is invented.|
|3. Real email and scheduled processing|Owner test, booking/both emails, newsletter sequence and customer activation/reset emails/account transitions are user-confirmed passed. Existing mail cron job is reported present.|Actual scheduled execution/controlled queued delivery and remaining order/fulfillment/reversal-mail workflows. Job existence or account email receipt alone does not identify a particular cron run.|
|4. Genuine Authorize.net sandbox|**Next active milestone:** one isolated sandbox purchase, matching provider transaction and order receipt/history/stock. No sandbox result reported yet.|Saved website Sandbox mode and separate credentials; same transaction ID/amount at provider; paid receipt/history/stock, then signed notifications, interrupted/duplicate/held/declined/cancelled paths and supported reversals. Never activate production or use real cards for this test.|
|5. Final populated-site quality|Content preparation and earlier limited text checks only.|Real populated devices, keyboard/player/forms/checkout, important old URLs and performance reviewed; blockers fixed and final code regression-tested.|
|6. Owner acceptance and final1.0.0|Not reached.|Prior milestones accepted at their correct scope, final owner approval, immutable release/package/handoff and backed-up deployment plan.|

The application foundations and RC2–RC7 enhancements remain implemented. Application code and delivered RC7 archive are unchanged by this documentation update. Prior application evidence remains in TEST_RESULTS.md and PR20. No new automated execution, assistant-run account operation or provider transaction is claimed.

Do not repeat confirmation of Chad Lindsey (guitar/keyboards/vocals), Ron Green (harmonica), Charlie Savell (drums), or Candidate3 selection. See CONFIRMED-LINEUP.md, PART-3-PUBLICATION-PREP.md and `design/launch-review-03/README.md` for prepared assets and their limits. A test fixture, rejected mockup or local worksheet is not published content.

Keep the locked design, external venue ticket links, remembered menu and manual one-page A2 setup. No setup code/token/20i/automatic database. Existing sites upgrade with backups and keep original private configuration/data/media/recovery materials.
