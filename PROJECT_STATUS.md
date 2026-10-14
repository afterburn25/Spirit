# Project status — 0.9.0-dev.8 implementation

User-requested change: remove the setup code and put installation on one page. Based on main1df65ce90124adda5ddb3fd5936be9f006dcee9a / dev7. Work is on work/single-page-installer; the new exact full verification is pending.

## Implemented in this checkpoint

No generated setup code/access hash/PRIVATE-SETUP receipt and no claim stage. Initial GET displays real hosting/write checks, URL, database settings and owner account fields together. One CSRF-protected POST installs and renders completion on the same URL, including verified deletion or retained-lock status and an explicit administration button. JavaScript supplies a busy state and restores cached-form controls; installation works without JavaScript. Errors remain on the form; non-secret values survive and passwords are not reflected.

Safe writes, private permissions, database prefix ownership, concurrent-install lock, interrupted migration recovery, independent completion locks and restricted physical deletion remain. New required upload/image-extension checks align the installer with the cumulative application. cURL/ZIP absence is visible as optional capability guidance. Legacy setup files are excluded; the packager also excludes stale manifests rather than creating duplicate entries.

## Verification plan

Expanded SQL/HTTP installer tests cover code-free entry, same-page correction/completion, CSRF/obsolete-action rejection, no password reflection, blocked deletion, replays, existing prefixes and interrupted recovery. Chromium exercises1440/390/320px and a JavaScript-disabled journey, with form/completion screenshots. Existing feature fixtures retain a test-only GET compatibility adapter; it generates no setup credential and performs no claim POST. All existing cumulative suites remain required. See TEST_RESULTS.md for actual results.

## Preserved and outstanding

Approved public design, discovery/media/player/CMS/history, external show ticket links, bookings/communication, catalog/customers/checkout/orders, staff permissions, backup/restore, same-schema code updates/recovery and retention are preserved. Migrations001–006 and pinned access/recovery files are unchanged. Real host, merchant sandbox, mailbox, artwork/content, populated-site quality and owner acceptance still precede v1.0.

Fresh setup is intentionally open until completion. Install promptly or restrict unattended staging through hosting-panel directory protection. A CSRF token or database connection is not proof that the first visitor owns the deployment. This change does not remove backup/update recovery keys or the private installation secret.
