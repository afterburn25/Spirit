# Single-page browser installer

From 0.9.0-dev.8, open this directory in the browser to see all hosting checks, database details and owner-account fields together. There is no setup code, generated access.php, claim step or private setup receipt. One protected POST installs and renders completion on the same page. A normal non-JavaScript submission also works; owner sign-in is a separate explicit button after completion.

Fresh setup is intentionally open until it is completed. Install immediately after extracting the package, or use hosting-panel directory protection for unattended staging. A CSRF token is not proof that the first visitor owns the deployment. The installer still requires valid database credentials and explicit owner details, refuses existing table prefixes and preserves permanent locks.

Private configuration and completion locks are published before removing the installer. Only known index.php, legacy access.php, .htaccess and README.md files are eligible for cleanup. Unknown files/symlinks and host restrictions are not bypassed. Review the same-page cleanup result and use the owner's Installation health to retry if necessary. Refreshing the old URL after physical removal can return404; do not rerun installation.
