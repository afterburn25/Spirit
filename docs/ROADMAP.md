# Roadmap — 1.1.0-rc.1 audience analytics

The original 1.0 feature foundation is retained. The owner-requested analytics extension and navigation fixes are implemented and automatically verified for candidate review. Actual desktop/mobile review, external acceptance and owner-approved publication remain.

| Milestone | Current status | Remaining work |
|---|---|---|
|1. Core website and administration|**Complete.** Public site, CMS/admin, media/music/shows/press, booking/newsletter, accounts, commerce/orders, branding and responsive design are implemented.|Fix only defects found by final CI/acceptance.|
|2. Production-safe fallback presentation|**Complete.** Approved copy fallbacks, confirmed lineup cards, scalable bundled logo, contained hero fallback, truthful empty states and dormant-store shell behavior are implemented.|Owner may replace fallback hero/logo or add real portraits/content through existing workflows; not a software dependency.|
|3. A2 installation, maintenance and recovery|**Implemented and extensively automated-tested; normal operation previously owner-confirmed.** Browser installer, backups, guarded update/rollback and independent recovery are present.|Real staging/host drill evidence and owner acceptance where required.|
|4. Email and scheduled processing|**Implemented.** Owner/booking/newsletter/customer mail flows previously owner-confirmed. Real mail worker now records a protected successful-run heartbeat exposed in Launch Checks.|Observe actual scheduled execution on the target host if required for final acceptance.|
|5. Commerce and Authorize.net|**Software complete; activation-gated.** Token-only browser payment boundary, orders, inventory holds/releases, cancellation, reporting/recovery paths and payment worker heartbeat are implemented/tested with synthetic provider boundaries.|If store remains closed: none for core launch. If enabling commerce: genuine sandbox/provider acceptance, real products/policy and owner approval.|
|6. Responsive/browser/accessibility quality|**Implemented.** 360/390/desktop coverage across public/store/checkout/booking, plus 320 stress checks and Chromium/Firefox/WebKit release review.|Real-host performance and final populated-content review after owner-selected assets/content.|
|7. Release integrity|**Implemented and CI verified.** Deterministic software release gate validates required source, package contents, manifest hashes, private-file exclusions and final-version blocker rules. All ten workflows and 23 jobs passed on application/test source `917997185a7512189353a4008ffbc1cd3b4f9951`.|Keep final package receipts and exact-ZIP screenshot manifests with the delivery; required checks on the latest PR head must pass before promotion.|
|8. Owner acceptance and original 1.0.0 milestone|**1.0.0 released; current candidate acceptance pending.**|Review remaining real-world observations and optional launch content. The current cumulative 1.1.0 release requires explicit owner approval of its screenshots and applicable acceptance checks before final promotion.|
|9. Audience analytics and navigation — 1.1.0-rc.1|**Implemented and CI verified.** Visitor/acquisition/client/location reports, charts/export, bounce/time/live, separate crawlers, optional City data and Search Console imports; additive migrations and consolidated public assets.|Review actual desktop/mobile screens from the delivery ZIP, deploy through the documented additive-update path and obtain owner approval.|

## Launch profile

A content-first 1.0 launch does **not** require invented material. Shows, portraits, Part 3 and merchandise may stay unpublished/closed until genuine data exists.

Commerce is an activation-gated feature. When storefront and payments are disabled, launch diagnostics treat the missing merchant credentials/provider acceptance/product catalog as dormant rather than broken. Enabling commerce restores the strict sandbox/policy/product/worker checks.

Final content source: `design/final-launch-content/FINAL-CONTENT-MAP.md`.
Production asset guidance: `design/final-launch-content/PRODUCTION-ASSET-SPECS.md`.
Final acceptance scope: `docs/acceptance/CONSOLIDATED-FINAL-PASS.md`.
