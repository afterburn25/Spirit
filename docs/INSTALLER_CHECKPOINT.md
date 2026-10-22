# Installer checkpoint — verified manual-only decision

Current cumulative0.9.0-dev.9. Application4736764ef681103c7d1b02b3851861a43c9201e5 passed full38567553630 and focused38567553677. The user explicitly removed automatic config/database/user creation because a cPanel API token is unavailable. Retain A2/cPanel manual setup, no setup code and the single-page workflow. Report cURL and ZIP separately as optional for installation. Earlier dev8 automatic provisioning is historical, not a feature to restore.

See SESSION_HANDOFF.md, CPANEL_INSTALLER.md and TEST_RESULTS.md for exact evidence, inert old-path upgrade compatibility, preserved private state and remaining real-host acceptance. Ten actual installer/health renders were reviewed; no A2 account or real provider transaction was used. No accepted v1.0 yet.
