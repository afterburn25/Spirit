# Resume the single-page A2/cPanel installer

Repository **afterburn25/Spirit**, authoritative **main**. Work branch `work/single-page-installer`, PR11, target **0.9.0-dev.8**. Read live refs, AGENTS.md, current status, CPANEL_INSTALLER.md and TEST_RESULTS.md before changing code. Preserve the approved design, original config/secret/media/business data, migrations001–006 and pinned recovery/access files.

## Fixed latest decisions

The user explicitly removed the package setup code and requires checks, database/owner settings, correction and completion on one page. Hosting is **A2 with cPanel only, no20i**. Optional automatic database/user/random-password creation is integrated; existing-database mode remains. Do not reintroduce package activation or derive hosting from another user project.

## Implementation

CpanelClient allows fixed Mysql operations over verified HTTPS2083, validates/pins public IPv4 DNS, disables proxies/redirects and bounds payload/time. Token is request-only. CpanelProvisioner runs under the installer operation lock, reads restrictions/inventory, saves private0600 generated credentials/progress before mutation, and grants only necessary database-scoped privileges. No root/WHM/reset/delete/global-grant functions. Owner validation/consent precede provider writes. Confirmed steps survive retry; unknown mutations pause rather than re-create/adopt resources. Read the documented uncertainty and local-MySQL limits before modifying it.

Single-page result reports actual folder deletion versus retained locked files. No setup receipt/hash is packaged. Existing installations must update, not reinstall. Backup/update keys remain protected.

## Current testing gate

56 cPanel units, syntax checks and12 packaging tests passed locally. Full new CI pending. Added actual loopback cURL TLS/POST tests; guarded CLI-only cPanel simulation with real newly created dedicated SQL databases/users; fresh/rejected/unknown/migration scenarios at root/subfolder. Only the isolated simulator uses a disposable SQL administrator. CI blocks external cPanel networking. No user A2 account, real provider credentials, inbox or merchant transaction has been used.

The initial baseline dev8 run38553237171 passed syntax/packaging and actual Apache HTTPS but failed a missing-directory404 expectation after successful installer deletion. PHP's development server falls back to its parent index. Tests now check the missing PHP file is404, no setup form returns, and replay cannot duplicate an owner; do not weaken production access rules. Run all cumulative SQL/browser/Apache suites, including one-page1440/390/320px and no-JS, inspect form/automatic-choice/completion screenshots, and record exact successful results before promoting main.

## Next steps

Resolve any exact CI failures without suppressing assertions. Complete documentation consistency (INSTALLATION, SECURITY, TEST_RESULTS, LAUNCH_CHECKLIST, changelog/roadmap) and remove stale instructions that present historical setup keys as current requirements. Preserve historical evidence as historical. Promote only reviewed verified code, compare canonical tracked source, then generate an immutable cumulative Full ZIP with no PRIVATE-SETUP. Actual A2 token/port/naming/quota/ACL acceptance remains separate; never claim simulation is that acceptance.

After dev8, continue remaining populated-site quality, production artwork/content, host/mail/merchant staging and owner acceptance from LAUNCH_CHECKLIST. No accepted1.0 yet. Local container cannot resolve network and lacks pdo_mysql/GD/cURL/ZIP; real integration runs in Actions. Source artifacts are available for canonical-byte comparison; do not invent sandbox links.
