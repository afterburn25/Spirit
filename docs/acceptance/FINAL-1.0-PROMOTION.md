# Final 1.0.0 promotion checklist

Current verified candidate:
- Version: `1.0.0-rc.10`
- Commit: `2a833986f5a39d2f8de99f41bd8640b0642648e8`
- Verified branch: `release/1.0.0-rc.10-verified`
- Installer SHA-256: `621c49cb9a7b960d9743f536dc5170ea7bcf19a3ccbb4d53dd07bc469248902e`
- All eight required workflows: green

## Before final version bump

- [ ] RC10 observed on production.
- [ ] Search page closed-store behavior confirmed:
  - no Merchandise filter;
  - no merchandise/product wording while storefront disabled.
- [ ] Home, Music, Shows, Band, Media, Booking, Press and Customer Account remain correct after RC10.
- [ ] Merch and Your bag remain absent from normal navigation while storefront disabled.
- [ ] Launch Checks reviewed on production with no unexplained critical failure.
- [ ] Mail-worker heartbeat is recent.
- [ ] Required recovery/rollback acceptance is recorded.
- [ ] Real-host desktop/phone/keyboard/zoom/performance review is accepted.
- [ ] If commerce remains disabled, Authorize.Net sandbox acceptance remains deferred and is not a final-launch blocker.
- [ ] If commerce is enabled, genuine sandbox/provider acceptance is complete before final approval.
- [ ] Owner explicitly approves the final launch state.

## Final promotion

Only after every applicable item above is accepted:

1. Change `VERSION` from `1.0.0-rc.10` to `1.0.0`.
2. Update README / PROJECT_STATUS / SESSION_HANDOFF / CHANGELOG to final-release language.
3. Remove only the blockers that have actually been satisfied; do not fabricate provider or host evidence.
4. Run the complete release gate and all required workflow matrices on the final source head.
5. Build the deterministic final cumulative ZIP.
6. Record final source commit and installer SHA-256.
7. Create immutable final release/tag/branch as supported by the available GitHub tooling.
8. Preserve RC7/RC8/RC9/RC10 candidate history unchanged.

No feature development should be reopened during this promotion unless a genuine release-blocking defect is discovered.
