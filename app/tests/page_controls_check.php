<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit(1);}
require dirname(__DIR__,2).'/includes/bootstrap.php';

$errors=[];
$check=static function(bool $ok,string $message) use (&$errors):void {
    if(!$ok){$errors[]=$message;}
};
$fields=Spirit\ContentSchema::fields('page');
foreach(['headline','eyebrow','subtitle','body','image_id','button_label','button_url'] as $field){
    $check(isset($fields[$field]),'Missing editable page field: '.$field);
}
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
$check(is_string($public)&&str_contains($public,'band-profile-list'),'Vertical band biography layout is missing.');
$check(is_string($public)&&!str_contains($public,'member-grid band-member-grid'),'Old band grid unexpectedly restored.');
$check(is_string($public)&&substr_count($public,'SectionPages::customButton($pageContent)')>=3,'Optional page CTAs are not connected to public section layouts.');
$check(is_string($shop)&&str_contains($shop,'SectionPages::customButton($pageContent)'),'Merchandise page CTA is not connected.');
if($errors){fwrite(STDERR,implode("\n",$errors)."\n");exit(1);}
echo "Page content, CTA and band layout checks passed.\n";
