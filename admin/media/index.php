<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot();$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 Spirit\Security::checkPost();
 try{if(post('rights_confirmed')!=='1'){throw new InvalidArgumentException('Confirm that the band has permission to use this file.');}$id=Spirit\Media::upload(is_array($_FILES['file']??null)?$_FILES['file']:[],post('alt_text'),(int)$user['id']);flash('File #'.$id.' uploaded privately. Select it in a content item to use it.');go('admin/media/');}catch(InvalidArgumentException|RuntimeException $e){$error=$e->getMessage();}
}
$files=Spirit\Media::all();Spirit\View::start('Media library','admin','library',$user);
?>
<div class="page-heading"><div><p class="eyebrow">ASSETS / MEDIA LIBRARY</p><h1>One library.<br><em>Your identity.</em></h1><p class="lead">Images, authorized recordings and press documents. Private until published content references them.</p></div></div>
<?php Spirit\View::notice($error);?>
<form method="post" enctype="multipart/form-data" class="panel stack"><?=csrf_field()?><h2>Upload a file</h2><div class="field-grid"><label>File<input type="file" name="file" required accept="image/jpeg,image/png,image/webp,audio/mpeg,audio/ogg,audio/wav,application/pdf"></label><label>Alternative text for images<input name="alt_text" maxlength="300" placeholder="Describe what the image shows"></label></div><label class="checkbox-label"><input type="checkbox" name="rights_confirmed" value="1" required> The band has permission to publish this file.</label><p class="small muted">Images/PDF: 12 MB maximum. Audio: 50 MB maximum. Hosting limits may be lower. Image metadata is removed by re-encoding; PDF downloads are attachments, not embedded documents.</p><button class="button" type="submit">Upload privately ↑</button></form>
<section class="media-admin-grid"><?php foreach($files as $file):?><article class="panel media-file"><?php if($file['kind']==='image'):?><img src="<?=e(Spirit\Media::url((int)$file['id']))?>" alt="<?=e($file['alt_text'])?>" loading="lazy"><?php else:?><div class="file-type"><?=e(strtoupper($file['kind']))?></div><?php endif;?><h2><?=e($file['original_name'])?></h2><p>#<?=(int)$file['id']?> · <?=e(number_format((int)$file['byte_size']/1024,1))?> KB</p><span class="pill"><?=Spirit\Content::publicMedia((int)$file['id'])?'USED PUBLICLY':'PRIVATE'?></span><a class="text-link" href="<?=e(Spirit\Media::url((int)$file['id']).'&download=1')?>">Download file ↓</a></article><?php endforeach;?></section>
<?php if(!$files):?><section class="panel empty-state"><h2>Your media belongs here.</h2><p>Start with approved band photographs, original artwork and authorized recordings. No demo files are published.</p></section><?php endif;Spirit\View::end();?>
