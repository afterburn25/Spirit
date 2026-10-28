<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot('content.manage');
try{$result=Spirit\Content::search($_GET);}catch(InvalidArgumentException $e){http_response_code(400);exit('Invalid content filters.');}
$items=$result['items'];$filter=$result['filter'];$kind=$filter['kind'];$label=Spirit\ContentSchema::kinds()[$kind];
$link=static fn(int $page):string=>app_url('admin/content/?'.http_build_query(array_merge($filter,['page'=>$page])));
Spirit\View::start($label,'admin',$kind,$user);
?>
<div class="page-heading"><div><p class="eyebrow">CONTENT / <?=e($label)?></p><h1>Your <?=e(strtolower($label))?>.<br><em>Your story.</em></h1><p class="lead">Find a draft, review its history, and publish only when the band approves it.</p></div><a class="button" href="<?=e(app_url('admin/content/edit.php?kind='.$kind))?>">Add <?=e($kind==='event'?'show':$kind)?> +</a></div>
<?php Spirit\View::notice(); ?>
<?php if($kind==='member'):?>
<section class="panel" aria-labelledby="member-guide-title">
<h2 id="member-guide-title">Manage your band lineup</h2>
<p>Add as many members as needed. Enter each name, musical role, approved portrait and full biography. Set the display order using the Sort order field (lower numbers appear first). Save drafts privately, preview, and publish when ready. A published member appears on the Band page as a portrait on the left and biography on the right.</p>
<div class="button-row"><a class="button" href="<?=e(app_url('admin/content/edit.php?kind=member'))?>">Add a band member</a><a class="button secondary" href="<?=e(app_url('admin/media/'))?>">Manage photographs</a><a class="text-link" href="<?=e(app_url('band/'))?>" target="_blank" rel="noopener">View the Band page ↗</a></div>
<?php if(Spirit\MemberOnboarding::canImport()):?>
<details class="panel" style="margin-top:1rem"><summary>Bring the existing lineup into the editor</summary>
<p>The previously displayed confirmed names and instruments can be imported as published, editable profiles. No portraits or biographies are invented. This option appears only when no member records, including deleted ones, exist.</p>
<form action="<?=e(app_url('admin/content/import-lineup.php'))?>" method="post"><?=csrf_field()?>
<label><input type="checkbox" name="confirm" value="yes" required> Import Chad Lindsey, Ron Green and Charlie Savell as editable profiles.</label>
<button class="button outline" type="submit">Import existing lineup</button></form></details>
<?php endif;?>
</section>
<?php endif;?>
<?php if($kind==='page'):?><link rel="stylesheet" href="<?=e(app_url('assets/css/section-editor.css'))?>"><?php require Spirit\Paths::root().'/app/views/admin/section-pages.php'; endif;?>
<section class="panel"><form method="get" class="content-filters"><input type="hidden" name="kind" value="<?=e($kind)?>"><label>Search title or URL name<input type="search" name="q" value="<?=e($filter['q'])?>" maxlength="100"></label><label>Visibility<select name="state"><?php foreach(['all'=>'All content','private'=>'Private drafts','published'=>'Published','changed'=>'Live with draft changes'] as $value=>$text):?><option value="<?=e($value)?>" <?=$filter['state']===$value?'selected':''?>><?=e($text)?></option><?php endforeach;?></select></label><label>Sort by<select name="sort"><?php foreach(['position'=>'Display order','updated'=>'Recently updated','title'=>'Title'] as $value=>$text):?><option value="<?=e($value)?>" <?=$filter['sort']===$value?'selected':''?>><?=e($text)?></option><?php endforeach;?></select></label><button class="button">Apply filters</button><a class="text-link" href="<?=e(app_url('admin/content/?kind='.$kind))?>">Clear</a></form>
<p class="small muted" role="status"><?=number_format($result['total'])?> matching item(s) · Page <?=$filter['page']?> of <?=$result['pages']?></p>
<div class="table-scroll" role="region" aria-label="Content results" tabindex="0"><table class="content-table"><thead><tr><th>Title</th><th>Visibility</th><th>Order</th><th>Last updated</th><th>Manage</th></tr></thead><tbody>
<?php foreach($items as $item):?><tr><td><strong><?=e($item['title'])?></strong><small><?=e($item['slug'])?></small></td><td><span class="pill"><?=$item['is_published']?($item['has_draft']?'LIVE + DRAFT':'PUBLISHED'):'PRIVATE DRAFT'?></span></td><td><?=$item['sort_order']?></td><td><?=e($item['updated_at'])?> UTC</td><td><a class="text-link" href="<?=e(app_url('admin/content/edit.php?kind='.$kind.'&id='.$item['id']))?>">Edit →</a><br><a class="text-link" href="<?=e(app_url('admin/content/history.php?id='.$item['id']))?>">History</a></td></tr><?php endforeach;?>
<?php if(!$items):?><tr><td colspan="5"><div class="empty-state"><h2>No matching content.</h2><p>Adjust the filters or add your first approved item. No invented content is seeded into this section.</p></div></td></tr><?php endif;?></tbody></table></div>
<nav class="content-pagination" aria-label="Content result pages"><?php if($filter['page']>1):?><a class="button secondary" rel="prev" href="<?=e($link($filter['page']-1))?>">← Previous</a><?php endif;?><span>Page <?=$filter['page']?> of <?=$result['pages']?></span><?php if($filter['page']<$result['pages']):?><a class="button secondary" rel="next" href="<?=e($link($filter['page']+1))?>">Next →</a><?php endif;?></nav></section>
<?php Spirit\View::end();?>
