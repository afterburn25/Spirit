<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\Auth::requirePermission('dashboard');
$categories=Spirit\HelpCatalog::categories();
$selected=is_string($_GET['topic']??null)?$_GET['topic']:'getting-started';
if(!array_key_exists($selected,$categories)){$selected='getting-started';}
Spirit\View::start('Help center','admin','help',$user);
?>
<link rel="stylesheet" href="<?=e(app_url('assets/css/admin-help.css'))?>">
<div class="page-heading"><div><p class="eyebrow">BACKSTAGE / KNOWLEDGE BASE</p><h1>How can we <em>help?</em></h1><p class="lead">Clear, practical guidance for managing Spiritual Gorillaz. Browse a subject to follow the steps and open the right admin screen.</p></div><span class="pill">HELP · v<?=e(Spirit\Paths::version())?></span></div>
<div class="help-layout"><nav class="panel help-topics" aria-label="Help topics"><h2>Browse topics</h2>
<?php foreach($categories as $key=>[$title,$intro,$guides]):?>
<a href="<?=e(app_url('admin/help/?topic='.$key))?>" <?=$key===$selected?'aria-current="page"':''?>><strong><?=e($title)?></strong><span><?=count($guides)?> guides</span></a>
<?php endforeach;?></nav>
<section class="help-articles" aria-labelledby="help-topic-title">
<div class="panel help-intro"><p class="eyebrow">GUIDES / <?=e(strtoupper($selected))?></p><h2 id="help-topic-title"><?=e($categories[$selected][0])?></h2><p><?=e($categories[$selected][1])?></p></div>
<?php foreach($categories[$selected][2] as $number=>[$title,$instructions,$path]):?>
<article class="panel help-guide"><span class="help-guide-number"><?=str_pad((string)($number+1),2,'0',STR_PAD_LEFT)?></span><div><h3><?=e($title)?></h3><p><?=e($instructions)?></p><a class="text-link" href="<?=e(app_url($path))?>">Open this admin screen →</a></div></article>
<?php endforeach;?>
<div class="panel help-note"><h3>Keeping your guides current</h3><p>New admin features should include updated Help Center instructions and a verified destination before release. If an item is unavailable to your staff role, ask the site owner to review permissions.</p></div>
</section></div>
<?php Spirit\View::end();
