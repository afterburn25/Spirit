<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{AdminNavigation,Permissions};
$count=0;
function check(bool $ok,string $message):void{global $count;if(!$ok){throw new RuntimeException($message);}echo 'PASS '.$message."\n";$count++;}
function keys(array $menu):array{$keys=[];foreach($menu['groups'] as $group){foreach($group['links'] as $link){$keys[]=$link['key'];}}return $keys;}
$owner=['id'=>1,'active'=>1,'role'=>'owner'];$menu=AdminNavigation::build($owner,'page');
check($menu['overview'],'Owner keeps the direct overview destination');
check(array_column($menu['groups'],'id')===['content','music','analytics','audience','commerce','settings','system'],'Owner navigation has a dedicated Analytics category');
check(count(keys($menu))===36 && count(array_unique(keys($menu)))===36,'All 36 grouped destinations plus the direct Overview are unique');
$byGroup = array_column($menu['groups'], null, 'id');
check(array_column($byGroup['analytics']['links'],'key')===['analytics','analytics-visitors','analytics-geography'],'Dashboard, visitor explorer and geography belong to Analytics');
check(array_column($byGroup['audience']['links'],'key')===['bookings','subscribers','mail','communication'],'Bookings and communication contains no analytics destinations');
foreach($menu['groups'] as $group){
    check(count($group['links'])>0,'No empty visible category: '.$group['id']);
    foreach($group['links'] as $link){
        check(str_starts_with($link['path'],'admin/')&&!str_contains($link['path'],'..')&&is_file(dirname(__DIR__).'/'.explode('?',$link['path'])[0].(str_ends_with(explode('?',$link['path'])[0],'/')?'index.php':'')),'Menu destination is an existing local administration route: '.$link['key']);
        $activeMenu=AdminNavigation::build($owner,$link['key']);$active=0;
        foreach($activeMenu['groups'] as $g){foreach($g['links'] as $l){$active+=(int)$l['current'];}}
        check($active===1 && $activeMenu['label']===$link['label'],'Exactly one active link and matching context for '.$link['key']);
    }
}
$original=['page','release','track','event','member','gallery','video','press','library','bookings','communication','mail','subscribers','store','orders','settings','discovery','account','updates','installation','launch','staff','activity','backups','upgrades','maintenance','roadmap'];
check(!array_diff($original,keys($menu)),'Every previous sidebar destination remains represented');
foreach(['content_editor'=>['page','member','library','gallery','video','press','release','track','event','account'],'store_manager'=>['library','store','store-categories','orders','account']] as $role=>$expected){
    $user=['id'=>2,'active'=>1,'role'=>$role];$m=AdminNavigation::build($user,'payment-settings');$actual=keys($m);sort($actual);sort($expected);
    check($actual===$expected,'Role has only permitted destinations: '.$role);
    check(!array_intersect(['analytics','analytics-visitors','analytics-geography'],$actual),'Every analytics destination remains absent from staff navigation: '.$role);
    check($m['section']==='Administration','A forbidden active key does not reveal privileged context: '.$role);
    foreach($m['groups'] as $g){check(count($g['links'])>0,'No empty staff category after server filtering');}
}
foreach([null,['id'=>1,'active'=>0,'role'=>'owner'],['id'=>3,'active'=>1,'role'=>'customer'],['id'=>4,'active'=>1,'role'=>'unknown']] as $user){
    $m=AdminNavigation::build($user,'account');check(!$m['overview']&&$m['groups']===[],'Anonymous/inactive/unknown identity cannot receive administration links');
}
$key=AdminNavigation::preferenceScope($owner,'/band','test-only-secret');
check((bool)preg_match('/^[a-f0-9]{32}$/D',$key),'Preference namespace is an opaque bounded identifier');
check($key===AdminNavigation::preferenceScope($owner,'/band','test-only-secret'),'Same installation and identity retain their preference scope');
check($key!==AdminNavigation::preferenceScope(['id'=>2,'active'=>1,'role'=>'owner'],'/band','test-only-secret'),'Two owners do not share saved menu preferences');
check($key!==AdminNavigation::preferenceScope($owner,'/other','test-only-secret'),'Two installations on the same origin use separate scopes');
check($key!==AdminNavigation::preferenceScope($owner,'/band','new-install-secret'),'A replacement installation receives a separate scope');
check($key!==AdminNavigation::preferenceScope(['id'=>1,'active'=>1,'role'=>'store_manager'],'/band','test-only-secret'),'Role changes do not inherit another capability layout');
check(!str_contains($key,'test-only-secret')&&!str_contains($key,'/band'),'Preference namespace exposes neither secret nor path');
check(!str_contains(AdminNavigation::icon('<script>'),'script')&&str_contains(AdminNavigation::icon('content'),'aria-hidden="true"'),'Only fixed decorative icon markup is rendered');
echo $count." administration navigation unit checks passed.\n";
