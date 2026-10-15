# RC7 verification — pending exact Actions results

Base:75a24384b2d17da96c83e99c34528644aa55e4c9, whose completed final main runs are documented in PR19. New runtime changes are limited to the public menu block and two existing stylesheets; VERSION is1.0.0-rc.7. No PHP business logic, installer, migration or recovery changes.

Fresh local validation passed230 PHP/inc syntax checks,812 PASS assertions in22 existing pure test scripts,eight JS syntax files and12 packaging tests. An isolated real-Chromium DOM/style test passed public focus/Tab/Escape/outside/resize controls and checkbox geometry at1440/390/320. It first reproduced focus loss because CSS hid the current link before the media-query change handler, then verified the corrected remembered-focus handling. It is not a database-backed website test. Local SQL extensions remain unavailable; no hosting/browser policy is bypassed.

New public-navigation workflow requires actual root/subfolder sites on Chromium/Firefox/WebKit, public menu checks at820/390/320, real playback on a short320px phone, read-only native admin control geometry/keyboard behavior at1440/390/320, forced-color geometry, native320px navigation and unchanged database fingerprints. All existing full/focused/storefront/branding/authoring suites remain mandatory. Exact new results and real screenshots are pending; none is assumed from RC6.

Earlier version details and exact RC6 main verification are preserved in Git at75a24384 and in PR19.
