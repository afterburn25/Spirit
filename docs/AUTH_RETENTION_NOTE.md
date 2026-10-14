# Authentication and bounded retention

Focused run38509384072 established that all updater/recovery assertions now pass after testing the deployed .htaccess rules with actual Apache. It next failed the cleanup unauthorized-operation assertion: an old unbounded DELETE in RequestLimit::consume pruned all expired counters even when the owner entered an incorrect password.

Authentication now only consumes or resets the requested counter. Current attempt limits, expiry resets, password verification, CSRF, session identity/version, role checks, row locks and account/IP limits are unchanged. The unrelated global DELETE was removed from request consumption and successful staff login. Expired counter storage is handled only by the already implemented bounded retention batch; configure the optional hosting-panel cleanup or perform reviewed owner batches rather than rely on sign-ins to prune the whole table.

This is a real separation-of-responsibility fix, not an altered passing expectation. The cleanup suite still requires incorrect passwords and preview-only requests to leave expired technical records unchanged. The separate thirteenth-sensitive-request regression still verifies that active limits are enforced. Full matrix results for this source remain pending.
