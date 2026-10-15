# Session handoff — RC4 verification continuation

Repository afterburn25/Spirit; main is authoritative. Read AGENTS.md and the fixed design/hosting decisions before writes. Inspect live refs and preserve concurrent work; never force push.

## Source and evidence

Baseline RC3 main: `cfa72c002c5cb3c292ea274176d1a5281bd968f8`, tree `c13699b9d7efde164fc39ef438aa080eb9fba9c6`.
RC4 branch: `work/rc4-history-verification`, PR17.
Tested head: `5373be7884b3b9e6d10dd9d051d0ffdf9dd4e021`, tree `5ffb3a8b2ff1b70c3894ad38c276a2c30e94dab1`.
Required completed successful workflows: full 38675061084; focused 38675061064; three-engine 38675061080. Finalization edits documentation only. Inspect any later exact-main verification before starting the next change.

RC3 exact-main full 38672702303 failed its content-history pagination test. Do not call the earlier parent retry a correction. Replace immediate page-wide counts with scoped, awaited exact data; no genuine assertion was dropped. The first RC4 commit b82f7939 was superseded: local Chromium preflight caught exact wrapped-label matching for selects, corrected in 5373be78. This was a new test locator correction, not a production defect. Current three-engine history logs show all 12 contexts and 443 reported passing checks each. See TEST_RESULTS.md and HISTORY_VERIFICATION.md, including the nonfatal older WebKit gallery runner diagnostic.

## Preservation

No runtime PHP/CSS/JavaScript, approved design, administration category/state, installer, migration, recovery kernel or provider change. VERSION is 1.0.0-rc.4; all old deployment paths retained. Deliver a cumulative new Full ZIP without overwriting RC3. Verify every manifest entry against exact canonical source, and record source, workflow IDs, archive SHA-256 and path preservation in the accompanying report. Tests/workflows are source-only, not deployed.

A2/cPanel manual one-page installer only. No setup code, token, 20i or automatic database provisioning. Keep original private config/encryption secret/data/media and recovery receipts. Existing installations upgrade, never reinstall or create a replacement DB. Same-schema updater and same-instance guarded restoration limits remain.

## Next work

This pass makes verification more reliable; it does not supply the final artwork/content. Continue with approved production master artwork and genuine band/store content when actually available, retaining the design lock and publishing controls. Do not invent identities, releases, products, dates, policies or acceptance evidence. Actual protected A2/extension/cleanup/recovery, receiving-mailbox and genuine merchant sandbox checks require the real authorized environment. Physical-device and owner approval remain. Final 1.0 must not be tagged while these acceptance items remain.
