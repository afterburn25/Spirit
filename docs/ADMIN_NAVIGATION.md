# Administration navigation — 1.0.0-rc.2

## Categories

| Category | Destinations |
|---|---|
| Website content | Pages & homepage; Band members; Media library; Photo gallery; Videos; Press downloads |
| Music & shows | Music releases; Audio tracks; Shows & ticket links |
| Bookings & communication | Booking inquiries; Subscribers; Delivery queue; Email settings |
| Store & orders | Products & inventory; Product categories; Customers; Orders & fulfillment; Store settings; Shipping, tax & terms; Payment settings; Payment notifications |
| Settings & access | Website settings; Search & sharing; Staff access; Account & security |
| System & maintenance | Launch checks; Installation health; Backups & safe restore; Browser upgrades; Database updates; Temporary data & retention; Activity history; Release roadmap |

Overview remains a direct link. All previous28 destinations are retained; six existing store/order endpoints gain direct shortcuts. Empty or unauthorized sections are removed on the server before rendering. Financial configuration, customers and access management remain owner-only, even when browser preferences are edited. Menus are not an authorization boundary; endpoint checks remain mandatory.

## Remembered state and same-page refresh

Each native details category starts expanded for a first visit. The user can close any category, including the current one. Current page and category stay visually identified. Expand all and Collapse all do not navigate or submit a form.

A versioned localStorage entry stores only known category booleans. An opaque HMAC-derived scope separates installations, accounts and roles; it is not an authentication capability. A tab-specific sessionStorage entry contains a bounded sidebar scroll offset and mobile-drawer boolean. No URL, page filter, form data, email or credential is stored. Ordinary browser navigation preserves the actual current path, query and hash on reload; JavaScript never assigns location or changes history to restore a previous page.

The drawer stays open/closed on same-page refresh. Clicking a page on a phone closes the drawer to show that destination while retaining categories. A new tab has its own drawer/scroll and selected page; same-account group preferences apply. Back/Forward cache restoration reapplies saved menu state without taking over page-scroll restoration.

Malformed, oversized and unknown preference fields are ignored. Storage exceptions do not disable navigation and show a persistence notice. No cross-device/server synchronization is claimed. No-JavaScript navigation remains visible and operable on phones but cannot use browser-script persistence. Unsaved form values and POST replays are not handled by menu memory.

## Accessibility and design

Native details/summary and link lists follow ordinary disclosure navigation, not desktop-app menu roles. Summary Enter/Space and standard Tab behavior remain native. Approved navy/gold styling includes fixed decorative SVG icons, hover/focus, reduced-motion and forced-colors rules. Phone drawer closed content is inert, open background content is inert, Tab remains within open navigation, and Escape/close return focus to Menu. Public design and installer behavior are unchanged.

Guidance reviewed: WAI disclosure navigation https://www.w3.org/WAI/ARIA/apg/patterns/disclosure/examples/disclosure-navigation/ and MDN localStorage/toggle https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/toggle_event .

## Verification

98 pure model/permission/namespace assertions and new real root/subfolder Chromium/Firefox/WebKit journeys cover reload, exact URL preservation, categories, scroll/drawer state, current store subsection, account/role isolation, corrupt/unavailable storage, native links and database fingerprints. Exact new run results are pending in TEST_RESULTS.md. Offline renders are not database-backed acceptance. Actual A2/provider/content approval gates remain separate.
