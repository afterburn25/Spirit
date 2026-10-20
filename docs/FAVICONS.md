# Transparent crowned-gorilla favicon

The user-selected dimensional gold crowned-gorilla head supplies the favicon in 1.1.0-rc.4. The wordmark is removed, and the surrounding pixels are genuinely transparent.

## Maximum apparent size

Browser tabs determine their own icon area; a larger source image cannot enlarge browser controls. Traditional tab favicons commonly use a16px square. The useful change is to remove excess padding while preserving the full subject. This set centers the visible crown-to-chin silhouette and fits it to 98% of a square. Its16px and 32px rendered forms occupy the full vertical pixel extent after antialiasing. Natural side clearance preserves the head's proportions.

Source: [MDN favicon glossary](https://developer.mozilla.org/en-US/docs/Glossary/Favicon). Google Search has separate processing and eligibility guidance: [Google Search Central](https://developers.google.com/search/docs/appearance/favicon-in-search). A site update does not force an immediate search-result icon refresh.

## Shipped files

| File | Size | Purpose |
|---|---|---|
|`images/icons/favicon-16.png`|16×16|Native small tab icon.|
|`images/icons/favicon-32.png`|32×32|Higher-density small icon.|
|`images/icons/favicon-48.png`|48×48|Additional icon size.|
|`images/icons/favicon-64.png`|64×64|Additional icon size.|
|`images/icons/favicon.ico`|16/32/48/64 frames|Multi-size ICO fallback.|
|`images/icons/apple-touch-icon.png`|180×180|Touch/bookmark icon source.|
|`images/icons/favicon-192.png`|192×192|Larger icon source.|
|`images/icons/favicon-512.png`|512×512|Large reusable icon source.|
|`images/favicon.svg`|256px embedded artwork|Retained compatibility URL with the same head.|

`views/favicon.php` provides one link set for public and shared administration/account headers. Every URL uses the configured installation prefix and release version. New URLs request the updated artwork; previously saved bookmarks or external caches may update on their own schedule.

## Source and reproducibility

The 1254×1254 transparent master and the exact image-generation edit prompt are retained under `design/favicon/`. The master was isolated from the approved sculpted gold logo with the built-in image-generation tool. It is not retouched by the export pipeline.

`tools/build_favicons.py` reads alpha bounds for centering, renders the unchanged master through a square SVG viewport in Chromium, captures PNGs at their exact target sizes, and packs those PNG bytes into ICO. Alpha values below8/255 do not determine the visible silhouette's centering, preventing almost invisible source noise from adding padding. The full visible crown, fur and chin are retained.

On a development machine with Playwright, Chromium and Pillow available:

```sh
python3 tools/build_favicons.py --browser /absolute/path/to/chromium --review-dir /tmp/spirit-favicon-review
```

The shipped PHP website does not need Python, Node, an image build or an external image service. Source tools and original artwork are development materials; ready-to-use icons are already in the Full ZIP.

## Updating an installed site

Use the complete rc.4 package through the existing guarded same-schema Browser upgrades workflow from rc.1/rc.2/rc.3 when its normal checks pass. Keep the original database, configuration, owner and uploads. No migration or reinstall is needed for this favicon change. The separate favicon ZIP is supplied for reuse; it does not replace the complete upgrade package.
