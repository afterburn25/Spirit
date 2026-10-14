# Project status — 0.9.0-dev.9

Application **4736764ef681103c7d1b02b3851861a43c9201e5** passed full workflow **38567553630** and focused maintenance **38567553677** on 2026-10-14. Both database environments, all twelve Chromium workflows and actual Apache HTTPS checks passed. The final documentation-only descendant changes no runtime, assets, database migrations, workflows or tests. Recheck live main before further edits.

## Landed user-requested changes

**A2 Hosting/cPanel, manual database configuration only.** Automatic database/user creation, hosting token fields, mode switching and all three Cpanel runtime class definitions are removed. No setup code or PRIVATE-SETUP receipt. One-page checks/manual database/owner/correction/completion remain, including no-JavaScript use, private writes, permanent locks and physical deletion verification.

Retired automatic form submissions are explicitly rejected without reflecting token values. A saved earlier incomplete automatic record blocks setup for manual review; it is not executed, deleted or reused. Completed installations keep their original DB/config/media. No hosting API or privileged SQL administrator is used by the installer.

The ambiguous cURL-and-ZIP notice is split into **PHP cURL** and **PHP ZIP**. Both remain optional for manual installation, with separate feature limitations and cPanel/support instructions. Owner Installation health shows the same checks and current PHP version after installer deletion. Its stale claim that maintenance is unavailable is corrected.

The three former Cpanel paths remain only as inert PHP declaration/comment files. They expose no classes, token handling or provider logic. This preserves the existing dev8 updater's cumulative-file rule while replacing its old API code. No schema/recovery validation was relaxed.

## Verified evidence

**480 cumulative pure assertions**, **12 packaging tests**, all real SQL/HTTP suites on PHP 8.2/MariaDB 11.4 and PHP 8.4/MySQL 8.4, all configured Chromium workflows and actual Apache HTTPS access tests passed. New 38 unit checks cover each optional-extension combination, manual/retired fields and absent API classes.

The new integration suite actually disables cURL and ZIP in child PHP configurations while retaining PDO MySQL/GD. Root and subfolder installations complete, delete the installer and permit owner sign-in/health/payment-settings/upgrade-screen access. No hosting account was contacted. Older private automatic state is preserved without execution; obsolete POSTs cannot leak credentials or install.

One-page browser installation passed at 1440/390/320 px and with JavaScript disabled. Ten form/completion/owner-health screenshots were inspected from artifact **11896183628**. Existing website/payment/staff/maintenance/media tests remain. See TEST_RESULTS.md for corrected test-message mismatch and cumulative-package compatibility checks.

## Remaining launch gates

Actual A2 permissions/PHP settings, real mailbox and genuine merchant sandbox acceptance, approved production artwork/content, populated-site accessibility/performance/security review and owner acceptance. No accepted v1.0. Backups/restoration and ZIP upgrades retain their documented same-instance/same-schema limits. No server extension has been enabled on the user's behalf.
