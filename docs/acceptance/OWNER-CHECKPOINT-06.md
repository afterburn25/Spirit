# Owner checkpoint 06 — customer accounts passed; sandbox checkout next

## Direct user confirmation

After the requested customer registration, activation-email receipt, password creation, customer sign-in/out, reset-email receipt, new-password sign-in and old-password rejection checks, the user replied **“yes all this passed”**. Record that requested sequence as **passed according to the user**. This completes the basic customer-account and activation/reset email acceptance check. Do not ask for another registration or password reset without a specific new problem.

No customer address, password, token link, message headers, timing or database rows were supplied or independently inspected. The previous instructions requested no manual mail-batch processing; the response confirms the requested flow but does not identify a particular cron execution or provide its output. Account email receipt and the previously reported mail-job presence remain distinct from independently observed scheduled-worker execution. Preserve checkpoints 01–05 and do not reopen accepted installation, owner-email, booking or newsletter tests.

## Next bounded test — one genuine Authorize.net sandbox order

Use protected staging or an explicitly isolated test storefront, an operator-controlled customer account and a test product/inventory record intended for that environment. Do not switch a live store that is accepting real orders, expose dummy merchandise to customers, or change production credentials merely to perform this test. No test purchase should trigger real packing or shipment.

In existing Orders & payments controls, open Authorize.net / Payment configuration and Sandbox settings. The saved **Current mode** must be sandbox; merely opening the Sandbox settings tab does not select that mode. Use credentials issued for the separate sandbox account. Keep keys and owner passwords out of chat/Git. **Test saved API authentication only** performs no charge and is only a prerequisite, not proof that checkout works. Do not type ENABLE LIVE PAYMENTS or enable production.

Authorize.net's separate sandbox merchant interface should have its internal Test Mode disabled (it calls this Live Mode), while the WEBSITE remains in Sandbox with sandbox credentials. This is not an instruction to activate production: sandbox transactions do not reach financial institutions. Sandbox Test Mode responses return transaction ID zero and do not produce stored transactions suitable for this test. Verify that the merchant interface is the sandbox before changing any internal mode.

Use the official Visa test number 4111111111111111, a future expiration such as 12/2030, and CVV 900 (documented successful CVV match) only in the sandbox checkout. Use the operator's controlled contact details, not another person's card or address. Card numbers in this paragraph are public test data, not live credentials.

Before adding one unit to the bag, note that variant's available stock in the isolated environment. Review item price, shipping, configured tax and final total before paying once. If the result is held, processing, uncertain or an error, report it rather than clicking Pay repeatedly or creating another order to resolve it.

Check these outcomes independently:

1. The website order is labelled Sandbox and paid, with one provider transaction ID and the expected total.
2. The SAME transaction ID and amount appear in the matching Authorize.net sandbox account, with an approved transaction (which may still await settlement). A success page or an authentication test alone is insufficient.
3. The website-generated sandbox receipt email arrives at the controlled buyer address and the signed-in customer's order history includes that same order.
4. For one purchased unit, available stock is one below its pre-checkout value overall. The checkout hold already reduces available stock; approved payment must not deduct it again. Refreshing the receipt does not create another order/payment or another stock deduction.

No successful sandbox transaction is yet reported. If sandbox credentials, an isolated storefront, or the required approved delivery/terms setup is unavailable, record that missing prerequisite rather than substitute production or make up policy/tax settings. Do not loosen security/permission checks to force payment through.

## Later checks remain separate

This first purchase will not alone verify signed notifications, authenticated reporting reconciliation, guest receipts, declines, held/uncertain/interrupted/duplicate paths, unpaid cancellation/expiry, void before settlement, refund after settlement, payment-worker execution or order/fulfillment/reversal email. The existing code supports one full reversal operation per order; void and refund testing need separate eligible sandbox orders. Never repeatedly submit an uncertain charge or reversal. Test fulfillment does not authorize actual shipping. Protected recovery, production artwork/content, real-device review and final owner approval also remain open.

Source read for this sequence: docs/PAYMENTS.md, app/OrderAdmin.php and views/admin/orders.php at 39428c9e04b0628ab50a4866ffbd61feed46abdd. Current primary testing reference: https://developer.authorize.net/hello_world/testing_guide.html (separate sandbox credentials, no bank processing, internal Live Mode, public test card and CVV response values). Those are documentation reads, not a newly executed provider test.

No account/password/email/queue, payment setting, order, stock, provider call, cron job, public content or deployment was changed by the assistant. Documentation only; application VERSION and delivered package remain RC7. No new automated run, runtime/schema/workflow/installer change, archive rebuild or final-release approval.
