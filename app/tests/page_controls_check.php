<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit(1);}
require dirname(__DIR__,2).'/includes/bootstrap.php';

$errors=[];
$check=static function(bool $ok,string $message) use (&$errors):void {
    if(!$ok){$errors[]=$message;}
};
$fields=Spirit\ContentSchema::fields('page');
foreach(['headline','eyebrow','subtitle','body','image_id','button_label','button_url','slot_1','slot_2','slot_3','slot_4'] as $field){
    $check(isset($fields[$field]),'Missing editable page field: '.$field);
}
$memberFields=Spirit\ContentSchema::fields('member');
foreach(['role','bio','image_id','portrait_alt','website_url'] as $field){
    $check(isset($memberFields[$field]),'Missing member editor field: '.$field);
}
$member=Spirit\ContentSchema::validate('member',['role'=>'Guitar','portrait_alt'=>'Gold guitar on a stage','website_url'=>'https://member.example.test/'],true);
$check($member['portrait_alt']==='Gold guitar on a stage'&&$member['website_url']==='https://member.example.test/','Approved portrait description and HTTPS link survive validation.');
foreach(['javascript:alert(1)','http://member.example.test/'] as $invalidUrl){
    try{Spirit\ContentSchema::validate('member',['website_url'=>$invalidUrl],true);$errors[]='An unsafe member website URL was accepted.';}catch(InvalidArgumentException $expected){}
}
try{Spirit\ContentSchema::validate('member',['portrait_alt'=>str_repeat('x',301)],true);$errors[]='Oversized portrait alternative text was accepted.';}catch(InvalidArgumentException $expected){}
$check(Spirit\SectionPages::text(['data'=>['body'=>'']],'body','Approved fallback copy')==='Approved fallback copy','Empty published Page body retains its approved designed text.');
$check(Spirit\SectionPages::text(['data'=>['body'=>'Owner-authored story']],'body','Approved fallback copy')==='Owner-authored story','Explicitly authored Page text continues to override the fallback.');
$labels=Spirit\SectionPages::slotLabels('home');
$check(count($labels)===4,'Home section headings are not all editable.');
$check(Spirit\SectionPages::heading(['data'=>['slot_1'=>'<script>']], 'slot_1','<em>Default</em>')==='&lt;script&gt;','Section heading override must be HTML escaped.');
$check(Spirit\SectionPages::heading(null,'slot_1','<em>Default</em>')==='<em>Default</em>','Approved default heading typography must survive without an override.');
$published=Spirit\ContentSchema::validate('page',['slug'=>'music'],true);
$check($published['button_label']===''&&$published['button_url']==='','Blank overrides must preserve existing page controls.');
try {
    Spirit\ContentSchema::validate('page',['slug'=>'music','button_label'=>'Explore'],true);
    $errors[]='Publishing a partial custom CTA was accepted.';
} catch(InvalidArgumentException $expected){}
$complete=Spirit\ContentSchema::validate('page',['slug'=>'music','button_label'=>'Explore','button_url'=>'https://example.com/music'],true);
$check($complete['button_url']==='https://example.com/music','Complete custom CTA was not preserved.');
$home=Spirit\ContentSchema::validate('page',['slug'=>'home','button_label'=>'Listen now'],true);
$check($home['button_label']==='Listen now','Existing page CTA fallback should accept a custom label.');
try {
    Spirit\ContentSchema::validate('page',['slug'=>'music','button_label'=>'Explore','button_url'=>'javascript:alert(1)'],true);
    $errors[]='Unsafe page CTA URL was accepted.';
} catch(InvalidArgumentException $expected){}
$sample=['data'=>['button_label'=>'Watch <special>','button_url'=>'https://example.com/music']];
$html=Spirit\SectionPages::customButton($sample);
$check(str_contains($html,'https://example.com/music'),'Approved custom CTA URL missing.');
$check(str_contains($html,'&lt;special&gt;')&&!str_contains($html,'<special>'),'Custom CTA label was not escaped.');
$check(Spirit\SectionPages::customButton(['data'=>['button_label'=>'Unsafe','button_url'=>'javascript:alert(1)']])==='','Unsafe legacy CTA must not render.');
$public=file_get_contents(dirname(__DIR__,2).'/app/views/public/sections.php');
$shop=file_get_contents(dirname(__DIR__,2).'/app/views/public/store.php');
$lineup=Spirit\MemberOnboarding::confirmed();
$check(count($lineup)===3,'Expected three existing factual lineup names.');
$slugs=array_column($lineup,2);
$check(count($slugs)===count(array_unique($slugs)),'Default lineup has duplicate URL slugs.');
$check(is_string($public)&&str_contains($public,'MemberOnboarding::showLegacyFallback()'),'Preserved legacy lineup fallback is missing.');
$check(is_string($public)&&str_contains($public,'band-profile-list'),'Vertical band biography layout is missing.');
$check(is_string($public)&&!str_contains($public,'member-grid band-member-grid'),'Old band grid unexpectedly restored.');
$check(is_string($public)&&substr_count($public,'SectionPages::customButton($pageContent)')>=3,'Optional page CTAs are not connected to public section layouts.');
$check(is_string($shop)&&str_contains($shop,'SectionPages::customButton($pageContent)'),'Merchandise page CTA is not connected.');
if($errors){fwrite(STDERR,implode("\n",$errors)."\n");exit(1);}
echo "Page content, CTA and band layout checks passed.\n";
