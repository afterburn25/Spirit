# Media and keyboard checkpoint — 2026-10-14

Version **0.9.0-dev.7**. Current application **7c5582075b4b47c01e721c2c5e30d4f4a593acbb**, tree **575c50eb2bc689c777d1fefe565f5aaea4eff66d**. Full run **38525702729** passed; focused maintenance **38525702713** passed.

| Environment | Job |
|---|---|
| PHP 8.2 / MariaDB 11.4 | 115725403861 |
| PHP 8.4 / MySQL 8.4, including twelve Chromium workflows | 115725404023 |
| Actual Apache PHP 8.4 HTTPS access rules | 115725403606 |

Source artifact **11875622065**, archive SHA-256 **c91fc877c3375b38b1b89ca318c17d7af92f3c3b1d238a8633f0169bf6fff51a**, was verified after download. All 300 canonical tracked files were compared; the only local pending changes at comparison were the owner and installation documentation. Runtime, assets, schema and tests matched. The only application difference from the earlier green 238fa431 checkpoint is `admin/roadmap.php` wording.

Earlier full **38524917377** and focused **38524917358** passed for 238fa431. Its **99-PNG browser artifact 11875780837**, SHA-256 **6cf645c1bddf1f8d61ed66079afec0021f734d66cd8ec3acf4cf84de406a6ebd**, was downloaded and verified. All nine new image/player/error-feedback renders at 1440px, 390px and 320px were inspected. These are actual synthetic-test renders, not new mockups or approved band content. Full-page captures can place fixed player/skip controls at the scrolled viewport position; the browser tests separately verify actual viewport focus and overlap bounds.

The cumulative suite contains 442 pure assertions, 11 packaging tests, the real SQL/HTTP/TLS/recovery workflows, twelve Chromium suites and actual Apache HTTPS. Media tests preserve transparency and original bytes, enforce publication before derivative access/304, bound cache paths/widths/allocations, and cover corrupt/private/read-only/symlink/missing-source/retention cases. Browser tests use actual audio and observable native dialog effects, without changing security policy or faking state.

Generated fixture: original 412,565 bytes; 640px copy 21,306 bytes (94.8% reduction); tested desktop 960px copy 64,820 bytes. The first-party fixture asset budget is 130,000 bytes. These figures are not measured production-band performance. Final real content, master artwork, provider/host and broader quality/owner acceptance remain; no accepted v1.0 is implied.
