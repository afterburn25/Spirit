# Project status — RC5 site logo candidate

Based on verified RC4 main `49d5bbbbab146e7833dced698af51458ec625ddc`; current work is `work/site-branding`. Exact new Actions verification is pending, so RC5 has not been promoted to main.

Implemented: owner logo draft, actual-homepage private preview, explicit public-use approval, publication and withdrawal; contained responsive header/footer images; named home links; a separate navigation-event branding module that leaves the original persistent player/navigation code untouched. Website settings copy no longer incorrectly calls the site a holding page or its existing CMS a future feature.

The versioned settings record separates draft and published image IDs. Current owner role/session and revisions are checked under a transaction; wrong-type/missing/damaged images and stale submissions are rejected. Only the published image gains public media authorization. Forms also record the last restore audit epoch, preventing an old snapshot from making a pre-restore form valid again. The backup engine is unchanged.

Local 228 PHP/inc syntax checks, 781 pure assertions (747 retained +34 new), seven JavaScript checks and 12 package tests passed. New SQL/HTTP and real three-engine browser checks must pass alongside all unchanged full/focused/storefront suites. The new dedicated workflow covers both SQL/PHP combinations and Chromium, Firefox and WebKit.

No production master artwork was generated successfully. The erroneous generated release graphics are excluded and prove no features, commits or tests. No installer, grouped-menu state, schema, pinned recovery, payment mode or real provider changes. All earlier deployment paths must be preserved in the final cumulative package.

Remaining: approved master artwork and real content/policies; actual A2/private-storage/extension/cleanup/recovery, receiving mailbox, genuine merchant sandbox, populated physical-device review and final owner approval. Final 1.0 is not released. Read docs/BRANDING.md and docs/LAUNCH_CHECKLIST.md.
