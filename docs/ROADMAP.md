# Release roadmap

Cumulative development version: **0.1.0-dev.2**. Foundation source is substantially implemented and has verified database/HTTP/browser smoke coverage. No accepted milestone or production release has been declared.

| Target | Scope | Status |
|---|---|---|
| 0.1.0 | Organized PHP/MySQL foundation, browser installer, private storage, owner login/admin, root/subfolder routing, lock and cleanup | Implemented foundation; SQL/HTTP and Chromium smoke green; final hosting/edge-case/package acceptance pending |
| 0.2.0 | Approved public design, shared header/footer, gold controls, responsive art and mobile navigation | Planned; holding page is not the redesign |
| 0.3.0 | Structured content, drafts/preview/publish, band, galleries, videos, safe uploads and press kit | Planned |
| 0.4.0 | Releases, authorized audio, streaming links and persistent local-audio player | Planned |
| 0.5.0 | Shows, event pages, venue data, status/archive and external ticket URLs | Planned; native ticketing excluded |
| 0.6.0 | Database-backed booking, private notes/status, email diagnostics, newsletter confirmation/unsubscribe | Planned |
| 0.7.0 | Products, variants, stock, cart, guest-checkout preparation, customer accounts/recovery/addresses | Planned |
| 0.8.0 | Authorize.net sandbox, protected keys, checkout, orders, shipping/tax settings, fulfillment and refunds | Planned; no card storage |
| 0.9.0 | Integrated staff permissions, audit, backup/restore, upgrade/migration safety, diagnostics, search/share metadata, performance/accessibility/regression checks | Planned; security starts at 0.1, not here |
| 1.0.0-rc.N | Staging, content/rights review, browser/device/payment checks and owner acceptance | Planned |
| 1.0.0 | Complete tested accepted release, cumulative package and owner documentation | Not released |

Development evidence is in docs/TEST_RESULTS.md. Acceptance requires actual workflows rather than buttons/tables. Do not attribute later code to an earlier green CI run. Hosting and live provider setup are separate from application tests.

Patch versions use x.y.1 onward. Never overwrite a delivered version with different contents. Full packages contain all earlier implemented functionality; supported upgrades must preserve config, uploads, content and business data. Upgrade automation is not yet implemented. No native tickets, fan club or unrequested redesign in the initial scope.
