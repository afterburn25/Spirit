#!/usr/bin/env python3
"""Encode the selected stage artwork as WebP at its original dimensions.

Chromium performs a native-size canvas export. The source artwork is never
cropped, retouched, sharpened or upscaled. The committed output is served by
PHP directly; hosting does not require this development-only export tool.
Requires Playwright, Pillow (inspection only), and Chromium.
"""

from __future__ import annotations

import argparse
import base64
import hashlib
import io
import json
from pathlib import Path

from PIL import Image
from playwright.sync_api import sync_playwright


ROOT = Path(__file__).resolve().parent.parent


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--source', type=Path, default=ROOT / 'design/background-stage/blue-lit-concert-stage-master.png')
    parser.add_argument('--output', type=Path, default=ROOT / 'images/backgrounds/spiritual-gorillaz-stage-background.webp')
    parser.add_argument('--report', type=Path, default=ROOT / 'design/background-stage/asset-verification.json')
    parser.add_argument('--browser', type=Path)
    args = parser.parse_args()
    source = args.source.read_bytes()
    with Image.open(io.BytesIO(source)) as master:
        master.load()
        original_size = master.size
        assert master.format == 'PNG'
    uri = 'data:image/png;base64,' + base64.b64encode(source).decode('ascii')
    with sync_playwright() as playwright:
        launch = {'headless': True}
        if args.browser:
            launch['executable_path'] = str(args.browser)
        browser = playwright.chromium.launch(**launch)
        page = browser.new_page()
        result = page.evaluate('''async source => {
            const image = new Image();
            image.src = source;
            await image.decode();
            const canvas = document.createElement('canvas');
            canvas.width = image.naturalWidth;
            canvas.height = image.naturalHeight;
            canvas.getContext('2d', {alpha: false}).drawImage(image, 0, 0);
            return {width: canvas.width, height: canvas.height,
                data: canvas.toDataURL('image/webp', 0.9)};
        }''', uri)
        browser_version = browser.version
        browser.close()
    assert result['data'].startswith('data:image/webp;base64,')
    output = base64.b64decode(result['data'].split(',', 1)[1], validate=True)
    with Image.open(io.BytesIO(output)) as image:
        image.load()
        assert image.format == 'WEBP'
        assert image.size == original_size == (result['width'], result['height'])
    assert args.source.read_bytes() == source
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_bytes(output)
    report = {
        'source': args.source.name,
        'source_dimensions': list(original_size),
        'source_bytes': len(source),
        'source_sha256': hashlib.sha256(source).hexdigest(),
        'output': args.output.name,
        'output_dimensions': list(original_size),
        'output_bytes': len(output),
        'output_sha256': hashlib.sha256(output).hexdigest(),
        'source_bitmap_modified': False,
        'crop_or_upscale_applied': False,
        'encoding': 'Chromium native-size WebP canvas export, quality 0.9',
        'browser_version': browser_version,
    }
    args.report.parent.mkdir(parents=True, exist_ok=True)
    args.report.write_text(json.dumps(report, indent=2) + '\n')
    print(json.dumps(report, indent=2))


if __name__ == '__main__':
    main()
