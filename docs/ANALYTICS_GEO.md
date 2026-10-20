# Offline analytics geography and crawler identification

## Country geography included

The package includes **DB-IP Lite Country, October 2026**, as a local MMDB file.
The analytics collector passes the connection IP into a local file lookup and
persists only the resulting country/region/city labels. `AnalyticsGeo` does not
store, log, send, or return the visitor's IP. It performs no API request or DNS
lookup. The normal page-view path needs no external account, API key, Composer,
host-side build, geolocation browser permission, or paid service.

The source is the [official DB-IP Lite Country download](https://db-ip.com/db/download/ip-to-country-lite).
DB-IP releases this reduced-coverage/accuracy dataset monthly under
[CC BY 4.0](https://creativecommons.org/licenses/by/4.0/). Preserve the linked
**[IP Geolocation by DB-IP](https://db-ip.com)** attribution in analytics views
and exported reports displaying or using the geography. `app/GeoData/NOTICE.txt`
and `country.json` retain the license, original URL, retrieval date and hashes.
Only the gzip container was removed; database bytes are unmodified.

Locations estimate an IP network's geography. They do not prove a person's
physical location. A country-only record never supplies a guessed state, city,
street address, or coordinate. Invalid, local, private, documentation, multicast
and reserved addresses yield unavailable geography. IPv4-mapped IPv6 addresses
are normalized consistently. No forwarded header is trusted by this module;
the collector must supply the actual trusted connection address.

## Optional region/city database

Country reporting works with the shipped package. Region/city reporting uses a
separate, legitimately licensed City MMDB only when the owner provides one.
The fixed destination is `analytics-geo/city.mmdb` beneath the installation's
existing private runtime; it is excluded from public files and release ZIPs.
Its directory must have mode `0700` and its file `0600`. It must be a regular
canonical file, not a symlink, URL or request-selected filesystem path.

`AnalyticsGeo::validateCityFile($path)` validates an owner-side local upload
before placement. Accepted files are at most **256 MiB**, use MMDB format 2,
contain a City database type and plausible bounded metadata, and successfully
decode probe lookups. Validation does not certify the accuracy or every record
of a third-party database. Every later record is separately decoded with bounded
resources and errors fall back to the valid country result. Unsupported, absent,
insecurely placed or malformed files yield explicit unavailable status. They do
not stop the analytics collector or website.

City records supply their own matching country; the implementation never joins
a city in one country to the country from a conflicting source. Missing city
or subdivision names remain empty. Coordinates, postal codes and registered-
country records are not persisted by this adapter. Preserve any additional
attribution required by the owner-provided database's license.

The bundled reader is [MaxMind DB Reader PHP v1.14.0](https://github.com/maxmind/MaxMind-DB-Reader-php/tree/v1.14.0),
pinned to `f3c92f68b3bec42a9aa780368399e03dc6b91e89`. Five runtime source files
and the complete Apache 2.0 license are under `app/ThirdParty/MaxMind`.
`SOURCE.json` records original and bundled SHA-256 hashes. The only changes are
namespace/import relocation to isolate this reader from older host extensions.
The pinned decoder limits nesting to 512 levels, decoded values to 65,536, and
produced string/byte payload to 2 MiB per decode. Lookup tree traversal is bounded
by address width. A 64-bit PHP runtime is required by this adapter; unsupported
platforms report unavailable geography rather than using unsafe integer math.

## Crawler identification

User-agent claims and range verification are separate:

- Google common crawler user agents can match the
  [official Google common-crawler ranges](https://developers.google.com/static/crawling/ipranges/common-crawlers.json).
  See Google's [verification documentation](https://developers.google.com/crawling/docs/crawlers-fetchers/verify-google-requests).
- Bingbot can match the [official Bingbot range list](https://www.bing.com/toolbox/bingbot.json).
- Yahoo Slurp and other recognized crawlers remain **reported by user agent**.
- A Google/Bing claim outside its range snapshot remains **reported**, not verified.
- An ordinary browser inside a provider network does not become a crawler without
  a matching crawler user agent. This classification is for reporting, not access control.

`app/GeoData/crawlers.json` preserves each official URL, publication date,
retrieval date and source hash. The bundled snapshots were retrieved on
**2026-10-20**. Google's source creation date is **2026-10-19**; Bing's current
published document still declares **2024-01-03**. These dates are recorded as
different facts in the bundled metadata and this documentation, never rewritten
to make an unchanged publisher list look new. The dashboard renders the stored
verification labels, not a separate snapshot-date panel.
The report's verified label means the claim matches that locally stored official
range snapshot; it does not mean a live DNS/provider verification was performed.
Snapshots older than 90 days after retrieval stop verifying new requests, leaving
user-agent reporting available. Previously stored observations retain their
historical verification result.

## Data refresh and package contract

Updates are explicit development/maintenance operations:

```sh
python3 tools/refresh_analytics_data.py --country-month 2026-10 --crawlers
```

The tool contacts only fixed HTTPS database/range endpoints, applies download and
decompression bounds, validates crawler CIDRs, and records source hashes. It sends
no visitor data. Review the resulting changes and tests before a new cumulative
release. `tools/package.py` packages the checked-in files without downloading data.
Do not silently replace an already-delivered version with refreshed contents.
`app/.htaccess` and `app/GeoData/.htaccess` deny web access to the datasets.

## Adapter API

`AnalyticsGeo::lookup(string $ip)` returns `country_code`, `country_name`,
`country` (ISO-code compatibility alias), `region`, `city`, `precision`, `source`,
`data_version`, and `status`. No result includes an IP. `status()` reports separate
country/city availability and required attribution. `reset()` clears request-local
readers after successful owner-side data replacement.

`AnalyticsCrawler::classify(string $ua, string $ip)` returns `null` for an ordinary
browser, or `family`, `verification`, `verified`, `source`, `data_version`, and
`snapshot_at`. `verification` is either `verified_range` or `reported_ua`.
`status()` exposes separate Google, Bing and Yahoo verification availability.

`tests/analytics_geo_unit.php` covers actual shipped-country lookup, optional
city behavior, malformed data fallback, address filtering, IPv4/IPv6 CIDR and
MMDB range boundaries, and spoofed crawler claims. Small official synthetic
fixtures remain under `tests/fixtures/analytics-geo`; they are not deployed.
