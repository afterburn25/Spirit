# Completed country-data transfer

This isolated preparation branch was used to transfer the attributed DB-IP Lite Country database into GitHub. The transfer completed and verified the original MMDB checksum. The cumulative application candidate is in [PR 37](https://github.com/afterburn25/Spirit/pull/37), on `work/1.1.0-analytics-and-navigation`.

The numbered files are XZ/Base64 transport chunks for the database, not application runtime code. The complete data is `app/GeoData/country.mmdb`. Preserve the [DB-IP attribution and license](../../app/GeoData/NOTICE.txt) and [source metadata](../../app/GeoData/country.json) when reusing it.

This preparation branch has no application review PR and is not the installation source.
