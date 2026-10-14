# Project status — 0.9.0-dev.9

Current work: manual-only database configuration and clear optional PHP extension checks. Base: verified dev8 main 1cffdccaaab20737b6ddf1f0f62ee306d5c61d51, full run 38563052511. New exact verification is pending; do not promote a failed/untested checkpoint.

## User-requested changes

A2 Hosting/cPanel only. Automatic database/user creation, hosting token fields, mode switching and all three Cpanel runtime classes are removed. No setup code or PRIVATE-SETUP receipt. One-page checks/manual database/owner/correction/completion remain, including no-JavaScript use, private writes, permanent locks and physical deletion verification.

Retired automatic form submissions are explicitly rejected without reflecting token values. A saved earlier incomplete automatic record blocks setup for manual review; it is not executed, deleted or reused. Completed existing installations keep their original DB/config/media. No hosting API or privileged SQL administrator is used by the installer.

The ambiguous cURL-and-ZIP notice is split into PHP cURL and PHP ZIP. Both remain optional for manual installation, with separate feature limitations and cPanel/support instructions. Owner Installation health now shows the same checks and current PHP version after the installer has been deleted. Its stale claim that maintenance is unavailable is corrected.

## Verification plan

38 new unit checks cover all four extension availability combinations, manual versus retired fields and removal of API classes. New real SQL/HTTP tests cover manual setup, rejected automatic POSTs, preserved legacy state and domain-root/subfolder installation with actual cURL and ZIP disabled in child PHP. Browser tests preserve one-page/no-code installation at 1440/390/320px and without JavaScript, plus owner health checks. Run every retained feature regression and actual Apache HTTPS suite.

The removed cPanel transport/provisioning tests correspond only to the deleted feature. Payment transport, TLS checks, upload/upgrade validation, data recovery and authorization tests remain. No immutable migrations or pinned recovery/access files changed.

## Remaining launch gates

Actual A2 permissions/PHP settings, real mailbox and genuine merchant sandbox acceptance, approved production artwork/content, populated-site accessibility/performance/security review and owner acceptance. No accepted v1.0. Backups/restoration and ZIP upgrades retain their documented same-instance/same-schema limits.
