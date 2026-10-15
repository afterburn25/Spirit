<?php declare(strict_types=1); ?>
<link rel="stylesheet" href="<?=e(app_url('assets/css/branding.css'))?>">
<div class="page-heading"><div><p class="eyebrow">SETTINGS / SITE IDENTITY</p><h1>Your logo.<br><em>Your identity.</em></h1><p class="lead">Prepare the approved logo privately, then publish it across the public header and footer.</p></div><a class="text-link" href="<?=e(app_url('admin/settings.php'))?>">← Website settings</a></div>
<?php Spirit\View::notice($error); ?>
<div class="branding-comparison">
<?php foreach (['published_logo_id'=>'Public now', 'draft_logo_id'=>'Saved private draft'] as $key=>$label): ?>
<section class="panel branding-card"><p class="eyebrow"><?=e($label)?></p><div class="branding-swatch" aria-label="<?=e($label)?> logo"><?php $logo=Spirit\Branding::image($state[$key]); echo $logo!==''?$logo:'<strong>'.e($settings['site_name']).'</strong>'; ?></div><p class="small muted"><?=$state[$key]>0?'Selected media #'.(int)$state[$key]:'Text wordmark; no image selected.'?></p></section>
<?php endforeach; ?>
</div>
<form method="post" class="editor-layout" id="branding-editor" data-edit-guard data-edit-guard-dirty="<?=$error?'true':'false'?>"><?=csrf_field()?><input type="hidden" name="revision" value="<?=e($values['revision'])?>"><input type="hidden" name="restore_epoch" value="<?=e($values['restore_epoch'])?>">
<section class="panel stack"><h2>Select the <em>approved artwork.</em></h2>
<?php Spirit\ContentAdmin::field('logo_id', ['Logo image', 'media:image', []], $values['logo_id'] ?: ''); ?>
<p class="small muted">Use a transparent PNG or WebP, or a JPEG prepared for the dark background. The full image is contained without cropping or stretching. Choosing no file uses the existing text wordmark. SVG uploads remain unsupported.</p>
<a class="text-link" href="<?=e(app_url('admin/media/'))?>" target="_blank" rel="noopener">Open Media library ↗</a>
<p class="small">The home link keeps the website name as its accessible label. A new upload remains private until it is published here or referenced by other published content.</p>
</section>
<aside class="panel stack"><p class="eyebrow">DRAFT / PREVIEW / PUBLISH</p><h2>Review before going live.</h2><button class="button" name="action" value="save">Save private logo draft</button><a class="button outline" target="_blank" rel="noopener" href="<?=e(app_url('admin/branding/preview.php'))?>">Preview saved logo ↗</a><p class="small muted">Preview uses the real homepage and saved draft logo. It does not publish content. Save your selection before opening it.</p><label class="checkbox-label"><input type="checkbox" name="approved" value="1" data-edit-guard-ignore><span>I approve the selected logo for public use.</span></label><button class="button outline" name="action" value="publish">Publish approved logo</button>
<?php if ($state['published_logo_id']>0): ?><button class="text-button" name="action" value="withdraw">Withdraw published logo</button><?php endif; ?>
<p class="small muted">Withdrawal restores the text wordmark and preserves the draft. An image still referenced by a published page or product stays public. Older open forms cannot silently replace newer changes.</p></aside>
</form>
