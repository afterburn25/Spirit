# Cumulative roadmap

Current **0.9.0-dev.5** content-history implementation checkpoint; new tests pending. Exact .9dev4 main2a9497e passed38511337940. Preserve selected design, external tickets, installer/private state and immutable migrations.

| Target | Scope | Current state |
|---|---|---|
|0.1|Browser installer, private runtime, owner, cleanup|Implemented/tested; actual host acceptance pending.|
|0.2|Selected responsive public design|Real components; final master artwork/content review pending.|
|0.3|CMS, band/media/press|Baseline implemented; searchable pagination/private revision restore now added with current tests pending.|
|0.4|Music and persistent audio|Implemented and browser-tested.|
|0.5|Shows and venue ticket URLs|Implemented/tested; no native ticketing.|
|0.6|Booking, transactional mail, newsletter|Implemented/tested; real provider inbox acceptance pending.|
|0.7|Catalog, variants, stock, cart, customer accounts|Implemented/tested; US addresses initially.|
|0.8|Token-only checkout, orders, fulfillment|Code/SQL/browser boundary tests pass in baseline; genuine merchant sandbox acceptance pending.|
|0.9|Staff, backups, upgrades, retention, quality|Staff/encrypted backups/same-schema ZIP recovery/retention tested. Content history in current verification; final metadata/media/accessibility/performance pending.|
|1.0.0-rc.N|Complete staging and acceptance|Not reached.|
|1.0.0|Complete explicitly accepted release|Not released.|

Every package is cumulative and immutable per version. Setup and recovery keys remain separate and private. Browser upgrades do not automatically roll across schemas; backup restore is scoped to the same installation and protects operational histories. Host, provider, real content, master artwork and owner acceptance are required before final1.0.
