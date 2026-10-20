# Transparent crowned-gorilla favicon — 1.1.0-rc.6

The approved sculpted gold crowned-gorilla head supplies the favicon. Its wordmark is removed and the surrounding pixels are genuinely transparent. The source bitmap is unchanged from rc.4/rc.5; rc.6 adjusts its export framing and active URL identity.

## Maximum proportional framing

The complete visible crown-to-chin silhouette now fits 100% of the square viewport, preserving its natural proportions, crown and chin. This removes the previous 1% margin at each vertical edge. The dimensional scale change from 98% fit is approximately 2.04%; the earlier artwork already filled nearly the entire height.

| Native export | Visible bounds before → after | Solid-core bounds before → after | Alpha-weighted occupied area before → after |
|---|---|---|---|
| 16px | 14 × 16 → 14 × 16px | 12 × 14 → 12 × 14px | 50.153% → 52.292% |
| 32px | 28 × 32 → 28 × 32px | 25 × 29 → 26 × 30px | 50.468% → 52.518% |
| 48px | 40 × 48 → 41 × 48px | 38 × 46 → 38 × 47px | 50.584% → 52.614% |
| 64px | 54 × 64 → 54 × 64px | 50 × 61 → 52 × 63px | 50.506% → 52.619% |

Visible bounds use alpha ≥ 8/255; solid core uses alpha ≥ 240/255. The 16px outer and core rectangles do not gain a pixel because of rounding, although painted area increases slightly. The 32px core gains one pixel in width and height. These measurements support a modest improvement, not a dramatic increase in apparent size.

The crown takes roughly the upper quarter of this naturally tall design, and transparency leaves some of the square empty. A complete crowned head cannot have the same occupied area as an opaque square without changing its design. The browser controls its own icon area; larger exported dimensions provide resolution choices rather than larger browser controls. The native-size review board is explicitly an image sample, not a screenshot of browser tab chrome.

Technical references: [MDN link element](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/link), [HTML icon link specification](https://html.spec.whatwg.org/multipage/links.html#rel-icon). [Google Search Central](https://developers.google.com/search/docs/appearance/favicon-in-search) describes separate search-result processing; a website update does not force an immediate search icon refresh.

## Shipped files and cache identity

The active directory is **`images/icons/crowned-gorilla-full-fit/`**.

| File within active directory | Size | Purpose |
|---|---|---|
| `favicon-16.png` | 16 × 16 | Native small icon. |
| `favicon-32.png` | 32 × 32 | Higher-density small icon. |
| `favicon-48.png` | 48 × 48 | Additional native size. |
| `favicon-64.png` | 64 × 64 | Additional native size. |
| `favicon.ico` | 16/32/48/64 frames | Multi-size ICO. |
| `apple-touch-icon.png` | 180 × 180 | Touch/bookmark source. |
| `favicon-192.png` | 192 × 192 | Larger icon source. |
| `favicon-512.png` | 512 × 512 | Large reusable source. |

`views/favicon.php` shares one link set across public and shared administration/account layouts. Every URL uses the configured installation prefix and release version. The new directory changes the active URL as well as its version query, so pages no longer request the old icon paths. Browsers and previously saved bookmarks may retain separate caches; this export does not establish which resource every browser's tab UI will choose.

The earlier flat `images/icons/` files remain as byte-identical aliases to the full-fit exports. `images/favicon.svg` keeps its existing compatibility URL with matching 256px embedded artwork. No old cumulative path is removed.

A new root `favicon.ico` is intentionally omitted: the pinned recovery kernel's root-file allowlist does not permit it. Adding that payload without changing recovery would make installed releases reject the upgrade. The supported installation-aware link set preserves the existing upgrade contract without modifying pinned recovery or access files.

## Source and reproducibility

The unchanged 1254 × 1254 transparent master and original image-generation edit prompt are retained under `design/favicon/`. Source SHA-256 is `3f3232cd3c314b3f227c1c598c8bf75f01c6b04b779cb6ece1bc7f51334f66bc`. The master was isolated from the approved logo with the built-in image tool; the export pipeline does not retouch its pixels.

`tools/build_favicons.py` reads alpha bounds for centering, renders the unchanged bitmap through the square SVG viewBox `-13 15 1223 1223` in Chromium, captures exact native PNG sizes and packs those PNG bytes into ICO frames. Almost invisible source noise below alpha 8/255 does not add padding. Pillow is used for pixel inspection only.

With Playwright, Chromium and Pillow available:

```sh
python3 tools/build_favicons.py \
  --browser /absolute/path/to/chromium \
  --review-dir design/favicon/maximum-fit-rc6 \
  --previous-icons design/favicon/maximum-fit-rc6/before
```

An isolated independent export reproduced all shipped icon bytes. The 50 scoped source/export/compatibility checks, measured comparison and native-size review are in `design/favicon/maximum-fit-rc6/`. The earlier top-level rc.4 review remains historical evidence of the 98% export. Installed-application URL checks belong in current `TEST_RESULTS.md` and the delivery receipt.

The PHP host needs no image build, Python, Node or external image service. Ready-to-use assets are included in the cumulative Full ZIP. Installed rc.1 through rc.5 use the normal guarded same-schema Browser upgrades path when its checks pass. Keep the original database, configuration, owner and uploads; no migration or reinstall is required.
