> **Current rc.10 layout:** read [DIRECTORY_LAYOUT.md](DIRECTORY_LAYOUT.md) and [LAYOUT_TRANSITION.md](LAYOUT_TRANSITION.md). An rc.8-or-earlier updater must refuse the changed foundation. Preserve existing data; never reinstall.

# Browser code upgrades and independent recovery

Development milestone 0.9.0-dev.3. New full regression execution is pending. Do not treat the following implementation description as a passed result; see TEST_RESULTS.md and exact Actions head.

## Supported workflow

Owner-only **Browser upgrades** accepts a newer, complete project installation ZIP. It requires CSRF, current owner password, and explicit acknowledgement that the package comes from a trusted project release. Manifest hashes validate bytes, not publisher authenticity; this is not a signed update feed or automatic download service.

The PHP ZIP extension with indexed stream support is needed only for this module. The host must allow private staging and actual rename/replacement in affected source directories. Limits: 32 MiB uploaded ZIP, 64 MiB expanded payload, 8 MiB per entry, 3,000 entries, 12-second validation budget. A complete pre-update data snapshot must fit the existing backup budget. No SSH, shell commands or executable test files are required on the host.

Staging streams every entry, rejects traversal, case collisions/duplicates, symlinks/special files, missing checksums, unexpected private/runtime/installer destinations, incompatible PHP syntax, same-version replacements, downgrades and omissions from the previously deployed cumulative manifest. PHP is parsed, never executed, during validation. The original config, installation secret, runtime and removed installer stay untouched. All access rules and the small independent recovery kernel are pinned.

This first code-updater iteration accepts **unchanged database schemas/migrations only**. A package adding or changing migrations or changing the pinned recovery layer is refused before application writes. Such releases require a reviewed staging deployment and the existing additive database update, not an unsafe automatic SQL rewind. Cross-schema unattended upgrades and rollback are not implemented.

## Before installation

Download and save the PRIVATE update-recovery receipt off the website. It contains a checkpoint and a random 256-bit recovery capability. Only its hash and authenticated-encrypted copy are stored in private metadata. Do not commit or publicly share the receipt. This capability is limited to restoring this installation's latest applied code checkpoint; it cannot choose arbitrary source or destinations.

Confirm receipt storage, then install reviewed code. The updater rechecks all current and staged hashes, copies original files into private rollback storage, creates an encrypted pre-update DB/config/media snapshot, and writes a durable journal before replacing application files. It uses the same shared/exclusive lock as normal requests. Public and scheduled application work stays paused while update verification is pending, including after an interrupted process exits.

## Verify or recover

A separate authenticated request must start the new application. The owner verifies its version, unchanged schema/ledger and installed file hashes before reopening public access. This startup/integrity test is not a complete acceptance of all application workflows; perform the release's documented staging checks as well.

Use **Roll back previous code** to restore the latest original code without rewinding database business rows or uploaded media. Rollback checks every original file before any write and refuses when the database schema has changed since the checkpoint. Newer customers, passwords, orders and consent survive code rollback.

When normal administration cannot start, open `/admin/upgrades/recover.php` under the installed base URL. This small pinned entrance does not load application bootstrap or uploaded PHP. Enter the private checkpoint/recovery key, its CSRF form and typed ROLLBACK confirmation. It checks the same schema fingerprint and originals and restores only the recorded code. A failed or interrupted rollback keeps maintenance active and can be retried with the saved receipt. Do not delete rollback files or the maintenance record to force success.

The frozen early guard runs before replaceable application classes. The local location.php pointer stores only the canonical private directory, is denied direct access, and is excluded from distribution archives. Recovery does not bypass provider permissions, repair a lost database, or restore after private originals are lost. Keep hosting-panel backups too.

## Implementation references

Official PHP manual: ZipArchive::getStreamIndex, ZipArchive::getExternalAttributesIndex, token_get_all with TOKEN_PARSE, and flock. Index-based bounded streams avoid bulk extractTo; external attributes distinguish links and special files; syntax parsing cannot prove runtime behavior; OS locks are supplemented by the durable update journal. Review actual behavior on Apache/LiteSpeed, OPcache and account ACLs during staging.
