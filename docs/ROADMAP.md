# Cumulative roadmap

Current **0.9.0-dev.8** includes the user-requested code-free single-page installer with optional **A2/cPanel** database creation. No 20i. Full workflow **38560767553** passed for application **b9d32849e9b556dbc2b6d4f2adb7526703b46562**; see TEST_RESULTS.md. Preserve the approved design, private data, immutable migrations and external ticket sellers.

| Target | Scope | Current state |
|---|---|---|
| 0.1 | One-page code-free installer, automatic/existing DB, private runtime, owner, self-deletion/lock | Implemented and full matrix verified; actual A2 account acceptance remains. |
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

Use LAUNCH_CHECKLIST.md for real-environment evidence. Packages remain cumulative and immutable. No setup code/receipt is generated. Panel tokens are request-only; generated database credentials remain private and uncertain provisioning pauses safely. Backup/update recovery keys remain separate. Browser updates are same-schema and guarded restoration is same-instance. Tests cannot substitute for approved content/master art, actual provider/host checks or owner acceptance.
