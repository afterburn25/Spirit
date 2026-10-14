<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$user=Spirit\ContentAdmin::boot('content.manage');try{$id=Spirit\Input::integer($_GET,'id',1,PHP_INT_MAX,1);}catch(InvalidArgumentException $e){http_response_code(400);exit('Invalid draft selection.');}$item=Spirit\Content::get($id);if(!$item){http_response_code(404);exit('Draft unavailable.');}
$view=match($item['kind']){'release'=>'release','event'=>'event','member'=>'member','page'=>'page',default=>'page'};
if($view==='page'&&$item['kind']!=='page'){$item['data']=['subtitle'=>$item['kind'],'body'=>$item['data']['description']??$item['data']['caption']??'This is a saved private '.$item['kind'].' item. Publish its parent section to see it in context.','image_id'=>$item['data']['image_id']??0,'focal'=>'center'];}
Spirit\PublicSite::render($view,$item);
