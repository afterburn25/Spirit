# Maintenance verification checkpoint

Main remains the verified cumulative **0.9.0-dev.2**, a621b4b270c33b53dc1a496956a7e02806ef3200, exact-main run38503430004 successful. This file does not change that production-boundary status.

The newer cumulative updater/retention branch cba460c ran after its queue delay and **failed** in the upgrade SQL/HTTP suite, run38506175839. Subsequent browser tests were skipped because the integration gate failed. They must not be described as passed or merely still queued.

This diagnostic-only change adds a focused Ubuntu22.04 job that runs the unchanged real upgrade and cleanup assertions, preserving displayed synthetic error notices as a small artifact and annotation. It neither skips failures nor replaces the full existing PHP/MySQL/MariaDB/nine-browser verification workflow. No credentials, card data or submitted form values are captured by its notice recorder.

Direct PHP guards for the recovery includes and their database-independent HTTP test were prepared and tested locally, but the attempted repository write was blocked by a tool safety-status check. Those local changes have **not** been promoted or treated as saved application code. An uncommitted intermediate tree also contains a known PDO constant transcription error and must never be committed. This diagnostic checkpoint is based directly on cba460c, not that intermediate tree.

Continue from the exact returned diagnostic notice, fix the actual regression, rerun the complete matrix, inspect real browser screenshots, then update all continuity documents before main promotion. Do not claim1.0, a real payment-provider transaction, live mailbox or hosting acceptance. Same-schema code update and same-instance/schema guarded backup-restore limits remain explicit.
