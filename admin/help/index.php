<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\Auth::requirePermission('dashboard');
$categories=Spirit\HelpCatalog::categories();
$selected=is_string($_GET['topic']??null)?$_GET['topic']:'getting-started';
if(!array_key_exists($selected,$categories)){$selected='getting-started';}
$query=is_string($_GET['q']??null)?trim($_GET['q']):'';
if(mb_strlen($query)>100){$query=mb_substr($query,0,100);}
$matches=[];
if($query!==''){
 foreach($categories as $categoryKey=>[$categoryTitle,$intro,$guides]){
  foreach($guides as $guide){if(mb_stripos($categoryTitle.' '.$guide[0].' '.$guide[1],$query)!==false){$matches[]=[$categoryKey,$categoryTitle,$guide];}}
 }
}
Spirit\View::start('Help center','admin','help',$user);
?>
<link rel="stylesheet" href="<?=e(app_url('assets/css/admin-help.css'))?>">
<div class="page-heading"><div><p class="eyebrow">BACKSTAGE / KNOWLEDGE BASE</p><h1>How can we <em>help?</em></h1><p class="lead">Clear guidance for managing Spiritual Gorillaz. Search the instructions, explore a topic, and open the right administration screen.</p></div><span class="pill">HELP · v<?=e(Spirit\Paths::version())?></span></div>
<form class="panel help-search" action="<?=e(app_url('admin/help/'))?>" method="get" role="search">
<label for="help-query">Search help articles</label><div class="help-search-row"><input id="help-query" type="search" name="q" maxlength="100" value="<?=e($query)?>" placeholder="Search: biography, photo, backup, SMTP, tickets…"><button class="button" type="submit">Search guides</button><?php if($query!==''):?><a class="button secondary" href="<?=e(app_url('admin/help/'))?>">Clear</a><?php endif;?></div></form>
<div class="help-layout"><nav class="panel help-topics" aria-label="Help topics"><h2>Browse topics</h2>
<?php foreach($categories as $key=>[$title,$intro,$guides]):?>
<a href="<?=e(app_url('admin/help/?topic='.$key))?>" <?=($query===''&&$key===$selected)?'aria-current="page"':''?>><strong><?=e($title)?></strong><span><?=count($guides)?> guides</span></a>
<?php endforeach;?></nav>
<section class="help-articles" aria-labelledby="help-topic-title">
<?php if($query!==''):?>
<div class="panel help-intro"><p class="eyebrow">HELP SEARCH</p><h2 id="help-topic-title">Results for “<?=e($query)?>”</h2><p role="status"><?=count($matches)?> matching guides. Search checks article titles and instructions.</p></div>
<?php if(!$matches):?><div class="panel help-note"><h3>No articles found</h3><p>Try shorter words such as “image”, “member”, “email”, “show” or “backup”, or browse the topics on the left.</p></div><?php endif;?>
<?php foreach($matches as $index=>[$key,$category,$guide]):[$title,$instructions,$path]=$guide;?>
<article class="panel help-guide"><span class="help-guide-number"><?=str_pad((string)($index+1),2,'0',STR_PAD_LEFT)?></span><div><p class="eyebrow"><?=e($category)?></p><h3><?=e($title)?></h3><p><?=e($instructions)?></p><a class="text-link" href="<?=e(app_url($path))?>">Open this admin screen →</a></div></article>
<?php endforeach;?>
<?php else:?>
<div class="panel help-intro"><p class="eyebrow">GUIDES / <?=e(strtoupper($selected))?></p><h2 id="help-topic-title"><?=e($categories[$selected][0])?></h2><p><?=e($categories[$selected][1])?></p></div>
<?php foreach($categories[$selected][2] as $number=>[$title,$instructions,$path]):?>
<article class="panel help-guide"><span class="help-guide-number"><?=str_pad((string)($number+1),2,'0',STR_PAD_LEFT)?></span><div><h3><?=e($title)?></h3><p><?=e($instructions)?></p><a class="text-link" href="<?=e(app_url($path))?>">Open this admin screen →</a></div></article>
<?php endforeach;?>
<?php endif;?>
<div class="panel help-note"><h3>Documentation that stays current</h3><p>New administrator features must include current Help Center instructions and tested destinations before their release. Screens display links only to pages your account can access.</p></div>
</section></div>
<?php Spirit\View::end();
