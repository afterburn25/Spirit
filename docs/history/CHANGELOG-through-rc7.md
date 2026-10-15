# Changelog

## 1.0.0-rc.7 — verified navigation and native-control quality

Improve the existing public phone menu's keyboard focus, outside dismissal, desktop/phone transitions and bounded scroll above playback. Keep the disclosure non-modal and preserve native no-JavaScript navigation. Correct shared admin checkbox height/alignment without changing checked values, consent or publication. Add root/subfolder three-engine packaged-site regression coverage for real menus, audio and native controls. No new design, business module, database schema, installer or hosting change.

Local230 syntax/812 pure/8 JS/12 packaging checks passed, plus an isolated Chromium DOM/style exercise. Tested adcafab92035816571e7951e71d73d6c0a22af89 (tree5d2d70be796b3ae9e293749126f189f2326442d1) passed all six required workflows: full38802392834, focused38802392855, storefront38802392802, branding38802392914, authoring38802392808 and navigation38802393254. Three new browser jobs each report361 passing checks. Source/artifact hashes and actual renders were verified. Finalization changes documentation only; production artwork/content and real-environment/owner approval remain outstanding.

---

## 1.0.0-rc.6 — verified authoring safeguards

Add opt-in unsaved-change notices and browser-supported departure confirmations to content, product-description and logo editors. No autosave, browser draft storage or background requests. Reverted ordinary edits become clean; rejected server responses remain unsaved. Legitimate saves and saved-preview popups keep native behavior. Browser/mobile/crash limitations remain explicit.

Correct the product editor losing entered fields after rejected saves. Redisplay only escaped authored fields and keep the submitted stale revision rather than adopting a newer one. Publication, stock, prices and customer data remain under their existing server rules. Native no-JavaScript correction remains usable.

Tested bb9d850ac4653ce594837a503f7caffd336dc369 passed full38785664074, focused38785664137, storefront38785664089, branding38785664253 and authoring38785664076. Four authoring jobs each report59 SQL/HTTP and105 application-browser PASS lines plus27 helper checks. Fresh local230 PHP/inc syntax,812 pure assertions,8 JS syntax and12 package tests passed. Exact source and artifacts were hashed; selected actual desktop/mobile renders were reviewed. Finalization changes documentation only.

Earlier test-harness failures are documented rather than hidden: native synthetic submit behavior, asynchronous dialog delivery, protocol-reload differences and SQL service startup. Native link/Location.reload cancellation remains required; protocol observations are diagnostic, not universal refresh guarantees. Bounded test-account readiness does not retry failed assertions. No engine or original workflow was removed.

## Prior cumulative releases

RC5 added owner logo drafts/preview/explicit publication/withdrawal and stale-form protection across backup restore. Verified sourceb16e9780 and final-main1101be37 evidence remain in Git at1101be37414701ae3488073e6cddee9348c1cae8.

RC4 hardened native pagination and private-history tests. RC3 connected eight Page artwork/copy placements to actual-layout previews. RC2 added grouped remembered administration navigation. RC1 added native public access and policy links; earlier releases implemented the application, installer, commerce and guarded maintenance foundations. Historical detailed changelogs remain in Git and docs/history; no previous delivered archive is overwritten.

No approved production artwork/content is invented or published, and no hosting/provider setting changes here. Final1.0 acceptance remains outstanding.
