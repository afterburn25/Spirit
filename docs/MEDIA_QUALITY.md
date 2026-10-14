# Responsive media and keyboard quality

Development0.9.0-dev.7. TEST_RESULTS.md records execution evidence. The approved layout is unchanged; these are real component/delivery changes, not alternative concepts.

## Image delivery

Published or authorized-preview image markup offers320/640/960/1440-pixel width candidates below the original size, plus the original. The browser chooses using srcset/sizes. Width/height reserve the original ratio. Small images are never upscaled or relabeled high-resolution. Original downloads and full-size gallery viewing retain their original bytes. Below-fold images are lazy; selected lead images are eager/high-priority.

Every request goes through the existing publication/role checks before reading generated cache or returning304. Unpublishing also withdraws derivatives. Copies live only in account-private cache under a fixed image-v1 checksum/width namespace. WebP is used where supported by GD, otherwise the original image format. Alpha is preserved. A single nonblocking file lock bounds concurrent GD allocations; busy requests use the original. Generation checks a conservative memory budget, source identity, output bytes/dimensions and private modes before atomic publication. Denied/unsafe cache locations, unavailable codecs or insufficient memory fall back to the valid original, not a broken image. No arbitrary resize dimensions or paths are accepted.

Original image uploads also have a conservative decode-memory check. A host with a small memory limit may require a smaller-resolution input even within the20-megapixel maximum. This is not an ownership/ACL bypass. Generated copies are optional caches, not backed-up source media; recognized old copies and abandoned temporary files participate in conservative retention. Source media, unknown names and the generation lock are preserved. Originals remain required even when a generated cache exists.

Representation-specific ETags, HEAD and byte-range responses use the delivered bytes. An unmatched If-Range requests a full representation, preventing mixed versions. Public images revalidate rather than staying readable indefinitely after unpublishing. Authorization cannot recall bytes a visitor already downloaded.

## Keyboard and form behavior

The gallery dialog has an accessible name/description, returns focus to its opener, and clears its hidden full-size image source after close. Audio controls expose queue availability, pressed track state and readable seek time. Closing returns focus to the active track or main content. A measured player-height inset and focus handling prevent the fixed player from covering keyboard targets. Page navigation exposes busy/loaded status and retains real playback.

Server validation summaries receive keyboard focus. The enhancement never reads or records sensitive field values. Ordinary Escape in admin fields no longer redirects focus to a closed menu. Phone form text is at least16 CSS pixels. Reduced motion and forced-color focus/borders are retained. These targeted changes do not constitute full WCAG certification.

## Reproducible budgets and remaining review

media_quality_integration.py uses a generated textured RGBA fixture to measure actual original/downsampled bytes; media_quality_browser.py verifies browser selection and a130,000-byte uncompressed first-party CSS/JS budget on its fixture page at1440/390/320px. This is not a field LCP/INP/CLS score or a universal bandwidth-reduction promise. Real artwork, recordings, devices, hosting and approved content still need acceptance. Payment network/SDK testing remains synthetic unless explicitly verified against a merchant sandbox.

Primary references consulted2026-10-14: PHP imagecopyresampled/imagesavealpha/imagewebp manuals (https://www.php.net/manual/en/function.imagecopyresampled.php ; https://www.php.net/manual/en/function.imagesavealpha.php ; https://www.php.net/manual/en/function.imagewebp.php), web.dev responsive image guidance (https://web.dev/learn/design/responsive-images), and W3C focus-not-obscured/target-size criteria (https://www.w3.org/WAI/WCAG22/Understanding/focus-not-obscured-minimum.html ; https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html).
