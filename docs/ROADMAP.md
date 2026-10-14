# Cumulative roadmap

Current **0.9.0-dev.9** removes automatic database creation at the user's request. A2/cPanel manual database only; no token, no setup code, no20i. Separate optional cURL/ZIP notices replace the combined check. Application4736764e passed full38567553630 and focused38567553677; see TEST_RESULTS.md for exact evidence, not the version number.

| Target | Scope | Current state |
|---|---|---|
| 0.1 | One-page code-free installer, manual existing DB, private runtime, owner, self-deletion/lock | Manual-only change verified across SQL/HTTP, browser and Apache suites, including actual no-cURL/no-ZIP installation. Actual A2 acceptance remains. |
| 0.2 | Approved responsive website design | Implemented; production artwork and real-content review remain. |
| 0.3 | CMS, media, band, press and private revision history | Implemented and tested, including pagination and append-only private restoration. |
| 0.4 | Music library and persistent audio | Implemented and browser-tested, with keyboard refinements. |
| 0.5 | Shows and external venue ticket links | Implemented and tested; no native ticket sales/scanning. |
| 0.6 | Booking, transactional email and newsletter | Implemented and tested; real mailbox/provider acceptance remains. |
| 0.7 | Catalog, variants, stock, cart and customer accounts | Implemented and tested; US addresses initially. |
| 0.8 | Token-only checkout, orders and fulfillment | Application boundaries tested; genuine merchant acceptance remains. |
| 0.9 | Staff, backups, updates, retention and quality | Core modules, discovery and responsive-media/keyboard pass implemented and tested. Broader final quality/operating-scope acceptance remains. |
| 1.0.0-rc.N | Complete staging and acceptance candidate | Not reached. |
| 1.0.0 | Complete explicitly accepted release | Not released. |

Use LAUNCH_CHECKLIST.md for real-environment evidence. Packages remain cumulative and immutable. No setup code/receipt is generated. No hosting API token or database provisioning exists. Earlier private pending state is preserved for manual review. Backup/update recovery keys remain separate. Browser updates are same-schema and guarded restoration is same-instance. Tests cannot substitute for approved content/master art, actual provider/host checks or owner acceptance.
