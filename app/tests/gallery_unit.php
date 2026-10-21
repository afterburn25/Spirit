<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\{ProductGallery,PublicSite};
$checks=0;
function check(bool $ok,string $label): void {global $checks;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$checks++;}
foreach([null,'',[],['2'],new stdClass(),false,true,-1,0,'0','02','2.0','2e0','../2','9',999,'2<script>'] as $input){check(ProductGallery::selected($input,3)===0,'Invalid photograph selector uses the first approved image');}
foreach([1,2,3,'1','2','3'] as $input){check(ProductGallery::selected($input,3)===(int)$input-1,'Position chooses only the approved image array');}
check(ProductGallery::selected('8',8)===7,'Last permitted photograph can be selected');
check(ProductGallery::selected('3',2)===0,'Position outside this product cannot choose unrelated media');
check(ProductGallery::selected('2',0)===0,'Empty gallery does not invent an image');
check(ProductGallery::selected('8',100)===7&&ProductGallery::selected('9',100)===0,'URL selection retains eight-photo product limit');
check(PublicSite::imageSizes('product-thumb-image')==='72px','Thumbnail uses its actual small display size');
check(PublicSite::imageSizes('extra product-thumb-image')==='72px','Extra classes cannot expand a thumbnail to a large source');
check(str_contains(PublicSite::imageSizes('product-main-image'),'680px'),'Product main image has its own bounded responsive sizing');
check(str_contains(PublicSite::imageSizes('page-image'),'820px'),'Ordinary reading-page sizing is preserved');
echo $checks." gallery unit checks passed.\n";
