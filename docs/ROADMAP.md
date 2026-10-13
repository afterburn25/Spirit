# Release roadmap

All milestones are cumulative. Development identifier now: 0.1.0-dev.1. No completed milestone is claimed.

| Target | Scope | Status |
|---|---|---|
| 0.1.0 | Organized PHP/MySQL foundation; browser installer; private storage; initial owner; login and admin; root/subfolder routing; lock and cleanup | Source draft; unit-tested; database/browser/hosting acceptance pending |
| 0.2.0 | Approved public design, shared header/footer, gold controls, responsive art and mobile navigation | Planned |
| 0.3.0 | Structured content; drafts/preview/publish; band; galleries; videos; safe uploads; press kit | Planned |
| 0.4.0 | Releases, authorized audio, streaming links and persistent local-audio player | Planned |
| 0.5.0 | Shows, event pages, venue data, statuses/archive and external ticket-seller URLs | Planned; native ticketing excluded |
| 0.6.0 | Database-backed booking inquiries, private notes/status, email diagnostics, newsletter confirmation/unsubscribe | Planned |
| 0.7.0 | Product catalog, variants, stock, cart, guest checkout preparation, customer accounts/recovery/addresses | Planned |
| 0.8.0 | Authorize.net sandbox, protected keys, checkout, orders, shipping/tax settings, fulfillment and refunds | Planned; no real card storage |
| 0.9.0 | Integrated permissions, audits, backup and tested restoration, upgrade/migration safety, diagnostics, search/share metadata, performance/accessibility/regression tests | Planned; security begins at 0.1, not here |
| 1.0.0-rc.N | Full staging install, content/rights review, browser/device and payment tests, owner acceptance | Planned |
| 1.0.0 | First complete, tested and explicitly accepted release with full package and owner documentation | Not released |

Acceptance must prove the actual workflows, not merely existence of tables or buttons. Sandbox approvals/declines/interruption/duplicates need evidence; mail delivery and backup restoration need evidence; public content must be approved. Live hosting and live payment setup are separate from release numbering.

Patch fixes use x.y.1 etc. Never overwrite a delivered version with different contents. Full packages contain all prior completed functionality. Supported upgrades must preserve content, config, uploads and business data; never silently reinstall. Later 1.1.0 features require agreed scope. No native tickets, fan club or additional redesigns in the initial scope.
