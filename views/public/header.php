<?php
$meta=Spirit\Metadata::build($title,$description,$canonical,$metaRecord??$record??null,$isPreview||$canonical===null);
$brandMarkup=Spirit\Branding::mark($settings);
$storeEnabled=Spirit\Catalog::options()['enabled'];
$primaryLinks=[
    'home'=>['Home',''],
    'music'=>['Music','music/'],
    'shows'=>['Shows','shows/'],
    'band'=>['The band','band/'],
    'media'=>['Media','media/'],
    'shop'=>['Merch','shop/'],
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php Spirit\Metadata::tags($meta,$settings['site_name']);?>
<meta name="theme-color" content="#050c14">
<link rel="icon" type="image/svg+xml" href="<?=e(app_url('images/favicon.svg'))?>">
<link rel="stylesheet" href="<?=e(app_url('assets/css/public.css'))?>">
<link rel="stylesheet" href="<?=e(app_url('assets/css/public-forms.css'))?>">
<script defer src="<?=e(app_url('assets/js/public.js'))?>"></script>
<link rel="stylesheet" href="<?=e(app_url('assets/css/commerce.css'))?>">
<script defer src="<?=e(app_url('assets/js/commerce.js'))?>"></script>
<script defer src="<?=e(app_url('assets/js/quality.js'))?>"></script>
<link rel="stylesheet" href="<?=e(app_url('assets/css/quality.css'))?>">
<link rel="stylesheet" href="<?=e(app_url('assets/css/public-access.css'))?>">
<link rel="stylesheet" href="<?=e(app_url('assets/css/section-pages.css'))?>">
<link rel="stylesheet" href="<?=e(app_url('assets/css/branding.css'))?>">
<script defer src="<?=e(app_url('assets/js/branding.js'))?>"></script>
<link rel="stylesheet" href="<?=e(app_url('assets/css/concept-header.css'))?>">
<link rel="stylesheet" href="<?=e(app_url('assets/css/home-reference-polish.css'))?>">
<script defer src="<?=e(app_url('assets/js/header-search.js'))?>"></script>
</head>
<body class="public-site">
<a class="skip-link" href="#site-content">Skip to content</a>
<div class="site-shell">
<header class="site-header">
    <a class="wordmark" href="<?=e(app_url())?>" aria-label="<?=e($settings['site_name'])?> home"><?=$brandMarkup?></a>
    <button type="button" class="nav-toggle" aria-controls="public-nav" aria-expanded="false"><span aria-hidden="true">☰</span><span>Menu</span></button>
    <nav class="public-nav" id="public-nav" aria-label="Main navigation">
        <span class="nav-primary">
        <?php foreach($primaryLinks as $key=>[$label,$path]):if($key==='shop'&&!$storeEnabled&&$active!=='shop')continue;?>
            <a href="<?=e(app_url($path))?>" <?=$active===$key?'aria-current="page"':''?>><?=e($label)?></a>
        <?php endforeach;?>
        </span>
        <span class="nav-actions">
            <a href="<?=e(app_url('booking/'))?>" <?=$active==='booking'?'aria-current="page"':''?> class="nav-book">Book the band</a>
            <button type="button" class="nav-search-toggle" aria-controls="site-search-panel" aria-expanded="false" aria-label="Open site search">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.75"></circle><path d="m15.7 15.7 5 5"></path></svg>
            </button>
        </span>
    </nav>
    <div class="site-search-panel" id="site-search-panel" hidden>
        <div class="site-search-head">
            <form action="<?=e(app_url('pages/search.php'))?>" method="get" role="search">
                <label class="site-search-label" for="site-search-input">Search Spiritual Gorillaz</label>
                <div class="site-search-row">
                    <svg class="site-search-field-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.75"></circle><path d="m15.7 15.7 5 5"></path></svg>
                    <input id="site-search-input" name="q" type="search" maxlength="120" autocomplete="off" placeholder="Search Spiritual Gorillaz...">
                    <button class="site-search-submit" type="submit" aria-label="Submit search"><span aria-hidden="true">→</span></button>
                </div>
            </form>
            <button type="button" class="site-search-close" aria-label="Close site search"><span aria-hidden="true">×</span></button>
        </div>
        <p class="site-search-popular-title">Popular searches</p>
        <nav class="site-search-popular" aria-label="Popular searches">
            <a href="<?=e(app_url('music/'))?>"><span class="popular-icon" aria-hidden="true">♫</span><span>Music</span><span class="popular-arrow" aria-hidden="true">›</span></a>
            <a href="<?=e(app_url('shows/'))?>"><span class="popular-icon" aria-hidden="true">◇</span><span>Shows</span><span class="popular-arrow" aria-hidden="true">›</span></a>
            <a href="<?=e(app_url('band/'))?>"><span class="popular-icon" aria-hidden="true">♙</span><span>The Band</span><span class="popular-arrow" aria-hidden="true">›</span></a>
            <?php if($storeEnabled||$active==='shop'):?><a href="<?=e(app_url('shop/'))?>"><span class="popular-icon" aria-hidden="true">♛</span><span>Merch</span><span class="popular-arrow" aria-hidden="true">›</span></a><?php endif;?>
            <a href="<?=e(app_url('booking/'))?>"><span class="popular-icon" aria-hidden="true">▣</span><span>Book the Band</span><span class="popular-arrow" aria-hidden="true">›</span></a>
        </nav>
    </div>
</header>
<main id="site-content" tabindex="-1" data-page="<?=e($active)?>">
<template data-site-brand><a class="wordmark" href="<?=e(app_url())?>" aria-label="<?=e($settings['site_name'])?> home"><?=$brandMarkup?></a></template>
<?php if($isPreview):?><div class="preview-banner">PRIVATE PREVIEW · Saved draft only. This page is not public.</div><?php endif;?>
