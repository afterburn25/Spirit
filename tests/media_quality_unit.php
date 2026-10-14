<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\ImageVariants;
$count=0;
function check(bool $ok,string $label):void{global $count;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$count++;}
function rejects(callable $fn,string $label):void{try{$fn();}catch(InvalidArgumentException $e){check(true,$label);return;}check(false,$label);}
check(ImageVariants::requestWidth(null)===0,'Missing size retains original media');
foreach(ImageVariants::WIDTHS as $width){check(ImageVariants::requestWidth((string)$width)===$width,'Fixed responsive width accepted: '.$width);}
foreach(['0','321','-640','0640','640.0','640x480','../../secret','',640,[],['640'],true] as $bad){rejects(fn()=>ImageVariants::requestWidth($bad),'Unbounded or malformed image-size request rejected');}
check(ImageVariants::geometry(1800,1200,640)===[640,427],'Downsampling retains proportional dimensions');
check(ImageVariants::geometry(120,80,320)===[120,80],'Small originals are never upscaled');
check(ImageVariants::geometry(10000,1,320)===[320,1],'Very wide image retains at least one output pixel');
foreach([[0,10,320],[10,0,320],[10001,1,320],[5000,5000,320],[100,100,321],[-1,100,320]] as $args){rejects(fn()=>ImageVariants::geometry(...$args),'Invalid or excessive image geometry refused');}
check(ImageVariants::memoryFits(1800,1200,640,64*1024*1024),'Ordinary photograph fits a bounded decode budget');
check(!ImageVariants::memoryFits(5000,4000,1440,64*1024*1024),'Oversized GD allocation is not attempted in a small memory budget');
check(!ImageVariants::memoryFits(1800,1200,640,0),'Exhausted budget fails safely');
$name=ImageVariants::cacheName(str_repeat('a',64),640,'webp');
check(ImageVariants::disposableName($name),'Only the fixed derivative cache namespace is eligible for cleanup');
check(ImageVariants::disposableName('.image-v1-'.str_repeat('b',24).'.tmp'),'Recognized abandoned image temporary output may be removed');
foreach(['.image-variants.lock','config.php',str_repeat('a',32).'.png','image-v1-'.str_repeat('a',64).'-641.webp','../'.$name,'image-v2-'.str_repeat('a',64).'-640.webp',$name.'.php'] as $bad){check(!ImageVariants::disposableName($bad),'Original, lock, unknown or unsafe file is preserved');}
foreach([['../x',640,'webp'],[str_repeat('a',64),321,'webp'],[str_repeat('a',64),640,'php']] as $args){rejects(fn()=>ImageVariants::cacheName(...$args),'Invalid cache identity cannot construct a path');}
check(str_ends_with(Spirit\PublicSite::imageSizes('page-image focal-center'),'820px'),'Reading-page size hint matches its padded 900px layout');
check(Spirit\PublicSite::imageSizes('focal-left page-image')===Spirit\PublicSite::imageSizes('page-image'),'Focal-point classes do not disable responsive layout selection');
check(str_ends_with(Spirit\PublicSite::imageSizes('hero-art focal-top'),'65vw'),'Hero sizing remains separate from reading-page sizing');
echo $count." media-quality pure checks passed.\n";
