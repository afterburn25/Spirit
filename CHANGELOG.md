# Changelog

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
