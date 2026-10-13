# Cumulative release roadmap

Current development checkpoint: **0.6.0-dev.1**. Consult docs/TEST_RESULTS.md for exact verification. A development version is not acceptance of every prior milestone.

| Target | Scope | Current status |
|---|---|---|
| 0.1.0 | Shared-host installer, private runtime, owner auth, root/subfolder, lock/removal | Implemented with additive schema and recovery tests. Actual host acceptance remains. |
| 0.2.0 | Selected public design and responsive pages | Real components implemented; approved-reference crops included; production art polish remains. |
| 0.3.0 | CMS/drafts/previews, band/media/press | Owner workflows implemented. Staff delegation and advanced revision restore/pagination remain. |
| 0.4.0 | Releases/tracks/streaming and persistent local audio | Implemented. CSP-compatible real playback regression test added; actual band audio approval remains. |
| 0.5.0 | Shows/detail/archive/filter/external tickets | Implemented. No native ticket issuance or scanning. |
| 0.6.0 | Booking, transactional email, newsletter opt-in | Recovered and integrated; notification recovery, owner inbox/queue/settings, consent/token/export and full new tests added. Exact new run pending. Provider inbox acceptance remains. |
| 0.7.0 | Merch catalog, variants, stock, cart, customers | Next implementation; store remains closed, no purchase claim. |
| 0.8.0 | Authorize.net sandbox/payments/orders/fulfillment | Planned; no real credentials or transaction results claimed. |
| 0.9.0 | Permissions, backup/restore/ZIP updates, audits/search/quality | Additive schema foundation exists; full maintenance and acceptance remain. |
| 1.0.0-rc.N | Complete staging/content/provider/browser/host acceptance | Not reached. |
| 1.0.0 | Complete tested and explicitly accepted release | Not released. |

Full packages are cumulative and immutable per delivered version. Preserve configuration/data/uploads. Do not add redesigns, native ticketing, newsletter campaigns or live deployment silently. No final release without complete scope and explicit acceptance.
