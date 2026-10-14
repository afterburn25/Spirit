# Installer checkpoint — manual-only decision supersedes automatic creation

Current target0.9.0-dev.9, based on verified main1cffdcca. The user explicitly removed automatic database/user creation because a cPanel API token is unavailable. Retain A2/cPanel manual setup, no setup code and the single-page workflow. See SESSION_HANDOFF.md, CPANEL_INSTALLER.md and TEST_RESULTS.md for current implementation, evidence and remaining real-host acceptance. Earlier dev8 automatic provisioning is historical, not a feature to restore.
