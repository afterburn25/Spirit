#!/usr/bin/env python3
"""Generate deterministic wide hero masters from the reviewed native artwork.

The generator adds only dark navy atmosphere and a feathered transition on the
left. It never upscales the source beyond the target height and keeps the full
right-side subject intact.
"""
from __future__ import annotations

import argparse
import random
from pathlib import Path
from PIL import Image, ImageDraw, ImageFilter

ROOT = Path(__file__).resolve().parents[1]

SPECS = {
    "home": (2640, 1240, "spiritual-gorillaz-home-hero.png", "spiritual-gorillaz-home-hero-wide.webp", 0.33),
    "music": (2640, 1000, "spiritual-gorillaz-music-hero.png", "spiritual-gorillaz-music-hero-wide.webp", 0.35),
    "shows": (2640, 1000, "spiritual-gorillaz-shows-hero.png", "spiritual-gorillaz-shows-hero-wide.webp", 0.34),
    "band": (2640, 1000, "spiritual-gorillaz-band-hero.png", "spiritual-gorillaz-band-hero-wide.webp", 0.34),
    "merch": (2640, 1000, "spiritual-gorillaz-merch-hero.png", "spiritual-gorillaz-merch-hero-wide.webp", 0.34),
    "booking": (2640, 1520, "spiritual-gorillaz-booking-hero.png", "spiritual-gorillaz-booking-hero-wide.webp", 0.30),
}

ACCENTS = {
    "home": ((2, 7, 12), (3, 19, 34), (18, 79, 132)),
    "music": ((2, 7, 12), (3, 16, 28), (45, 83, 119)),
    "shows": ((2, 7, 12), (3, 16, 29), (15, 83, 142)),
    "band": ((2, 7, 12), (3, 15, 27), (20, 78, 130)),
    "merch": ((2, 7, 12), (4, 16, 28), (30, 80, 121)),
    "booking": ((2, 7, 12), (7, 14, 19), (72, 55, 33)),
}


def mix(a: tuple[int, int, int], b: tuple[int, int, int], t: float) -> tuple[int, int, int]:
    return tuple(round(a[i] * (1.0 - t) + b[i] * t) for i in range(3))


def atmosphere(name: str, width: int, height: int) -> Image.Image:
    left, mid, accent = ACCENTS[name]
    canvas = Image.new("RGB", (width, height), left)
    draw = ImageDraw.Draw(canvas)
    for x in range(width):
        u = x / max(1, width - 1)
        if u < 0.56:
            color = mix(left, mid, u / 0.56)
        else:
            color = mix(mid, accent, min(1.0, (u - 0.56) / 0.44) * 0.40)
        draw.line((x, 0, x, height), fill=color)

    glow = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    g = ImageDraw.Draw(glow)
    g.ellipse(
        (int(width * 0.46), int(height * -0.16), int(width * 1.04), int(height * 1.08)),
        fill=(*accent, 72),
    )
    g.ellipse(
        (int(width * 0.28), int(height * 0.36), int(width * 0.88), int(height * 1.22)),
        fill=(*mid, 52),
    )
    glow = glow.filter(ImageFilter.GaussianBlur(max(80, height // 7)))
    canvas = Image.alpha_composite(canvas.convert("RGBA"), glow)

    rng = random.Random(f"spiritual-gorillaz-{name}-1.0.13")
    grain_small = Image.new("L", (max(1, width // 12), max(1, height // 12)))
    grain_small.putdata([rng.randrange(0, 256) for _ in range(grain_small.width * grain_small.height)])
    grain = grain_small.resize((width, height), Image.Resampling.BILINEAR)
    grain_rgba = Image.merge("RGBA", (grain, grain, grain, Image.new("L", (width, height), 12)))
    canvas = Image.blend(canvas, grain_rgba, 0.035)
    return canvas


def build(name: str, spec: tuple[int, int, str, str, float], output_dir: Path) -> Path:
    tw, th, src_name, out_name, fade_fraction = spec
    source = Image.open(ROOT / "images" / src_name).convert("RGBA")
    sw = round(source.width * th / source.height)
    resized = source.resize((sw, th), Image.Resampling.LANCZOS)
    x0 = tw - sw

    canvas = atmosphere(name, tw, th)
    fade = max(1, round(sw * fade_fraction))
    mask = Image.new("L", (sw, th), 255)
    mdraw = ImageDraw.Draw(mask)
    for x in range(fade):
        t = x / max(1, fade - 1)
        t = t * t * (3.0 - 2.0 * t)
        mdraw.line((x, 0, x, th), fill=round(255 * t))
    canvas.paste(resized, (x0, 0), mask)

    vignette = Image.new("RGBA", (tw, th), (0, 0, 0, 0))
    vdraw = ImageDraw.Draw(vignette)
    vdraw.rectangle((0, 0, tw, max(1, th // 14)), fill=(0, 0, 0, 26))
    vdraw.rectangle((0, th - max(1, th // 11), tw, th), fill=(0, 0, 0, 48))
    vignette = vignette.filter(ImageFilter.GaussianBlur(max(18, th // 45)))
    canvas = Image.alpha_composite(canvas, vignette).convert("RGB")

    output_dir.mkdir(parents=True, exist_ok=True)
    output = output_dir / out_name
    canvas.save(output, "WEBP", quality=90, method=6, exact=True)
    return output


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", type=Path, default=ROOT / "images" / "extended")
    args = parser.parse_args()
    for name, spec in SPECS.items():
        path = build(name, spec, args.output)
        print(path.relative_to(ROOT) if path.is_relative_to(ROOT) else path)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
