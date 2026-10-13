<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$user = Spirit\Auth::requireOwner();
$milestones = [
 '0.1.0' => ['Foundation & browser installation', 'Implemented; host acceptance pending'],
 '0.2.0' => ['Selected public visual design', 'Implemented; production art pending'],
 '0.3.0' => ['Content, band, media & press kit', 'Implemented; advanced editing pending'],
 '0.4.0' => ['Music library & persistent audio player', 'Implemented'],
 '0.5.0' => ['Shows & external venue ticket links', 'Implemented'],
 '0.6.0' => ['Booking, email & newsletter', 'Implemented; provider acceptance pending'],
 '0.7.0' => ['Merchandise, cart & customer accounts', 'Planned'],
 '0.8.0' => ['Authorize.net payments & orders', 'Planned'],
 '0.9.0' => ['Integration, maintenance & hardening', 'Planned'],
 '1.0.0-rc.N' => ['Staging & owner acceptance', 'Planned'],
 '1.0.0' => ['First complete accepted release', 'Not released'],
];
Spirit\View::start('Release roadmap', 'admin', 'roadmap', $user);
?>
<div class="page-heading"><div><p class="eyebrow">PROJECT / RELEASE ROADMAP</p><h1>One vision.<br><em>Measured progress.</em></h1><p class="lead">A milestone is complete only when its workflow is implemented and verified.</p></div></div>
<section class="panel roadmap"><?php foreach ($milestones as $version => [$name, $status]): ?><div class="roadmap-row"><strong>v<?= e($version) ?></strong><span><?= e($name) ?></span><span class="pill"><?= e($status) ?></span></div><?php endforeach; ?></section>
<div class="notice info">The approved design remains fixed. Show tickets link to the venue’s seller. Merchandise checkout is a separate module. No native ticket sales, fabricated band content, or additional redesigns.</div>
<?php Spirit\View::end(); ?>
