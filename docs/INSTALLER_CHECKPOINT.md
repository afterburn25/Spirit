# Single-page installer checkpoint — work in progress

Base: main 1df65ce90124adda5ddb3fd5936be9f006dcee9a. Development target: 0.9.0-dev.8. This checkpoint is not a verified release and must not replace main until reviewed and tested.

## User decisions

- Remove the setup code, package claim screen and PRIVATE-SETUP receipt.
- Hosting checks, URL/database/owner details, correction and completion belong on one browser page.
- Keep safe private writes, CSRF, prefix/concurrency protection, interrupted-install recovery, permanent installation locks and verified physical cleanup.
- Latest follow-up asks whether the installer can create the database, database user and password itself. This is a proposed addition, NOT implemented in this checkpoint. The Spiritual Gorillaz hosting control panel has not been confirmed; do not assume its separate 20i project or A2 FTP account identifies this site's provider.

## Automatic provisioning proposal

Retain an existing-database mode. A separately authorized automatic mode may use a supported hosting account API to create a fresh database, a dedicated user, a random password and appropriate database-scoped privileges, then verify the resulting PDO connection and continue the same installer. cPanel exposes Mysql/create_database, Mysql/create_user and Mysql/set_privileges_on_database via authenticated UAPI. Other panels require their own confirmed contract. Do not use guessed APIs, root credentials, silent existing-user password resets, destructive retries or a plaintext hosting credential in the public package. An API token authorizing hosting operations is not a replacement package setup code. Never ask the user to put secrets in Git or chat.

Before implementation: establish actual panel/provider and permitted authentication. Specify database/user name restrictions, account prefixes/quotas, DNS/TLS/SSRF protections, least necessary privileges, retry/idempotency and partial-failure ownership tracking. Do not claim universal shared-host database-creation privileges or real-provider success from synthetic tests.

## Resume work

Runtime/form/packager and new fixture changes are saved, but exact SQL/HTTP/browser/Apache verification is pending. Local working copy was /mnt/data/spirit-install-work; do not assume it exists in a future runtime. Check the branch head and recover source through GitHub.

Remaining documentation consistency: update docs/SECURITY.md, docs/TEST_RESULTS.md and docs/LAUNCH_CHECKLIST.md to describe code-free setup and the newly expanded tests. Those documents still describe the earlier verified dev7 baseline in this checkpoint. Run current lint, 12 packaging tests, full unit matrix, both database environments, all cumulative browser suites (enhanced installer1440/390/320px and no-JavaScript flow), and actual Apache access checks. Inspect form/completion screenshots. Preserve immutable migrations001–006 and the pinned recovery/access layer. Automatic host provisioning should not be counted as delivered until separately implemented and verified.

Fresh code-free setup is open until completion: install promptly or protect unattended staging with hosting-panel directory protection. Post-install locks and unrelated backup/update recovery keys remain. No provider transaction, mailbox delivery, live deployment or final v1.0 acceptance is claimed.
