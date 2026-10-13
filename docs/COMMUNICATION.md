# Booking, email and subscriber administration

Development checkpoint 0.6.0-dev.1. Read TEST_RESULTS.md for exact verification and outstanding provider/host checks.

## Booking inquiries

Visitors submit event/contact details and separate consent to be contacted about the inquiry. A nonce and database unique key prevent repeated submissions creating duplicate inquiries. The receipt is tied to that visitor's session and is not publicly retrievable by its reference.

Admin > Booking inquiries provides status/search filters, pages of 25 records, organizer details, assignment to active owners and private notes. Stale edits are refused. Confirmed is an owner-managed inquiry status, not a contract, charge, reserved inventory or automatically published show. Booking never opts an organizer into marketing.

The inquiry is saved before notifications. If queue creation fails, the inquiry retains pending notification state; the next worker reconciles it. Owner/customer notices are inserted atomically and use unique deduplication keys. No inquiry details or private notes are exposed in public pages or queue error messages.

## Configure and verify SMTP

Admin > Email settings stores From name/address, SMTP host/port/username/password, private booking-notification address and optional public contact address. Blank SMTP password preserves the saved value; clearing is explicit. Only the Public contact email appears on the site. Blank private booking email uses the first active owner's address.

Supported transport is password-based AUTH LOGIN over verified STARTTLS (commonly 587) or implicit TLS (commonly 465). Plaintext transport, untrusted certificates and hostname mismatches are refused. Providers requiring OAuth or other authentication methods are not supported by this transport. Confirm the provider's credentials, sender authorization and supported mechanism outside the code tests.

SMTP credentials and queued message bodies are authenticated-encrypted using a key derived from the account-private installation secret. Never remove or rotate that secret casually. Back up the private config securely alongside the database; losing the key makes stored secrets/bodies unreadable.

Enable delivery, then send an owner test and inspect the provider AND receiving mailbox. A configured connection or SMTP acknowledgement is not guaranteed inbox delivery. Provider/domain SPF, DKIM, DMARC and deliverability acceptance are separate deployment checks.

## Queue and retries

Admin > Delivery queue displays recipient, subject, status, attempts and redacted diagnostic codes, not message bodies or secret tokens. Sent means SMTP acknowledged acceptance. Definite failures retry with backoff, stop after five attempts and allow an owner retry. Uncertain means acceptance could not be verified or a worker was interrupted during sending; it never automatically resends. Verify the provider/mailbox before acknowledging duplicate risk and retrying.

The worker uses a database advisory lock to prevent concurrent sending. Expired or superseded subscription messages are cancelled before delivery. An unsubscribe that races with an already in-flight SMTP send cannot recall that message; future eligible sends remain suppressed.

A form submission attempts a small shutdown batch after saving. This is best-effort, not a permanent background daemon; some PHP/server configurations may delay completing the response while it runs. Manual owner processing works without cron. For reliable retries on a quiet website, use a hosting-panel scheduled PHP task, for example every five minutes:

```text
/usr/bin/php /home/ACCOUNT/public_html/band/tasks/mail.php --document-root=/home/ACCOUNT/public_html
```

Use the host's actual PHP binary and paths. `--document-root` is the server's public document root, not automatically the installation subfolder. No SSH is required when the panel supports scheduled commands. The worker is CLI-only, rejects HTTP invocation and does not put credentials in the command. Review queue diagnostics; do not treat schedule creation as verified sending.

## Newsletter consent

Enable mailing-list signup only after SMTP configuration. The website then exposes signup with explicit marketing consent. A pending address is NOT a confirmed subscriber. The confirmation link expires in 48 hours; only its hash is stored outside the encrypted message body. A second request inside the cooldown does not enqueue another confirmation. A successful confirmation queues a welcome containing an unsubscribe link.

Opening a confirmation or unsubscribe link does not change state; a CSRF-protected confirmation button performs the action, avoiding email-scanner GET requests changing consent. Token pages are no-store/no-referrer/noindex. Unsubscribe is idempotent. Old confirmation/welcome messages are cancelled when no longer consistent with the subscription state.

Admin > Subscribers lists pending, confirmed and unsubscribed addresses. Export includes only currently confirmed addresses, their confirmation timestamp, consent version and source; spreadsheet formula prefixes are escaped. Re-export immediately before a permitted mailing and respect later unsubscribe changes. This checkpoint does not include a marketing campaign composer or mailing provider synchronization. An old export is not a live suppression list.

## Deployment boundaries

The new schema is additive. Existing supported 0.5 installations upload reviewed code and use Admin > Database updates; never rerun an unlocked installer or drop tables. Preserve all original config, uploaded media and content. Full ZIP upgrade/backup/restore automation is not yet implemented. Staff delegation remains later work; these screens are owner-only. No real provider mailbox delivery or production deployment has been verified merely by the tests.
