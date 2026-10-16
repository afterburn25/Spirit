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

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "tools"))
import package as packager  # noqa: E402

REQUIRED = [
    "VERSION",
    "README.md",
    "PROJECT_STATUS.md",
    "SESSION_HANDOFF.md",
    "CHANGELOG.md",
    "docs/LAUNCH_CHECKLIST.md",
    "docs/acceptance/CONSOLIDATED-FINAL-PASS.md",
    "install/index.php",
    "admin/launch/index.php",
    "admin/upgrades/RecoveryEngine.php",
    "admin/upgrades/recover.php",
    "app/PublicSite.php",
    "app/Orders.php",
    "app/PaymentSettings.php",
    "app/UpdateManager.php",
    "tests/public_browser.py",
    "tests/commerce_browser.py",
    "tests/payment_browser.py",
    "tests/communication_browser.py",
    "tests/release_browser.py",
    "tests/upgrade_browser.py",
    "tests/backup_browser.py",
]

FORBIDDEN_PACKAGE_PREFIXES = ("tests/", "tools/")
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
    if version == "1.0.0" and unresolved:
        fail("Final 1.0.0 is blocked by PROJECT_STATUS Remaining blockers.")

    for path in [
        "tests/public_browser.py",
        "tests/commerce_browser.py",
        "tests/payment_browser.py",
        "tests/communication_browser.py",
    ]:
        text = (ROOT / path).read_text(encoding="utf-8")
        if "360" not in text or "390" not in text:
            fail(f"{path} does not enforce both 360px and 390px launch widths.")
    release_browser = (ROOT / "tests/release_browser.py").read_text(encoding="utf-8")
    if "firefox" not in release_browser or "webkit" not in release_browser or "360" not in release_browser:
        fail("Cross-browser release review does not include Firefox, WebKit and 360px coverage.")

    for base in ["app", "views", "assets", "includes", "admin", "install", "shop", "booking"]:
        for path in (ROOT / base).rglob("*"):
            if not path.is_file() or path.suffix.lower() not in {".php", ".js", ".css", ".inc"}:
                continue
            text = path.read_text(encoding="utf-8", errors="ignore")
            if re.search(r"\b(?:TODO|FIXME)\b", text):
                fail(f"Unresolved implementation marker in {path.relative_to(ROOT)}.")

    with tempfile.TemporaryDirectory(prefix="spirit-release-gate-") as tmp:
        archive = packager.package(Path(tmp))
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
