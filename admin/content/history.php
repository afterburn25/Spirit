<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot('content.manage');header('Cache-Control: private, no-store');header('X-Robots-Tag: noindex, nofollow');header('Referrer-Policy: no-referrer');
try{$id=Spirit\Input::integer($_GET,'id',1,PHP_INT_MAX,1);$page=Spirit\Input::integer($_GET,'page',1,1000000,1);$selected=Spirit\Input::integer($_GET,'revision',0,4294967295,0);}catch(InvalidArgumentException $e){http_response_code(400);exit('Invalid history selection.');}
$item=Spirit\Content::get($id);if(!$item){http_response_code(404);exit('Content history unavailable.');}$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 Spirit\Security::checkPost();
 try{
  if(post('action')!=='restore'){throw new InvalidArgumentException('Choose the restore action.');}
  $source=Spirit\Input::integer($_POST,'source_revision',1,4294967295,1);$expected=Spirit\Input::integer($_POST,'revision',1,4294967294,1);
  Spirit\Content::save($item['kind'],['id'=>$id,'revision'=>$expected],(int)$user['id'],'restore',$source);
  flash('Revision '.$source.' restored as a new private draft. The live website has not changed. Review and explicitly publish when ready.');go('admin/content/edit.php?kind='.$item['kind'].'&id='.$id);
 }catch(InvalidArgumentException|RuntimeException $e){$error=$e instanceof PDOException?'The restore could not complete. No changes were committed.':$e->getMessage();}
}
$history=Spirit\ContentHistory::listing($id,$page);$selected=$selected?:($history['items'][0]['revision']??0);$record=$selected?Spirit\ContentHistory::get($id,(int)$selected):null;
if($selected&&!$record){http_response_code(404);exit('This revision is not available for the selected content item.');}
$current=['title'=>$item['title'],'slug'=>$item['slug'],'data'=>$item['data'],'sort_order'=>$item['sort_order']];$differences=$record?Spirit\ContentHistory::difference($item['kind'],$record['document'],$current):[];
$link=static fn(int $p):string=>app_url('admin/content/history.php?'.http_build_query(['id'=>$id,'page'=>$p,'revision'=>$selected]));
Spirit\View::start('Version history','admin',$item['kind'],$user);
?>
<div class="page-heading"><div><p class="eyebrow">CONTENT / VERSION HISTORY</p><h1>Keep the story.<br><em>Recover the details.</em></h1><p class="lead"><?=e($item['title'])?> · Current draft revision <?=$item['revision']?></p></div><a class="text-link" href="<?=e(app_url('admin/content/edit.php?kind='.$item['kind'].'&id='.$id))?>">← Back to editor</a></div>
<?php Spirit\View::notice($error);?>
<div class="notice info">Restoring creates a new private draft. It does not publish, unpublish, or erase a later revision. Older revisions without a recorded sort order keep the current order.</div>
<section class="panel"><h2>Saved versions</h2><div class="table-scroll" role="region" aria-label="Saved content versions" tabindex="0"><table class="content-table"><thead><tr><th>Revision</th><th>Action</th><th>Saved by</th><th>Saved UTC</th><th>Compare</th></tr></thead><tbody><?php foreach($history['items'] as $row):?><tr><td>#<?=(int)$row['revision']?></td><td><?=e(ucfirst($row['action']))?></td><td><?=e($row['actor_name']??'Former staff member')?></td><td><?=e($row['created_at'])?></td><td><a class="text-link" <?=$selected==(int)$row['revision']?'aria-current="true"':''?> href="<?=e(app_url('admin/content/history.php?'.http_build_query(['id'=>$id,'revision'=>$row['revision'],'page'=>$history['page']])))?>">Compare #<?=(int)$row['revision']?></a></td></tr><?php endforeach;?></tbody></table></div><nav class="content-pagination" aria-label="Revision history pages"><?php if($history['page']>1):?><a class="button secondary" href="<?=e($link($history['page']-1))?>">← Previous</a><?php endif;?><span><?=$history['total']?> versions · Page <?=$history['page']?> of <?=$history['pages']?></span><?php if($history['page']<$history['pages']):?><a class="button secondary" href="<?=e($link($history['page']+1))?>">Next →</a><?php endif;?></nav></section>
<?php if($record):?><section class="panel"><p class="eyebrow">COMPARE SAVED #<?=(int)$record['revision']?> WITH CURRENT DRAFT #<?=$item['revision']?></p><h2>Review before restoring.</h2><div class="revision-comparison"><?php foreach($differences as $difference):?><article class="revision-field <?=$difference['changed']?'is-changed':''?>"><h3><?=e($difference['label'])?> <span class="pill"><?=$difference['changed']?'CHANGED':'UNCHANGED'?></span></h3><div><section><h4>Saved revision</h4><pre><?=e($difference['saved']===''?'(empty)':$difference['saved'])?></pre></section><section><h4>Current draft</h4><pre><?=e($difference['current']===''?'(empty)':$difference['current'])?></pre></section></div></article><?php endforeach;?></div><form method="post" class="stack"><?=csrf_field()?><input type="hidden" name="action" value="restore"><input type="hidden" name="source_revision" value="<?=(int)$record['revision']?>"><input type="hidden" name="revision" value="<?=$item['revision']?>"><p>A newer edit will prevent this operation until you reload. Referenced media must still be available and of the correct type.</p><button class="button">Restore as private draft</button></form></section><?php endif;?>
<?php Spirit\View::end();?>
