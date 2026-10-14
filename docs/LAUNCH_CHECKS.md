# Launch checks and upload-limit feedback

Open **Admin > Launch checks** or the dashboard link. Only an active owner can view/export it; editor, store-manager, customer and anonymous access is denied. The page is no-store/noindex. It shows local PHP/extensions/limits, installation flags, migration ledger, configuration and published counts.

**Observed** means only the described local condition was seen. **Review required** is not a failed workflow, and **Not checked** is not a pass. Configured SMTP, checkout, publication and indexing remain review tasks. There is no overall ready badge, percentage, checkbox that bypasses testing, auto-launch, settings write or external request. A read-only writable flag is not an I/O test; Installation health performs its own real probe.

Download support report uses POST and CSRF. JSON contains only fixed software/extension/limit/state fields; no hostname, path, DB name, credentials, logs, email/customer information, merchant key, counter or content text is exported. It is not saved on the site. Review before sharing; software-version information is still included. Never attach private configuration or recovery receipts to a public issue.

Media uploads, backup imports and ZIP upgrade staging display current domain-PHP limits. The whole multipart request includes form data and framing; a file smaller than upload_max_filesize can still exceed post_max_size. Application caps, image-decode memory and upstream server limits remain separate. Unknown settings are shown as unknown rather than guessed. No limit is raised by this code.

On the three protected upload screens, an oversized POST gets413 and specific feedback before any requested action. PHP may already have discarded both POST and FILES. Smaller invalid-CSRF requests still get403; valid normal requests retain file validation and owner reauthentication. This is not a generic body-parser bypass or a change to the independent pinned recovery entrance. Web-server rejection before PHP runs may still show the provider's own error page.

For A2, check the PHP configuration serving the actual domain and ask support about upload_max_filesize/post_max_size where needed. cURL and ZIP remain independent features, optional for manual installation, not token requirements. Keep manual-only one-page setup and follow LAUNCH_CHECKLIST.md for actual host/provider/content/owner acceptance.

Official PHP references consulted2026-10-14: https://www.php.net/manual/en/ini.core.php and https://www.php.net/manual/en/function.ini-parse-quantity.php .
