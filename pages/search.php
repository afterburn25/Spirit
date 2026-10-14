<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
Spirit\Storefront::boot();
try{$results=Spirit\Discovery::search($_GET);}catch(InvalidArgumentException $e){http_response_code(400);$results=['items'=>[],'total'=>0,'page'=>1,'pages'=>1,'q'=>'','kind'=>'all'];$searchError='Use a text search and valid page/filter values.';}
Spirit\PublicSite::render('search',null,['results'=>$results,'error'=>$searchError??null]);
