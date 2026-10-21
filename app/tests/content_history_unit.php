<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\{ContentHistory as H,ContentSchema as S};
$checks=0;
function check(bool $ok,string $label):void{global $checks;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$checks++;}
function rejects(callable $fn,string $label):void{try{$fn();}catch(InvalidArgumentException $e){check(true,$label);return;}check(false,$label);}
check(H::query([])===['kind'=>'page','q'=>'','state'=>'all','sort'=>'position','page'=>1],'Content filters have deterministic bounded defaults');
foreach([['page'=>'-1'],['page'=>'1.5'],['page'=>['1']],['page'=>'1000001'],['q'=>['x']],['q'=>str_repeat('x',101)],['q'=>"bad\0query"],['kind'=>['page']],['kind'=>'users'],['sort'=>'DROP'],['state'=>'secret']] as $bad){rejects(fn()=>H::query($bad),'Malformed content filters rejected');}
check(H::pattern('100%_!')==='%100!%!_!!%','SQL LIKE wildcard characters are literal search text');
check(H::query(['page'=>'42','q'=>' é '])['q']==='é','UTF-8 search is preserved and trimmed');
foreach(['{','null','[]','{"title":[],"slug":"x","data":{}}','{"title":"x","slug":"x","data":"bad"}'] as $bad){rejects(fn()=>H::document($bad),'Malformed revision snapshot is refused');}
$a=['title'=>'Old <script>','slug'=>'story','data'=>['body'=>'Before','image_id'=>7]];
$b=['title'=>'New','slug'=>'story','data'=>['body'=>'After','image_id'=>9],'sort_order'=>3];
$d=H::difference('page',$a,$b);
check($d[0]['changed']&&$d[0]['saved']==='Old <script>','Comparison stores plain text, not generated markup');
check(!$d[1]['changed'],'Unchanged fields are identified');
check($d[2]['saved']==='Not recorded in this older revision','Legacy sort metadata is not invented');
check(count($d)===count(S::fields('page'))+3,'Comparison covers declared fields only');
foreach([['body'=>['bad']],['subtitle'=>true],['image_id'=>[]],['focal'=>['right']],['image_id'=>'999999999999999999999999'],['body'=>"bad\xff"]] as $bad){rejects(fn()=>S::validate('page',$bad,false),'Typed content fields cannot silently erase data');}
check(S::validate('page',['image_id'=>7],false)['image_id']===7,'Stored integer media IDs survive revision restoration');
check(S::validate('track',['release_id'=>8,'audio_id'=>9],true)['release_id']===8,'Stored integer release and audio fields validate');
foreach(S::kinds() as $kind=>$label){$data=S::validate($kind,[],false);check(S::validate($kind,$data,false)===$data,'Saved document round-trips typed fields for '.$kind);}
echo $checks." content history unit checks passed.\n";
