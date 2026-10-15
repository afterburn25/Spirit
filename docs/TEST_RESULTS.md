# RC5 verification — owner logo workflow

## Exact tested source

Commit **b16e9780c41b2f1ea751361c4cd8c3ae27e51c58**, tree **5802cee7016b918348ec9f0e29f4f99941f6cc8f**. The source recovered from the downloaded logo-workflow artifact reconstructs this exact tree. On 2026-10-15 the resumed session rechecked all four workflow conclusions and independently reran local syntax, pure-unit and packaging checks. This finalization changes documentation only. Additional final-main execution and delivery hashes are recorded in PR18 and the accompanying release report after completion, never inferred.

| Workflow | Run | Result |
|---|---|---|
| Website and installer verification | 38681481396 | Success |
| Focused maintenance diagnostics | 38681481344 | Success |
| Populated storefront browser verification | 38681481322 | Success |
| Site logo workflow verification | 38681481271 | Success |

The existing full/focused/storefront workflow files are unchanged. Full verification includes the two PHP/database environments, all 13 retained Chromium workflow steps and Apache HTTPS. The existing three-engine gallery/release/navigation/section/history suites also passed.

## Logo-specific verification

Four jobs cover PHP 8.2/MariaDB 11.4/Chromium, PHP 8.4/MySQL 8.4/Chromium, PHP 8.4/MariaDB 11.4/Firefox and PHP 8.4/MariaDB 11.4/WebKit. Each downloaded integration log contains 109 reported passing checks; each browser log contains 179. Counts include setup and repeated contexts, not unique production transactions.

SQL/HTTP tests exercise read-only preparation, actual-layout private preview, CSRF/method/owner boundaries, wrong-type/missing/damaged media, explicit publication/withdrawal, original/derivative access, replacement drafts, shared public references, stale forms, and current-logo/pre-branding snapshot restoration. The unchanged backup engine preserves audit history; the restore audit epoch rejects forms opened before a restore even when the recovered numeric revision matches.

Each browser job covers eight contexts: root/subfolder multiplied by JavaScript at 1440/390/320px and JavaScript-disabled at 390px. Tests observe actual image decoding, ratio and containment, named home links, private/public separation, navigation refresh, continued playback with the same audio node, withdrawal and native approval-checkbox alignment. These are not physical-device or complete accessibility tests.

## Downloaded artifacts and visual review

The four archive hashes were independently verified again in the resumed session. Each contains six synthetic screenshots, two test logs and the tested source. Twenty-four images were retained across four jobs; selected desktop/mobile renders were visually reviewed. Patterned logo images are test fixtures, not approved band artwork.

| Artifact | SHA-256 |
|---|---|
| 11948581079 — PHP 8.4/Chromium | 13b2e77ed49684e76f9b3540619eb8fa0db0bc331257c35ecfe6551d60a94351 |
| 11948755823 — Firefox | c53265eeabc3256479ffa56099190a5fd828cea5e6ee70868bd10321fa38b709 |
| 11948750873 — WebKit | e01fa09202288a60374795d75dbb7bcb948cad43d7d1ee8c3999b267fe93a479 |
| 11948735765 — PHP 8.2/Chromium | b4d2ee017b3039b2b6d37235bce4b97aedace598da572b79c8b735fa8f401879 |

Earlier bebc6c0b logo tests passed, but rendered review found the inherited grid label misaligning the approval checkbox. The final b16e9780 change corrects the CSS and adds a measured first-line alignment assertion. No failing assertion or permission requirement was dropped.

## Local and cumulative-package checks

PHP 8.4.24: 228 PHP/inc syntax files, 781 passing pure assertions (747 retained plus 34 branding), seven JavaScript syntax files, Python compilation and 12 packaging tests passed in the resumed session. Permission-aware tests ran as an unprivileged user. Local PHP has no PDO MySQL/GD/cURL/ZIP; actual database/application-browser/Apache results are from Actions.

Final package generation must verify every payload hash against canonical source, preserve all 271 prior RC4 manifest paths, and retain byte-identical migrations, pinned access/recovery files, installer, original public.js, administration-navigation code and Backups.php. Tests, workflows, private runtime and synthetic/generated release artwork are excluded. Exact archive counts and digests belong in the completed delivery report, not a predicted result.

## Interrupted finalization and acceptance boundary

The earlier session successfully saved and tested RC5 but failed its final documentation commit because it referenced tree objects that did not exist. Main therefore remained RC4. The resumed session recovered the existing documentation tree 4bd944e90cda2dab9fccf5383e6f3039379c124e and finishes from returned, verified Git objects; the application is not rebuilt from a release graphic.

Generated release infographics were incorrect and are neither implementation screenshots nor proof of features, commits or tests. They are excluded. No real A2 setting/deployment, mailbox delivery, merchant sandbox transaction, production master artwork/content/policies, physical-device review or final owner approval was performed. These remain final-1.0 acceptance requirements. Historical RC4 evidence remains in Git; no earlier delivered ZIP is overwritten.
