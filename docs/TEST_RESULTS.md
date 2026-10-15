# RC7 verification — completed application checkpoint

Tested **adcafab92035816571e7951e71d73d6c0a22af89**, tree **5d2d70be796b3ae9e293749126f189f2326442d1**. BaseRC6 main75a24384. Finalization changes documentation only; later main-run results and final package hashes belong in PR20/delivery report after observation.

| Workflow | Run | Result |
|---|---|---|
|Website and installer|38802392834|Success|
|Focused maintenance|38802392855|Success|
|Existing populated storefront|38802392802|Success|
|Site logo|38802392914|Success|
|Authoring safety|38802392808|Success|
|Public navigation and native controls|38802393254|Success|

Full verification retained PHP8.2/MariaDB11.4 and PHP8.4/MySQL8.4, all13 existing Chromium workflow steps and actual Apache HTTPS. Existing three-engine gallery/release/section/admin-navigation/history, branding and authoring workflows passed. The PHP8.2 full job's browser steps remain skipped under their pre-existing PHP8.4 condition; this does not mean the three-engine workflows were skipped.

## New actual browser coverage

Jobs116654740632 (Chromium/MySQL),116654741009 (Firefox/MariaDB) and116654741268 (WebKit/MariaDB) passed. Each downloaded log contains361 PASS lines, including repeated contexts and shared setup. Root/subfolder public menu checks use820/390/320px, keyboard Tab/reverse Tab/arrows/Escape, outside clicks/focus, responsive transitions in both directions, native destination navigation, current focus and retained CSP. A320x568 playback case requires the actual last menu link to scroll into view above the actual player and verifies continued playback with the same audio node.

Native admin controls are checked at1440/390/320px across product editing, variants, categories, store settings, media upload and branding. Geometry, first-line text alignment, full label height and actual Space-key toggling are verified. The logo page also receives forced-color geometry checks. No changed control is submitted. Catalog/customer/address/token/mail snapshot tables remain unchanged; this is not a claim that login audit/session activity never occurs. Native320px no-JavaScript public navigation retains every destination. These are targeted assertions, not complete WCAG, screen-reader, physical-device or field-performance certification.

## Downloaded source and renders

All three artifacts were SHA-256 verified. Their source archives match all386 local files and the exact tested tree. Each contains four actual synthetic screenshots and a browser log. Selected Chromium desktop/mobile and Firefox mobile renders were inspected. Full-page screenshots retain fixed elements at the capture scroll position; the delivered phone preview is explicitly a320x568 top-viewport excerpt, not a reconstructed image.

| Artifact | SHA-256 |
|---|---|
|12005640534 Chromium|de5ab07e28f7b3839b89b7a73ecc5595ba32a7cb6596b41974553adb35fbcd27|
|12004897533 Firefox|adbcd59477b7e706f9f6e03480333e8839f2000f5dbac85fa52f83611dce930d|
|12004981413 WebKit|c3ecc4293164a8000de49c1d87bba96a1787cb9b73b60ba7392878171cf5bc59|

## Local execution and preservation

230 PHP/inc syntax files,812 existing pure assertions in22 scripts,eight JS syntax files, Python compilation and12 package tests passed. Permission-aware PHP tests ran as non-root. An isolated real-Chromium DOM/style exercise reproduced CSS hiding a focused element before its media-query handler, then verified the corrected recovery and negative control (page content does not lose focus). This helper is not a database-backed site run. Local PHP lacks PDO MySQL/GD/cURL/ZIP; end-to-end evidence is Actions-only and no browser/hosting policy was bypassed.

A preview package retained all282 RC6 manifest paths and had283 payload files plus the manifest. Every payload hash matched source. All PHP business logic/forms, installer, six migrations, shipped access rules, pinned recovery and administration navigation were unchanged. Public.js audio/queue/fetch code outside its menu block is also unchanged. Final documentation/package hashes are verified separately before delivery. Tests, workflows, private state and synthetic media are not shipped in the Full ZIP.

No real A2 deployment/configuration, mailbox delivery, merchant transaction, production artwork/content/policy publication, physical-device or final owner acceptance occurred. These substantive gates still remain. Do not reinstall an existing website to apply this cumulative candidate.
