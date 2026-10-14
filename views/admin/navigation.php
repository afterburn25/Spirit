<?php
// $menu is permission-filtered by AdminNavigation; preferences never select links.
$navScope = Spirit\AdminNavigation::preferenceScope($user, Spirit\Paths::basePath(), Spirit\Paths::config()['secret'] ?? '');
?>
<button class="admin-menu-toggle" type="button" aria-controls="sidebar" aria-expanded="false" hidden><span aria-hidden="true">☰</span> Menu</button>
<button class="admin-nav-backdrop" type="button" tabindex="-1" aria-label="Close administration menu" hidden></button>
<aside class="sidebar grouped-sidebar" id="sidebar" data-nav-scope="<?=e($navScope)?>">
    <div class="admin-sidebar-heading">
        <a class="brand" href="<?=e(app_url('admin/'))?>"><span class="brand-crown" aria-hidden="true">♛</span><span>SPIRITUAL<br><strong>GORILLAZ</strong></span></a>
        <button class="admin-sidebar-close" type="button" aria-label="Close administration menu" hidden>×</button>
        <p class="nav-label">BACKSTAGE / <?=e(strtoupper(Spirit\Permissions::ROLES[$user['role']] ?? 'STAFF'))?></p>
        <div class="admin-nav-tools" hidden><button type="button" data-nav-action="expand">Expand all</button><button type="button" data-nav-action="collapse">Collapse all</button></div>
        <p class="admin-nav-memory" id="admin-nav-memory" role="status" hidden>Menu choices stay in this browser.</p>
    </div>
    <div class="admin-nav-scroll" id="admin-nav-scroll">
        <nav class="admin-navigation" aria-label="Administration">
            <?php if($menu['overview']):?><a class="admin-overview" href="<?=e(app_url('admin/'))?>" <?=$active==='dashboard'?'aria-current="page"':''?>><?=Spirit\AdminNavigation::icon('overview')?><span class="admin-nav-label">Overview</span></a><?php endif;?>
            <?php foreach($menu['groups'] as $group):?>
            <details class="admin-nav-group<?=$group['current']?' has-current':''?>" data-nav-group="<?=e($group['id'])?>" open>
                <summary aria-controls="admin-links-<?=e($group['id'])?>"><?=Spirit\AdminNavigation::icon($group['icon'])?><span class="admin-nav-label"><?=e($group['label'])?></span><span class="admin-nav-count" aria-hidden="true"><?=count($group['links'])?></span><span class="admin-nav-chevron" aria-hidden="true"></span><?php if($group['current']):?><span class="nav-sr-only">Current section</span><?php endif;?></summary>
                <ul class="admin-nav-links" id="admin-links-<?=e($group['id'])?>">
                    <?php foreach($group['links'] as $link):?><li><a data-nav-link="<?=e($link['key'])?>" href="<?=e(app_url($link['path']))?>" <?=$link['current']?'aria-current="page"':''?>><span class="admin-nav-label"><?=e($link['label'])?></span><?php if($link['current']):?><span class="admin-current-dot" aria-hidden="true"></span><?php endif;?></a></li><?php endforeach;?>
                </ul>
            </details>
            <?php endforeach;?>
        </nav>
    </div>
    <div class="sidebar-bottom"><div class="admin-build"><span class="pill">RELEASE CANDIDATE</span><span>v<?=e(Spirit\Paths::version())?></span></div><div class="admin-sidebar-actions"><a href="<?=e(app_url())?>" target="_blank" rel="noopener">Public site ↗</a><form action="<?=e(app_url('admin/logout.php'))?>" method="post"><?=csrf_field()?><button class="text-button" type="submit">Sign out</button></form></div></div>
</aside>
