# Public navigation and native controls — RC7

The existing phone navigation remains a disclosure of ordinary links, not a modal dialog or an ARIA application menu. Enter/Space opens it normally. Tab and Shift+Tab move through or away from links without a trap; leaving it closes it. Escape closes and returns to the menu button. Arrow Down/Up on that button optionally opens and focuses the first/last link. Clicking outside dismisses without stealing focus from other interactive content.

Moving between desktop and phone layouts clears stale open state. A focused control that becomes hidden transfers to its visible equivalent; focus already in page content stays there. The menu's maximum height follows the actual viewport and player top. Long menus scroll on short screens, keeping the last booking link reachable above playback. The audio node, queue and playback code are not replaced. Without JavaScript, the existing expanded native mobile navigation remains unchanged.

Shared administration checkboxes no longer inherit the46px text-input minimum. Native controls retain their platform checked/unchecked, keyboard, focus and forced-color behavior. Labels remain clickable and readable. Specialized logo controls keep their existing16px sizing. This is CSS only: nothing is pre-checked, consent is not inferred, no publication/stock/payment action is submitted and administration menu preferences are untouched.

## Verification and scope

Local230 PHP/inc syntax,812 existing pure assertions,eight JS checks and12 packaging tests passed. A real Chromium isolated markup exercise passed; exact packaged-site three-engine verification and visual review are pending. The added workflow uses disposable fixtures only and a payment-network kill switch, not the live website or a real merchant.

These are targeted usability corrections. They are not full WCAG certification, assistive-technology or physical-device approval, field performance measurement, production artwork or genuine host/provider acceptance. Keep the approved design, external venue ticket links and manual one-page A2 installer. Existing sites upgrade with backups rather than reinstall. Final1.0 remains subject to genuine content/artwork and external/owner acceptance.

Primary references consulted:
https://www.w3.org/WAI/ARIA/apg/patterns/disclosure/examples/disclosure-navigation/
https://developer.mozilla.org/en-US/docs/Web/API/FocusEvent/relatedTarget
