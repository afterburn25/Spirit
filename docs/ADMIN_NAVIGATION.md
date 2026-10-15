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

Overview remains a direct link. All 28 prior destinations are retained; six existing store/order endpoints gain direct shortcuts, giving 34 owner destinations. Empty or unauthorized sections are removed on the server. Financial configuration, customers and access management remain owner-only even if someone edits browser preferences. Endpoints retain their own permission checks.

## Remembered state and same-page refresh

Categories start expanded on a first visit. Close or open each independently, including the current category. Its parent remains highlighted while closed. Expand all and Collapse all change only menu layout.

Browser localStorage saves known category booleans under an opaque identifier scoped to the installation, account and role. Tab-specific sessionStorage holds a bounded sidebar scroll offset and mobile-drawer state. No URL, filter, form data, email or credential is saved. Browser refresh retains the actual current page, query and hash; the menu never redirects to Overview or to a remembered destination.

The phone drawer retains its open/closed state on refresh. Selecting a destination closes it to expose the new page, without resetting categories. New tabs keep independent pages and drawer/scroll state while using the same account's saved group preferences. Back/Forward cache restoration reapplies preferences without taking over page-scroll restoration. A passive older tab cannot overwrite a newer choice merely by reloading or following a link. Open tab displays are not continuously synchronized, and a new explicit choice may update the saved layout.

Corrupt, oversized and unknown preference data is ignored. Browser storage restrictions show a truthful persistence notice and leave navigation working. Clearing browser site data resets saved layout. There is no cross-device/server synchronization or unsaved-form recovery. Native no-JavaScript links/disclosures remain usable but do not use script-based persistence.

## Accessibility and design

Native details/summary and link lists use ordinary disclosure navigation rather than desktop-application menu roles. Enter/Space and standard Tab behavior remain native. Navy/gold styling includes decorative icons, chevrons, section counts, current-item markers, hover/focus, reduced-motion and forced-colors rules.

On phones, closed navigation is inert; the background is inert while the drawer is open. Tab stays in the open navigation, Escape/close return focus to Menu, and the backdrop is pointer-only rather than an extra Tab stop. The sidebar scrolls independently. Public pages and installer behavior are unchanged.

## Verified evidence

Application2947013d passed full38654637873, focused38654637735 and dedicated three-engine38654637749. The navigation suite covers domain root/subfolder at1440/820/390/320 pixels, exact URL refresh, explicit closed current sections, sidebar scroll, phone drawer/focus, new-tab and passive-tab behavior, per-account/role isolation, malformed/unavailable storage, native links and unchanged database fingerprints. Each browser logs251 navigation assertions;98 pure model/permission/scope assertions are included in the cumulative689. See TEST_RESULTS.md for exact jobs, artifacts and limits.

Prior guidance references: WAI disclosure navigation https://www.w3.org/WAI/ARIA/apg/patterns/disclosure/examples/disclosure-navigation/ ; MDN localStorage https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage ; toggle event https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/toggle_event . Automated browser checks are not physical-device or complete accessibility certification.
