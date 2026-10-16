# RC8 live deployment runbook

Verified source checkpoint: `9fc007c4f5e493ddb0727b5189a5356f00546894`  
Verified branch: `release/1.0.0-rc.8-verified`  
Application version: `1.0.0-rc.8`  
Installer ZIP SHA-256: `b9f47ce97be9a905354b28d21cb76f4340c51a857289e1b1a422838be6aeb5f0`

## Pre-deployment state

The current public production site is still serving the pre-RC8 presentation. Before upgrading, preserve this as the rollback baseline.

Observed public differences from RC8:
- Home still uses the older “Music with a higher purpose” hero wording.
- Music still uses the older “Real stories. Real sound.” copy.
- Band still shows the generic unpublished-story/member empty state instead of the confirmed Chad Lindsey / Ron Green / Charlie Savell factual lineup.
- Booking still uses the older “Let’s make it happen” wording.
- Press still uses the older generic biography/download empty state.
- Merch and “Your bag” remain advertised globally even though no products are published.

## Supported upgrade path

Use the existing owner-only **Browser upgrades & recovery** screen.

Do **not**:
- reinstall the application;
- create a replacement database;
- overwrite the private configuration;
- overwrite runtime storage or uploads;
- manually unzip the release over the public document root;
- overwrite the historical RC7 archive with RC8 bytes.

RC7 -> RC8 is intended as a same-schema code upgrade. No database migration file or pinned recovery-layer file changed between the verified RC7 baseline and RC8.

## Deployment sequence

1. Sign into the existing owner administration.
2. Open **Browser upgrades & recovery**.
3. Upload the verified full ZIP:
   - filename: `Spiritual-Gorillaz-1.0.0-rc.8-Full.zip`
   - SHA-256: `b9f47ce97be9a905354b28d21cb76f4340c51a857289e1b1a422838be6aeb5f0`
4. Enter the current owner password.
5. Confirm the ZIP is the trusted project release.
6. Stage the package. Staging must not replace live files.
7. Download and save the **PRIVATE update-recovery receipt** off the website.
8. Confirm the recovery receipt is stored safely.
9. Install the staged update.
10. Allow the new code to start under the maintenance gate.
11. Review the new application/version/integrity state.
12. Use **Confirm** only after the RC8 application reports its version/integrity checks successfully.
13. If startup/integrity fails, do not retry blindly. Use the saved recovery receipt and the independent recovery page to restore the previous code.

## Immediate post-upgrade public checks

The following public behavior should change immediately if RC8 is installed:

### Home
Expected fallback subtitle/body:
- “Real people. Shared songs. A reason to come together.”
- “Music. People. Purpose. Start with a song and stay for the connection.”

### Music
Expected subtitle:
- “Find a song. Stay for the feeling.”

### Band
When no member CMS profiles are published, the page must still show:
- Chad Lindsey — Guitar, keyboards and vocals
- Ron Green — Harmonica
- Charlie Savell — Drums

These fallback cards must be non-clickable and must not invent portraits or biographies.

### Booking
Expected subtitle:
- “Let’s talk about your event.”

The page must still state that an inquiry does not reserve a date or take a payment.

### Press
Expected subtitle:
- “Band information and booking inquiries.”

### Store
If the storefront remains disabled:
- **Merch** must disappear from the global main navigation.
- **Your bag** must disappear from the footer.
- Direct shop access may still show the truthful closed-store state.
- Customer account routes may remain available.

### Branding
When no owner-published logo is active, the bundled scalable crown/wordmark SVG should render instead of the former text-only fallback.

## Post-upgrade owner checks

Open **Launch checks** and verify:
- installed version is RC8;
- schema ledger is current;
- installation lock remains valid;
- installer folder remains removed;
- private runtime remains protected;
- no unresolved payment operations exist;
- no unfinished mail jobs are unexpectedly stuck;
- mail-worker heartbeat is recent after the scheduled task runs;
- payment-maintenance heartbeat is only required when commerce is active;
- dormant commerce is reported as a safe state if store/payments are disabled.

## Real-host acceptance still required

After RC8 is confirmed:
- verify the scheduled mail task actually records a recent heartbeat;
- if commerce will be enabled, complete genuine Authorize.net sandbox acceptance before enabling it;
- exercise the supported backup/recovery drill on staging or the approved same-instance environment;
- review real pages on phone/desktop, keyboard/zoom, performance and content;
- explicitly approve the final launch state before promoting to final 1.0.0.

## Rollback trigger

Use recovery instead of pressing forward if any of these occur after install:
- RC8 version does not load;
- integrity confirmation fails;
- administration cannot start;
- maintenance cannot clear safely;
- installed file hashes do not match;
- schema fingerprint is not the expected unchanged schema.

Rollback restores previous code only. It does not rewind newer business rows or uploaded media.
