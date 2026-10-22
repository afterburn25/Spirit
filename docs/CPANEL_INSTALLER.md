# A2/cPanel — manual database setup and PHP notices

**0.9.0-dev.9 supersedes the automatic setup in dev8.** The user cannot obtain a token and explicitly requested manual setup. No cPanel API token, setup code, database-generation option or 20i integration is used.

## Fresh database preparation

In A2 cPanel open **Database Wizard** (older label **MySQL Database Wizard**) or **Manage My Databases** (older label **MySQL Databases**). Create a fresh database and dedicated database user, choose a strong password, and assign the user to this database. Use database-scoped privileges only; no server-root or WHM credentials.

The application needs SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX and REFERENCES for its tables/probes/migrations. cPanel's ALL PRIVILEGES choice for this dedicated database is an alternative; it must not be a global/server grant. Do not use phpMyAdmin to create databases/users because cPanel needs to track them.

Copy the complete database and database-user names, including any account prefix, into the one-page installer. Use the database-user password, not the cPanel login password. Host defaults to localhost and port3306; use A2's supplied MySQL host when different. The table prefix defaults to sg_ and is separate from the account prefix in config/database/user names.

The installer connects to the existing database, probes table privileges, creates website tables and the owner, saves private configuration, permanently locks setup and attempts verified installer-folder deletion. It never creates or deletes a hosting config/database/user. Errors remain on the same page; non-secret values survive correction but password values are cleared. No API token is needed for this flow.

## PHP cURL / PHP ZIP notices

The old combined notice could not tell you which extension was missing. The installer and owner Installation health now report each separately. A Notice is not an installation-blocking Required check.

| Check | Needed for | Missing-extension behavior |
|---|---|---|
| PHP cURL | Payment-server connections | Manual installation continues; payment connections cannot be used without cURL. |
| PHP ZIP | Browser ZIP upgrades | Manual installation continues; the application cannot process ZIP upgrades without ZIP. |

In cPanel open **Software > Select PHP Version > Extensions**, enable curl and zip for the PHP version used by this domain, then refresh hosting checks. If the controls are unavailable, or an enabled box does not resolve the notice, ask A2 support to enable the extension for the website's actual PHP environment. A per-directory PHP override may differ from the account default. Neither a cPanel token nor a site setup code is involved. An application installer cannot guarantee that it can install server PHP modules.

## Existing or interrupted installations

Never replace a working site's config/database/user just to apply this update. Back up and use the documented same-schema browser upgrade or reviewed older-version deployment. Keep original config, encryption secret, database, uploads and separate recovery keys. The update does not recreate the removed installer.

A dev8 automatic attempt that stopped before completion may have a private cpanel-provision.php record. The new installer leaves it untouched and refuses a new setup until manual owner/A2 review. It does not execute the record, reveal its credentials, infer ownership, reset passwords or remove any resources. Do not delete it simply to bypass the check. If automatic setup was never attempted, this condition does not apply.

Fresh code-free setup remains open until completion. Install promptly or use cPanel Directory Privacy while unattended. Actual A2 host permissions, extensions and service acceptance still require staging validation.

## Official instructions consulted 2026-10-14

- cPanel Database Wizard: https://docs.cpanel.net/cpanel/databases/database-wizard/
- cPanel MySQL/Manage My Databases: https://docs.cpanel.net/cpanel/databases/mysql-databases/
- Hosting PHP Selector: https://kb.hosting.com/docs/changing-php-versions-and-settings-in-cpanel
- Directory-specific PHP: https://kb.hosting.com/docs/changing-the-php-version-for-a-specific-directory
