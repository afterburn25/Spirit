<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\ProductEditForm as F;
$count=0;
function verify(bool $ok,string $label):void{global $count;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$count++;}
$empty=F::values(null);
verify($empty===['name'=>'','description'=>'','size_guide'=>'','category_id'=>0,'image_ids'=>[],'slug'=>'','revision'=>1],'New product has only authored fields and revision defaults');
$product=['data'=>['name'=>'Saved','description'=>'Original','size_guide'=>'Cold wash','category_id'=>3,'image_ids'=>[7,8]],'slug'=>'saved','revision'=>12];
$original=F::values($product);
verify($original['name']==='Saved'&&$original['revision']===12&&$original['image_ids']===[7,8],'Normal GET keeps saved product values');
$posted=['name'=>'New <script>text</script>','slug'=>'invalid slug','description'=>'Unsent description','size_guide'=>'Changed','category_id'=>'4','revision'=>'9','image_ids'=>['8','10'],'approved'=>'1','csrf'=>'PRIVATE TOKEN','password'=>'PRIVATE PASSWORD'];
$values=F::values($product,$posted);
foreach(['name','slug','description','size_guide','category_id','revision'] as $field){verify($values[$field]===$posted[$field],'Rejected save preserves authored '.$field);}
verify($values['revision']==='9','Rejected stale save retains its old revision rather than adopting latest');
verify($values['image_ids']===[8,10],'Multi-select IDs retain submitted selection');
verify(!isset($values['csrf'],$values['password'],$values['approved']),'Secrets and publication approval are not reflected');
verify(!str_contains(json_encode($values),'PRIVATE TOKEN')&&!str_contains(json_encode($values),'PRIVATE PASSWORD'),'Unexpected private field values cannot enter correction fields');
verify(!str_contains(e($values['name']),'<script>'),'Form output escaping preserves text without active markup');
verify(F::values($product,[])['image_ids']===[],'Absent multi-select is a deliberately cleared selection');
verify(F::values($product,['image_ids'=>[]])['image_ids']===[],'Explicit empty multi-select is retained');
verify(F::values($product,['image_ids'=>['7','7','8']])['image_ids']===[7,8],'Repeated image IDs are deduplicated');
foreach([['image_ids'=>['../file']],['image_ids'=>['-1']],['image_ids'=>[['bad']]],['image_ids'=>['0']],['image_ids'=>['4294967295']],['image_ids'=>['99999999999999999999']],['image_ids'=>'7'],['image_ids'=>array_fill(0,9,'7')]] as $input){verify(F::values($product,$input)['image_ids']===[7,8],'Invalid image input cannot become a selected arbitrary resource');}
foreach(['name','description','slug','revision','category_id','size_guide'] as $field){verify(F::values($product,[$field=>['bad']])[$field]===$original[$field],'Array input cannot leak into '.$field.' rendering');}
verify(F::values($product)===$original,'Projection leaves its original product unchanged');
echo $count." product form checks passed.\n";
