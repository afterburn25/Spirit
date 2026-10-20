# Roadmap — 1.1.0-rc.2 graphical analytics

The original 1.0 feature foundation and the delivered rc.1 analytics work are retained. The owner's rc.2 corrections are implemented: a dedicated Analytics category, Overview snapshots, expanded graphical reports, visitor exploration and public header/search adjustments. Focused local analytics, Navigation/Overview, public header/navigation and packaging checks have passed. Actual desktop/phone QA captures were inspected. Latest-head CI and exact-package delivery captures remain pending at this source checkpoint. Earlier rc.1 results are historical evidence, not rc.2 verification.

| Milestone | Current status | Remaining work |
|---|---|---|
|1. Core website and administration|**Complete.** Public site, CMS/admin, media/music/shows/press, booking/newsletter, accounts, commerce/orders, branding and responsive design are implemented.|Fix only defects found by final CI/acceptance.|
|2. Production-safe fallback presentation|**Complete.** Approved copy fallbacks, confirmed lineup cards, scalable bundled logo, contained hero fallback, truthful empty states and dormant-store shell behavior are implemented.|Owner may replace fallback hero/logo or add real portraits/content through existing workflows; not a software dependency.|
|3. A2 installation, maintenance and recovery|**Implemented and extensively automated-tested; normal operation previously owner-confirmed.** Browser installer, backups, guarded update/rollback and independent recovery are present.|Real staging/host drill evidence and owner acceptance where required.|
|4. Email and scheduled processing|**Implemented.** Owner/booking/newsletter/customer mail flows previously owner-confirmed. Real mail worker now records a protected successful-run heartbeat exposed in Launch Checks.|Observe actual scheduled execution on the target host if required for final acceptance.|
|5. Commerce and Authorize.net|**Software complete; activation-gated.** Token-only browser payment boundary, orders, inventory holds/releases, cancellation, reporting/recovery paths and payment worker heartbeat are implemented/tested with synthetic provider boundaries.|If store remains closed: none for core launch. If enabling commerce: genuine sandbox/provider acceptance, real products/policy and owner approval.|
|6. Responsive/browser/accessibility quality|**Implemented and locally verified; final CI pending.** Existing public/store/checkout/booking browser coverage is retained. Focused rc.2 Overview checks pass at 1440/390/320px with native charts and data without JavaScript; a Chromium phone navigation journey also passes.|Complete the final rc.2 CI browser matrix and exact-package desktop/mobile review and target-host/populated-content observations.|
|7. Release integrity|**Implemented; rc.2 local package tests and deterministic gate passed.** The deterministic gate validates required source, package contents, manifest hashes, private-file exclusions and final-version blocker rules. rc.1's all-ten-workflow result is archived as baseline evidence.|Run applicable checks on the latest rc.2 head; retain immutable Full ZIP/checksum and exact-ZIP screenshot manifests before promotion.|
|8. Owner acceptance and original 1.0.0 milestone|**1.0.0 released; current candidate acceptance pending.**|Review remaining real-world observations and optional launch content. The current cumulative 1.1.0 release requires explicit owner approval of its screenshots and applicable acceptance checks before final promotion.|
|9. Audience analytics and navigation — 1.1.0-rc.2|**Implemented; focused local analytics, Overview and public-navigation checks passed.** Dedicated owner Analytics category; seven-day Overview snapshot and native traffic chart; expanded graphical reports; filterable anonymous visitor/session explorer; retained bounce/time/live, crawler, geography and Search Console features. Public search sits beside Book the Band with larger centered navigation.|Observe latest-head CI; review exact-ZIP desktop/mobile screenshots and obtain owner approval. Use the upgrade path matching the installed version.|

## Analytics upgrade paths

The schema remains migrations **007/008**, unchanged from rc.1. **rc.1 → rc.2** is a normal guarded same-schema Browser upgrade when its integrity/recovery checks pass. **1.0.x → rc.2** requires the documented code deployment and additive Database updates flow; the browser updater deliberately rejects that schema transition. Preserve the installed owner, database, encryption secret, private runtime/configuration and uploads. Do not reinstall or bypass checks. See `ANALYTICS.md` for deployment instructions.

## Launch profile

A content-first 1.0 launch does **not** require invented material. Shows, portraits, Part 3 and merchandise may stay unpublished/closed until genuine data exists.

Commerce is an activation-gated feature. When storefront and payments are disabled, launch diagnostics treat the missing merchant credentials/provider acceptance/product catalog as dormant rather than broken. Enabling commerce restores the strict sandbox/policy/product/worker checks.

Final content source: `design/final-launch-content/FINAL-CONTENT-MAP.md`.
Production asset guidance: `design/final-launch-content/PRODUCTION-ASSET-SPECS.md`.
Final acceptance scope: `docs/acceptance/CONSOLIDATED-FINAL-PASS.md`.
