# Merchandise and customer accounts — 0.7.0-dev.1

Cumulative development milestone, not a live-payment release. See TEST_RESULTS.md for exact execution evidence. Existing 001–003 migrations are unchanged; 004 adds only new tables. Preserve the approved design and account-private config/secret, media, bookings and subscribers.

## Owner workflow

Open **Merchandise & customers** in the owner sidebar. Categories are optional. A hidden category hides associated public products, direct detail links and otherwise-private product media.

Create a product draft with stable URL name, description, size/care information and up to eight uploaded photographs. The URL remains immutable until a redirect-aware editor exists. Save the draft, then add variants. Each SKU has size/color, positive USD price and active flag. New variants start with zero stock. Record a signed stock adjustment and reason. Adjustments use a durable idempotency key, revision check and audit history; replays and stale edits cannot double-apply or overwrite newer inventory.

Publication needs approval, description, photograph and active priced variant. Sold-out products may remain published. **Saving a private draft does not alter its published snapshot.** Unpublishing also withdraws otherwise-private photos. Images are contained, not cropped. Price, stock and active-variant edits are explicitly immediate operational changes, separate from content drafts.

Store settings starts closed with no fake products. Opening it displays published products and a homepage collection. Listing/search/category filters are paginated. Customer administration shows name/email/status, not password hashes or tokens. Suspending a verified customer revokes sessions and account links; restoration preserves the password. These screens are owner-only until staff delegation is implemented.

## Browser-owned bag

Bags persist up to 30 days using random HttpOnly/SameSite cookies, Secure under HTTPS and installation-scoped. Only a keyed hash is stored in the database. Bags are separate from customer identity; a shared browser can see its existing bag, so customer logout disconnects that browser association. No address or credential is stored in a bag.

Server-only USD calculations use integer cents. Quantity changes reject unpublished/inactive merchandise, hidden categories, insufficient stock and malformed values. Identical repeated submissions do not add twice. Every view rereads current prices and stock. Unavailable quantities remain removable, are clearly marked and excluded from the available-item subtotal. Limits: 20 per variant, 30 distinct variants. A bag is **not a stock reservation**.

Checkout, shipping/tax calculations, order creation and payment forms are closed until .8. No card details are requested and no fake order history exists. Payments must revalidate and atomically reserve inventory rather than trust an earlier bag view. Expired tokens cannot retrieve bags; automated retention cleanup remains .9.

## Customer identity and email

Customer records are separate from staff users; registration cannot set an administrator role. Enable registration/recovery after configuring transactional email and scheduled/owner queue processing. Account messages are queued rather than synchronously attempting SMTP based on account existence. No provider inbox delivery is implied.

Registration is **email first**. Name/email/consent request an activation link; only its recipient chooses a password. This avoids pre-registering another person's email with an attacker-known password. Pending accounts cannot sign in. Registration/reset responses do not disclose whether an address is eligible. Rate limits, cooldown and response-time padding reduce abuse; perfect constant timing is not claimed.

Activation expires after 48 hours; resets after 30 minutes. Tokens are random, hashed in token storage, encrypted in queued bodies, single-use and revocable. GET does not mutate credentials. CSRF-protected completion sets a password and invalidates outstanding account links. Reset does not automatically sign in. Password change requires the current password and revokes other sessions. Account/token pages prohibit caching/indexing/referrer leakage. Used/expired/suspended account messages are cancelled before delivery; a message already in flight cannot be recalled.

Configure and verify the hosting-panel mail schedule or owner queue process. Preserve the installation encryption secret in backups. Real credentials, mailbox acceptance and provider authentication compatibility require deployment testing. Customer account consent never opts a person into newsletter marketing.

## Address book

Verified customers may save, edit or delete up to ten **United States** addresses. Revision checks protect edits; every operation binds the signed-in customer, not just a submitted ID. Another customer's guessed ID cannot reveal/modify/delete an address. State is text and ZIP syntax is checked; this is not postal deliverability validation. International shipping and address normalization remain future work.

## Verification

Tests cover money/input boundaries, product publication/private drafts/media access, SKU stock/replay/conflicts, bag isolation/price spoofing/stale availability/replay, email-first activation, reset/expiry/session revocation, address ownership, owner suspension and additive .6 upgrade preservation. Browser journeys render actual products/bags/customer pages and owner catalog screens at desktop/mobile sizes. Synthetic fixtures remain isolated and are not seeded publicly.

References consulted: OWASP Forgot Password and Session Management Cheat Sheets; PHP password_verify documentation. Exact sources: https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html ; https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html ; https://www.php.net/manual/en/function.password-verify.php .

Payments/orders, staff delegation, backup/restore/browser upgrades, retention, broader security/accessibility/performance and owner/provider/host acceptance remain. Do not call this an accepted v1.0 release.
