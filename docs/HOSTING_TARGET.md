# Confirmed hosting target

User confirmation, 2026-10-14: **A2 Hosting with cPanel. No 20i integration for this project.** Do not infer provider settings from the user's separate hosting-reseller project.

The requested installer is a single browser page with no package setup code. It must offer cPanel API database/user provisioning with a generated strong database password, plus an existing-database alternative. cPanel account API authorization is separate from the removed package setup-code gate. The provider/account must permit the API operations; never bypass provider restrictions or claim a real account was verified without evidence.

Use HTTPS and a cPanel API token entered only into the installer, not Git or chat. Do not retain that token in permanent settings or logs. Restrict new user privileges to the newly created application database. Preserve unrelated resources, avoid password resets/deletions on retries, and report partial/uncertain outcomes. Do not use SSH/root, WHM reseller credentials, or 20i services.
