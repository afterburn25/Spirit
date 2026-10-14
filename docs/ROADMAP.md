# Cumulative roadmap

Current **0.9.0-dev.5**, application 9546b7c, full verification **38513423793** passed. Preserve selected design, external ticket sellers, shared-host browser installer/private state and immutable migrations.

| Target | Scope | Status |
|---|---|---|
| 0.1 | Browser installer, private runtime, owner, cleanup | Implemented/tested; actual host acceptance pending. |
| 0.2 | Selected responsive design | Real components; final master artwork/content review pending. |
| 0.3 | CMS, band/media/press | Implemented/tested, including searchable pagination and private revision restore. |
| 0.4 | Music and persistent audio | Implemented/browser-tested. |
| 0.5 | Shows and venue ticket URLs | Implemented/tested; no native ticketing. |
| 0.6 | Booking, transactional mail, newsletter | Implemented/tested; provider inbox acceptance pending. |
| 0.7 | Catalog, variants, stock, cart, customers | Implemented/tested; US addresses initially. |
| 0.8 | Token-only checkout, orders, fulfillment | Application/SQL/browser boundaries tested; genuine merchant sandbox acceptance pending. |
| 0.9 | Staff, backups, updates, retention, quality | Staff, encrypted backups, same-schema ZIP recovery, retention and content history tested. Metadata/search/media/accessibility/performance work remains. |
| 1.0.0-rc.N | Complete staging and acceptance | Not reached. |
| 1.0.0 | Complete explicitly accepted release | Not released. |

Full packages are cumulative and immutable per version. Setup/recovery keys remain separate and private. Browser updates do not automatically cross schemas; guarded backup restore is same-instance and protects operational histories. Host, providers, approved content/master artwork and owner acceptance remain required before final v1.0.
