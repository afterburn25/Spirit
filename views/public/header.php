<?php
$meta = Spirit\Metadata::build($title, $description, $canonical, $metaRecord ?? $record ?? null, $isPreview || $canonical === null);
$brandMarkup = Spirit\Branding::mark($settings);
$storeEnabled = Spirit\Catalog::options()['enabled'];
$assetVersion = rawurlencode(Spirit\Paths::version());
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php Spirit\Metadata::tags($meta, $settings['site_name']); ?>
<meta name="theme-color" content="#050c14">
<script src="<?=e(app_url('assets/js/js-capability.js'))?>?v=<?=e($assetVersion)?>"></script>
<link rel="icon" type="image/svg+xml" href="<?=e(app_url('images/favicon.svg'))?>">
<link rel="stylesheet" href="<?=e(app_url('assets/css/site.css'))?>?v=<?=e($assetVersion)?>">
<script defer src="<?=e(app_url('assets/js/site.js'))?>?v=<?=e($assetVersion)?>"></script>
<script defer src="<?=e(app_url('assets/js/analytics.js'))?>?v=<?=e($assetVersion)?>"></script>
</head>
<body class="public-site">
<a class="skip-link" href="#site-content">Skip to content</a>
<div class="site-shell">
<header class="site-header">
  <a class="wordmark" href="<?=e(app_url())?>" aria-label="<?=e($settings['site_name'])?> home"><?=$brandMarkup?></a>
  <button type="button" class="nav-toggle" aria-controls="public-nav" aria-expanded="false"><span aria-hidden="true">☰</span><span>Menu</span></button>
  <nav class="public-nav" id="public-nav" aria-label="Main navigation">
    <?php foreach (['home'=>['Home',''], 'music'=>['Music','music/'], 'shows'=>['Shows','shows/'], 'band'=>['The band','band/'], 'media'=>['Media','media/'], 'shop'=>['Merch','shop/'], 'booking'=>['Book the band','booking/']] as $key => [$label, $path]):
      if ($key === 'shop' && !$storeEnabled && $active !== 'shop') continue;
    ?>
    <a href="<?=e(app_url($path))?>" <?=$active === $key ? 'aria-current="page"' : ''?> class="<?=$key === 'booking' ? 'nav-book' : ''?>"><?=e($label)?></a>
    <?php endforeach; ?>
  </nav>
  <div class="header-search" data-header-search>
    <button class="nav-search" type="button" aria-label="Open site search" aria-expanded="false" aria-controls="header-search-panel"><svg viewBox="0 0 24 24" width="24" height="24" focusable="false" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 5 5"></path></svg></button>
    <noscript><a class="button secondary" href="<?=e(app_url('pages/search.php'))?>">Search</a></noscript>
    <div class="header-search-panel" id="header-search-panel" hidden>
      <form class="header-search-dropdown-form" method="get" action="<?=e(app_url('pages/search.php'))?>" role="search">
        <label for="header-search-query">Search <?=e($settings['site_name'])?></label>
        <div class="header-search-fields">
          <input id="header-search-query" name="q" type="search" maxlength="100" autocomplete="off" placeholder="Music, shows, members, pages…" required>
          <select name="kind" aria-label="Search section">
            <option value="all">Everything</option>
            <option value="release">Music</option>
            <option value="event">Shows</option>
            <option value="member">Band members</option>
            <option value="page">Pages</option>
            <?php if ($storeEnabled): ?><option value="product">Merchandise</option><?php endif; ?>
          </select>
          <button class="button" type="submit">Search</button>
        </div>
        <p class="header-search-hint">Type a phrase and choose where to look.</p>
      </form>
    </div>
  </div>
</header>
<main id="site-content" tabindex="-1" data-page="<?=e($active)?>">
<?php if (is_array($analyticsPage ?? null)): ?><template data-analytics data-endpoint="<?=e($analyticsPage['endpoint'])?>" data-token="<?=e($analyticsPage['token'])?>"></template><?php endif; ?>
<template data-site-brand><a class="wordmark" href="<?=e(app_url())?>" aria-label="<?=e($settings['site_name'])?> home"><?=$brandMarkup?></a></template>
<?php if ($isPreview): ?><div class="preview-banner">PRIVATE PREVIEW · Saved draft only. This page is not public.</div><?php endif; ?>
