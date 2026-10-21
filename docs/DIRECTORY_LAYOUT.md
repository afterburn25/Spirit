# Directory layout — 1.1.0-rc.10

The application now has exactly ten primary folders. Images remain at the root.

```
admin/                   Owner/staff entry points and independent recovery
app/                     Application services
  http/                  Public handlers grouped by feature (same public URLs)
  tasks/                 CLI-only scheduled workers
  views/                 Public, owner and installation templates
  tests/                 Development-only tests (not in the installation ZIP)
  tools/                 Development-only build tools (not in the installation ZIP)
assets/                  Browser CSS and JavaScript
config/
  database/migrations/   Immutable numbered SQL migrations
  .htaccess              Denies direct access
  README.md              Private configuration guidance
  (actual credentials remain outside the public document root)
docs/                    Instructions, history, review records
  design/                Source design evidence (not in the installation ZIP)
images/                  Artwork, logos, icons and background images
includes/                Bootstrap, shared helpers, explicit public route map
install/                 One-page fresh installer; removed/locked after setup
storage/                 Denied placeholder, not public private-data storage
uploads/                 Denied placeholder; existing private media is preserved
```

The hidden `.github/` folder remains at repository root because GitHub requires
that location for workflows. It is excluded from the deployment ZIP. Small root
entry and metadata files remain: `index.php`, `.htaccess`, `VERSION`, `README.md`,
and `CHANGELOG.md`. Source-only AGENTS, PROJECT_STATUS and SESSION_HANDOFF remain
at root for agent continuity, and are not deployment files.

## Routing

Public URLs remain `/music/`, `/shows/`, `/band/`, `/media/`, `/press/`,
`/booking/`, `/shop/`, `/account/`, `/pages/`, `/newsletter/` and `/payments/`.
They are mapped by an exact allowlist in includes/public-routes.php into
app/http/. No URL-derived filename is included. Published `.php` endpoints and
query strings remain supported. Unknown or encoded/traversal paths are rejected.
The root front controller runs the independent maintenance guard before routing.
Apache/LiteSpeed must honor `.htaccess` and mod_rewrite. Do not omit dotfiles.
Private code/templates/configuration stay denied by directory rules.

## Maintenance

Scheduled tasks move to app/tasks/mail.php, app/tasks/payments.php and
app/tasks/cleanup.php. Update their paths in cPanel Cron Jobs; retain their
existing arguments and environment. Their public HTTP URLs remain unavailable.
There is no SSH, Node or Python requirement on the hosting account.

New source commands: `python3 app/tools/package.py --output ./dist`,
`python3 app/tools/release_gate.py`, and `php app/tests/run.php`.
The package excludes app/tests, app/tools, docs/design, runtime data and secrets.

## Recovery boundary

rc.10 uses recovery generation grouped-v2. Earlier recovery jobs are preserved
but cannot partially restore the old folder layout over this code. Complete
cross-layout rollback uses the pre-transition hosting backup. Once transitioned,
future same-layout, same-schema packages use the guarded Browser upgrades flow.
All eight SQL migrations are byte-identical to rc.8; only their directory moves.
