# Changelog

## 1.0.9 — full-span hero and Search correction

- change production desktop hero geometry to a true 16:9 viewport-wide canvas;
- make the Music artwork reach both screen edges without cropping or leaving an unintended left strip;
- apply the same full-span geometry to Shows, Band and Merch at production desktop widths;
- preserve the complete right-side subject through `object-fit: contain` and right-center positioning;
- keep the approved dark atmospheric left side available for real HTML copy;
- explicitly reserve and render the right-side Search control in the sticky header;
- retain the dropdown Search interaction, centered navigation, dimensional Home title, animated logo and mobile complete-art stacking.

## 1.0.8 — full-width atmospheric hero system

- replace boxed/contained interior heroes with edge-to-edge viewport heroes;
- add a full-viewport atmospheric extension layer for Home, Music, Shows, Band, Media, Press, Booking and Merch;
- keep the complete right-side subjects visible at a zoomed-out scale;
- extend the atmospheric background in CSS while keeping the native subject artwork at its original resolution;
- preserve sharp native image detail and the approved dark navy / blue / gold presentation;
- keep real HTML headings, copy, buttons, forms and navigation separate from the artwork;
- stack the full original artwork beneath the copy on mobile;
- retain sticky centered navigation, dropdown Search, dimensional Home title, CMS overrides and all existing application behavior.

## 1.0.7 — complete interior-hero framing correction

- reduce the displayed artwork scale on Music, Shows, Band and Merch so subjects no longer appear zoomed;
- keep each complete native 16:9 hero master visible with deliberate breathing room;
- remove destructive edge-to-edge cover behavior and reset inherited image transforms and filters;
- preserve the editorial text-left / artwork-right composition on desktop;
- stack the complete uncropped artwork beneath the copy on mobile;
- preserve the complete 4:3 Booking equipment scene;
- retain the 1.0.6 sticky centered navigation, dropdown Search and dimensional Home title.

## 1.0.6 — navigation and hero regression correction

- restore a larger, truly centered desktop navigation layout;
- make the entire navigation strip sticky during page scrolling;
- replace Search page navigation on magnifier click with an accessible dropdown search panel;
- keep full Search results behavior after the dropdown form is submitted;
- reduce destructive hero zoom/cropping and preserve the native wide artwork on desktop and mobile;
- add layered metallic depth to “Music With a Higher Purpose”;
- preserve all 1.0.5 CMS, installer, booking, player, commerce and animated-branding behavior.

## 1.0.5 — approved animated identity

The owner-selected Concept 2 crown-and-shield wordmark is now the bundled public identity.

Changes:
- add the transparent APNG logo to the real public header;
- add a matching transparent static PNG for the footer, reduced-motion visitors and fallback behavior;
- preserve the published Media-library logo override and original image proportions;
- resize the header responsively without displacing navigation, booking or Search controls;
- retain the approved native-resolution Home, Music, Shows, Band, Booking and Merch design;
- extend the cumulative package with the new identity assets and styling.

The update changes branding presentation only; the verified PHP/CMS, player, booking, commerce, installer, upgrade and recovery behavior remains intact.

## 1.0.0 — final release

Promote the fully verified RC10 software line to final 1.0.0 after explicit owner instruction to proceed to final release.

Final release includes:
- complete public site and owner administration;
- approved launch-copy fallbacks and metadata;
- confirmed Chad Lindsey / Ron Green / Charlie Savell factual lineup fallback;
- external venue ticket-link model;
- persistent audio, media, booking, newsletter and search;
- customer accounts;
- optional dormant merchandise/order/payment subsystem;
- closed-store navigation, account and Search behavior;
- browser installer, cumulative upgrades and independent same-schema recovery;
- encrypted backups, diagnostics and worker-heartbeat support;
- deterministic packaging/release gate;
- 360 px/390 px desktop/mobile and Chromium/Firefox/WebKit verification.

The RC10 verification baseline was fully green on `2a833986f5a39d2f8de99f41bd8640b0642648e8`.

Commerce remains disabled at final release. Genuine Authorize.net sandbox/provider acceptance is deferred until commerce activation. No unobserved provider result, cron execution, recovery drill or host-performance measurement is claimed by the version promotion.

## 1.0.0-rc.10 — dormant-store search cleanup

RC10 fixes the final closed-store public inconsistency found during live acceptance: the Search page still advertised merchandise and exposed a Merchandise filter even while the storefront was disabled.

Changes:
- hide the Merchandise search filter while the storefront is disabled;
- use non-commerce Search introduction/empty-state copy while the storefront is disabled;
- preserve merchandise search when the storefront is enabled;
- add closed-store browser coverage for Search copy/filter behavior.

RC9 remains frozen as the verified historical candidate.

## 1.0.0-rc.9 — dormant-store customer navigation fix

RC9 fixes one real post-RC8 runtime inconsistency discovered during live acceptance: customer account pages were hard-coded to present themselves as shop pages and always advertised Merchandise / Your bag even when the storefront was disabled.

Changes:
- customer/account views no longer force the global navigation into the shop-active state;
- customer-local Merchandise and Your bag links render only while the storefront is enabled;
- closed-store account pages use non-commerce wording and no longer advertise guest checkout;
- unavailable registration/recovery routes return to sign-in instead of sending visitors to a closed merchandise catalog;
- customer sign-in/account access remains available with a dormant storefront;
- browser coverage enforces both closed-store and enabled-store customer-navigation and copy behavior.

RC8 remains frozen as the all-green historical candidate.

## 1.0.0-rc.8 — software-complete candidate

RC8 added final launch-copy fallbacks, confirmed-lineup reconciliation, the scalable bundled logo, contained fallback hero, dormant-store navigation/diagnostics, checkout validation hardening, scheduled-worker heartbeat evidence, expanded 360 px/cross-browser coverage and the deterministic software release gate.

## Previous history

Earlier detailed checkpoints and release history remain in `docs/history/`, `docs/acceptance/` and prior commits.
