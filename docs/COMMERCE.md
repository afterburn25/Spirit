# Merchandise and customer accounts

Catalog/account foundation introduced in0.7; optional checkout/orders introduced in0.8. Read current TEST_RESULTS.md and PAYMENTS.md for exact verification and provider limits. No live-payment release is claimed. Preserve original applied migrations/config/secret/media/data; new005 is additive.

## Owner workflow

Merchandise & customers manages optional categories, private drafts/public product snapshots and SKU inventory. Hidden categories hide associated public products and otherwise-private photographs. Product URL is stable; redirect-aware edits remain future work. Up to eight images, description and size/care notes; first selected image is primary. Images are contained rather than cropped.

Save a draft, add positive USD-priced variants with unique SKUs and size/color. New variants start at zero stock. Record signed quantity changes with reasons. Durable idempotency and revision checks prevent double adjustments/stale overwrites; history is audited. Prices/stock/active variants are immediate operational edits, distinct from product content drafts.

Publish requires approval, description, photograph and active priced variant. Sold-out items may remain published. Draft changes do not affect the public snapshot; unpublishing withdraws otherwise-private media. Store starts closed/no fake products. Opening published catalog also enables homepage merchandise. Listings/search/category filters are paginated. Customer owner controls expose name/email/status, not passwords/tokens; suspension revokes sessions/links. Staff delegation remains future work.

## Browser bag and order holds

Random HttpOnly/SameSite, Secure-under-HTTPS installation cookies identify30-day bags; only keyed hashes are stored. Bags are browser-owned, not automatically account-shared; customer logout disconnects the browser bag. No addresses/credentials in bag records.

All prices use integer cents and current server data. Quantity checks reject unpublished/inactive/hidden/unavailable items. Repeated identical submissions are idempotent. Unavailable lines stay removable and excluded from available subtotal. Limits20 per variant/30 variants. A bag itself never reserves stock.

When separately enabled and configured, .8 checkout computes shipping/tax and final totals before a payment. Preparing an order reserves available stock transactionally for15 minutes; successful payment consumes rather than rededucts it. Unknown payment acceptance retains reservation pending reporting. See PAYMENTS.md; provider acceptance is not implied. Expired bag tokens cannot access bags; retention cleanup remains later work.

## Customer identity and email

Separate customer tables prevent signup from setting staff roles. Enable registration/reset after SMTP and scheduled/owner processing are configured. Responses are neutral and timing-padded/rate-limited, not claimed perfectly constant-time. Account messages are queued rather than doing synchronous account-dependent SMTP.

Email-first activation lets only the mailbox holder select a password. Pending identities cannot sign in. Activation48 hours/reset30 minutes; random hashes, encrypted queued links, single-use and revocable. GET does not change credentials; explicit CSRF POST completes. Reset does not auto-login. Password change requires current password and revokes other sessions. Unused links and suspended-account mail are suppressed before delivery; in-flight mail cannot be recalled. Preserve the installation secret. Customer consent never becomes newsletter marketing consent.

Private account/token pages prohibit caching/referrers/indexing. Customer order history binds actual authenticated checkout identity, not unverified matching email. Guest receipt access uses session/private capability and is detailed in PAYMENTS.md.

## Address book and boundaries

Verified customers manage up to ten US addresses; all reads/updates/deletes bind ownership and revision. A guessed address ID cannot access another account. State/ZIP validation is not postal deliverability. International checkout/address normalization remain future work.

Tests cover decimal/input boundaries, draft/public/media authorization, variant prices/stock/replay/conflicts, bag isolation/tampering, email-first activation/reset/expiry/revocation, address ownership, owner suspension and additive upgrade preservation, plus actual desktop/mobile renders. Test data is not publicly seeded. No provider inbox, actual transactions, complete security/accessibility review or accepted1.0 claim.

Primary identity references: OWASP Forgot Password and Session Management Cheat Sheets; PHP password_verify documentation. Links: https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html ; https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html ; https://www.php.net/manual/en/function.password-verify.php .
