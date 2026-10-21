# Checkout, orders and payment operations — 0.8.0-dev.1

Development implementation. Read TEST_RESULTS.md for the exact tested commit and boundaries. The application has not been accepted against a real Authorize.net merchant account. Do not enable production solely because synthetic tests pass.

## Configuration and staged acceptance

The owner sidebar includes Orders & payments. Save Shipping, tax & store terms first. Initial delivery support is the United States: a flat shipping amount, explicitly configured state rates and optional state/ZIP overrides, plus whether shipping is taxable. Rates are entered as percentages with up to four decimal places; calculations use integer cents and an integer rate. Unsupported destinations fail closed. These are merchant-configured rules, not an automatic tax service or legal determination. State-level or ZIP-level rules may be insufficient for a particular jurisdiction. Independently verify rates, destination boundaries, shipping treatment and rounding before opening sales. Address validation checks syntax, not postal deliverability.

Store terms must be approved before enabling checkout. Each order retains the accepted terms and its calculated totals. Changes to current prices or policies do not rewrite an existing order. The amount is displayed before payment. This checkpoint does not implement carrier quotes, discount codes, partial fulfillment or international checkout.

Payment settings separate sandbox and production credentials. Configure API Login ID, Transaction Key, Signature Key and Public Client Key. Secrets are encrypted using the private installation secret and never reflected in the owner form. Blank secret fields preserve existing values. The current owner password is required to save credentials, test API authentication, void or refund. Each environment's merchant Login ID becomes fixed after initial configuration; credential rotation is allowed, silent merchant migration is not.

Start in sandbox. The settings screen's authentication test performs no charge and does not certify checkout, webhooks, PCI scope or merchant approval. Production additionally requires typing `ENABLE LIVE PAYMENTS`. This explicit gate is not a substitute for the acceptance checklist below. Never put credentials in Git, support screenshots, issue comments or chat. Preserve the private installation secret with encrypted backups; losing it makes protected settings and private order data unreadable.

## Browser payment and PCI boundary

The selected navy/gold design extends to guest checkout, final review, tokenized card form, private receipt and owner order screens. The official Accept.js library is loaded from the matching Authorize.net environment on payable order pages only. Sensitive card inputs have no HTML `name` attribute. JavaScript sends them directly to Authorize.net, clears them after tokenization and submits only the opaque token, CSRF and operation nonce to the application. A missing library fails closed. The payment handler rejects unexpected fields before creating an operation. No PAN or security code is stored; only a provider-masked last-four reference may be retained for supported refunds. Single-use opaque values are not stored or logged.

A custom Accept.js form is not a promise of zero PCI obligations. Authorize.net documents SAQ A-EP for this custom-form integration and a different scope for its hosted form option. The merchant must confirm applicable compliance requirements and complete its own review. Never add analytics/session replay to sensitive checkout fields. Keep actual production CSP and provider domain behavior in the staging acceptance tests.

## Inventory and irreversible operations

Preparing an order locks the browser bag, product records and variants and rechecks current public visibility, price and stock. It deducts available stock into a 15-minute hold and writes an immutable item/price/address/policy snapshot in one database transaction. Repeating the same preparation cannot create a second hold. Only one active checkout is permitted for a bag. A bag by itself does not reserve stock.

Cancellation and expiry release only unsubmitted holds, exactly once. Definite declines release a hold. Processing, held and uncertain payments never expire into freely available stock: a transaction may have been accepted. These states require authenticated provider reconciliation. The available quantity shown in administration already excludes held stock.

Before a charge API call, the application commits a durable payment operation and processing state. Concurrent or repeated payment submissions cannot make a second charge for that order. A network timeout, ambiguous response, duplicate-transaction response, worker interruption or database failure after possible acceptance must not be blindly retried. The order is uncertain, or remains processing until maintenance flags it. Report the existing transaction rather than requesting another charge. An approved result consumes the existing hold without deducting inventory a second time; only purchased quantities are removed from the original bag.

A returned browser URL is informational and cannot mark an order paid. Authenticated reporting must match environment, merchant, invoice, amount and transaction type. Reporting does not preserve the original request `refId`; a signed webhook supplies `merchantReferenceId`. Automatic unknown-transaction recovery therefore requires that signed correlation, a previously known transaction ID, or an explicit owner-selected order/transaction reconciliation. An unexpected payment for already released inventory is held for owner review rather than treated as fulfillable.

## Webhooks and quiet-site processing

Configure separate HTTPS receiver URLs in the matching Authorize.net account:

- `https://YOUR-SITE/OPTIONAL-SUBFOLDER/payments/sandbox.php`
- `https://YOUR-SITE/OPTIONAL-SUBFOLDER/payments/production.php`

Subscribe to relevant payment auth-capture, fraud held/approved/declined, void and refund notifications. Copy the Signature Key exactly. Signature validation uses HMAC-SHA512 over the unmodified request body with that copied key string and a timing-safe comparison. Do not decode/re-encode JSON before hashing. The receiver validates first, persists a bounded notification and deduplicates notification IDs before acknowledging. It performs no charge and no provider lookup before acknowledgement. Matching signatures alone are not payment evidence; the reporting worker verifies current provider state.

Use the owner Payment notifications screen to process or retry reporting lookups. A failed lookup is not a new payment attempt. For regular processing on a quiet shared-host site, configure the hosting panel's scheduled PHP command, for example every five minutes:

```text
/usr/bin/php /home/ACCOUNT/public_html/band/tasks/payments.php --document-root=/home/ACCOUNT/public_html
```

Use actual host paths and PHP executable. This runs bounded expiry/interruption/receipt maintenance and signed-notification reporting. Keep the separate `app/tasks/mail.php` schedule for queued email. Both workers are CLI-only; no public cron token or arbitrary web execution endpoint exists. The document-root argument is the server's actual document root, not automatically the application subfolder. These tasks are not permanent background daemons. Verify their execution and queue states on the real hosting account.

## Receipts, customer access and fulfillment

Customer-account order history includes only orders attached to that authenticated customer at preparation time, not every order with a matching email. Guest receipts require the originating session or a private 30-day capability link. The reference alone does not reveal addresses or purchase details. Capability links are removed from the address bar after granting session access; private pages are no-store, no-referrer and noindex. An expired guest link requires owner support; automatic guest-link resend is not included yet.

Payment and fulfillment states are separate. Only paid orders with consumed inventory can be marked processing, shipped or completed. Shipment requires carrier and tracking information. Revision checks reject stale owner edits. Receipts and fulfillment notices use the durable encrypted mail queue. A receipt-queue fault never reverses a successful payment; maintenance reconciles missing paid-order receipts. Sandbox orders and messages are clearly labeled test transactions. SMTP acceptance is still not a guarantee of inbox delivery.

## Voids, refunds and explicit limitations

The owner may request a full void before settlement or a full refund after settlement, with current-password reauthentication and explicit confirmation. The application verifies original transaction status and total first. Refunds use only the provider's last-four card reference, not a stored card number. The durable reversal operation precedes its irreversible API call. Repeating a reversal cannot call the payment mutation twice. Unknown results remain reversal-uncertain until reporting resolves them.

This checkpoint supports one reversal operation per order. Rejected or uncertain operations cannot be blindly resubmitted. Partial refunds, externally initiated unmatched refunds and chargebacks need provider/owner review and remain visible in the notification diagnostics rather than being silently claimed as successful. A refund does not automatically restock potentially shipped goods. Record any actual return as a separate audited inventory adjustment. Final release acceptance must decide whether these supported limits fit the store's operating procedures; they are not hidden behind a nominal completed-version label.

## Reproducible test boundary

CI blocks real payment-network calls with a trusted process environment setting. SQL/HTTP tests use a CLI-only synthetic gateway transport, real database locks and realistic reporting shapes, including the absence of transaction `refId`. Separate transport tests exercise actual cURL HTTPS against disposable loopback TLS servers, including certificate/hostname failures, timeouts, redirects and malformed/oversized responses. Browser tests replace the external SDK only inside Playwright and capture the token-only submission; they then exercise the real service with the synthetic gateway boundary. There is no production route to select test behavior, and no actual Authorize.net sandbox acceptance is implied.

Run the full existing installer/content/communication/commerce suite plus payment units, concurrent stock/charge tests, signed native-HTTP notification tests, interrupted/ambiguous payment recovery, full void/refund boundaries, owner/guest/customer authorization, additive .7 upgrade and desktop/mobile checkout rendering. Publish results only for the exact tested code.

## Required real-provider and host acceptance

Before live sales, verify actual merchant sandbox activation, matching SDK/API credentials, real SDK tokenization, approved and declined sandbox charges, AVS/CVV behavior, held transactions, true provider-signed notifications, merchantReferenceId correlation, server reporting, void/refund settlement timing, delivery workers, receipts, HTTPS/CSP and hosting access rules. Verify the actual approved product content, inventory, shipping/tax/store policies, PCI/security review, backups and recovery. Do not perform a live charge without explicit authorization. No completed v1.0 or live deployment is established by this development checkpoint.

Primary implementation references consulted: Authorize.net Accept.js documentation (`https://developer.authorize.net/api/reference/features/acceptjs.html`), Webhooks documentation (`https://developer.authorize.net/api/reference/features/webhooks.html`), Payment Transactions (`https://developer.authorize.net/api/reference/features/payment_transactions.html`) and the official SDK transaction-details contract (`https://github.com/AuthorizeNet/sdk-php/blob/master/lib/net/authorize/api/contract/v1/TransactionDetailsType.php`).
