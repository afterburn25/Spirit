# Unpaid checkout cancellation — next test, result pending

## Scope of the continuation

The user replied **“continue”** after instructions for the first genuine Authorize.net sandbox purchase. This is permission to continue the work, **not confirmation that the purchase passed**. Keep that purchase, matching provider transaction, receipt/history and paid-stock checks pending under OWNER-CHECKPOINT-06.md. There is no new owner acceptance result in this document.

The next independent checkout check is cancellation before payment. It exercises the existing reservation/release path without entering card details or submitting a charge. It does not require a previous successful purchase, but it still requires the existing checkout prerequisites. Do not bypass disabled checkout, invent credentials/tax/policy settings or switch an operating production store into sandbox.

## Safe test setup

Use protected staging or an explicitly isolated test storefront already configured in saved Sandbox mode. Use the operator's existing test customer, a test-only delivery address, and one inventory item designated for that environment. Do not expose dummy merchandise publicly or affect real orders. Leave the production merchant and working SMTP/cron settings unchanged. Do not start another checkout for a bag already awaiting payment verification; stop and resolve that order separately.

Use one unit and note the exact variant/SKU's starting available stock, N. Keep other stock edits/orders out of this isolated test so the comparison is meaningful. A separate owner browser session can inspect inventory while the customer window performs checkout; do not replace the owner session with a customer login.

## Existing workflow and expected observations

1. Add one unit to the customer's bag. Available stock remains N: adding to a bag does not reserve inventory.
2. Continue to checkout, use the isolated test delivery details, review the displayed terms, and select **Review order and total**. This creates the unpaid order and its stock hold, not a payment. The order must show **SANDBOX ORDER** and **Awaiting payment**. Note its reference locally. Available stock should be N minus one.
3. Leave card fields blank and do not click Pay. Before the displayed hold expires, select **Cancel this checkout and release stock** on that unpaid order.
4. The same order should show **Checkout cancelled** and no payable card form. Available stock should return to N. In owner order details, the reservation should be released and there should be no newly created payment operation or provider transaction ID for this never-paid order.
5. Refresh that same cancelled receipt and the inventory display. It must remain cancelled and available stock must still be N, not N plus one. No duplicate order or second release should appear. Items remaining in the bag are not themselves a failure; a bag is not a reservation.

Example only: starting stock 5 -> bag 5 -> unpaid reservation 4 -> cancellation 5 -> refresh 5. These numbers are illustrative, not an instruction to seed or alter inventory.

If the receipt instead shows paid, processing, held, uncertain or reversal states, this is not the eligible cancellation test. Do not force a cancel, retry Pay, create another charge or manually edit database state. If the hold already expired, record that as a different path rather than claiming the cancellation test passed. If checkout is unavailable, report the missing prerequisite and leave this check pending. No credentials, card details, full private receipt links or customer data need to be shared.

## Source inspection, not execution

Read source at 2bb2367702feb3e9e19910aeea1c1ad044a89e1d:

- app/CheckoutPage.php (blob a34f97de4005d4548d6d1825789a2d14f605e34f): customer order access, CSRF and separate prepare/pay/cancel handlers.
- views/public/checkout.php (blob 7fa824386fdb2608b58037c2edc641bf24c7684a): actual review/cancel labels, sandbox banner, 15-minute reservation notice and the nonpayable cancelled receipt.
- app/Orders.php: prepare deducts the held quantity once; cancel accepts awaiting_payment and releases held stock; already cancelled/expired orders return without a second release; submitted payment states are refused. No gateway request occurs in the cancellation method.

These are expected behavior and existing code boundaries, not a newly executed automated or hosted test. No order, stock, payment, queue, settings, cron job or deployment was changed by the assistant. No result is marked passed until the operator reports it. A later confirmation of this cancellation check must not silently count as confirmation of the earlier paid purchase.

Software remains RC7; runtime, VERSION, tests, workflows and delivered archives are unchanged. Actual paid sandbox transactions, signed notifications/reporting, decline/held/uncertain paths, eligible full reversals, scheduled-worker observations, protected recovery, final content/artwork, physical-device review and owner release approval remain separate.
