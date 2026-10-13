# Browser installer

Do not visit this directory without the package-specific setup code. Source checkouts intentionally have no access.php key. The packager inserts only its SHA-256 hash; the secret is supplied separately and must never be uploaded.

After installation the application publishes private configuration and a completion lock, then removes only these known files: index.php, access.php, .htaccess and README.md, followed by this directory. Unknown files, symlinks or host permission restrictions stop cleanup without bypassing the permanent lock. Use the owner's Installation health page to retry.
