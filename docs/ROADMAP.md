# Cumulative release roadmap

Current work **0.9.0-dev.1**: staff access and audit, full new verification pending. Prior .8 application cd3d645b passed38493194973. Version numbers do not imply production acceptance.

| Target | Scope | Status |
|---|---|---|
| 0.1 | Browser installer/private runtime/owner/cleanup | Implemented and regression-tested; actual host acceptance remains. |
| 0.2 | Selected responsive public design | Real components; final master artwork remains. |
| 0.3 | CMS/drafts/band/media/press | Implemented; advanced revisions and large-library controls remain. |
| 0.4 | Music and persistent player | Implemented and browser-tested. |
| 0.5 | Shows and venue ticket URLs | Implemented and tested; no native tickets. |
| 0.6 | Booking/email/newsletter consent | Implemented and tested; real provider inbox acceptance pending. |
| 0.7 | Catalog/variants/stock/cart/customer accounts | Implemented and tested; US addresses initially. |
| 0.8 | Token-only checkout/payment/orders/fulfillment | Full synthetic-boundary code tests passed; genuine provider sandbox acceptance pending. |
| 0.9 | Staff/backup/restore/upgrades/retention/quality | Staff and audit implemented, new regression run pending; other maintenance modules unfinished. |
| 1.0.0-rc.N | Complete staging/content/provider/host acceptance | Not reached. |
| 1.0.0 | First complete tested and explicitly accepted release | Not released. |

Cumulative immutable archives; preserve all config/data/media and old migrations. No fake public content, secrets in Git, destructive reinstall or additional redesigns.
