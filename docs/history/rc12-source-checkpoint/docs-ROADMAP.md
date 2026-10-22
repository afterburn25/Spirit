# Roadmap — 1.1.0-rc.12

| Area | Implemented status | Remaining acceptance |
|---|---|---|
| Functional folder grouping | Ten deployment roots, images retained at root | One-time A2 same-root transition for older installations |
| Short-page footer | rc.12 fills viewport and keeps footer content-sized in normal flow | Owner and physical-device review |
| Audio-player clearance | Measured player height restored; installed 320px keyboard regression passed | Current CI and physical-device review |
| Music release filters | Recovered rc.11 wrapping retained | Current full storefront CI |
| URLs, data and recovery | Exact route allowlist, immutable migrations, grouped-v2 boundary retained | Actual A2 same-layout update acceptance |
| Approved design | Animated 3D logo, fixed stage/smoke, transparent header/footer and compact credit retained | Owner visual review |
| Analytics | Dedicated graphs, Visitor Explorer and Overview snapshots retained | Preserve owner access and measurement definitions |
| CMS, media, music, shows and booking | Existing implementation retained | Full current CI and enabled-provider acceptance |
| Commerce | Optional, activation-gated; no native ticket sales | Catalog, policies and merchant acceptance before activation |

## Publication
The cumulative candidate belongs to work/1.1.0-analytics-and-navigation, draft
PR 37. Main and production remain unchanged. Read TEST_RESULTS.md for observed
local results; verify the exact published head's Actions before claiming CI.

## Transition and evidence
Read LAYOUT_TRANSITION.md and TEST_RESULTS.md. No reinstall, database replacement,
private-runtime relocation or destructive folder deletion. Old rc.8 updaters
must reject the changed foundation; grouped rc.10/rc.11 installations may use
guarded same-layout updates. The unavailable earlier rc.9 package requires
separate comparison.
