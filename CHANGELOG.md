# Changelog

## Customer-account sequence passed — genuine sandbox checkout next; software remains RC7

Record **“yes all this passed”** as the user's acceptance of registration, activation-email receipt, password creation, sign-in/out, reset-email receipt, new-password sign-in and old-password rejection. Do not repeat those checks or infer passwords, token links, headers, a specific cron execution or final approval. Preserve checkpoints01–05. See `docs/acceptance/OWNER-CHECKPOINT-06.md`.

Prepare one isolated Authorize.net sandbox purchase with matching provider transaction, website receipt/history and one stock deduction. Read current order controls and official testing guidance; distinguish saved website Sandbox mode from the separate sandbox merchant interface's internal Test Mode. No actual merchant test has run. Five Markdown files only: no account/password/mail/queue, payment/stock/job/settings operation, runtime/VERSION/test/workflow change, live publication, deployment or archive rebuild. Other acceptance milestones remain open.

## Existing mail job confirmed — customer-account checks next; software remains RC7

Record **“yes next”** against the question about a listed `tasks/mail.php` cron job. Accept job presence without inferring cadence, paths, output or successful execution. Move past repeat setup questions while retaining quiet-site execution as a separate evidence item. Preserve all prior accepted installation/email/booking/newsletter checks. See `docs/acceptance/OWNER-CHECKPOINT-05.md`.

Prepare the existing customer activation/sign-in/reset sequence from current templates/controllers. Use an operator-controlled address in a separate private browser session, keep owner credentials private, and distinguish queued account-mail delivery from proof of cron execution. Five Markdown files only: no customer/password/email/queue/job/settings action, runtime/VERSION/test/workflow change, release or archive rebuild by the assistant. Final acceptance remains open.

## Newsletter flow confirmed — scheduled processing next; software remains RC7

Record **“yes they all work continue”** as the user's acceptance of the requested newsletter signup/confirmation/welcome/unsubscribe sequence. Preserve earlier installation/report, owner-email and booking checks without repeating them. No address, token, headers, inbox placement or timing is invented. See `docs/acceptance/OWNER-CHECKPOINT-04.md`.

Next inspect the existing cPanel mail job before changing schedules, resolve actual CLI/document-root paths, and distinguish scheduled execution from interactive sending and empty batches. Reread tasks/mail.php and current official cPanel guidance. Five Markdown files only: no worker run, job, configuration, subscription, queue, message, content publication, runtime/VERSION/test/workflow or prior archive changed by the assistant. Remaining acceptance and final release stay open.

## Booking inquiry and both emails confirmed — newsletter next; software remains RC7

Record **“yes all that works”** as the user's confirmation of the three requested booking-test outcomes: saved inquiry, band-notification receipt and organizer-acknowledgement receipt. Complete that basic check while retaining earlier accepted installation/report and owner-email results. Do not repeat them without a new specific problem. No reference, recipient address, message header or timing is invented. See `docs/acceptance/OWNER-CHECKPOINT-03.md`.

Next verify existing newsletter signup, explicit confirmation, welcome and unsubscribe with an operator-controlled test address; quiet-site scheduled delivery remains separate. Read current newsletter source for labels and transitions. Five Markdown files only: no assistant message send, subscription change, queue processing, setting/job change, content publication, runtime/VERSION/test/workflow or archive modification. Other acceptance gates and final release remain open.

## Owner test email received — booking verification next; software remains RC7

Record **“yes it does”** as the user's confirmation that the requested owner test email arrived. Complete that basic delivery check without inferring inbox/spam placement, headers, universal deliverability or other message workflows. Preserve the earlier accepted installation/report checkpoint. Next verify one marked booking inquiry and its two notifications; newsletter/scheduled delivery remain open. See `docs/acceptance/OWNER-CHECKPOINT-02.md`.

Documentation only. No email sent, queue processed, settings changed, job created, website content published, runtime/VERSION/tests/workflows changed or prior archive rebuilt by the assistant. No new automated test or final-launch acceptance claimed.

## User installation checkpoint — email verification next; software remains RC7

Record the user's **“yes all that works lets move on”** after the latest RC7 installation/upgrade/report instructions. Normal installation/upgrade/admin and Launch checks/report workflows are user-confirmed working. Stop requiring the same report or repeated setup before continuing. Separate user-reported normal operation from unobserved restore/rollback, real email, merchant and final acceptance.

Document the next existing communication flow: review queued mail, send the owner test, verify mailbox receipt, then booking/newsletter and quiet-site scheduled processing. Read actual controls and batch behavior from current source. No SMTP settings, queued mail, cPanel jobs, hosting data or public content changed; no new test pass or final-release approval is claimed. Documentation only; preserve runtime, VERSION, tests/workflows and all prior artifacts.

## Band and press content 03 — application remains RC7

Prepare a real one-page Band & Booking PDF and its reproducible source using only the user-confirmed lineup. Add reviewed-field worksheets for the Band page, three minimal member profiles, Press page and download entry. No invented biography, portrait, genre, venue, fee, recording-specific credit or replacement logo. The PDF is an actual document, not a website mockup or complete electronic press kit.

Six private field/slug sets pass unchanged ContentSchema without a database. Press publication remains blocked without an actual uploaded PDF ID. Poppler-rendered document visually inspected; names, roles, text bounds and two link annotations checked; byte-identical local rebuild. Current website availability was not verified. Exact hashes and boundaries are in `design/launch-review-03/README.md`.

Content artifact/source/docs only; no CMS upload/save/publication, new application VERSION, runtime, tests, workflows, installer, schema, payment or administration-state change. Part3 media and all prior delivered archives unchanged. Document review, actual content placement, remaining production artwork/biographies/photos and real-environment/final acceptance remain outstanding.

## Previous work

The full earlier changelog through the user-confirmed lineup is preserved verbatim in [docs/history/CHANGELOG-through-lineup-confirmation.md](docs/history/CHANGELOG-through-lineup-confirmation.md). The exact roster confirmation remains in `design/launch-review-01/CONFIRMED-LINEUP.md`; do not ask again. Earlier application evidence remains in TEST_RESULTS.md and PR20. Final1.0 is not released.
