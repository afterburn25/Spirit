# Current rc.10 verification

Scope: recovered directory organization from verified rc.8, not the unavailable
rc.9 build. Historical test receipts under docs/history are not current results.

Observed locally: all PHP syntax checks, 26 pure PHP suites (including
analytics, branding, footer, recovery, permissions), 15 package unit tests and
Python compilation passed. YAML workflow files parsed. Native server/database
and GitHub results are recorded separately as they complete; no missing extension
is treated as enabled.

No new UI is designed by this change. Original artwork, public CSS/JavaScript
and all eight SQL migrations must remain byte-identical to rc.8. Root/subdirectory
routing and private access passed actual Apache HTTPS/PHP verification: 44
exact URL aliases at root/subdirectory, GET queries, POST bodies, slash
redirects, unknown/encoded rejection, and private-source denial. Business
handlers in the routing test are explicitly disposable JSON probes, not
a substitute for the separate SQL/application suites. Cross-layout rc.8-to-rc.10 installation remains a staged,
manual hosting-panel transition; no automatic deployment claim is made.

A dedicated GitHub workflow now rehearses rejection by the exact delivered rc.8
updater and the one-time same-root file transition with real disposable SQL.
Its result is pending until observed. No local SQL tests were claimed: this
container has no PDO MySQL extension or database server.
