# Spiritual Gorillaz — current development work

Authoritative repository **afterburn25/Spirit**, branch **main**. Read AGENTS.md, PROJECT_STATUS.md, SESSION_HANDOFF.md and the release roadmap before changing code. Preserve the approved cinematic navy/gold/blue crowned-gorilla design and organized PHP/MySQL shared-host application.

**0.8.0-dev.1** adds guest/customer checkout, exact server totals, stock reservations, tokenized Authorize.net integration, private orders, authenticated notification/reporting workflows, fulfillment, and bounded full void/refund operations. New verification is pending on `work/payments-orders`. No real provider sandbox or production transaction has been performed. Checkout defaults closed and sandbox-first. Read **docs/PAYMENTS.md** for precise scope, limitations and staging acceptance requirements.

Existing migrations 001–004 remain unchanged; 005 is additive. Preserve private configuration, encryption secret, database and uploaded media. Browser installation still tests real writable private storage and permanently locks before restricted installer self-deletion. Never bypass hosting ownership with 0777 or destructive reinstall. Full packages are cumulative; the PRIVATE setup receipt stays off the server and out of Git.

The verified .7 baseline is application 6a9396f / main documentation descendant 9abe656b991f04bc34f77667d871be91440bd09c. New .8 code needs its own evidence. Local lint and 58 payment units pass; database/browser/provider boundaries are recorded in TEST_RESULTS.md. Staff delegation, complete backup/restore/browser upgrades, advanced catalog functions, quality checks, production artwork and actual owner/provider/host acceptance remain. This is not an accepted v1.0 release.

Deployment target adds PHP cURL to the existing PHP8.2+, PDO MySQL, OpenSSL, fileinfo and GD requirements. No host-side Node, Composer, Python, SSH or root requirement. Package on the development machine with `python3 tools/package.py --output ./dist`. Read INSTALLATION.md, OWNER_GUIDE.md, COMMUNICATION.md, COMMERCE.md and PAYMENTS.md before staging.
