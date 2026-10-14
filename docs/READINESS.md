# Launch readiness — consolidated dev11 checkpoint

The separately delivered dev10 local preview and saved GitHub work/launch-diagnostics branch are consolidated here. Use **Admin > Launch checks** (`admin/launch/`). The former `admin/readiness/` URL invokes the same owner-only handler, including protected report POSTs. There is one dashboard, no duplicate set of settings or provider actions.

The page has 43 observations/review tasks covering current PHP capabilities/limits, installation/private configuration, service settings, published-only content and merchandise, unresolved email/payment work and six external acceptance tasks. Production selected and enabled always surfaces a live-charge warning, even if another checkout prerequisite is absent. No secret is decrypted and no external provider is called. Counts are not a completion percentage.

Support export uses the fixed `spirit-support-v1` format and `Spirit-support-report.json` name. It contains software versions, extension booleans, numeric limits and allowlisted check states, not raw configurations, paths, URLs, personal data, message bodies or credentials. Review before sharing. The previous preview export format is superseded, not a promised public API.

Media, backup import and ZIP staging show current PHP limits. A request discarded by PHP for exceeding post_max_size returns a usable 413 error page without processing an action or replacing CSRF checks on ordinary requests. The installer stays manual, one page, without setup code or cPanel token. Missing PHP ZIP still needs hosting configuration; the dashboard cannot install server modules.

External hosting, mailbox, real merchant sandbox, recovery drill, master artwork/content and populated-site owner acceptance stay review-required. Exact new verification is pending; see TEST_RESULTS.md.
