#!/usr/bin/env python3
"""Render tightly framed favicon assets from the approved transparent master.

Chromium renders the unchanged master through a square SVG viewport. Python
orchestrates that rendering, packages PNG bytes into ICO, and inspects output;
it does not retouch, crop, resize, or repaint the bitmap with Pillow.

Example:
  python3 tools/build_favicons.py --browser /path/to/chromium \
    --review-dir /tmp/spirit-favicon-review

Requires Playwright, Pillow (inspection only), and Chromium. The generated
PNG/ICO/SVG assets are committed; the deployed PHP website needs none of these
build dependencies.
"""

from __future__ import annotations

import argparse
import base64
import hashlib
import io
import json
from pathlib import Path
import struct

from PIL import Image
from playwright.sync_api import sync_playwright


ROOT = Path(__file__).resolve().parent.parent
PNG_NAMES = {
    16: "favicon-16.png",
    32: "favicon-32.png",
    48: "favicon-48.png",
    64: "favicon-64.png",
    180: "apple-touch-icon.png",
    192: "favicon-192.png",
    512: "favicon-512.png",
}
ICO_SIZES = (16, 32, 48, 64)
ACTIVE_ICON_DIRECTORY = "crowned-gorilla-full-fit"
SILHOUETTE_FILL = 1.0
VISIBLE_ALPHA_THRESHOLD = 8
OPAQUE_CORE_THRESHOLD = 240


def digest(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def image_details(data: bytes) -> dict:
    with Image.open(io.BytesIO(data)) as image:
        image.load()
        rgba = image.convert("RGBA")
        alpha = rgba.getchannel("A")
        alpha_values = alpha.tobytes()
        width, height = rgba.size
        bounds = alpha.getbbox()
        corners = [alpha.getpixel(point) for point in
                   ((0, 0), (width - 1, 0), (0, height - 1), (width - 1, height - 1))]
        def threshold_details(threshold: int) -> tuple[list[int] | None, int]:
            points = [index for index, value in enumerate(alpha_values) if value >= threshold]
            bounds = [min(index % width for index in points), min(index // width for index in points),
                      max(index % width for index in points) + 1, max(index // width for index in points) + 1] if points else None
            return bounds, len(points)

        visible_bounds, visible_pixels = threshold_details(VISIBLE_ALPHA_THRESHOLD)
        core_bounds, core_pixels = threshold_details(OPAQUE_CORE_THRESHOLD)
        return {
            "width": width,
            "height": height,
            "mode": image.mode,
            "alpha_extrema": list(alpha.getextrema()),
            "alpha_bounds": list(bounds) if bounds else None,
            "visible_alpha_bounds": visible_bounds,
            "visible_alpha_threshold": VISIBLE_ALPHA_THRESHOLD,
            "visible_alpha_pixels": visible_pixels,
            "opaque_core_bounds": core_bounds,
            "opaque_core_threshold": OPAQUE_CORE_THRESHOLD,
            "opaque_core_pixels": core_pixels,
            "alpha_weighted_area_percent": round(sum(alpha_values) / 255 / (width * height) * 100, 3),
            "transparent_pixels": alpha_values.count(0),
            "transparent_corners": all(value == 0 for value in corners),
            "bytes": len(data),
            "sha256": digest(data),
        }


def pack_ico(pngs: dict[int, bytes]) -> bytes:
    """ICO container with unchanged, independently rendered PNG payloads."""
    header = struct.pack("<HHH", 0, 1, len(ICO_SIZES))
    entries = bytearray()
    payloads = bytearray()
    offset = len(header) + len(ICO_SIZES) * 16
    for size in ICO_SIZES:
        data = pngs[size]
        entries.extend(struct.pack("<BBBBHHII", size, size, 0, 0, 1, 32, len(data), offset))
        payloads.extend(data)
        offset += len(data)
    return header + bytes(entries) + bytes(payloads)


def review_html(pngs: dict[int, bytes], previous_icons: dict[int, bytes]) -> str:
    def uri(size: int) -> str:
        return "data:image/png;base64," + base64.b64encode(pngs[size]).decode("ascii")

    rows = []
    for background, label in (("dark", "Dark browser theme"), ("light", "Light browser theme")):
        cells = []
        for size in (16, 32, 48, 64):
            previous = ''
            if size in previous_icons:
                old_uri = "data:image/png;base64," + base64.b64encode(previous_icons[size]).decode("ascii")
                previous = f'<div class="icon"><img src="{old_uri}" width="{size}" height="{size}" alt="Previous 98 percent fit"><small>Previous 98%</small></div>'
            cells.append(f'<div class="sample"><div class="pair">{previous}<div class="icon"><img src="{uri(size)}" width="{size}" height="{size}" '
                         f'alt="Full-fit crowned gorilla at {size} pixels"><small>Full fit 100%</small></div></div><span>{size} × {size} px</span></div>')
        rows.append(f'<section class="{background}"><h2>{label}</h2><div class="samples">{"".join(cells)}</div></section>')
    enlarged = []
    for size in (16, 32):
        previous = ''
        if size in previous_icons:
            old_uri = "data:image/png;base64," + base64.b64encode(previous_icons[size]).decode("ascii")
            previous = f'<div class="icon"><img src="{old_uri}" width="128" height="128" alt="Previous {size} pixel icon enlarged without smoothing"><small>Previous 98%</small></div>'
        enlarged.append(f'<div class="sample"><div class="pair">{previous}<div class="icon"><img src="{uri(size)}" width="128" height="128" '
                        f'alt="Full-fit {size} pixel icon enlarged without smoothing"><small>Full fit 100%</small></div></div><span>{size}px source × {128 // size}</span></div>')
    return f'''<!doctype html><html lang="en"><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Spiritual Gorillaz — actual favicon sizes</title>
<style>*{{box-sizing:border-box}}body{{margin:0;padding:28px;background:#53606b;color:#fff;font:15px Arial,sans-serif}}
main{{max-width:900px;margin:auto}}h1{{font-size:25px;margin:0 0 8px}}p{{line-height:1.6;margin:0 0 22px}}
section{{border-radius:12px;padding:26px 30px;margin:20px 0}}h2{{font-size:15px;margin:0 0 26px}}
.dark{{background:#07111c;color:#f2d184}}.light{{background:#f6f1e8;color:#26313c}}
.samples{{display:flex;align-items:flex-end;justify-content:space-between;gap:30px;min-height:100px}}.sample{{min-width:120px;display:flex;align-items:center;flex-direction:column;gap:18px}}
.pair{{display:flex;align-items:flex-end;gap:14px}}.icon{{display:flex;flex-direction:column;align-items:center;gap:12px;min-width:66px}}small{{font-size:10px;opacity:.8}}
.sample img{{display:block;object-fit:contain}}.sample span{{font-size:12px;font-variant-numeric:tabular-nums}}
.pixels .samples{{justify-content:space-around}}.pixels img{{image-rendering:pixelated}}
.large{{display:flex;align-items:center;gap:28px;background:#07111c;padding:22px 30px;border-radius:12px}}
.large img{{width:160px;height:160px}}.large p{{margin:0;max-width:450px;color:#b9c7d5}}
</style><main><h1>Transparent crowned gorilla favicon</h1>
<p>Native-size image samples, not screenshots of browser controls. The same complete crown-to-chin silhouette now fills the visible viewport height, with its proportions and transparency preserved. The change from 98% to 100% is about 2% in each dimension.</p>
{''.join(rows)}<section class="dark pixels"><h2>Enlarged pixels for inspection — nearest-neighbor display</h2><div class="samples">{''.join(enlarged)}</div></section>
<div class="large"><img src="{uri(512)}" alt="Crowned gorilla detail"><p>Detail view from the 512 px export. The transparent silhouette occupies about half its square, so the full head still looks narrower than a solid square icon. The browser controls the tab-icon area.</p></div></main></html>'''


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--source", type=Path, default=ROOT / "design/favicon/sculpted-gold-gorilla-master.png")
    parser.add_argument("--output-dir", type=Path, default=ROOT / "images/icons")
    parser.add_argument("--legacy-svg", type=Path, default=ROOT / "images/favicon.svg")
    parser.add_argument("--browser", type=Path)
    parser.add_argument("--review-dir", type=Path)
    parser.add_argument("--previous-icons", type=Path, help="Previous native PNG directory for measured before/after comparisons")
    args = parser.parse_args()
    source = args.source.read_bytes()
    assert source.startswith(b"\x89PNG\r\n\x1a\n"), "Expected a PNG master"
    source_info = image_details(source)
    assert source_info["alpha_extrema"][0] == 0, "Master needs a genuine transparent background"
    assert source_info["alpha_bounds"], "Master is empty"
    # Alpha below 8/255 is visually negligible fringe. Measure the visible head
    # for centering. Fit the complete visible head without additional margins;
    # the source bitmap and its natural crown-to-head proportions stay unchanged.
    left, top, right, bottom = source_info["visible_alpha_bounds"]
    width, height = right - left, bottom - top
    side = max(width, height) / SILHOUETTE_FILL
    view_x, view_y = (left + right - side) / 2, (top + bottom - side) / 2
    master_uri = "data:image/png;base64," + base64.b64encode(source).decode("ascii")
    viewport = " ".join(f"{value:.9f}" for value in (view_x, view_y, side, side))
    svg = (
        f'<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" '
        f'viewBox="{viewport}"><image href="{master_uri}" width="{source_info["width"]}" '
        f'height="{source_info["height"]}"/></svg>'
    )
    document = f'<!doctype html><meta charset="utf-8"><style>html,body{{margin:0;background:transparent}}#stage{{line-height:0}}svg{{display:block}}</style><div id="stage">{svg}</div>'
    args.output_dir.mkdir(parents=True, exist_ok=True)
    active_dir = args.output_dir / ACTIVE_ICON_DIRECTORY
    active_dir.mkdir(parents=True, exist_ok=True)
    args.legacy_svg.parent.mkdir(parents=True, exist_ok=True)
    if args.review_dir:
        args.review_dir.mkdir(parents=True, exist_ok=True)
    browser_errors: list[str] = []
    pngs: dict[int, bytes] = {}
    previous_icons = {size: (args.previous_icons / PNG_NAMES[size]).read_bytes() for size in ICO_SIZES} if args.previous_icons else {}
    with sync_playwright() as playwright:
        launch = {"headless": True}
        if args.browser:
            launch["executable_path"] = str(args.browser)
        browser = playwright.chromium.launch(**launch)
        page = browser.new_page(viewport={"width": 1024, "height": 1024}, device_scale_factor=1)
        page.on("pageerror", lambda error: browser_errors.append(str(error)))
        page.set_content(document, wait_until="load")
        page.evaluate("async uri => { const image = new Image(); image.src = uri; await image.decode(); }", master_uri)
        for size in sorted(set(PNG_NAMES) | {256}):
            page.evaluate("size => { const stage=document.querySelector('#stage'); const svg=stage.querySelector('svg'); stage.style.width=size+'px';stage.style.height=size+'px';svg.setAttribute('width',size);svg.setAttribute('height',size); }", size)
            pngs[size] = page.locator("#stage").screenshot(omit_background=True, scale="css")
            if size in PNG_NAMES:
                (args.output_dir / PNG_NAMES[size]).write_bytes(pngs[size])
                (active_dir / PNG_NAMES[size]).write_bytes(pngs[size])
        if args.review_dir:
            review = review_html(pngs, previous_icons)
            (args.review_dir / "favicon-actual-sizes.html").write_text(review)
            page.set_viewport_size({"width": 960, "height": 800})
            page.set_content(review, wait_until="load")
            page.screenshot(path=str(args.review_dir / "favicon-actual-sizes.png"), full_page=True)
        browser_version = browser.version
        browser.close()
    assert not browser_errors, browser_errors
    ico = pack_ico(pngs)
    (args.output_dir / "favicon.ico").write_bytes(ico)
    (active_dir / "favicon.ico").write_bytes(ico)
    with Image.open(io.BytesIO(ico)) as icon:
        assert icon.ico.sizes() == {(size, size) for size in ICO_SIZES}
        for size in ICO_SIZES:
            frame = icon.ico.getimage((size, size))
            assert frame.size == (size, size)
            assert frame.getchannel("A").getextrema()[0] == 0
    compatibility_svg = (
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" role="img" '
        'aria-label="Spiritual Gorillaz crowned gorilla">\n'
        '<image width="256" height="256" href="data:image/png;base64,'
        + base64.b64encode(pngs[256]).decode("ascii") + '"/>\n</svg>\n'
    )
    args.legacy_svg.write_text(compatibility_svg)
    records = []
    for size, name in PNG_NAMES.items():
        details = image_details(pngs[size])
        assert details["width"] == size and details["height"] == size
        assert details["alpha_extrema"][0] == 0 and details["alpha_extrema"][1] >= 240
        assert details["transparent_corners"], f"Painted background corner in {name}"
        assert details["alpha_bounds"], f"Empty output {name}"
        records.append({"file": name, **details})
    report = {
        "source": args.source.name,
        "source_details": source_info,
        "source_bitmap_modified": False,
        "source_viewport": [view_x, view_y, side, side],
        "silhouette_fill_fraction": SILHOUETTE_FILL,
        "active_icon_directory": ACTIVE_ICON_DIRECTORY,
        "legacy_png_and_ico_paths_preserved": True,
        "preserves_aspect_ratio": True,
        "renderer": "Chromium SVG viewport; device scale factor 1",
        "browser_version": browser_version,
        "pngs": records,
        "ico": {"file": "favicon.ico", "dimensions": list(ICO_SIZES), "bytes": len(ico), "sha256": digest(ico)},
        "compatibility_svg": {"file": args.legacy_svg.name, "embedded_png_dimensions": 256, "bytes": len(compatibility_svg.encode()), "sha256": digest(compatibility_svg.encode())},
        "browser_errors": browser_errors,
    }
    if previous_icons:
        report["comparison"] = [{"size": size, "before": image_details(previous_icons[size]), "after": image_details(pngs[size])} for size in ICO_SIZES]
    if args.review_dir:
        (args.review_dir / "favicon-verification.json").write_text(json.dumps(report, indent=2) + "\n")
    print(json.dumps(report, indent=2))


if __name__ == "__main__":
    main()
