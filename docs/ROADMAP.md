# Cumulative release roadmap

Current cumulative development checkpoint: **0.6.0-dev.1**. Full application verification passed for dd0db412 in Actions **38479213942**. Consult docs/TEST_RESULTS.md for evidence and boundaries. A development version is not final production acceptance of every prior milestone.

| Target | Scope | Current status |
|---|---|---|
| 0.1.0 | Shared-host installer, private runtime, owner auth, root/subfolder, lock/removal | Implemented and regression-tested. Actual host acceptance remains. |
| 0.2.0 | Selected public design and responsive pages | Real components tested at desktop/mobile widths; approved-reference crops included; production art polish remains. |
| 0.3.0 | CMS/drafts/previews, band/media/press | Owner workflows implemented and tested. Staff delegation and advanced revision restore/pagination remain. |
| 0.4.0 | Releases/tracks/streaming and persistent local audio | Real playback and persistent-navigation checks pass with production CSP. Actual band audio approval remains. |
| 0.5.0 | Shows/detail/archive/filter/external tickets | Implemented and tested. No native ticket issuance or scanning. |
| 0.6.0 | Booking, transactional email, newsletter opt-in | Implemented; full unit, SQL/HTTP, TLS failure/recovery and desktop/mobile browser suite passed. Provider inbox and host acceptance remain. |
| 0.7.0 | Merch catalog, variants, stock, cart, customers | Next implementation; store remains closed, no purchase claim. |
| 0.8.0 | Authorize.net sandbox/payments/orders/fulfillment | Planned; no real credentials or transaction results claimed. |
| 0.9.0 | Permissions, backup/restore/ZIP updates, audits/search/quality | Additive schema foundation exists; full maintenance and acceptance remain. |
| 1.0.0-rc.N | Complete staging/content/provider/browser/host acceptance | Not reached. |
| 1.0.0 | Complete tested and explicitly accepted release | Not released. |

Full packages are cumulative and immutable per delivered version. Preserve configuration/data/uploads. Do not add redesigns, native ticketing, newsletter campaigns or live deployment silently. No final release without complete scope and explicit acceptance.
