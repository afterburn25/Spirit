# Cumulative release roadmap

Current development work **0.7.0-dev.1**, verification pending. Verified baseline .6 main 6b00769fee969570872c88446110429047dc0499 passed run 38479822564. See TEST_RESULTS.md. Every package is cumulative; retain the selected design and existing data.

| Target | Scope | Status |
|---|---|---|
| 0.1 | Shared-host installer, private runtime, owner auth, root/subfolder, lock/removal | Implemented and prior regression-tested; actual host acceptance remains. |
| 0.2 | Approved responsive public design | Real components; final high-resolution artwork and visual acceptance remain. |
| 0.3 | CMS, private drafts/previews, band/media/press | Implemented; staff delegation and advanced revision controls remain. |
| 0.4 | Music, streaming links and persistent local audio | Implemented; actual band recordings need approval. |
| 0.5 | Shows, details, archives, external venue tickets | Implemented. No native ticket issuing or scanning. |
| 0.6 | Booking, transactional email, newsletter opt-in | Implemented and .6 tests passed; provider inbox acceptance remains. |
| 0.7 | Merchandise/variants/inventory/bag/customer accounts | Implemented in branch; new SQL/browser verification pending. US address book initially. Checkout closed. |
| 0.8 | Authorize.net sandbox, guest checkout, orders and fulfillment | Next. No payment or order claim. |
| 0.9 | Staff roles, backup/restore/browser upgrades, audits/SEO/quality | Additive schema foundation exists; full tools and acceptance remain. |
| 1.0.0-rc.N | Complete staging/content/provider/browser/host acceptance | Not reached. |
| 1.0.0 | Complete tested and explicitly accepted release | Not released. |

Do not silently introduce redesigns, native event ticketing, marketing campaigns or production deployment. Preserve configuration/encryption secret, uploaded media and all existing data through supported upgrades. Never overwrite a delivered version's package.
