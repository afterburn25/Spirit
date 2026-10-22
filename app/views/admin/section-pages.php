<?php declare(strict_types=1); ?>
<section class="panel section-directory" aria-labelledby="section-directory-title">
<p class="eyebrow">YOUR DESIGNED PAGES</p><h2 id="section-directory-title">Place your artwork and introductions.</h2>
<p>Use the existing Page editor for these fixed layouts. Upload approved artwork in Media library, save a private draft, then preview the real section before publishing. This does not replace its releases, events, products or forms.</p>
<div class="section-directory-grid">
<?php foreach (Spirit\SectionPages::SECTIONS as $slug => [$name,$path,$view,$width,$description]): $matches=Spirit\SectionPages::records($slug); ?>
<article class="section-directory-card"><h3><?=e($name)?></h3><p class="small muted"><?=e($description)?></p><p class="small">URL name: <code><?=e($slug)?></code></p>
<?php if (!$matches): ?><span class="pill">DEFAULT PRESENTATION</span><a class="text-link" href="<?=e(app_url('admin/content/edit.php?kind=page&placement='.$slug))?>">Prepare <?=e(strtolower($name))?> draft →</a>
<?php else: foreach ($matches as $match): ?>
<a class="text-link" href="<?=e(app_url('admin/content/edit.php?kind=page&id='.(int)$match['id']))?>">Edit <?=e($match['title'])?> →</a><span class="small muted"><?=$match['is_published']?($match['has_draft']?'Live with draft changes':'Published'):'Private draft'?></span>
<?php endforeach; if(count($matches)>1): ?><p class="small">More than one saved page refers to this section. Review both records and their URL names; nothing is merged automatically.</p><?php endif; endif; ?>
<a class="small text-link" href="<?=e(app_url($path))?>" target="_blank" rel="noopener">View public section ↗</a>
</article><?php endforeach; ?>
</div><p class="small muted">Layout headings stay in the approved design. The saved title identifies the record; search/share fields control metadata; subtitle, page text, feature image and focus control the section content. Existing private drafts are never published by opening this directory.</p>
</section>
