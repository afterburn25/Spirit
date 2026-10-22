<?php
declare(strict_types=1);
$_SERVER['SCRIPT_NAME']='/tests/section_pages_unit.php';$_SERVER['SCRIPT_FILENAME']=__FILE__;
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\{SectionPages as S,PublicSite,ContentSchema};
$count=0;
function verify(bool $ok,string $message):void{global $count;if(!$ok){throw new RuntimeException($message);}echo 'PASS '.$message."\n";$count++;}
verify(count(S::SECTIONS)===8,'Exactly eight existing designed page placements');
foreach(S::SECTIONS as $slug=>[$label,$path,$view,$width,$description]){
 verify(S::definition($slug)[0]===$label&&S::slugForView($view)===$slug,'Fixed section and view mapping: '.$slug);
 verify(PublicSite::url(['kind'=>'page','slug'=>$slug])==='/'.$path,'Section Page resolves to its real canonical route: '.$slug);
 verify(!str_contains($path,'://')&&!str_contains($path,'..'),'Placement cannot choose another host or filesystem path: '.$slug);
 verify($width>=1000&&$description!=='','Each placement has pixel guidance and a usage explanation: '.$slug);
}
foreach(['../config','https://example.test','Music','checkout','','home?publish=1'] as $slug){verify(S::definition($slug)===null,'Unknown placement is not accepted: '.$slug);}
verify(S::slugForView('product')===null&&S::slugForView('cart')===null,'Product and private cart views cannot load catalog Page data');
verify(PublicSite::url(['kind'=>'page','slug'=>'privacy'])==='/pages/?slug=privacy','Standalone policy/custom pages keep their route');
verify(S::text(null,'body','Fallback')==='Fallback','Absent Page leaves existing copy');
verify(S::text(['data'=>['subtitle'=>'   ']],'subtitle','Fallback')==='Fallback','Empty optional subtitle leaves a readable default');
verify(S::text(['data'=>['body'=>['invalid']]],'body','Fallback')==='Fallback','Malformed stored copy cannot become an array string');
verify(S::text(['data'=>['body'=>'<script>fixture</script>']],'body')==='<script>fixture</script>','Text helper preserves plain input for output escaping');
verify(str_contains(PublicSite::text(S::text(['data'=>['body'=>'<script>fixture</script>']],'body')),'&lt;script&gt;'),'Rendered section copy escapes active markup');
foreach(['center','top','left','right'] as $focal){verify(S::focal(['data'=>['focal'=>$focal]])===$focal,'Supported focal point is retained: '.$focal);}
verify(S::focal(['data'=>['focal'=>'x" onload="bad']])==='center','Invalid focus cannot inject a CSS/HTML value');
verify(S::focal(null)==='center','Missing focus defaults to center');
verify(S::artwork(null)===''&&S::artwork(['data'=>['image_id'=>0]])==='','No image produces no invented artwork or extra element');
verify(str_contains(S::resolution(['width'=>320,'height'=>200],1400),'Below the suggested'),'Reference-sized image is identified honestly');
verify(str_contains(S::resolution(['width'=>1600,'height'=>900],1400),'Still review'),'Adequate pixel width does not certify approval or quality');
verify(str_contains(S::resolution([],1400),'could not be read'),'Missing dimensions are not reported as passing');
verify(S::records('unmapped')===[],'Unknown placement cannot initiate a database query');
verify(ContentSchema::mediaIds('page',['image_id'=>7,'share_image_id'=>8])===[7=>'image',8=>'image'],'Existing typed publication media references remain sufficient');
echo $count." section-page assertions passed.\n";
