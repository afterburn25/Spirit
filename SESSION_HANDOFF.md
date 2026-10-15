# Resume here — verified RC5 site logo workflow

Repository **afterburn25/Spirit**, main authoritative. Work was developed on **work/site-branding**, PR18, from RC4 main49d5bbbbab146e7833dced698af51458ec625ddc. Read live refs, AGENTS.md, status, design lock and test records before edits. Preserve concurrent work; never force-push.

## Exact source and completed evidence

Tested commit **b16e9780c41b2f1ea751361c4cd8c3ae27e51c58**, tree **5802cee7016b918348ec9f0e29f4f99941f6cc8f**. Completed successful workflows: full38681481396, focused38681481344, storefront38681481322, logo38681481271. Documentation-only finalization preserves runtime/tests/workflows/VERSION. Later exact-main runs and final package hashes are recorded separately in PR18 and the delivery report; do not infer them.

All four logo jobs passed109 SQL/HTTP and179 browser checks in their downloaded logs. Cover JS1440/390/320 and native390 at root/subfolder (8 contexts/job), two SQL/PHP combinations and all three engines. Twenty-four synthetic screenshots were downloaded and hashed; selected final desktop/mobile renders were reviewed. The earlier bebc6c0b logo run passed, but visual review found inherited checkbox-grid alignment. b16e9780 fixes the row and adds an actual geometry assertion; use the later evidence, not just the first pass.

Local228 PHP/inc syntax,781 pure assertions,7 JS syntax checks and12 package tests passed. Local PHP lacks PDO MySQL/GD/cURL/ZIP, so actual application integration evidence is Actions-only. Canonical downloaded source reconstructed tree5802cee7. Packaging validation preserved every271 prior payload path, all six migrations, five pinned recovery/access files, installer, original public.js, admin-navigation runtime and Backups.php. Final archives are immutable; re-establish runtime paths before links.

## Implementation to retain

app/Branding.php stores separate draft/published logo IDs and revision in one settings record. admin/branding is owner-only. Native save/publish/withdraw enforce CSRF, a locked current owner/session, revision and image integrity. Preview is the real homepage, GET/HEAD-only, no-store/noindex/no-referrer, with related content still public-only. Media::serve authorizes only the published branding image in addition to existing public references.

Header/footer preserve ratio and named home links. An inert server-rendered template and assets/js/branding.js update the header on spirit:navigated without fetching HTML or replacing the audio node. Original public.js and backup engine are unchanged. The existing restore audit epoch rejects old forms even when an old snapshot restores a matching numeric revision. Current-logo and pre-branding snapshots were tested. Website settings links to the editor and its obsolete holding-page/future-CMS copy is corrected.

Three generated release graphics were erroneous, not screenshots/source/test results. None is in the repository or package. Do not implement or claim their unrelated features. Production crowned-gorilla master artwork still has not been completed in this pass.

## Fixed scope and next work

Preserve approved navy/gold/blue design, external venue ticket sellers, grouped remembered navigation and manual one-page A2/cPanel installer. No setup code/token/20i/automatic DB. Existing sites upgrade with backups, never reinstall or replace original private config/encryption secret/data/media/recovery receipts. Same-schema updates and same-instance restoration limits remain.

Next substantive work is approved production artwork and genuine band/store content/policies, followed by real A2/mailbox/merchant/recovery/device and owner acceptance. The new controls do not mean an approved logo or master artwork was supplied. No real service was configured, no site deployed and no final1.0 acceptance is claimed.
