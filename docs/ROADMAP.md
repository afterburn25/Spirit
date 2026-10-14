# Cumulative roadmap — 0.9.0-dev.12

Current quality pass fixes complete responsive product-photo selection, portrait container geometry, failure/stale-response recovery, keyboard/native fallback and adds Chromium/Firefox/WebKit storefront verification. Consult TEST_RESULTS.md for exact source/run evidence. Version numbers are not project-completion percentages.

| Target | Scope | Current state |
|---|---|---|
| 0.1 | One-page code-free manual installer, private runtime, owner, self-deletion/lock | Implemented and regression-tested. Actual A2 account acceptance remains. No setup code, token or automatic DB creation. |
| 0.2 | Approved responsive public design | Implemented. Production master artwork and approved populated-site review remain. |
| 0.3 | CMS, band/media/press, search and private revision history | Implemented and tested, including publication boundaries and append-only private restoration. |
| 0.4 | Releases/tracks and persistent local audio | Implemented and browser-tested, including navigation to merchandise in the new engine matrix. |
| 0.5 | Shows and external venue ticket links | Implemented and tested; no native ticket sales or admission scanning. |
| 0.6 | Booking, transactional email and newsletter | Implemented and tested. Actual receiving-mailbox/provider acceptance remains. |
| 0.7 | Catalog, variants, stock, cart and customers | Implemented. Multi-photo selection and portrait gallery layouts corrected and tested across three engines. Initial addresses remain US-only. |
| 0.8 | Token-only checkout, orders and fulfillment | Application boundaries tested. Genuine merchant sandbox acceptance remains. |
| 0.9 | Staff, backups, upgrades, retention, discovery and quality | Core modules and passive Launch checks/413 upload feedback implemented. Current gallery quality pass adds to, not replaces, broader populated-site accessibility/performance/security review. |
| 1.0.0-rc.N | Complete staging and acceptance candidate | Not reached. |
| 1.0.0 | Complete explicitly accepted release | Not released. |

Packages are cumulative and immutable. Preserve prior configuration/data/media and recovery keys. Six migrations and five pinned recovery/access files remain unchanged; browser upgrades remain same-schema and guarded restoration same-instance. A2/cPanel only, no20i. Use LAUNCH_CHECKLIST.md for actual host, provider, content and owner acceptance; fixtures and configured settings are not launch approval.
