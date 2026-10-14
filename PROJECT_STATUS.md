# Project status — 0.9.0-dev.12 (verification pending)

Branch work/storefront-quality starts from actual verified main21e9bd6f8d022a643ac091bd3975e8b1ea0cd0e6. Full exact-main dev11 run38591939605 passed. Do not label the new source verified until its own cumulative and three-browser workflows pass.

## Implemented this pass

Fixed stale product srcset: a new photo replaces the complete decoded image (responsive sources, intrinsic dimensions, alt), not just fallback src. The current photograph stays in place on failure. Per-gallery requests discard stale completions. Selection retains keyboard focus, quantity and audio; it replaces rather than appends photo-only browser history. Canonical product metadata is unchanged. Real links select approved product-array positions without JavaScript. Thumbnail sizing now reflects the72px display slot.

31 unit assertions cover selector validation and sizing. New real SQL/HTTP/browser fixtures create a published product with three distinct synthetic images and check Chromium, Firefox and WebKit at1440/390/320, no-JavaScript fallback, failure and out-of-order requests, form preservation, real audio navigation and withdrawal of unpublished media. These runs are pending, not completed evidence. All prior tests remain mandatory.

## Preserved boundaries

Manual one-page A2/cPanel installer, no setup code, no token or automatic DB creation; safe private writes/deletion/locks. No immutable schema or pinned access/recovery changes. All cumulative site/admin/shop/maintenance features remain. Approved art/content, actual A2/inbox/merchant, populated-site quality and owner acceptance remain before1.0. No production hosting or service settings changed.
