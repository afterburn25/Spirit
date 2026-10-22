# Full-fit crowned-gorilla favicon — rc.6 evidence

The same approved transparent crowned gorilla head now fits the complete visible crown-to-chin height of the square export viewport. Its crown, face, natural proportions and source pixels are unchanged. This is a modest fit correction and a fresh active URL, not a claim that a website can enlarge browser controls.

## Source and geometry

- Source: `../sculpted-gold-gorilla-master.png`, 1254 × 1254 RGBA.
- Unchanged source SHA-256: `3f3232cd3c314b3f227c1c598c8bf75f01c6b04b779cb6ece1bc7f51334f66bc`.
- Visible source bounds at alpha ≥ 8/255: `[90, 15, 1107, 1238]`, measuring 1017 × 1223px. Lower-alpha source fringe does not determine centering.
- Full-fit square SVG viewBox: `-13 15 1223 1223`.
- The previous viewport fitted the same silhouette at 98% height. The new 100% fit scales both dimensions by `1 / 0.98`, an approximately 2.04% increase, without stretching or repainting.
- Chromium 143.0.7499.4 rendered the unchanged bitmap through the SVG viewport at device scale factor 1. Pillow inspected pixels only; it did not resize, crop, sharpen, retouch or generate artwork.

The crown occupies roughly the upper quarter of the icon, and the silhouette remains naturally narrower than its height. Transparency means the head covers only about half the square. Removing the remaining margin cannot make this design appear as dense as an opaque square mark.

## Measured native-size comparison

The `before/` PNGs are the exact rc.5 exports from baseline commit `6c5248e1cd6033ce0b24bd9db97621431908fa4e`. Bounds below are measured from pixels, not inferred from the file dimensions. “Visible” means alpha ≥ 8/255; “solid core” means alpha ≥ 240/255. Alpha-weighted area is the sum of normalized alpha divided by the square's pixel count.

| Export | Visible bounds, previous → full fit | Solid-core bounds, previous → full fit | Solid-core pixels, previous → full fit | Alpha-weighted area, previous → full fit |
|---|---|---|---|---|
| 16px | 14 × 16 → 14 × 16px | 12 × 14 → 12 × 14px | 103 → 104 | 50.153% → 52.292% |
| 32px | 28 × 32 → 28 × 32px | 25 × 29 → 26 × 30px | 455 → 477 | 50.468% → 52.518% |
| 48px | 40 × 48 → 41 × 48px | 38 × 46 → 38 × 47px | 1090 → 1140 | 50.584% → 52.614% |
| 64px | 54 × 64 → 54 × 64px | 50 × 61 → 52 × 63px | 1940 → 2019 | 50.506% → 52.619% |

At 16px, the faint outside extent and solid-core rectangle remain the same size because of pixel rounding; the increased painted area is small. The 32px core gains one pixel in width and height. These observations support a modest improvement, not a large apparent-size claim.

`favicon-actual-sizes.png` and its self-contained HTML show the previous and current exports at native 16/32/48/64px sizes on light and dark surfaces. A separate row displays 16px at 8× and 32px at 4× using nearest-neighbor CSS rendering, explicitly labeled for pixel inspection. These are image-size samples, not fabricated browser-chrome screenshots. `favicon-verification.json` contains the source bounds, complete before/after measurements, hashes and empty browser-error list.

## Active URLs and compatibility

Shared public, administration and account markup uses installation-prefix-aware `app_url()` paths, followed by `?v=<VERSION>`:

- `images/icons/crowned-gorilla-full-fit/favicon.ico`, containing exact 16/32/48/64px PNG frames.
- `images/icons/crowned-gorilla-full-fit/favicon-16.png`, `favicon-32.png`, `favicon-48.png`, `favicon-64.png`, `favicon-192.png` and `favicon-512.png`.
- `images/icons/crowned-gorilla-full-fit/apple-touch-icon.png`, 180 × 180px.

The distinct directory gives the updated artwork a fresh URL identity even when an older favicon path has been cached. The existing flat `images/icons/` PNG/ICO URLs remain present as byte-identical compatibility copies of the full-fit exports. `images/favicon.svg` retains its URL with an embedded full-fit 256px PNG. All PNG exports have genuinely transparent corners; no opaque badge/background is added.

The shared markup advertises each native size, so browsers can select an appropriate supported icon. Browser-specific cache/bookmark behavior and search-engine refresh timing remain outside this asset export's control. The headless export does not establish which icon a particular browser's tab UI will choose.

**Do not add a root `favicon.ico` to this same-schema release.** The pinned `admin/upgrades/RecoveryEngine.php` root-file allowlist excludes it, and `app/UpdatePackage.php` would reject such a payload before installation. Adding it only to `tools/package.py` would break the guarded upgrade path. The root access rules, recovery kernel, package root allowlist and database migrations are unchanged. The declared installation-aware icon URLs and existing compatibility paths provide the current supported fallback set.

## Reproduction

Run from the repository root with Playwright, Pillow and Chromium available:

```sh
python3 tools/build_favicons.py \
  --browser /absolute/path/to/chromium \
  --review-dir design/favicon/maximum-fit-rc6 \
  --previous-icons design/favicon/maximum-fit-rc6/before
```

The builder writes the native PNG and ICO set to both the active child directory and legacy flat directory, plus the compatibility SVG. The PHP host needs no browser, Python or image-generation service. For an isolated check, pass both `--output-dir /temporary/icons` and `--legacy-svg /temporary/favicon.svg` as well as a temporary review directory.

The source hash, PNG transparency and dimensions, ICO frame contents, compatibility-copy equality, prefix-aware link decoding, and guarded-update path acceptance are verified separately. Installed-application URL checks and release-package evidence belong in the rc.6 application verification record.
