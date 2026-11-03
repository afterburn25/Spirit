<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot('content.manage');$kind=is_string($_GET['kind']??null)?$_GET['kind']:'page';$id=0;$error=null;
try{$kind=Spirit\Input::choice($_GET,'kind',array_keys(Spirit\ContentSchema::kinds()),'page');$id=Spirit\Input::integer($_GET,'id',0,PHP_INT_MAX,0);$fields=Spirit\ContentSchema::fields($kind);}catch(InvalidArgumentException $e){http_response_code(400);exit('Unknown content section.');}
$item=$id?Spirit\Content::get($id):null;if($id&&(!$item||$item['kind']!==$kind)){http_response_code(404);exit('Content not found.');}
$values=$item?array_merge($item['data'],['title'=>$item['title'],'slug'=>$item['slug'],'sort_order'=>$item['sort_order'],'revision'=>$item['revision']]):['sort_order'=>0,'revision'=>0];
if(!$id&&isset($_GET['placement'])){
 try{$placement=Spirit\Input::choice($_GET,'placement',array_keys(Spirit\SectionPages::SECTIONS),'home');}catch(InvalidArgumentException $e){http_response_code(400);exit('Invalid page placement.');}
 if($kind!=='page'){http_response_code(400);exit('Only Pages can use section placement.');}
 if($_SERVER['REQUEST_METHOD']==='GET'){
  $matches=Spirit\SectionPages::records($placement);
  if(count($matches)===1){go('admin/content/edit.php?kind=page&id='.(int)$matches[0]['id']);}
  if(count($matches)>1){go('admin/content/?kind=page&q='.rawurlencode($placement));}
  $values+=['slug'=>$placement,'title'=>Spirit\SectionPages::SECTIONS[$placement][0]];
 }
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 Spirit\Security::checkPost();$values=array_filter($_POST,fn($v)=>is_string($v));
 try{
  $submitted=array_merge($_POST,['id'=>$id]);
  foreach($fields as $field=>$definition){
   if($definition[1]!=='media:image'||!isset($_FILES['direct_'.$field])||!is_array($_FILES['direct_'.$field]))continue;
   $file=$_FILES['direct_'.$field];
   if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)continue;
   if(post('image_rights_confirmed')!=='1')throw new InvalidArgumentException('Confirm image publishing rights before uploading.');
   $mediaId=Spirit\Media::upload($file,post('direct_alt_'.$field),(int)$user['id']);
   $submitted[$field]=(string)$mediaId;
  }
  $saved=Spirit\Content::save($kind,$submitted,(int)$user['id'],post('action'));flash(match(post('action')){'publish'=>'Published. Referenced media is now publicly available.','unpublish'=>'Unpublished. Your draft is preserved.',default=>'Private draft saved. The current published version has not changed.'});go('admin/content/edit.php?kind='.$kind.'&id='.$saved);}catch(InvalidArgumentException|RuntimeException $e){$error=$e instanceof PDOException?'The save could not complete. No changes were committed.':$e->getMessage();}
}
Spirit\View::start($id?'Edit '.$item['title']:'Add '.$kind,'admin',$kind,$user);
?>
<div class="page-heading"><div><p class="eyebrow">CONTENT / <?=e(Spirit\ContentSchema::kinds()[$kind])?></p><h1><?= $id?'Edit the':'Set the'?> <em>details.</em></h1></div><div><a class="text-link" href="<?=e(app_url('admin/content/?kind='.$kind))?>">← Back to list</a> <?php if($kind==='member'):?><a class="text-link" href="<?=e(app_url('admin/help/?topic=members'))?>">Band member help ↗</a><?php elseif($kind==='page'):?><a class="text-link" href="<?=e(app_url('admin/help/?topic=pages'))?>">Page editing help ↗</a><?php endif;?></div></div>
<?php Spirit\View::notice($error);if($kind==='page'):?><link rel="stylesheet" href="<?=e(app_url('assets/css/section-editor.css'))?>"><?php require Spirit\Paths::root().'/app/views/admin/section-artwork.php';endif;?>
<form method="post" enctype="multipart/form-data" class="editor-layout" data-edit-guard data-edit-guard-dirty="<?=$error?'true':'false'?>"><?=csrf_field()?><input type="hidden" name="revision" value="<?=e($values['revision']??0)?>"><div class="panel stack"><?php if($kind==='page'):?><p class="small muted">The published layout and existing content remain unchanged until you publish. Set the optional headline and short heading to replace default text. A button on Music, Shows, Media or Merchandise needs both its label and an HTTPS destination; other existing buttons retain their default destination when left blank.</p><?php endif;?><label>Title<input name="title" value="<?=e($values['title']??'')?>" maxlength="180" required></label><label>URL name<input name="slug" value="<?=e($values['slug']??'')?>" pattern="[a-z0-9][a-z0-9-]{0,89}" maxlength="90" placeholder="Generated from title when empty"></label>
<?php $slotLabels=$kind==='page'?Spirit\SectionPages::slotLabels((string)($values['slug']??'')):[];foreach($fields as $key=>$definition):if(str_starts_with($key,'slot_')){if(!isset($slotLabels[$key])){continue;}$definition[0]=$slotLabels[$key];}?><div class="field-stack"><?php Spirit\ContentAdmin::field($key,$definition,$values[$key]??'');?></div><?php endforeach;?><label class="checkbox-label"><input type="checkbox" name="image_rights_confirmed" value="1"> I confirm I have permission to use any new image uploaded here.</label><p class="small muted">Direct image uploads are private until the associated content is published. JPEG, PNG, WebP; up to 50 MB subject to host limits.</p></div>
<aside class="panel editor-actions"><p class="eyebrow">PUBLISHING</p><h2>Keep control.</h2><p>Draft changes do not alter the published page. Publishing makes its selected files public too.</p><label>Sort order<input type="number" name="sort_order" value="<?=e($values['sort_order']??0)?>" min="-100000" max="100000"></label><p class="small muted">Lower numbers appear first.</p><button class="button wide" name="action" value="save">Save private draft</button><button class="button outline wide" name="action" value="publish">Publish approved content</button>
<?php if($id):?><a class="button outline wide" href="<?=e(app_url('admin/content/history.php?id='.$id))?>">Version history &amp; restore</a><a class="button outline wide" target="_blank" rel="noopener" href="<?=e(app_url('admin/content/preview.php?id='.$id))?>">Preview saved draft ↗</a><?php endif;?>
<?php if($item&&$item['is_published']):?><button class="text-button" name="action" value="unpublish">Remove from public site</button><?php endif;?><p class="small muted">Only use approved names, photographs, music, dates and documents. Save before previewing.</p></aside></form>
<?php if($kind==='member'&&$id):?>
<section class="panel" aria-labelledby="remove-member-title">
<h2 id="remove-member-title">Remove this band member</h2>
<p>Removing a member takes their profile off the website and content list. Existing media files are retained, and this cannot be undone through the editor.</p>
<form action="<?=e(app_url('admin/content/delete.php'))?>" method="post">
<?=csrf_field()?>
<input type="hidden" name="id" value="<?=$id?>">
<input type="hidden" name="kind" value="member">
<input type="hidden" name="revision" value="<?=e($values['revision']??0)?>">
<label><input type="checkbox" name="confirm" value="yes" required> I understand this member will be removed from the website.</label>
<button class="button outline" type="submit">Remove band member</button>
</form></section>
<?php endif;?>
<?php Spirit\View::end();?>
