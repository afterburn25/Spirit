<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
$categories=Spirit\HelpCatalog::categories();
$errors=[];$seen=[];
foreach($categories as $slug=>$category){
 if(!preg_match('/^[a-z][a-z-]*$/D',(string)$slug)){$errors[]='Invalid topic: '.$slug;}
 if(count($category)!==3||!is_string($category[0])||!is_string($category[1])||!is_array($category[2])){$errors[]='Invalid topic schema: '.$slug;continue;}
 foreach($category[2] as $guide){
  if(count($guide)!==3||!is_string($guide[0])||!is_string($guide[1])||!is_string($guide[2])){$errors[]='Invalid guide schema: '.$slug;continue;}
  if(trim($guide[0])===''||trim($guide[1])===''){$errors[]='Empty title or steps in '.$slug;}
  $path=$guide[2];
  if(!preg_match('~^admin/[a-z0-9/_?.=&-]+$~D',$path)||str_contains($path,'..')){$errors[]='Unsafe guide destination: '.$path;continue;}
  $pathname=explode('?',substr($path,6),2)[0];
  if(str_ends_with($pathname,'/')){$pathname.='index.php';}
  if(!str_ends_with($pathname,'.php')){$errors[]='Not a PHP endpoint: '.$path;continue;}
  if(!is_file(dirname(__DIR__,2).'/admin/'.$pathname)){$errors[]='Missing help destination: '.$path;}
  $key=$slug.'|'.$guide[0];if(isset($seen[$key])){$errors[]='Duplicate article: '.$key;}$seen[$key]=true;
 }
}
if($errors){fwrite(STDERR,implode("\n",$errors)."\n");exit(1);}
echo "Help catalog checked: ".count($categories)." categories, ".count($seen)." guides.\n";
