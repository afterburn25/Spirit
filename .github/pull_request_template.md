# Summary
Explain the visible feature, intended audience and affected administrator/public pages.

## Verification
- [ ] PHP syntax, unit and focused feature checks pass
- [ ] Browser screenshots reviewed on desktop and mobile; keyboard navigation checked
- [ ] Existing site data, uploaded media, users and private configuration preserved
- [ ] Safe same-root upgrade and recovery path verified when installation files change
- [ ] New routes enforce authentication, authorization and CSRF as appropriate
- [ ] Public text, links and image uploads are validated and escaped
- [ ] No ZIP archive is nested inside another ZIP archive

## Documentation — required for every change
- [ ] Help Center instructions updated in `app/HelpCatalog.php` whenever an admin feature, field or workflow changes
- [ ] Help Center links point to real permission-filtered administration screens
- [ ] `php app/tests/help_catalog_check.php` and `php app/tests/page_controls_check.php` pass where applicable
- [ ] Existing workflow examples and troubleshooting guidance updated for renamed or removed features
- [ ] User-visible changes and limitations documented in the roadmap or release notes

## Release protection
- [ ] Version and acceptance evidence reconciled
- [ ] Relevant GitHub Actions finish successfully on the exact final branch head
- [ ] Draft PR stays unmerged until checks and owner review are complete
- [ ] No claim of production deployment without verified host evidence

## Reviewer notes
List screenshots, known limitations, migration steps and any outstanding operational acceptance here.
