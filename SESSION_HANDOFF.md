# Resume RC7 public navigation and control quality

Current branch: work/public-navigation-quality, based on main75a24384b2d17da96c83e99c34528644aa55e4c9 / tree3d3c9dc48b19bbc96229a483b217afbadec6e2e9. Inspect live refs before writes, preserve concurrent work and never force-push.

The public menu in assets/js/public.js now implements non-modal disclosure focus behavior: Tab is not trapped, Escape returns to the trigger, outside clicks/focus dismiss, optional arrow keys enter links, and responsive transitions keep focus visible. CSS can hide a control before its media-query event, so only remembered connected menu focus may be restored when the browser falls back to body. Content focus is not pulled back. Bounds respond to viewport/player size; short menus scroll rather than clipping their final links. No public audio/queue/fetch code changes outside this menu block.

Admin-content CSS sizes only checkbox-label controls; existing specialized logo styling remains. No form defaults, names, checked values, action or permission changes. No permanent menu preference was added to the public site; the administration menu's original remembered state is unchanged.

Local230 syntax/812 existing pure/8 JS/12 packaging checks passed; isolated Chromium markup/style behavior passed, not a SQL site run. New public_navigation_browser.py and public-navigation.yml must execute on Chromium/Firefox/WebKit, including real playback and unsent controls. All five older application workflows remain required. Exact new CI is pending. No generated screenshots or production data may be used as test evidence.

Next: complete exact runs, inspect actual mobile menu/product-control renders, finalize status/evidence and create an immutable cumulative RC7 Full ZIP. Retain every282 RC6 payload path, six migrations, pinned recovery/access and installer bytes. Existing sites upgrade with backups, not reinstall. A2/cPanel manual one-page setup only, no setup code/token/20i/automatic DB. Preserve original private config/encryption secret/database/media/recovery keys. Real artwork/content/policies and actual A2/mailbox/merchant/recovery/device/owner acceptance remain.

Earlier version details and exact RC6 main verification are preserved in Git at75a24384 and in PR19.
