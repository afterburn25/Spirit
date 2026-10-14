# Staging and launch acceptance

This is the remaining acceptance checklist, not a declaration that the website is production-ready. Read PROJECT_STATUS.md and TEST_RESULTS.md for the exact verified application and known limits. Keep a dated record of the environment, tester, result and evidence for every acceptance item. Do not record passwords, payment keys, setup codes, recovery keys or customer details in Git.

## 1. Approved band content and artwork

- Confirm the logo and production-resolution artwork match the locked design. Reference crops must not be described as finished high-resolution masters.
- Approve the real band story, member names, musical roles and photographs. Do not publish generated people as actual members or use the concept's example identities.
- Supply and approve releases, track titles, recordings, credits, video links, photographs and press downloads. Confirm usage permission before publishing media.
- Verify every published performance's full date, local timezone, venue, admission information and external ticket URL. Do not invent shows to fill empty sections.
- Approve actual merchandise photographs, descriptions, sizes/colors, prices, stock and fulfillment arrangements. Remove synthetic test records; deployment packages do not seed them.
- Approve the real booking contact, sender addresses, public contact information, store terms, shipping/returns information and appropriate privacy text. Do not present unfinished policy text as the band's approved policy.

## 2. Actual shared-host staging

Use a separate staging domain/subfolder and database first. Do not overwrite the existing live website for these tests.

- Verify the actual account-private runtime lies outside every public domain mapping. Check PHP extensions, HTTPS, ownership, access rules, upload limits and open_basedir restrictions.
- Perform a browser installation and confirm creation of the owner account, protected configuration, database tables and installation lock. Verify actual installer-folder removal; a retained locked folder is not a deleted folder.
- Test owner, editor, store-manager and customer sign-in. Verify staff cannot inherit owner payment/backup/system privileges and customers cannot see another customer's information.
- Upload approved images, recordings and PDF documents; verify publication, private previews, unpublishing and direct-file authorization. Test responsive copies and original downloads without altering source media.
- Test actual desktop and phone navigation, search, old published links, forms, galleries, player controls and playback continuity. Include keyboard, zoom, reduced-motion and focus checks.
- Test an encrypted backup, retain the independent recovery key privately, verify import, and perform a supported guarded restore on staging. Exercise a supported same-schema code upgrade and rollback/recovery. These checks do not establish cross-host disaster recovery or unattended cross-schema rollback.

## 3. Mail delivery and scheduled work

- Configure the actual supported SMTP account and sender identity through administration. Verify the test message in both the provider log and receiving mailbox.
- Submit a real staging booking inquiry and confirm its database record, owner notification and organizer acknowledgement. It must not reserve a date or subscribe the organizer to marketing.
- Complete newsletter confirmation/unsubscribe and customer/staff activation/password recovery through the actual mailbox. Verify expired and already-used links fail safely.
- Verify payment receipts and fulfillment messages after the sandbox order tests below. SMTP acceptance alone is not proof of inbox delivery.
- Configure the hosting panel's mail/payment scheduled commands as documented and verify actual executions on a quiet site. Cleanup scheduling remains an explicit owner choice, not an automatically created task.

## 4. Genuine merchant sandbox acceptance

Keep production payment enablement off until the owner approves live operation. Automated fixture tests do not satisfy this section.

- Verify matching sandbox credentials and actual Authorize.net Accept.js loading/tokenization under the real site's HTTPS and Content Security Policy.
- Exercise approved, declined and held sandbox transactions, including duplicate submission, interrupted responses and reporting-based recovery. Verify totals, address handling, stock holds and their release/consumption.
- Receive genuine provider-signed notifications at the configured environment-specific URL. Verify raw-body signature and merchant/reference/amount/type correlation against actual authenticated reporting.
- Verify settlement timing and the supported full void/refund workflow, order privacy, account history, inventory adjustments and receipt delivery. Review explicit limits such as partial refunds, partial shipments and discount-code support in PAYMENTS.md before accepting operating scope.
- Independently verify the merchant's real shipping/tax/terms setup, supported destinations, security/compliance obligations and fulfillment procedure. Configured rates are not an automatic jurisdiction-aware tax service.
- Do not make a live charge as a test without separate explicit authorization.

## 5. Release candidate and owner acceptance

Resolve every blocking defect, preserve a verified full backup and recovery materials, record the final code/package hashes, and re-run the complete current regression matrix. Review real populated pages against the approved design, not only synthetic fixture screens. Measure performance with the approved assets and actual hosting; fixture transfer budgets are not field Core Web Vitals.

Only then prepare the release candidate, record the owner's acceptance and plan the live cutover. Preserve and map any actual legacy-site URLs deliberately. Do not infer v1.0 acceptance from a green test run, a version number, the existence of a checkout button or the absence of reported bugs.
