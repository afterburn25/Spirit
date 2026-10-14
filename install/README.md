# Manual-only single-page browser installer

From 0.9.0-dev.9, use an existing database and its assigned database user. Automatic cPanel creation is removed. No hosting API token, setup code, generated access.php, claim step or private setup receipt is required. Hosting checks, database/owner fields, corrections and completion remain on one page. Native forms also work without JavaScript; owner sign-in is a separate explicit button after completion.

PHP cURL and PHP ZIP have separate optional notices. Manual installation may continue without them; payment connections and browser ZIP upgrades require their corresponding extension. See docs/CPANEL_INSTALLER.md for A2 cPanel configuration and support instructions.

Fresh setup is intentionally open until completed. Install promptly after extracting the package or protect unattended staging through hosting-panel directory protection. CSRF and database credentials are not proof that the first visitor owns the deployment. Existing table prefixes and completed installations cannot be silently replaced. Do not rerun setup on a working installation or create a replacement database for an update.

Private configuration and completion locks are published before removing the installer. Only known index.php, legacy access.php, .htaccess and README.md files are eligible for cleanup. Unknown files/symlinks and host restrictions are not bypassed. Review the same-page cleanup result and use owner Installation health to retry if necessary. Refreshing the old URL after physical removal may return 404; do not reinstall.
