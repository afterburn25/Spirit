<?php $faviconVersion = rawurlencode(Spirit\Paths::version()); ?>
<link rel="icon" type="image/x-icon" sizes="16x16 32x32 48x48 64x64" href="<?=e(app_url('images/icons/crowned-gorilla-full-fit/favicon.ico'))?>?v=<?=e($faviconVersion)?>">
<?php foreach ([16, 32, 48, 64, 192, 512] as $faviconSize): ?>
<link rel="icon" type="image/png" sizes="<?=$faviconSize?>x<?=$faviconSize?>" href="<?=e(app_url('images/icons/crowned-gorilla-full-fit/favicon-' . $faviconSize . '.png'))?>?v=<?=e($faviconVersion)?>">
<?php endforeach; ?>
<link rel="apple-touch-icon" sizes="180x180" href="<?=e(app_url('images/icons/crowned-gorilla-full-fit/apple-touch-icon.png'))?>?v=<?=e($faviconVersion)?>">
<?php unset($faviconVersion, $faviconSize); ?>
