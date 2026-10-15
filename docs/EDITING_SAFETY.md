# Authoring safeguards — 1.0.0-rc.6

Content, product-description and logo editors display a gold-accented unsaved-changes notice. When the browser supports it, leaving or reloading a dirty editor also requests its generic confirmation. Cancel departure to stay with the current input; save explicitly to commit a draft. Reverting ordinary edits to the original values removes the unnecessary warning. A rejected server submission stays marked unsaved even before another edit.

Normal submissions through the marked form proceed without a false departure interruption. Validation or a canceled submission does not permanently bypass warnings. A saved-preview popup shows the last saved draft; it does not autosave or discard your unsent changes.

## Product error correction

Previously, a rejected product save could redisplay the older stored product, losing your entered text from the visible form. The editor now preserves allowlisted editable fields for correction with normal HTML escaping. An old conflicting revision remains old, so it cannot silently overwrite another user's later draft. Correcting the form does not change public merchandise, inventory, prices or customer records until the existing authorized action succeeds. This field retention works without JavaScript.

## No autosave or secret storage

The warning compares only marked authored controls in memory. It does not save drafts to localStorage, sessionStorage, cookies, IndexedDB or the server and makes no background requests. Hidden, password, file and approval controls are excluded from value inspection; unmarked payment/account/backup/upgrade operational forms do not participate. Existing server authorization, CSRF, revision and publication controls remain authoritative.

Browser confirmations require interaction and can be suppressed. They are not reliable protection against mobile app termination, operating-system shutdown or a crash. Save regularly. Without JavaScript, there is no departure-warning enhancement. Returning to a browser-restored page rechecks input, but this is not a guarantee of recovering a closed tab.

## Verified scope

Tested bb9d850a passed full38785664074, focused38785664137, storefront38785664089, branding38785664253 and authoring38785664076. Four authoring jobs cover the two PHP/database environments and Chromium/Firefox/WebKit, with actual root/subfolder editors, JS1440/390/320 and native390 correction. Each reports59 SQL/HTTP,105 application-browser PASS lines and27 isolated helper checks. See TEST_RESULTS.md for exact artifacts and the difference between protocol reload diagnostics and native Location.reload assertions. Tests are not a blanket guarantee about every browser toolbar or device.

The approved design, remembered grouped menu, manual one-page A2 installer, original public player, six migrations and pinned recovery files are unchanged. No actual hosting or provider operation or production-content publication occurred. Final artwork, real content and external acceptance remain separate.

Primary browser references retained from implementation review2026-10-15: https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeunload_event ; https://developer.mozilla.org/en-US/docs/Web/API/HTMLFormElement/submit_event ; https://developer.mozilla.org/en-US/docs/Web/API/Window/pageshow_event .
