# Resume here — verified manual-only one-page installer

Repository **afterburn25/Spirit**, **main** authoritative. Current cumulative version **0.9.0-dev.9**. Inspect live refs before writing. Read AGENTS.md, status and TEST_RESULTS.md. Never force-push or overwrite concurrent changes.

## Latest user decisions

The user cannot get a cPanel token and explicitly removed automatic database creation. **A2/cPanel, manual existing database only, no 20i, no hosting API token, no setup code.** Keep all setup fields/checks/corrections/completion on one page. A pasted combined cURL/ZIP notice led to separate optional-extension diagnostics. Do not hide missing features or restore automatic setup in a future session.

## Verified application

Application **4736764ef681103c7d1b02b3851861a43c9201e5**, tree **7bd4574ce745e4bdee6419b0033afdbde48c7f3c**, passed full workflow **38567553630** and focused maintenance **38567553677**. PHP 8.2/MariaDB 11.4 job115864285085, PHP 8.4/MySQL 8.4 job115864285952 and Apache HTTPS job115864285705 passed. All twelve Chromium workflows ran in the PHP8.4 job. Final documentation-only changes leave runtime/assets/schema/workflows/tests unchanged. Inspect exact main ref and new runs before resuming.

480 pure assertions and12 packaging tests passed. Real SQL/HTTP installation with cURL/ZIP disabled passed at root/subfolder. The one-page browser flow passed1440/390/320 and no-JavaScript, including correction, cleanup and owner access. Inspected ten actual installer/form/completion/health renders from artifact11896183628. Synthetic data only; no production host/provider contacted.

## Implementation to preserve

Automatic database/user creation and token/mode fields are removed. Domain/request handlers reject obsolete automatic fields without secret reflection. The three former Cpanel PHP paths contain only inert declarations/comments, not class definitions or API behavior. Keep them while older supported updater manifests require all prior files. Their omission was discovered to break cumulative-package acceptance; compatibility was preserved without relaxing update validation.

Earlier private cpanel-provision.php state is detected by existence only and retained for manual owner/A2 review. Never execute/adopt/delete it or reset unrelated credentials. Existing manual pending.php recovery, prefix/operation ownership and permanent setup locks remain.

Installer::optionalChecks reports cURL and ZIP independently in setup and owner Installation health, including the current PHP version. Manual setup works without either; payment connections need cURL and browser ZIP upgrades need ZIP. Enable them for the PHP environment serving the domain via cPanel Select PHP Version or A2 support. No token or application-side module-install bypass is needed or promised.

## Corrections and continuity

The first new run stopped on a test expecting the retired wording 'provisioning failed'. Updated it to the current manual connection/table message and required the correction form with no completion markup; all actual interruption/recovery/data assertions remain. No failing workflow was skipped. Removed cPanel API tests only because that feature was deleted; retained payment TLS, data-recovery, permission, browser and Apache suites.

Canonical tracked source was compared to local application bytes. Package compatibility was checked against the delivered dev8 manifest: no previous payload paths omitted, six immutable migrations and five pinned access/recovery files unchanged. Reverify final archive hashes against canonical source when delivering. The packager emits only the cumulative Full ZIP, no setup receipt.

## Next work and limits

Actual A2 manual database/PHP settings/private-storage/deletion acceptance, real mailbox and merchant sandbox, approved master artwork/content, populated-site quality and owner acceptance remain. Do not silently enable production services or label the site1.0. Existing sites upgrade; never create a replacement DB or delete locks to reinstall. Keep original private config/encryption secret/database/uploads and unrelated backup/update recovery keys. Same-schema updates and same-instance restoration retain their documented limits.

Local PHP lacks PDO MySQL/GD/cURL/ZIP. Local syntax and pure/package tests are separate from real Actions integration. Test-only optional-extension simulation is never deployed; real absence tests use isolated child PHP configurations. Do not assume container paths from this session exist later; recover canonical source from GitHub and verify exact files before linking.
