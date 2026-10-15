# Authoring safeguards — RC6 candidate

Page/content, product-description and logo editors now warn about unsaved authored changes. The browser chooses its generic leave/reload confirmation. A local gold-accented message explains that changes have not been saved. Restoring the original field values removes the unnecessary warning. Saving or publishing through the marked form proceeds normally; invalid input and cancelled submission leave the warning active. Opening a saved-preview popup does not save or discard edits.

This is **not autosave**. It does not persist drafts in localStorage, sessionStorage, cookies, IndexedDB or the server, and it makes no network calls. Only explicitly marked authoring forms participate; password, token, hidden, file and unmarked operational forms are not inspected. Publication approval is not treated as authored content. Existing server-side authorization, CSRF, revision and publication controls remain authoritative.

Product correction fixes a real earlier gap: the editor used to render the stored product after a rejected POST, discarding submitted descriptions/name in the visible form. An explicit allowlist now redisplays authored fields, with ordinary HTML escaping. A stale submission keeps its old revision rather than silently adopting the newest one. Product/category data, stock, prices, customer information and live publication are not changed by redisplay.

Without JavaScript, product error correction still preserves fields; departure warnings do not exist. Browser confirmations require user interaction and may be suppressed, particularly on mobile process termination. Save regularly. A restored browser page rechecks its fields; there is no guarantee of recovery after closing a tab, browser crash or operating-system shutdown. Server-rejected responses are marked unsaved, not treated as a saved baseline.

## Scope and verification

This is final content-entry hardening, not a different design or an expansion of payment/hosting scope. The grouped menu, its saved state, one-page manual A2 installer, public site, original player, six migrations and pinned recovery files remain unchanged.

Local230 PHP/inc syntax checks,812 pure assertions,8 JavaScript checks,27 isolated Chromium lifecycle checks and12 packaging tests passed. Exact candidate SQL/browser and complete regression workflows are pending. New CI includes root/subfolder, desktop/mobile, real leave/reload dialogs, native no-JavaScript correction, public-draft separation, stale edits and unchanged database checks. Test fixtures never enter production packages.

Read-only public-page observations on2026-10-15 found spiritualgorillaz.com serving the redesigned public layout. Its music, band, shows and store still show empty/default content. This does not establish installed version, private-storage/deletion state, mailbox delivery, payments or owner acceptance. No remote forms were submitted and no hosting settings changed.

Primary browser references consulted2026-10-15:
- https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeunload_event
- https://developer.mozilla.org/en-US/docs/Web/API/HTMLFormElement/submit_event
- https://developer.mozilla.org/en-US/docs/Web/API/Window/pageshow_event
