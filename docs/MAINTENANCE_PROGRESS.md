# Maintenance verification checkpoint

Main remains verified cumulative0.9.0-dev.2, a621b4b270c33b53dc1a496956a7e02806ef3200, exact-main run38503430004 successful. New .9dev4 code remains separate until its exact full tests pass.

The focused diagnostic run38508494941 identified the exact failure after all ten invalid-package rejection cases and the primary staging/apply/rollback journey: the lightweight PHP test server does not interpret the shipped .htaccess rules, so a403 expectation for RecoveryEngine.php ran on the wrong server. The rules are already present in the deployed package; this is not an absent access rule or a failed archive validator.

Those access assertions now run in app/tests/apache_access.py against real Apache with mod_php, rewrite/authz and verified HTTPS at both the root and a subfolder. The test checks eleven protected paths per installation, executable PHP, accessible public CSS/security headers, and a private uncached independent recovery form.32 assertions passed locally as a non-root user, with successful process cleanup. A third full-workflow job runs this exact Apache test; existing database and all nine Chromium suites remain unchanged except relocating the server-specific access assertions to their correct environment. This expands coverage instead of silently skipping or mocking the check.

Tests use a disposable local certificate trusted only by the test client, never disable TLS validation, and bind Apache to loopback/high ports. Installing test-only Apache does not add a deployment dependency beyond the project's already documented Apache/LiteSpeed target. An actual hosting provider's configuration still requires staging verification.

The optional direct-PHP include guards prepared locally were not applied after a tool safety-status block. No blocked write is retried through another action and no intermediate tree is promoted. This change leaves the existing recovery kernel and access rules unchanged and tests their actual supported server behavior. Continue from the exact new diagnostic/full results; fix remaining real regressions before main promotion. No final1.0, provider transaction or live mailbox acceptance is claimed.

Apache directive reference: https://httpd.apache.org/docs/current/howto/htaccess.html .
