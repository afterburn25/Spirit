# Verification — 1.0.0-rc.3

## Exact application and execution

Application **28536a6102047fb89dd9cd6b04b5be3eed85e619**, tree **5989bf3f21c3e9b74c3db18d75c3c026b0402055**. The final follow-up changes documentation only; runtime, assets, schema, workflows and tests remain identical. Source-artifact success is not application verification.

Full workflow **38667039903**, retry attempt2: **completed/success**.
Focused maintenance **38667039834**: completed/success.
Three-engine storefront/release/navigation/section workflow **38667039771**: completed/success.

| Coverage | Job | Result |
|---|---|---|
| PHP8.2 / MariaDB11.4 cumulative SQL/HTTP, retry |116216142837|PASS|
| PHP8.4 / MySQL8.4 SQL/HTTP and13 Chromium workflows, retry |116216140290|PASS|
| Actual Apache PHP8.4 HTTPS, retry |116216141943|PASS|
| Chromium gallery/release/navigation/section Pages |116201249911|PASS|
| Firefox gallery/release/navigation/section Pages |116201249513|PASS|
| WebKit gallery/release/navigation/section Pages |116201249783|PASS|

The first full-run status was reported failed while retrieved decoded job logs showed successful later steps. Those responses were inconsistent, and the original failure cause was not established. The failed-job retry was explicitly requested on the unchanged source. Do not describe this as a proven product fix, silently turn a failed attempt into a pass, or drop any failed test. The accepted evidence is the completed retry above. The source and three-engine logs were inspected separately.

## Local checks and real integration boundaries

Local **223 PHP/inc syntax checks,747 distinct pure assertions and12 packaging tests passed**, with6 JavaScript syntax checks and40 Python compilation checks. Pure checks ran as an unprivileged account. PHP here is8.4.24 and has no PDO MySQL/GD/cURL/ZIP; local lint/pure checks are not a substitute for real SQL, image or browser testing. The new58 section assertions are included once in747, not multiplied by environments.

The full workflow retains both database environments, actual no-cURL/no-ZIP manual installation, all existing authorization, private storage, prefix/recovery, communication, payment, staff, backup, update and retention tests, plus13 Chromium journeys and actual Apache HTTPS access rules. No CSP, endpoint permission, payment-network protection, update or recovery validation was removed or relaxed.

Section SQL/HTTP tests use real saved Page/media records through existing forms. They cover eight unsaved preparation links without database mutation, exact lookup beyond1100 library records, conflicting draft/published slugs, private previews, escaped copy, rejected POSTs, original/derivative authorization, explicit publication, private replacements, history restoration, withdrawal, normal content-editor/store-manager boundaries and sitemap exclusion. A preview never adopts arbitrary media or publishes other content. Existing business data is not replaced by an installation package.

## Three-engine and visual evidence

Each engine's preserved logs records **137 section assertions**, in addition to212 gallery,1491 release-review and251 navigation assertions. These are execution counts, not a claim of that many unique scenarios. The new section journeys run at1440/390/320 pixels on root and subfolder installations. They exercise the actual Page editor, image inspector, saved-draft popup, publication, portrait and landscape rendering, disabled booking preview and native no-JavaScript access. All use synthetic patterned images and text, never production band identities or inventory.

| Artifact | Engine | SHA-256 |
|---|---|---|
|11941991844|Chromium|0e7fa10f6f9f098489731bb77db258b5d1e50d7687d68ab63cec00dd7db53b28|
|11942726143|Firefox|06387dbfb7c1b0ef45f5c015b7041304897c95de96107bf9e65361d1bfb79ad8|
|11942146852|WebKit|aa72e6745c063427bb07a7853fdf152707c23f75d838e1c5c6d2c6bb75a54a96|

All three downloaded archive digests and final successful log lines were verified.36 section screenshots are retained. Selected desktop editor/directory and narrow public-section/editor renders were visually reviewed; contact sheets crop only the review view, and the original full-page screenshots remain unchanged. Not every cumulative screenshot was newly visually inspected. A nonfatal Playwright response-cleanup warning in the earlier gallery log is not being represented as a zero-warning run; its process and assertions completed successfully.

## Source and package integrity

Source artifact11941841098, SHA-25662ab2255ff124e57bcb56d92e92868f147aa8eebce6be4ef6fb3203a234e8677, reconstructs the exact application Git tree. Comparison against the previously delivered RC2 ZIP preserved all264 prior manifest paths. Six migrations, five pinned recovery/access files, all manual-installer paths and grouped-navigation runtime remain byte-identical. The final report records canonical source, manifest and immutable delivery hashes. Deployment ZIPs exclude executable tests/build tools, real runtime data, private receipts and setup codes.

## Outstanding acceptance

These tests do not establish actual A2 PHP ZIP/cURL settings, private-root mappings/ACL/OPcache/proxy behavior, installer cleanup or recovery on the user's account; receiving-mailbox delivery; genuine merchant tokenization/reporting/settlement/reversal; production master artwork/approved content/policies; physical-device accessibility/performance/security review; or final owner approval. No hosting setting, real charge or production service was changed. Same-schema upgrades and same-instance guarded restore retain their documented limits. This is not accepted final1.0.
