<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot();$db=Spirit\Database::connect();$search=Spirit\Input::text($_GET,'q',80);$where=$search!==''?' WHERE a.action LIKE ?':'';$args=$search!==''?['%'.$search.'%']:[];
$q=$db->prepare('SELECT COUNT(*) FROM '.Spirit\Database::table('audit').' a'.$where);$q->execute($args);$pages=max(1,(int)ceil((int)$q->fetchColumn()/30));$page=max(1,min($pages,Spirit\Input::integer($_GET,'page',1,PHP_INT_MAX,1)));
$q=$db->prepare('SELECT a.action,a.created_at,u.name FROM '.Spirit\Database::table('audit').' a LEFT JOIN '.Spirit\Database::table('users').' u ON u.id=a.user_id'.$where.' ORDER BY a.id DESC LIMIT 30 OFFSET '.(($page-1)*30));$q->execute($args);$rows=$q->fetchAll();
Spirit\View::start('Activity history','admin','activity',$user);
?>
<div class="page-heading"><div><p class="eyebrow">BACKSTAGE / AUDIT</p><h1>A clear <em>history.</em></h1><p class="lead">Recorded website actions. Secrets, passwords and private message bodies are not displayed.</p></div></div><section class="panel"><form method="get" class="shop-filter"><label>Filter action<input name="q" maxlength="80" value="<?=e($search)?>"></label><button class="button" type="submit">Filter activity</button></form><div class="table-scroll"><table class="content-table"><thead><tr><th>Action</th><th>Staff</th><th>Time (UTC)</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?=e($row['action'])?></td><td><?=e($row['name']??'System')?></td><td><?=e($row['created_at'])?></td></tr><?php endforeach;?></tbody></table></div><?php Spirit\Storefront::pager(compact('page','pages'),'admin/activity/',['q'=>$search]);?></section><?php Spirit\View::end();?>
