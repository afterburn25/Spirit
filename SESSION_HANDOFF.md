# Session handoff — resume here

Repository **afterburn25/Spirit**, authoritative **main**, development checkpoint **0.5.0-dev.1**. Preserve AGENTS.md and the user's selected cinematic design. No more mockups, no native ticketing, no fake content, no broad permission changes or secrets in Git.

## Current work

The application now includes the selected public layout, structured owner CMS/media library, band/press/gallery pages, music/releases/local-audio player and shows/external ticket sellers, plus remaining installer ownership/path fixes and additive schema handling. The new source has expanded automated tests. **Read the newest Actions run before claiming it is verified.** Prior foundation green run 38456010840 applies only to the old code.

## Where to resume

1. Inspect live main, latest run, PROJECT_STATUS and TEST_RESULTS. Fix any regression instead of suppressing a test. Preserve concurrent changes; never force-push.
2. Verify root/subfolder installation, sentinel CREATE-before-INSERT ownership recovery, older schema update, draft/public isolation, media access/ranges, music playback across navigation and every public route at 1440px/390px.
3. Continue into booking + durable email queue + newsletter double opt-in/unsubscribe, then merchandise/accounts, sandbox-first Authorize.net, then maintenance/backup/upgrade hardening. Do not stop merely because high-resolution art or live provider credentials are not supplied.
4. High-resolution art is not finished: Git contains a small reference thumbnail plus isolated gorilla crops from the approved original; prepare proper production assets without changing the selected identity. Do not label these as high-resolution master art or use the full concept as a clickable website.
5. Staff delegation, full pagination, revision restoration/archive UI, event structured data, final metadata/redirects, accessibility and measured performance still need work. Publish only approved actual content.
6. Keep documentation honest: describe implemented, automated-tested, provider/host-blocked and pending separately. No accepted 1.0 tag or live deployment without approval.

## Environment

GitHub connector reads/writes work. Local network clone failed and local PHP lacks pdo_mysql/GD; real DB/browser validation runs in isolated GitHub Actions. No production secrets should be requested in chat. Host PHP cannot override ACLs or discover every arbitrary web-server alias. Legacy dev.1 runtime is detected and blocked rather than silently ignored, but automatic relocation is not claimed.

Each milestone is cumulative; packaging preserves private receipts, config/data/uploads and excludes executable test tooling from deployment. Existing installations use admin/updates.php for additive schema, not a destructive reinstall.
