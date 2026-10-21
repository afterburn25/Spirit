#!/usr/bin/env python3
"""Refresh public offline analytics data on a development machine, never a page view.

Country example: python3 app/tools/refresh_analytics_data.py --country-month 2026-10
Crawler example: python3 app/tools/refresh_analytics_data.py --crawlers
The checked-in output is packaged as-is; hosts need no network, Python, or Composer.
"""
from __future__ import annotations

import argparse
import datetime as dt
import gzip
import hashlib
import io
import ipaddress
import json
import re
import tempfile
import urllib.parse
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / "app" / "GeoData"
COUNTRY_SOURCE = "https://db-ip.com/db/download/ip-to-country-lite"
CRAWLER_SOURCES = {
    "google": "https://developers.google.com/static/crawling/ipranges/common-crawlers.json",
    "bing": "https://www.bing.com/toolbox/bingbot.json",
}


def download(url: str, limit: int) -> bytes:
    request = urllib.request.Request(url, headers={"User-Agent": "Spirit-offline-data-refresh/1.0"})
    with urllib.request.urlopen(request, timeout=30) as response:
        final = urllib.parse.urlsplit(response.geturl())
        requested = urllib.parse.urlsplit(url)
        if final.scheme != "https" or final.hostname != requested.hostname:
            raise ValueError("Unexpected data download redirect.")
        body = response.read(limit + 1)
    if not body or len(body) > limit:
        raise ValueError("Data download is empty or exceeds the fixed size limit.")
    return body


def write_json(path: Path, value: dict) -> None:
    path.write_text(json.dumps(value, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


def refresh_country(month: str, stage: Path, retrieved: str) -> list[str]:
    if not re.fullmatch(r"20\d{2}-(0[1-9]|1[0-2])", month):
        raise ValueError("Country month must be YYYY-MM.")
    if month > dt.datetime.now(dt.timezone.utc).strftime("%Y-%m"):
        raise ValueError("A future country release cannot be downloaded.")
    url = f"https://download.db-ip.com/free/dbip-country-lite-{month}.mmdb.gz"
    compressed = download(url, 20 * 1024 * 1024)
    with gzip.GzipFile(fileobj=io.BytesIO(compressed)) as stream:
        body = stream.read(32 * 1024 * 1024 + 1)
    if len(body) < 512 or len(body) > 32 * 1024 * 1024:
        raise ValueError("Country database has an invalid size.")
    if b"\xab\xcd\xefMaxMind.com" not in body[-131072:]:
        raise ValueError("Downloaded country database lacks MMDB metadata.")
    (stage / "country.mmdb").write_bytes(body)
    write_json(stage / "country.json", {
        "format": 1,
        "source": "DB-IP Lite Country",
        "data_version": month,
        "source_url": COUNTRY_SOURCE,
        "download_url": url,
        "retrieved_at": retrieved,
        "license": "CC-BY-4.0",
        "license_url": "https://creativecommons.org/licenses/by/4.0/",
        "attribution_label": "IP Geolocation by DB-IP",
        "attribution_url": "https://db-ip.com",
        "size_bytes": len(body),
        "sha256": hashlib.sha256(body).hexdigest(),
        "compressed_sha256": hashlib.sha256(compressed).hexdigest(),
        "modifications": "Gzip container removed; MMDB database bytes are unmodified.",
        "precision": "country",
        "limitations": "Approximate IP network location, reduced Lite coverage/accuracy; not a person's physical location.",
    })
    return ["country.mmdb", "country.json"]


def refresh_crawlers(stage: Path, retrieved: str) -> list[str]:
    providers = {}
    for family, url in CRAWLER_SOURCES.items():
        body = download(url, 1024 * 1024)
        source = json.loads(body)
        rows = source.get("prefixes")
        published = source.get("creationTime")
        if not isinstance(rows, list) or not 1 <= len(rows) <= 4096 or not isinstance(published, str):
            raise ValueError(f"Invalid {family} crawler range document.")
        dt.datetime.fromisoformat(published.removesuffix("Z"))
        networks = set()
        for row in rows:
            if not isinstance(row, dict):
                raise ValueError("Crawler prefix must be an object.")
            cidr = row.get("ipv4Prefix") or row.get("ipv6Prefix")
            network = ipaddress.ip_network(cidr, strict=True)
            if network.prefixlen == 0:
                raise ValueError("A crawler range cannot match every address.")
            networks.add(str(network))
        providers[family] = {
            "source_url": url,
            "published_at": published,
            "retrieved_at": retrieved,
            "source_sha256": hashlib.sha256(body).hexdigest(),
            "prefixes": sorted(networks, key=lambda value: (ipaddress.ip_network(value).version, int(ipaddress.ip_network(value).network_address), ipaddress.ip_network(value).prefixlen)),
        }
    write_json(stage / "crawlers.json", {"format": 1, "providers": providers})
    return ["crawlers.json"]


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--country-month")
    parser.add_argument("--crawlers", action="store_true")
    args = parser.parse_args()
    if not args.country_month and not args.crawlers:
        parser.error("Choose --country-month YYYY-MM and/or --crawlers.")
    retrieved = dt.datetime.now(dt.timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z")
    OUTPUT.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix="spirit-offline-data-") as directory:
        stage = Path(directory)
        names = []
        if args.country_month:
            names.extend(refresh_country(args.country_month, stage, retrieved))
        if args.crawlers:
            names.extend(refresh_crawlers(stage, retrieved))
        # Nothing is written until every requested download and structural check succeeds.
        for name in names:
            target = OUTPUT / name
            temporary = OUTPUT / (name + ".new")
            temporary.write_bytes((stage / name).read_bytes())
            temporary.replace(target)
        print(json.dumps({"files": names, "retrieved_at": retrieved, "bytes": sum((OUTPUT / name).stat().st_size for name in names)}))


if __name__ == "__main__":
    main()
