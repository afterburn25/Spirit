# Cumulative roadmap

Current **0.9.0-dev.11** consolidates launch checks and upload-limit feedback. A2/cPanel manual database only; no token, no setup code, no20i. The exact verified application is d699fd6e, full38589219303 and focused38589219399. Consult TEST_RESULTS.md rather than treating a version as a completion percentage.

| Target | Scope | Current state |
|---|---|---|
| 0.1 | One-page code-free installer, existing manual DB, private runtime, owner, self-deletion/lock | Implemented and regression-tested, including no-cURL/no-ZIP installation. Actual A2 acceptance remains. |
| 0.2 | Approved responsive website | Implemented. Production artwork and approved populated-site review remain. |
| 0.3 | CMS, media, band, press and revision history | Implemented and tested, including pagination and append-only private draft restoration. |
| 0.4 | Releases, tracks and persistent audio | Implemented and browser-tested with keyboard refinements. |
| 0.5 | Shows and external venue ticket links | Implemented and tested. No native tickets or admission scanning. |
| 0.6 | Booking, transactional email and newsletter | Implemented and tested. Actual receiving-mailbox/provider acceptance remains. |
| 0.7 | Catalog, variants, stock, cart and customers | Implemented and tested; initial addresses are US-only. |
| 0.8 | Token-only checkout, orders and fulfillment | Application boundaries tested. Genuine merchant sandbox acceptance remains. |
| 0.9 | Staff, backups, upgrades, retention, discovery and quality | Core workflows tested.43 passive launch checks, private report export and oversized-upload feedback added. Broader populated-site quality and operating-scope acceptance remain. |
| 1.0.0-rc.N | Complete staging and acceptance candidate | Not reached. |
| 1.0.0 | Complete explicitly accepted release | Not released. |

Follow LAUNCH_CHECKLIST.md for real-environment evidence. Packages are cumulative and immutable. Preserve existing configuration, original encryption secret, database/media, earlier private recovery state and separate backup/update recovery keys. Six migrations and five pinned recovery/access files are unchanged. Browser upgrades remain same-schema; guarded restoration remains same-instance. No report, configured service or test replaces actual provider/host/content/owner acceptance.
