# Launch checks and upload feedback — 0.9.0-dev.11

Open **Admin > Launch checks** (`admin/launch/`). A link is also available on the owner overview. Only an active owner may view the diagnostics or export a report. The former preview URL, `admin/readiness/`, invokes the same protected handler. There is one dashboard, not another settings system.

## Reading the results

43 observations/review tasks cover current PHP capabilities/limits, installation/private configuration, service settings, published-only content and merchandise, unresolved email/payment work and six external acceptance tasks. **Observed** is a local fact, not certification of an entire workflow. **Needs attention** identifies a known missing prerequisite or unresolved work. **Review required** deliberately remains for actual owner/provider acceptance. **Not checked** means unavailable data, not an invented zero count or successful check.

Production selected and enabled always surfaces a live-charge warning, even if another checkout prerequisite is absent. Viewing or refreshing does not send email, contact a gateway, change settings, process jobs, create databases or verify a backup. Empty queues and published record counts do not prove successful delivery, rights, accuracy or completion. The page does not calculate a project-completion percentage.

## Support report

Choose **Download support report**. The export uses fixed `spirit-support-v1` fields and the filename `Spirit-support-report.json`. It contains application/PHP versions, extension booleans, numeric limits and allowed check states. It omits raw configurations, hostnames, paths, database names, personal details, message bodies, logs and credentials. Download requires owner authorization and a valid POST/CSRF token. The report is private/no-store/no-referrer and is not written into a publicly accessible website file. Review it before sharing.

The earlier local-preview export format is superseded; it was not a promised external API. Inert old helper/template paths are retained for cumulative-updater compatibility only. They must not regain the preview implementation.

## Upload-size feedback

Media upload, backup import and browser ZIP staging display the domain's actual `upload_max_filesize` and `post_max_size`, including whole-form overhead. Application limits, decoded-image memory and upstream host limits still apply. No size setting is changed automatically.

When PHP receives a POST larger than its whole-request limit, it can discard both the fields and files. These authenticated upload pages detect the limit breach from the request length and return a readable HTTP413 response without processing the requested action. They explain the cause, show a fresh form and direct the user to the relevant host limits. Normal-size requests still require CSRF; this rejection path is not permission to process a form without a token. A host/proxy rejection before PHP executes remains the provider's own response.

PHP ZIP is still required for browser ZIP upgrades. Missing ZIP does not prevent manual installation or passive diagnostics, but this page does not install extensions or bypass A2 controls. The one-page manual installer, no setup code and no cPanel-token decision remain unchanged.

## Verification and acceptance

The complete regression run for application d699fd6e passed both SQL environments, thirteen browser workflows and Apache HTTPS; exact evidence is in TEST_RESULTS.md. Actual diagnostics/alias/export access, redaction, read-only database fingerprints, schema/query failures, no-optional-extension environments and oversized upload protection are exercised. Browser checks cover1440/390/320px and include real keyboard report download, fresh upload feedback, focus and support-card spacing.

Actual hosting, receiving mailbox, real merchant sandbox, recovery drill, master artwork/content and populated-site owner approval remain review-required. Automated tests do not grant final launch acceptance.

Official PHP references: https://www.php.net/manual/en/ini.core.php#ini.post-max-size and https://www.php.net/manual/en/function.ini-parse-quantity.php (consulted2026-10-14).
