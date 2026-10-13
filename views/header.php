<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · Spiritual Gorillaz</title>
<meta name="theme-color" content="#06101c">
<link rel="icon" href="<?= e(app_url('images/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(app_url('assets/css/app.css')) ?>">
<script src="<?= e(app_url('assets/js/app.js')) ?>" defer></script>
</head>
<body class="<?= e($area) ?>">
<a class="skip" href="#main">Skip to content</a>
<?php if ($area === 'admin'): ?>
<button class="menu-toggle" type="button" aria-controls="sidebar" aria-expanded="false">☰ Menu</button>
<aside class="sidebar" id="sidebar">
<a class="brand" href="<?= e(app_url('admin/')) ?>"><span class="brand-crown" aria-hidden="true">♛</span><span>SPIRITUAL<br><strong>GORILLAZ</strong></span></a>
<p class="nav-label">BACKSTAGE / OWNER</p>
<nav aria-label="Administration">
<?php foreach (['dashboard' => ['Overview', 'admin/'], 'settings' => ['Website settings', 'admin/settings.php'], 'account' => ['Account & security', 'admin/account.php'], 'installation' => ['Installation health', 'admin/installation.php'], 'roadmap' => ['Release roadmap', 'admin/roadmap.php']] as $key => [$label, $path]): ?>
<a <?= $active === $key ? 'aria-current="page"' : '' ?> href="<?= e(app_url($path)) ?>"><?= e($label) ?><span aria-hidden="true">↗</span></a>
<?php endforeach; ?>
</nav>
<div class="sidebar-bottom"><span class="pill">FOUNDATION BUILD</span><p>v<?= e(\Spirit\Paths::version()) ?></p><a href="<?= e(app_url()) ?>" target="_blank" rel="noopener">Open public site ↗</a><form action="<?= e(app_url('admin/logout.php')) ?>" method="post"><?= csrf_field() ?><button class="text-button" type="submit">Sign out</button></form></div>
</aside>
<div class="workspace"><header class="topbar"><span>Spiritual Gorillaz <span class="muted">/ Administration</span></span><span class="user-chip"><?= e($user['name'] ?? 'Owner') ?></span></header>
<?php else: ?>
<header class="simple-header"><a class="brand" href="<?= e(app_url()) ?>"><span class="brand-crown" aria-hidden="true">♛</span><span>SPIRITUAL<br><strong>GORILLAZ</strong></span></a><span class="pill"><?= $area === 'install' ? 'BROWSER INSTALLER' : ($area === 'login' ? 'OWNER ACCESS' : 'MUSIC · PEOPLE · PURPOSE') ?></span></header>
<div class="public-wrap">
<?php endif; ?>
<main id="main" class="main">
