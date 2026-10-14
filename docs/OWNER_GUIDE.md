# Owner guide — current development modules

Sign in with the owner created by browser installation. No public admin registration/default password exists. Back up before updates; existing supported installations use owner Database updates, never destructive reinstall. See INSTALLATION.md.

## Content workflow

Choose pages, releases, tracks, shows, members, gallery, videos or press downloads. Structured fields preserve the selected design. Save private draft leaves the published snapshot untouched; saved-draft preview is owner-only/no-store/noindex. Publish approved content updates public visibility/media references. Unpublish retains drafts. Stale revisions are rejected rather than overwritten. Revision history is stored; advanced restore/archive controls remain later work.

Media library accepts authorized JPEG/PNG/WebP, MP3/OGG/WAV and PDF. Images are decoded/re-encoded; scripts/SVG/archives refused. Unreferenced uploads stay private; published pages/products authorize their selected media. PDFs download. Provide meaningful alternative text and confirm usage rights.

Reserved page URLs home/band/booking/press feed their designed sections. Generic pages have separate URLs. Website settings control name/tagline/welcome. The selected home composition remains fixed; home content may select its hero image.

## Music and shows

Create/publish a release and its authorized uploaded tracks. Audio is user-initiated and persists during supported internal public browsing. External platforms use outbound links, not promised cross-platform continuous playback.

Shows require complete local date/time, timezone, venue/city and optional address. Paste the venue's HTTPS ticket-seller URL. Missing tickets, free/sold-out/postponed/cancelled labels are explicit; past events archive automatically. Do not seed concept examples as real bookings. No native tickets/scanning.

## Booking and communication

Inquiries save before email work. Review organizer details, assign owners, manage status and private notes. Stale edits and duplicate submissions are guarded. Inquiry confirmation is not a reservation or marketing enrollment.

Configure TLS SMTP, test with provider/mailbox, inspect retries/uncertain delivery. Verify acceptance before acknowledging duplicate risk on retry. Preserve the encryption secret. Newsletter requires separate consent and confirmation, supports unsubscribe and confirmed-only export. No campaign composer. See COMMUNICATION.md for quiet-site scheduled processing.

## Merchandise and customers

Open Merchandise & customers. Add categories/product drafts, uploaded photographs, SKU variants, prices and signed stock adjustments with reasons. Publish approved product snapshots separately from immediate price/stock changes. Catalog starts closed and contains no fake products. Images are contained, not cropped. Customer signup is email-first; customer identity never grants staff access. Addresses are customer-bound and initially US-only. Owner suspension revokes sessions. See COMMERCE.md.

## Orders and payments

Orders & payments adds delivery/tax/store terms, protected environment-specific Authorize.net values, private order review, fulfillment/tracking, notification inbox and reporting. Start in sandbox; no actual provider acceptance has yet been established. Save/test credentials only through protected administration, not chat/Git. Final totals are computed before payment and stock holds are transactional.

Payment and fulfillment differ. Do not fulfill unverified/held/uncertain orders. Never blindly retry an uncertain charge/reversal; reconcile the original provider transaction. Full void/refund is bounded to one reversal operation per order and does not automatically restock possibly shipped goods. Partial/external unmatched refunds require review. See PAYMENTS.md for exact operating limits, provider acceptance and separate payment/mail schedules.

## Still unfinished

Delegated staff management, complete automatic backup/restore/browser ZIP upgrades, retention, advanced CMS/SEO/accessibility/performance, final high-resolution artwork and staging acceptance remain. A development version is not the completed accepted1.0 site.
