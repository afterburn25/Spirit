#!/usr/bin/env python3
"""Compose a release review board exclusively from verified application captures.

Usage:
    python app/tests/render_analytics_review_board.py --screenshots CAPTURE_DIR \
        --output REVIEW_DIR --archive Spiritual-Gorillaz-vVERSION-Full.zip

Both capture manifests must describe the exact supplied ZIP. The board contains
the actual source PNGs, embedded in standalone HTML and cropped only by CSS.
All admin captures must be explicitly marked synthetic by their capture manifest.
This helper neither installs the site nor contacts a running application.
"""
from __future__ import annotations

import argparse
import base64
import hashlib
import html
import json
import re
import struct
import tempfile
import zipfile
from datetime import datetime, timezone
from pathlib import Path

from playwright.sync_api import sync_playwright


# Only these named sources may be read or embedded. A manifest cannot nominate
# arbitrary local files, remote URLs, or a different application's screenshots.
SOURCES = {
    'analytics-1440-overview-Synthetic.png': ('analytics', 'admin/analytics', 1440),
    'analytics-overview-1440-Synthetic.png': ('analytics', 'admin', 1440),
    'analytics-overview-1440-first-screen-Synthetic.png': ('analytics', 'admin', 1440),
    'analytics-visitors-1440-Synthetic.png': ('analytics', 'admin/analytics/visitors.php', 1440),
    'analytics-visit-detail-1440-Synthetic.png': ('analytics', 'admin/analytics/visitors.php', 1440),
    'analytics-visit-detail-panel-1440-Synthetic.png': ('analytics', 'admin/analytics/visitors.php', 1440),
    'analytics-390-full-Synthetic.png': ('analytics', 'admin/analytics', 390),
    'public-desktop-1440-home.png': ('public', '', 1440),
    'public-phone-390-home.png': ('public', '', 390),
}
BOARD_WIDTH = 1920
NOTICE = 'Synthetic demonstration data'
PNG_SIGNATURE = b'\x89PNG\r\n\x1a\n'


def require(condition: bool, message: str) -> None:
    if not condition:
        raise ValueError(message)


def digest(path: Path) -> str:
    result = hashlib.sha256()
    with path.open('rb') as stream:
        for block in iter(lambda: stream.read(1024 * 1024), b''):
            result.update(block)
    return result.hexdigest()


def png_size(data: bytes, name: str) -> tuple[int, int]:
    require(len(data) >= 33 and data[:8] == PNG_SIGNATURE
            and data[12:16] == b'IHDR', f'{name}: expected a PNG screenshot.')
    width, height = struct.unpack('>II', data[16:24])
    require(0 < width <= 10000 and 0 < height <= 100000 and width * height <= 100_000_000,
            f'{name}: screenshot dimensions exceed the review-board limit.')
    return width, height


def load_inputs(screenshots: Path, archive: Path) -> tuple[str, str, dict, dict]:
    """Validate the package and every allowlisted capture before creating files."""
    require(archive.is_file(), 'The supplied release ZIP does not exist.')
    package_hash = digest(archive)
    with zipfile.ZipFile(archive) as bundle:
        require(bundle.namelist().count('VERSION') == 1,
                'The release ZIP must contain exactly one root VERSION file.')
        require(bundle.getinfo('VERSION').file_size <= 128, 'The release VERSION is invalid.')
        version = bundle.read('VERSION').decode('utf-8').strip()
    require(re.fullmatch(r'[0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?', version) is not None,
            'The release VERSION must be a safe semantic version.')

    manifests = {}
    for kind in ['analytics', 'public']:
        path = screenshots / f'{kind}-capture-manifest.json'
        require(path.is_file() and not path.is_symlink(), f'Missing regular capture manifest: {path.name}')
        require(path.stat().st_size <= 2 * 1024 * 1024, f'{path.name}: manifest is unexpectedly large.')
        raw = path.read_bytes()
        manifest = json.loads(raw)
        require(isinstance(manifest, dict), f'{path.name}: invalid manifest object.')
        require(manifest.get('version') == version, f'{path.name}: release version does not match the ZIP.')
        require(manifest.get('package_sha256') == package_hash,
                f'{path.name}: package SHA-256 does not match the supplied ZIP.')
        require(manifest.get('device_scale_factor') == 1,
                f'{path.name}: source captures must use one device pixel per CSS pixel.')
        require(isinstance(manifest.get('screenshots'), list), f'{path.name}: screenshot records are missing.')
        if kind == 'analytics':
            require(manifest.get('synthetic_data_only') is True
                    and manifest.get('runtime_code_modified') is False
                    and manifest.get('production_site_accessed') is False,
                    'Analytics evidence must explicitly describe synthetic data in an unmodified disposable installation.')
        else:
            require(manifest.get('public_content_seeded') is False,
                    'Public evidence must explicitly describe unseeded application content.')
        manifests[kind] = {'file': path.name, 'sha256': hashlib.sha256(raw).hexdigest(), 'data': manifest}

    sources = {}
    for name, (kind, route, expected_width) in SOURCES.items():
        entries = [row for row in manifests[kind]['data']['screenshots']
                   if isinstance(row, dict) and row.get('file') == name]
        require(len(entries) == 1, f'{name}: expected exactly one record in the {kind} capture manifest.')
        record = entries[0]
        recorded_route = record.get('route')
        require(isinstance(recorded_route, str)
                and recorded_route.split('?', 1)[0].strip('/') == route,
                f'{name}: recorded route does not match the allowlisted application page.')
        viewport = record.get('viewport')
        require(isinstance(viewport, dict) and viewport.get('width') == expected_width
                and isinstance(viewport.get('height'), int) and viewport['height'] > 0,
                f'{name}: recorded viewport is invalid.')
        path = screenshots / name
        require(path.is_file() and not path.is_symlink(), f'{name}: source PNG is missing or is a symlink.')
        require(path.stat().st_size <= 40 * 1024 * 1024, f'{name}: source PNG is unexpectedly large.')
        data = path.read_bytes()
        source_hash = hashlib.sha256(data).hexdigest()
        require(record.get('sha256') == source_hash,
                f'{name}: source PNG SHA-256 does not match its capture manifest.')
        width, height = png_size(data, name)
        if record.get('element'):
            require(name == 'analytics-visit-detail-panel-1440-Synthetic.png'
                    and record['element'] == '#visit-detail',
                    f'{name}: unexpected element capture.')
        else:
            require(width == expected_width and height >= viewport['height'],
                    f'{name}: PNG dimensions do not agree with its capture viewport.')
        sources[name] = {
            'file': name, 'sha256': source_hash, 'manifest': manifests[kind]['file'],
            'route': recorded_route, 'viewport': viewport,
            'source_width': width, 'source_height': height,
            'full_page': record.get('full_page'), 'element': record.get('element'),
            'synthetic_demonstration_data': kind == 'analytics',
            'data_url': 'data:image/png;base64,' + base64.b64encode(data).decode('ascii'),
        }
    return version, package_hash, manifests, sources


def panels_for(sources: dict) -> list[dict]:
    """Pixel coordinates always refer to the original, hash-verified source PNG."""
    specifications = [
        ('dashboard', '01 / AUDIENCE ANALYTICS', 'The dedicated analytics dashboard',
         'analytics-1440-overview-Synthetic.png', None,
         'Complete desktop viewport. The sidebar, summary metrics and charts remain visible.'),
        ('analytics-phone', 'RESPONSIVE VIEW', 'Analytics on a phone',
         'analytics-390-full-Synthetic.png', 1100,
         'Top 1,100 source pixels of the full mobile analytics capture.'),
        ('overview', '02 / ADMIN OVERVIEW', 'The audience snapshot at sign-in',
         'analytics-overview-1440-first-screen-Synthetic.png', None,
         'Complete first desktop viewport of the main admin Overview.'),
        ('journey', '03 / VISITOR JOURNEY', 'One anonymous session, page by page',
         'analytics-visit-detail-panel-1440-Synthetic.png', None,
         'Complete session-details element captured directly from the visitor explorer.'),
        ('public-header', '04 / PUBLIC NAVIGATION', 'The public header and search control',
         'public-desktop-1440-home.png', 210,
         'Top 210 source pixels of the actual desktop homepage; focuses on the public header and search control.'),
        ('visitor-list', '05 / VISITOR EXPLORER', 'A filterable history of anonymous visits',
         'analytics-visitors-1440-Synthetic.png', 880,
         'Top 880 source pixels of the full visitor-list capture; shows navigation, filters and the first recorded sessions.'),
        ('public-phone', 'RESPONSIVE VIEW', 'The public homepage on a phone',
         'public-phone-390-home.png', 844,
         'Top 844 source pixels of the actual mobile homepage, matching its original capture viewport.'),
    ]
    panels = []
    for key, eyebrow, title, source_name, limit, description in specifications:
        source = sources[source_name]
        height = source['source_height'] if limit is None else min(limit, source['source_height'])
        panels.append({
            'id': key, 'eyebrow': eyebrow, 'title': title, 'source_file': source_name,
            'source_sha256': source['sha256'],
            'crop': {'x': 0, 'y': 0, 'width': source['source_width'], 'height': height},
            'crop_description': description,
            'synthetic_demonstration_data': source['synthetic_demonstration_data'],
        })
    return panels


def panel_html(panel: dict, sources: dict) -> str:
    source = sources[panel['source_file']]
    crop = panel['crop']
    width_ratio = source['source_width'] / crop['width'] * 100
    left = -crop['x'] / crop['width'] * 100
    top = -crop['y'] / crop['height'] * 100
    badge = NOTICE if panel['synthetic_demonstration_data'] else 'Actual public application'
    badge_class = 'synthetic' if panel['synthetic_demonstration_data'] else 'public'
    escape = html.escape
    return f'''<figure class="capture-card" id="panel-{panel['id']}">
 <figcaption><div><p class="panel-eyebrow">{escape(panel['eyebrow'])}</p><h2>{escape(panel['title'])}</h2></div>
 <span class="data-label {badge_class}">{escape(badge)}</span></figcaption>
 <div class="capture-frame" data-crop-id="{panel['id']}" style="aspect-ratio:{crop['width']}/{crop['height']}">
  <img src="{source['data_url']}" width="{source['source_width']}" height="{source['source_height']}"
       alt="{escape(panel['title'])}. {escape(badge)}. {escape(panel['crop_description'])}" draggable="false"
       style="width:{width_ratio:.8f}%;left:{left:.8f}%;top:{top:.8f}%">
 </div>
 <p class="capture-note">{escape(panel['crop_description'])}</p>
</figure>'''


def document(version: str, archive_name: str, package_hash: str, panels: list[dict], sources: dict) -> str:
    cards = {panel['id']: panel_html(panel, sources) for panel in panels}
    return '''<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="Content-Security-Policy" content="default-src 'none'; img-src data:; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'; connect-src 'none'">
<title>Spiritual Gorillaz · ''' + html.escape(version) + ''' · Actual application review</title>
<style>
*{box-sizing:border-box}html{background:#060b12;color:#ecf0f6;font-family:Arial,Helvetica,sans-serif}
body{margin:0;width:1920px}#review-board{width:1920px;padding:44px;background:radial-gradient(ellipse at 92% 0%,#17243a 0,transparent 38%),#070d16}
.board-header{display:flex;justify-content:space-between;gap:34px;align-items:flex-start;padding:0 0 26px;border-bottom:1px solid #394152}
.brand-line{margin:0 0 14px;color:#e3c079;font-size:17px;font-weight:700;letter-spacing:.19em}
h1{margin:0;font-family:Georgia,'Times New Roman',serif;font-size:52px;font-weight:400;line-height:1.13;letter-spacing:-.025em}
.board-intro{margin:17px 0 0;max-width:1200px;font-size:21px;color:#bdc9da;line-height:1.5}
.release-stamp{padding:18px 24px;border:1px solid #8e7548;border-radius:14px;color:#efce8a;background:#151a22;text-align:right;white-space:nowrap}
.release-stamp span{display:block;font-size:12px;letter-spacing:.17em;margin-bottom:10px;color:#bdc9da}.release-stamp strong{font-size:24px}
.evidence-line{display:flex;align-items:center;gap:16px;margin:22px 0 26px;font-size:16px;color:#cad5e4;line-height:1.5}
.evidence-line b{padding:7px 12px;border:1px solid #625137;border-radius:8px;background:#242015;color:#f2d392;font-size:14px;white-space:nowrap}
.board-grid{display:grid;gap:26px;margin:0 0 26px;align-items:start}.hero-grid,.history-grid{grid-template-columns:minmax(0,1fr) 348px}.detail-grid{grid-template-columns:1fr 1fr}
.capture-card{margin:0;padding:16px;border:1px solid #2c3b50;border-radius:17px;background:#0d1725;box-shadow:0 12px 30px #0003;min-width:0}
.capture-card figcaption{min-height:94px;display:flex;flex-direction:column;align-items:flex-start;justify-content:space-between;gap:10px;padding:4px 3px 14px}
.panel-eyebrow{margin:0 0 7px;color:#d6b36f;font-size:13px;letter-spacing:.13em;font-weight:700}.capture-card h2{margin:0;font-size:22px;line-height:1.22;font-weight:600;letter-spacing:-.015em}
.data-label{font-size:12px;line-height:1.2;padding:5px 8px;border-radius:5px;font-weight:700}.data-label.synthetic{color:#f0d39b;background:#312b20;border:1px solid #6a5837}.data-label.public{color:#b7d9e3;background:#15303a;border:1px solid #315963}
.capture-frame{position:relative;overflow:hidden;width:100%;border:1px solid #26364d;border-radius:9px;background:#050a10}
.capture-frame img{position:absolute;display:block;height:auto;max-width:none;object-fit:initial}
.capture-note{margin:12px 3px 1px;font-size:13px;line-height:1.45;color:#9eafc4}
.public-strip{margin-bottom:26px}.public-strip figcaption{min-height:90px;flex-direction:row;align-items:center}.public-strip .capture-note{font-size:14px}
.board-footer{padding:24px 0 0;border-top:1px solid #394152;display:flex;justify-content:space-between;gap:28px;color:#9eafc4;font-size:14px;line-height:1.6}
.board-footer strong{color:#d4dfec;font-weight:600}.package-name{overflow-wrap:anywhere}.hash{font-family:monospace;color:#cfb680;font-size:13px}.footer-note{max-width:570px;text-align:right}
@media print{html,body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}
</style></head><body><main id="review-board">
<header class="board-header"><div><p class="brand-line">SPIRITUAL GORILLAZ · RELEASE REVIEW</p>
<h1>Audience analytics &amp; navigation</h1><p class="board-intro">Actual application captures of the dedicated dashboard, the admin Overview, detailed visitor journeys and the public header.</p></div>
<div class="release-stamp"><span>EXACT PACKAGE</span><strong>v''' + html.escape(version) + '''</strong></div></header>
<p class="evidence-line"><b>''' + NOTICE + '''</b> All admin screenshots use disposable test data. Their numbers demonstrate the interface and are not production results.</p>
<section class="board-grid hero-grid" aria-label="Desktop and mobile analytics">''' + cards['dashboard'] + cards['analytics-phone'] + '''</section>
<section class="board-grid detail-grid" aria-label="Admin Overview and visitor journey">''' + cards['overview'] + cards['journey'] + '''</section>
<section class="public-strip" aria-label="Public navigation">''' + cards['public-header'] + '''</section>
<section class="board-grid history-grid" aria-label="Visitor history and public mobile view">''' + cards['visitor-list'] + cards['public-phone'] + '''</section>
<footer class="board-footer"><div><strong>Verified source package</strong><br><span class="package-name">''' + html.escape(archive_name) + '''</span><br><span class="hash">SHA-256 ''' + package_hash + '''</span></div>
<div class="footer-note">Every panel is a labeled crop of a hash-verified screenshot. The accompanying provenance JSON records the source files, crop coordinates, capture manifests and output hashes.</div></footer>
</main></body></html>'''


def render_board(screenshots: Path, output: Path, archive: Path) -> dict:
    version, package_hash, manifests, sources = load_inputs(screenshots, archive)
    panels = panels_for(sources)
    stem = f'Spiritual-Gorillaz-v{version}-ReviewBoard'
    names = {'html': stem + '.html', 'png': stem + '.png', 'provenance': stem + '.provenance.json'}
    require(not any((output / name).exists() for name in names.values()),
            'A review board for this version already exists in the output directory; choose a new directory.')
    source_html = document(version, archive.name, package_hash, panels, sources)
    with tempfile.TemporaryDirectory(prefix='spirit-review-board-') as directory:
        staging = Path(directory)
        html_path, png_path = staging / names['html'], staging / names['png']
        html_path.write_text(source_html, encoding='utf-8')
        with sync_playwright() as engine:
            browser = engine.chromium.launch()
            try:
                context = browser.new_context(viewport={'width': BOARD_WIDTH, 'height': 1080},
                                              device_scale_factor=1, reduced_motion='reduce')
                # The standalone page contains embedded PNGs and local CSS only.
                # Fail closed if a future layout attempts to fetch remote content.
                context.route('http://**/*', lambda route: route.abort())
                context.route('https://**/*', lambda route: route.abort())
                page = context.new_page()
                page.goto(html_path.as_uri(), wait_until='load')
                decoded = page.locator('img').evaluate_all('''async images => await Promise.all(images.map(async image => {
                    try { await image.decode(); return image.complete && image.naturalWidth > 0; }
                    catch { return false; }
                }))''')
                require(len(decoded) == len(panels) and all(decoded), 'Every source screenshot must decode before rendering.')
                page.evaluate('document.fonts.ready')
                require(page.evaluate('document.documentElement.scrollWidth === 1920'),
                        'Review-board layout has unexpected horizontal overflow.')
                geometry = page.locator('[data-crop-id]').evaluate_all('''nodes => Object.fromEntries(nodes.map(node => {
                    const r = node.getBoundingClientRect();
                    return [node.dataset.cropId, {x:r.x, y:r.y, width:r.width, height:r.height}];
                }))''')
                for panel in panels:
                    panel['board_rectangle'] = {key: round(value, 3) for key, value in geometry[panel['id']].items()}
                page.screenshot(path=str(png_path), full_page=True, animations='disabled')
                browser_version = browser.version
                context.close()
            finally:
                browser.close()
        board_width, board_height = png_size(png_path.read_bytes(), names['png'])
        require(board_width == BOARD_WIDTH, 'Rendered PNG width does not match the intended board width.')
        source_records = [{key: value for key, value in source.items() if key != 'data_url'}
                          for source in sources.values()]
        provenance = {
            'schema_version': 1, 'version': version,
            'rendered_at_utc': datetime.now(timezone.utc).isoformat(),
            'description': 'Actual application screenshots composed with HTML/CSS and Chromium; no replacement artwork.',
            'data_notice': NOTICE + '. All admin metrics are disposable fixtures, not production results.',
            'production_site_accessed': False,
            'package': {'file': archive.name, 'sha256': package_hash},
            'renderer': {'file': 'app/tests/render_analytics_review_board.py', 'sha256': digest(Path(__file__)),
                         'browser': 'Chromium', 'browser_version': browser_version, 'device_scale_factor': 1},
            'capture_manifests': [{'file': item['file'], 'sha256': item['sha256'],
                                   'captured_at_utc': item['data'].get('captured_at_utc'),
                                   'package_name': item['data'].get('package_name'),
                                   'package_sha256': item['data']['package_sha256']}
                                  for item in manifests.values()],
            'sources': source_records, 'panels': panels,
            'supplemental_verified_sources': [name for name in sources if name not in {panel['source_file'] for panel in panels}],
            'outputs': {'png': {'file': names['png'], 'sha256': digest(png_path), 'width': board_width, 'height': board_height},
                        'html': {'file': names['html'], 'sha256': digest(html_path), 'self_contained': True}},
        }
        (staging / names['provenance']).write_text(json.dumps(provenance, indent=2) + '\n', encoding='utf-8')
        # Exclusive creation preserves any previously delivered board of this
        # version. Validation and rendering finish before outputs are published.
        output.mkdir(parents=True, exist_ok=True)
        for name in names.values():
            with (output / name).open('xb') as destination:
                destination.write((staging / name).read_bytes())
    return {'version': version, 'outputs': {key: str(output / value) for key, value in names.items()},
            'source_count': len(sources), 'panel_count': len(panels), 'package_sha256': package_hash}


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--screenshots', required=True, type=Path, help='Folder containing both capture manifests and allowlisted PNGs.')
    parser.add_argument('--output', required=True, type=Path, help='Folder for the immutable versioned PNG, standalone HTML and provenance JSON.')
    parser.add_argument('--archive', required=True, type=Path, help='Exact cumulative release ZIP used by both capture runs.')
    args = parser.parse_args()
    result = render_board(args.screenshots.resolve(), args.output.resolve(), args.archive.resolve())
    print(json.dumps(result, indent=2), flush=True)


if __name__ == '__main__':
    main()
