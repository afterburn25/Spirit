# Resume here — RC2 grouped administration navigation

Repository afterburn25/Spirit; main authoritative. Working branch work/release-candidate / PR15, based on97fe1bfb765c9d6e037785961f572e6c73eef46f. User asked for polished collapsible relevant categories, retaining menu state and current page on refresh. Inspect current refs before writes; never force-push or lose concurrent changes.

RC1 application e881a9a7 passed full38646174199, storefront38646174182 and focused38646174211. Source-only commit97fe1bfb enables canonical artifacts on this branch; it is not application verification. Current RC2 navigation code is pending exact new CI.

AdminNavigation builds six server-permission-filtered categories and34 owner destinations, with precise active keys for the six added store/order shortcuts. It is display structure, not new authorization. Native details and ordinary links work without page JavaScript. New admin-navigation.css/js are admin-only. Original app.js and installer handlers remain unchanged.

Browser-local state stores only known category booleans under an installation/account/role-scoped opaque identifier. Tab storage stores bounded sidebar scroll and drawer state. No remembered URL or forced redirect: native refresh keeps path/query/hash. Preserve explicit closed current categories and highlight their parent. Phone link selection closes the drawer but preserves groups. Storage exceptions show a truthful notice rather than breaking navigation. No cross-device or unsaved-form persistence is promised.

Local219 syntax/689 pure/12 packaging checks passed. New navigation browser tests exercise root/subfolder Chromium/Firefox/WebKit at1440/820/390/320 plus native no-JavaScript and unavailable-storage paths. Exact full/focused/storefront runs and rendered evidence remain to be verified. Preserve every prior regression, CSP, permission check and database/recovery limit. A fixed SCRIPT_NAME/SCRIPT_FILENAME in public_access_unit makes its existing URL assertions independent of CLI working-directory length; production path code was not changed.

Next: commit/run the complete tree, resolve actual failures, inspect actual navigation screenshots, record exact jobs and source integrity, update remaining documentation and deliver immutable RC2 Full ZIP. Promote only after required verification. Do not claim final1.0, genuine host/mailbox/merchant acceptance or production artwork approval.

Fixed scope: A2/cPanel manual one-page installer; no20i, setup code/PRIVATE-SETUP, token or automatic database/user creation. Keep all previous paths, six migrations, five pinned access/recovery files, original private config/encryption secret/database/media and separate recovery keys. Existing sites upgrade rather than reinstall. See ADMIN_NAVIGATION.md and RELEASE_CANDIDATE.md.
