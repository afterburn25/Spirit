<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\{Permissions,Staff};
$checks=0;
function check(bool $ok,string $label):void{global $checks;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$checks++;}
$owner=['role'=>'owner','active'=>1];$editor=['role'=>'content_editor','active'=>1];$store=['role'=>'store_manager','active'=>1];
foreach(['owner','dashboard','account','content.manage','media.manage','store.manage','orders.view','orders.fulfill'] as $p){check(Permissions::allows($owner,$p),'Owner capability: '.$p);}
foreach(['dashboard','account','content.manage','media.manage'] as $p){check(Permissions::allows($editor,$p),'Content editor capability: '.$p);}
foreach(['owner','store.manage','orders.view','orders.fulfill'] as $p){check(!Permissions::allows($editor,$p),'Content editor denied: '.$p);}
foreach(['dashboard','account','media.manage','store.manage','orders.view','orders.fulfill'] as $p){check(Permissions::allows($store,$p),'Store manager capability: '.$p);}
foreach(['owner','content.manage'] as $p){check(!Permissions::allows($store,$p),'Store manager denied: '.$p);}
foreach([null,[],['role'=>'owner','active'=>0],['role'=>'customer','active'=>1],['role'=>'editor','active'=>1],['role'=>'invented','active'=>1]] as $u){check(!Permissions::allows($u,'owner')&&!Permissions::allows($u,'content.manage'),'Unknown, suspended or unauthenticated account fails closed');}
check(!Permissions::allows($owner,'unknown-capability'),'Even owners cannot acquire an undefined capability');
foreach(array_keys(Permissions::ROLES) as $r){check(Staff::role(['role'=>$r])===$r,'Known role validates: '.$r);}
foreach(['admin','../owner','OWNER',[]] as $r){$bad=false;try{Staff::role(['role'=>$r]);}catch(Throwable $e){$bad=true;}check($bad,'Unknown or malformed role refused');}
echo $checks." staff permission unit checks passed.\n";
