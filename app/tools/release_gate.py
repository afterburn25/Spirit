#!/usr/bin/env python3
"""Deterministic software release gate for Spiritual Gorillaz.

This validates source/package integrity only. It never claims provider, hosting,
content, or owner acceptance. Final 1.0.0 is refused while PROJECT_STATUS still
contains unresolved launch blockers.
"""
from __future__ import annotations

import hashlib
import json
import re
import sys
import tempfile
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "app/tools"))
import package as packager  # noqa: E402

REQUIRED = [
    "VERSION",
    "README.md",
    "PROJECT_STATUS.md",
    "SESSION_HANDOFF.md",
    "CHANGELOG.md",
    "docs/LAUNCH_CHECKLIST.md",
    "docs/acceptance/CONSOLIDATED-FINAL-PASS.md",
    "images/spiritual-gorillaz-logo.svg",
    "install/index.php",
    "admin/launch/index.php",
    "admin/upgrades/RecoveryEngine.php",
    "admin/upgrades/recover.php",
    "app/PublicSite.php",
    "app/Orders.php",
    "app/PaymentSettings.php",
    "app/UpdateManager.php",
    "app/TaskHeartbeat.php",
    "app/tasks/mail.php",
    "app/tasks/payments.php",
    "app/tests/public_browser.py",
    "app/tests/commerce_browser.py",
    "app/tests/payment_browser.py",
    "app/tests/communication_browser.py",
    "app/tests/release_browser.py",
    "app/tests/upgrade_browser.py",
    "app/tests/backup_browser.py",
    "admin/analytics/index.php",
    "admin/analytics/geography.php",
    "admin/analytics/live.php",
    "admin/analytics/visitors.php",
    "app/http/pages/analytics.php",
    "app/Analytics.php",
    "app/AnalyticsReport.php",
    "app/AnalyticsCharts.php",
    "app/AnalyticsVisitors.php",
    "app/AnalyticsGeo.php",
    "app/AnalyticsSearchConsole.php",
    "app/GeoData/country.mmdb",
    "app/GeoData/NOTICE.txt",
    "app/ThirdParty/MaxMind/LICENSE.txt",
    "config/database/migrations/007.sql",
    "config/database/migrations/008.sql",
    "assets/css/site.css",
    "assets/js/site.js",
    "assets/js/analytics.js",
    "assets/css/admin-analytics.css",
    "assets/css/admin-analytics-overview.css",
    "docs/ANALYTICS.md",
    "app/tests/analytics_browser.py",
]

FORBIDDEN_PACKAGE_PREFIXES = ("app/tests/", "app/tools/", "docs/design/")
FORBIDDEN_PACKAGE_PARTS = (
    "PRIVATE-SETUP",
    "PRIVATE-KEY",
    "PRIVATE-RECOVERY",
)
VERSION_RE = re.compile(r"^\d+\.\d+\.\d+(?:-[A-Za-z0-9]+(?:[.-][A-Za-z0-9]+)*)?$")


def fail(message: str) -> None:
    raise RuntimeError(message)


def blockers(status: str) -> list[str]:
    marker = "## Remaining blockers"
    start = status.find(marker)
    if start < 0:
        return ["PROJECT_STATUS.md is missing the Remaining blockers section"]
    tail = status[start + len(marker):]
    next_heading = tail.find("\n## ")
    section = tail[:next_heading if next_heading >= 0 else None].strip()
    if not section:
        return []
    normalized = re.sub(r"\s+", " ", section).strip()
    if normalized.lower() in {"none.", "none", "no remaining blockers."}:
        return []
    return [normalized]


def main() -> int:
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    if not VERSION_RE.fullmatch(version):
        fail("VERSION is not a supported semantic release identifier.")

    missing = [path for path in REQUIRED if not (ROOT / path).is_file()]
    if missing:
        fail("Missing required release files: " + ", ".join(missing))

    readme = (ROOT / "README.md").read_text(encoding="utf-8")
    if not readme.startswith(f"# Spiritual Gorillaz — {version}\n"):
        fail("README release heading does not match VERSION.")

    status = (ROOT / "PROJECT_STATUS.md").read_text(encoding="utf-8")
    unresolved = blockers(status)

    bundled_logo = (ROOT / "images/spiritual-gorillaz-logo.svg").read_text(encoding="utf-8")
    lowered_logo = bundled_logo.lower()
    for forbidden in ["<script", "<foreignobject", "onload=", "onclick=", "javascript:"]:
        if forbidden in lowered_logo:
            fail("Bundled fallback logo contains active SVG content.")
    if version == "1.0.0" and unresolved:
        fail("Final 1.0.0 is blocked by PROJECT_STATUS Remaining blockers.")

    for path in [
        "app/tests/public_browser.py",
        "app/tests/commerce_browser.py",
        "app/tests/payment_browser.py",
        "app/tests/communication_browser.py",
    ]:
        text = (ROOT / path).read_text(encoding="utf-8")
        if "360" not in text or "390" not in text:
            fail(f"{path} does not enforce both 360px and 390px launch widths.")
    release_browser = (ROOT / "app/tests/release_browser.py").read_text(encoding="utf-8")
    if "firefox" not in release_browser or "webkit" not in release_browser or "360" not in release_browser:
        fail("Cross-browser release review does not include Firefox, WebKit and 360px coverage.")

    for base in ["app", "assets", "includes", "admin", "install"]:
        for path in (ROOT / base).rglob("*"):
            if path.is_relative_to(ROOT / "app/tests") or path.is_relative_to(ROOT / "app/tools"):
                continue
            if not path.is_file() or path.suffix.lower() not in {".php", ".js", ".css", ".inc"}:
                continue
            text = path.read_text(encoding="utf-8", errors="ignore")
            if re.search(r"\b(?:TODO|FIXME)\b", text):
                fail(f"Unresolved implementation marker in {path.relative_to(ROOT)}.")

    with tempfile.TemporaryDirectory(prefix="spirit-release-gate-") as tmp:
        base=Path(tmp)
        archive = packager.package(base/"first")
        second = packager.package(base/"second")
        if archive.read_bytes()!=second.read_bytes():
            fail("Repeated package builds are not byte-reproducible.")
        with zipfile.ZipFile(archive) as bundle:
            names = bundle.namelist()
            if "docs/PACKAGE-MANIFEST.json" not in names:
                fail("Generated package is missing its manifest.")
            if any(name.startswith(FORBIDDEN_PACKAGE_PREFIXES) for name in names):
                fail("Generated package contains development/test source.")
            upper_names = ["/" + name.upper() for name in names]
            for token in FORBIDDEN_PACKAGE_PARTS:
                if any(token in name for name in upper_names):
                    fail(f"Generated package contains forbidden private/runtime path marker: {token}")
            manifest = json.loads(bundle.read("docs/PACKAGE-MANIFEST.json"))
            if manifest.get("version") != version:
                fail("Package manifest version does not match VERSION.")
            files = manifest.get("files")
            if not isinstance(files, dict) or len(files) != len(names) - 1:
                fail("Package manifest does not cover exactly the payload files.")
            for name, digest in files.items():
                if name not in names:
                    fail(f"Manifest references missing payload file: {name}")
                actual = hashlib.sha256(bundle.read(name)).hexdigest()
                if actual != digest:
                    fail(f"Manifest checksum mismatch: {name}")

    print(f"PASS software release gate for {version}")
    if unresolved:
        print("NOTE external/final acceptance blockers remain; this is not production approval.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
