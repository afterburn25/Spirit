# Project status

Updated 2026-10-13. Development checkpoint **0.5.0-dev.1**. The earlier 0.1.0-dev.2 foundation passed run 38456010840. The new integrated application below is awaiting its expanded CI results; do not carry the previous green check forward as proof of new code.

## Added in this checkpoint

- Durable sentinel ownership embedded in table CREATE, database-prefix advisory lock, non-destructive legacy-runtime detection and actionable hosting diagnostics.
- Idempotent, checksum-recorded additive migrations and an owner-only schema update for existing foundation installations.
- Real public Home, Music, release detail, Shows, event detail, Band, member, Media, Press, generic page and coordinated booking/merchandise state pages.
- Structured owner editing with private drafts, immutable published snapshots, saved-draft preview, unpublish, revision history and optimistic conflict detection.
- Private image/audio/PDF uploads, rights confirmation, MIME limits, image re-encoding, publication-based authorization and streamed audio ranges.
- A persistent local-audio player, supported same-origin public navigation, photo dialog, responsive navigation and reduced-motion styling.
- Published releases/tracks/streaming links and complete event years/timezones, upcoming/past archive, city filter and external venue ticket buttons with status-aware alternatives.
- Approved reference thumbnail and two isolated artwork crops stored in Git. These are lower-resolution concept-derived assets, not final high-resolution artwork masters.

## Verification status

Expanded schema/path/SQL/HTTP/media/publishing and Chromium desktop/mobile/player suites are included. Await the run for this commit before reporting new pass counts. No live hosting, real payment or production email test is claimed.

## Still to build or finish

Booking submissions, newsletter confirmation/unsubscribe and transactional email; merchandise/accounts/cart and Authorize.net; delegated staff permissions; polished maintenance/backup/restore/ZIP upgrades; event structured data, redirects and final search/accessibility/performance acceptance; high-resolution production artwork and genuine band content. Lists currently cap at 1000 items and advanced revision restore/archive UI is not implemented.

Actual shared-host Apache/TLS/alias/ownership acceptance and safe legacy-runtime migration remain separate checks. Detection refuses an unsafe fresh install; it does not claim automatic legacy migration. The original image file itself is not in Git; the derived reference thumbnail is. No final v1.0 release or user acceptance has occurred.
