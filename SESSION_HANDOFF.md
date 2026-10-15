# Resume after verified RC7 navigation/control quality

Repository afterburn25/Spirit; main authoritative. Work branch work/public-navigation-quality, PR20, base75a24384b2d17da96c83e99c34528644aa55e4c9. Inspect live refs, AGENTS.md and design/hosting decisions before writes; preserve concurrent work and never force-push.

## Exact evidence

Tested adcafab92035816571e7951e71d73d6c0a22af89 (tree5d2d70be796b3ae9e293749126f189f2326442d1) passed all six required workflows: full38802392834, focused38802392855, storefront38802392802, branding38802392914, authoring38802392808 and navigation38802393254.

Downloaded three-engine source archives match all386 candidate files and reconstruct the exact source tree. Each new browser log has361 PASS lines, including repeated contexts/shared setup rather than361 distinct features. Four screenshots per engine,12 total, were retained and selected real renders reviewed. Finalization modifies documentation only; additional final-main results and delivery hashes are recorded separately in PR20/report after completion, not predicted.

Local230 PHP/inc syntax,812 existing pure assertions in22 scripts,eight JS checks and12 package tests passed. An isolated Chromium DOM/style exercise found CSS could hide the focused control before the media-query handler; the visible-focus recovery was corrected before the successful candidate run. This helper is not a full application test. Local SQL/image/ZIP extensions are absent; use actual Actions evidence.

## Implementation to preserve

Only the public menu block in assets/js/public.js and two CSS files changed at runtime. Ordinary disclosure navigation remains non-modal: Tab leaves freely, Escape returns to the trigger, arrow keys can enter links, outside focus/click closes, responsive changes avoid hidden focus. In-memory last-menu focus is used only when CSS has left body active; content focus is not pulled back. Viewport/player bounds provide scrolling rather than clipping the final booking link. Native no-JavaScript mobile links remain expanded. Audio/queue/fetch logic outside the menu block is unchanged.

Shared administration checkbox sizing/alignment is CSS only; specialized branding controls retain16px geometry. No input defaults, consent/publication actions, PHP business logic or saved menu state changed. Evidence full-page captures retain fixed elements at their capture scroll position; a labelled320x568 excerpt is provided for the actual phone viewport, not a generated mockup.

## Package and next tasks

Preserve every282 RC6 payload path, all six migrations, all shipped access rules, pinned recovery and installer bytes. Final cumulative RC7 delivery is immutable. Existing sites upgrade with backups, never reinstall or replace original private config/encryption secret/database/media/recovery keys. A2/cPanel manual one-page installer only: no setup code, hosting token,20i or automatic DB.

Next substantive launch work remains approved master artwork and genuine content/policies, followed by actual A2/mailbox/genuine merchant/recovery/device and owner acceptance. Do not substitute test fixtures or invent band members/releases/shows/products. No live hosting/provider operation or final1.0 acceptance is claimed. Earlier exact RC6 history remains at75a24384 and PR19.
