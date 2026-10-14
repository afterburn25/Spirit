<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot('content.manage');$kind=is_string($_GET['kind']??null)?$_GET['kind']:'page';
try {$items=Spirit\Content::listing($kind);$label=Spirit\ContentSchema::kinds()[$kind];}catch(InvalidArgumentException $e){http_response_code(400);exit('Unknown content section.');}
Spirit\View::start($label,'admin',$kind,$user);
?>
<div class="page-heading"><div><p class="eyebrow">CONTENT / <?=e($label)?></p><h1>Your <?=e(strtolower($label))?>.<br><em>Your story.</em></h1><p class="lead">Save a private draft, preview it, then publish when the band approves it.</p></div><a class="button" href="<?=e(app_url('admin/content/edit.php?kind='.$kind))?>">Add <?=e($kind==='event'?'show':$kind)?> +</a></div>
<?php Spirit\View::notice(); ?>
<?php if($kind==='page'):?><div class="notice info">Use the URL names <strong>home</strong>, <strong>band</strong>, <strong>booking</strong>, and <strong>press</strong> for the corresponding public sections. Other pages appear at their own page URL.</div><?php endif;?>
<section class="panel"><div class="table-scroll"><table class="content-table"><thead><tr><th>Title</th><th>Visibility</th><th>Order</th><th>Last updated</th><th>Manage</th></tr></thead><tbody>
<?php foreach($items as $item):?><tr><td><strong><?=e($item['title'])?></strong><small><?=e($item['slug'])?></small></td><td><span class="pill"><?=$item['is_published']?($item['has_draft']?'LIVE + DRAFT':'PUBLISHED'):'PRIVATE DRAFT'?></span></td><td><?=$item['sort_order']?></td><td><?=e($item['updated_at'])?> UTC</td><td><a class="text-link" href="<?=e(app_url('admin/content/edit.php?kind='.$kind.'&id='.$item['id']))?>">Edit →</a></td></tr><?php endforeach;?>
<?php if(!$items):?><tr><td colspan="5"><div class="empty-state"><h2>Nothing published by accident.</h2><p>Add your first <?=e($kind==='event'?'show':$kind)?>. No invented content is seeded into this section.</p></div></td></tr><?php endif;?></tbody></table></div></section>
<?php Spirit\View::end();?>
